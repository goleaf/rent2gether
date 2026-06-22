<?php

namespace App\Services\Messaging;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\ConversationSafetyWarning;
use App\Models\User;
use Illuminate\Support\Collection;

class ConversationSafetyService
{
    public function checkMessageBeforeSend(User $sender, Conversation $conversation, string $body): Collection
    {
        $warnings = collect();

        if ($this->detectPossibleOffPlatformPayment($body)) {
            $warnings->push($this->createWarning($conversation, null, 'possible_off_platform_payment', [
                'triggered_by_user_id' => $sender->id,
                'message_key' => 'messages.messages.off_platform_payment_warning',
            ]));
        }

        if ($this->detectPossibleAccessDetails($body)) {
            $warnings->push($this->createWarning($conversation, null, 'possible_sensitive_access_details', [
                'triggered_by_user_id' => $sender->id,
                'message_key' => 'messages.messages.access_details_warning',
            ]));
        }

        if ($this->detectPossibleExactAddressBeforeAllowed($conversation, $body)) {
            $warnings->push($this->createWarning($conversation, null, 'possible_exact_address_before_allowed', [
                'triggered_by_user_id' => $sender->id,
                'message_key' => 'messages.messages.access_details_warning',
            ]));
        }

        return $warnings;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function createWarning(Conversation $conversation, ?ConversationMessage $message, string $warningKey, array $context = []): ConversationSafetyWarning
    {
        $warning = ConversationSafetyWarning::query()->create([
            'conversation_id' => $conversation->id,
            'conversation_message_id' => $message?->id,
            'warning_key' => $warningKey,
            'severity' => $context['severity'] ?? 'warning',
            'triggered_by_user_id' => $context['triggered_by_user_id'] ?? null,
            'visible_to_sender' => $context['visible_to_sender'] ?? true,
            'visible_to_recipient' => $context['visible_to_recipient'] ?? false,
            'message_key' => $context['message_key'] ?? null,
            'context_json' => $context,
        ]);

        app(ConversationEventService::class)->record($conversation, 'safety_warning_created', [
            'warning_key' => $warningKey,
            'warning_id' => $warning->id,
            'user_id' => $warning->triggered_by_user_id,
        ]);

        return $warning;
    }

    public function detectPossibleOffPlatformPayment(string $body): bool
    {
        $normalized = mb_strtolower($body);

        foreach (['direct bank transfer', 'bank transfer', 'cash outside', 'pay outside', 'card number', 'переведи напрямую', 'оплата наличными', 'номер карты', 'банковский перевод'] as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    public function detectPossibleAccessDetails(string $body): bool
    {
        $normalized = mb_strtolower($body);

        if (preg_match('/\b(code|door code|код|пароль)\b/u', $normalized) === 1 && preg_match('/\d{3,8}/', $normalized) === 1) {
            return true;
        }

        return false;
    }

    public function detectPossibleExactAddressBeforeAllowed(Conversation $conversation, string $body): bool
    {
        $conversation->loadMissing('property');
        $property = $conversation->property;

        if (! $property || $conversation->booking_id !== null) {
            return false;
        }

        $fragments = collect([
            $property->address_line_1 ?? null,
            $property->address_line_2 ?? null,
            $property->house_number ?? null,
            $property->apartment_number ?? null,
        ])->filter(fn (?string $value): bool => is_string($value) && mb_strlen(trim($value)) >= 3);

        return $fragments->contains(fn (string $fragment): bool => mb_stripos($body, $fragment) !== false);
    }
}
