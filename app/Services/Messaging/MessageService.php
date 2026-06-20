<?php

namespace App\Services\Messaging;

use App\Enums\BookingStatus;
use App\Enums\MessageThreadType;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Notification;
use App\Models\Property;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MessageService
{
    public function getOrCreateConversation(User $userA, User $userB, ?int $bookingId = null, ?int $bedId = null): Conversation
    {
        $ids = [min($userA->id, $userB->id), max($userA->id, $userB->id)];

        return Conversation::query()->firstOrCreate([
            'participant_one_id' => $ids[0],
            'participant_two_id' => $ids[1],
            'booking_id' => $bookingId,
        ], [
            'bed_id' => $bedId,
            'last_message_at' => now(),
        ]);
    }

    public function getOrCreateThread(
        User $guest,
        User $host,
        MessageThreadType|string $type = MessageThreadType::PreBooking,
        ?Booking $booking = null,
        ?Property $property = null,
        ?SleepingPlace $sleepingPlace = null,
    ): MessageThread {
        $threadType = $type instanceof MessageThreadType ? $type : MessageThreadType::from($type);
        $property ??= $booking?->property ?: $sleepingPlace?->property;
        $sleepingPlace ??= $booking?->sleepingPlace;

        return MessageThread::query()->firstOrCreate([
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'booking_id' => $booking?->id,
            'sleeping_place_id' => $sleepingPlace?->id,
        ], [
            'type' => $threadType,
            'property_id' => $property?->id,
            'last_message_at' => now(),
            'status' => 'open',
        ]);
    }

    /**
     * @param  list<array<string, mixed>>|bool  $attachments
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function send(Conversation|MessageThread $target, User $sender, string $body, array|bool $attachments = [], bool $isImportant = false, bool $systemMessage = false, ?string $locale = null): Message
    {
        if (is_bool($attachments)) {
            $isImportant = $attachments;
            $attachments = [];
        }

        $body = trim($body);

        if ($body === '' && $attachments === []) {
            throw ValidationException::withMessages([
                'body' => __('messages.errors.empty_message'),
            ]);
        }

        return $target instanceof MessageThread
            ? $this->sendToThread($target, $sender, $body, $attachments, $isImportant, $systemMessage, $locale)
            : $this->sendToConversation($target, $sender, $body, $attachments, $isImportant, $systemMessage, $locale);
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function sendSystemMessage(Conversation|MessageThread $target, string $body): Message
    {
        $sender = $target instanceof MessageThread
            ? $target->host
            : $target->participantOne;

        if (! $sender instanceof User) {
            throw ValidationException::withMessages([
                'body' => __('messages.errors.thread_unavailable'),
            ]);
        }

        return $this->send($target, $sender, $body, [], false, true);
    }

    public function markConversationRead(Conversation $conversation, User $reader): void
    {
        $conversation->messages()
            ->where(function (Builder $query) use ($reader): void {
                $query->where('recipient_user_id', $reader->id)
                    ->orWhere(function (Builder $query) use ($reader): void {
                        $query->whereNull('recipient_user_id')
                            ->where('sender_id', '!=', $reader->id);
                    });
            })
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markThreadRead(MessageThread $thread, User $reader): void
    {
        $this->ensureThreadParticipant($thread, $reader);

        $thread->messages()
            ->where(function (Builder $query) use ($reader): void {
                $query->where('recipient_user_id', $reader->id)
                    ->orWhere(function (Builder $query) use ($reader): void {
                        $query->whereNull('recipient_user_id')
                            ->where('sender_id', '!=', $reader->id);
                    });
            })
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * @param  list<array<string, mixed>>  $attachments
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    private function sendToThread(MessageThread $thread, User $sender, string $body, array $attachments, bool $isImportant, bool $systemMessage, ?string $locale): Message
    {
        return DB::transaction(function () use ($thread, $sender, $body, $attachments, $isImportant, $systemMessage, $locale): Message {
            $thread = $this->loadThread($thread, lock: true);
            $this->ensureThreadParticipant($thread, $sender);

            $recipient = $this->recipientForThread($thread, $sender);

            if (! $recipient instanceof User) {
                throw ValidationException::withMessages([
                    'body' => __('messages.errors.recipient_missing'),
                ]);
            }

            $this->ensureAddressMayBeShared($thread, $sender, $body);

            $conversation = $this->legacyConversationForThread($thread);

            $message = Message::query()->create([
                'conversation_id' => $conversation->id,
                'thread_id' => $thread->id,
                'sender_id' => $sender->id,
                'sender_user_id' => $sender->id,
                'recipient_user_id' => $recipient->id,
                'booking_id' => $thread->booking_id,
                'property_id' => $thread->property_id,
                'sleeping_place_id' => $thread->sleeping_place_id,
                'body' => $body,
                'attachments' => $attachments,
                'attachments_json' => $attachments,
                'is_important' => $isImportant,
                'important' => $isImportant,
                'is_system_message' => $systemMessage,
                'system_message' => $systemMessage,
                'locale' => $locale ?: app()->getLocale(),
            ]);

            $thread->update(['last_message_at' => now()]);
            $conversation->update(['last_message_at' => now()]);
            $this->notifyRecipient($thread, $message, $recipient);

            return $message;
        });
    }

    /**
     * @param  list<array<string, mixed>>  $attachments
     *
     * @throws AuthorizationException
     */
    private function sendToConversation(Conversation $conversation, User $sender, string $body, array $attachments, bool $isImportant, bool $systemMessage, ?string $locale): Message
    {
        if (! $conversation->hasParticipant($sender)) {
            throw new AuthorizationException(__('messages.errors.not_participant'));
        }

        $recipient = (int) $conversation->participant_one_id === (int) $sender->id
            ? $conversation->participantTwo
            : $conversation->participantOne;

        $message = Message::query()->create([
            'conversation_id' => $conversation->id,
            'thread_id' => null,
            'sender_id' => $sender->id,
            'sender_user_id' => $sender->id,
            'recipient_user_id' => $recipient?->id,
            'booking_id' => $conversation->booking_id,
            'body' => $body,
            'attachments' => $attachments,
            'attachments_json' => $attachments,
            'is_important' => $isImportant,
            'important' => $isImportant,
            'is_system_message' => $systemMessage,
            'system_message' => $systemMessage,
            'locale' => $locale ?: app()->getLocale(),
        ]);

        $conversation->update(['last_message_at' => now()]);

        return $message;
    }

    private function loadThread(MessageThread $thread, bool $lock = false): MessageThread
    {
        $query = MessageThread::query()
            ->with([
                'guest:id,name',
                'guest.setting:id,user_id,locale',
                'host:id,name',
                'host.setting:id,user_id,locale',
                'booking:id,status,payment_status,guest_user_id,host_user_id,property_id,sleeping_place_id',
                'property:id,host_user_id,user_id,address_line_1,address_line_2,house_number,apartment_number,show_exact_address_before_booking,show_exact_address_after_payment',
                'sleepingPlace:id,property_id,display_name',
            ]);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->findOrFail($thread->id);
    }

    /**
     * @throws AuthorizationException
     */
    private function ensureThreadParticipant(MessageThread $thread, User $user): void
    {
        if (! $thread->hasParticipant($user)) {
            throw new AuthorizationException(__('messages.errors.not_participant'));
        }
    }

    private function recipientForThread(MessageThread $thread, User $sender): ?User
    {
        return (int) $thread->guest_user_id === (int) $sender->id
            ? $thread->host
            : $thread->guest;
    }

    private function legacyConversationForThread(MessageThread $thread): Conversation
    {
        $guest = $thread->guest;
        $host = $thread->host;

        return $this->getOrCreateConversation($guest, $host, $thread->booking_id);
    }

    /**
     * @throws ValidationException
     */
    private function ensureAddressMayBeShared(MessageThread $thread, User $sender, string $body): void
    {
        if ((int) $thread->host_user_id !== (int) $sender->id) {
            return;
        }

        $property = $thread->property;

        if (! $property instanceof Property || $this->canExposeExactAddress($thread)) {
            return;
        }

        foreach ($this->addressFragments($property) as $fragment) {
            if (mb_stripos($body, $fragment) !== false) {
                throw ValidationException::withMessages([
                    'body' => __('messages.errors.address_hidden'),
                ]);
            }
        }
    }

    private function canExposeExactAddress(MessageThread $thread): bool
    {
        $property = $thread->property;

        if (! $property instanceof Property) {
            return true;
        }

        if ((bool) $property->show_exact_address_before_booking) {
            return true;
        }

        if (! (bool) $property->show_exact_address_after_payment || ! $thread->booking instanceof Booking) {
            return false;
        }

        $booking = $thread->booking;
        $status = $booking->status instanceof BookingStatus ? $booking->status->value : (string) $booking->status;
        $paymentStatus = $booking->payment_status instanceof PaymentStatus ? $booking->payment_status->value : (string) $booking->payment_status;

        return $paymentStatus === PaymentStatus::Paid->value
            || in_array($status, [
                BookingStatus::Confirmed->value,
                BookingStatus::Paid->value,
                BookingStatus::ReadyForCheckIn->value,
                BookingStatus::CheckedIn->value,
                BookingStatus::InProgress->value,
                BookingStatus::ActiveStay->value,
            ], true);
    }

    /**
     * @return list<string>
     */
    private function addressFragments(Property $property): array
    {
        return collect([
            $property->address_line_1,
            $property->address_line_2,
            $property->house_number,
            $property->apartment_number,
        ])
            ->filter(fn (?string $value): bool => is_string($value) && mb_strlen(trim($value)) >= 3)
            ->map(fn (string $value): string => trim($value))
            ->values()
            ->all();
    }

    private function notifyRecipient(MessageThread $thread, Message $message, User $recipient): void
    {
        Notification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => 'message_received',
            'notifiable_type' => User::class,
            'notifiable_id' => $recipient->id,
            'user_id' => $recipient->id,
            'data' => [
                'message_thread_id' => $thread->id,
                'message_id' => $message->id,
                'sender_user_id' => $message->sender_user_id,
            ],
            'title_key' => 'notifications.message_received.title',
            'body_key' => 'notifications.message_received.body',
            'action_url' => route('messages.show', [
                'locale' => $this->localeFor($recipient),
                'thread' => $thread,
            ]),
            'channel' => 'database',
            'status' => 'unread',
        ]);
    }

    private function localeFor(User $user): string
    {
        $locale = $user->setting?->locale ?: app()->getLocale();

        return in_array($locale, config('localization.supported_locales'), true)
            ? $locale
            : (string) config('app.fallback_locale', 'en');
    }
}
