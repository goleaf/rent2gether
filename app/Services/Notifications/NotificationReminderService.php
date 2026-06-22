<?php

namespace App\Services\Notifications;

use App\Models\Booking;
use App\Models\Notification;
use App\Models\NotificationReminder;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class NotificationReminderService
{
    public function __construct(
        private readonly NotificationNumberService $numbers,
        private readonly NotificationTemplateService $templates,
        private readonly NotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function scheduleReminder(User $user, string $reminderType, CarbonInterface $scheduledFor, array $context = []): NotificationReminder
    {
        $booking = $context['booking'] ?? null;
        $template = $this->templates->getByKey($this->templateKeyForReminder($reminderType));

        return NotificationReminder::query()->create([
            'reminder_number' => $this->numbers->generateReminderNumber(),
            'user_id' => $user->id,
            'recipient_type' => $context['recipient_type'] ?? 'guest',
            'reminder_type' => $reminderType,
            'status' => 'scheduled',
            'priority' => $context['priority'] ?? $template?->default_priority ?? 'normal',
            'source_type' => $context['source_type'] ?? null,
            'source_id' => $context['source_id'] ?? null,
            'booking_id' => $booking instanceof Booking ? $booking->id : ($context['booking_id'] ?? null),
            'property_id' => $booking instanceof Booking ? $booking->property_id : ($context['property_id'] ?? null),
            'room_id' => $booking instanceof Booking ? $booking->room_id : ($context['room_id'] ?? null),
            'sleeping_place_id' => $booking instanceof Booking ? $booking->sleeping_place_id : ($context['sleeping_place_id'] ?? null),
            'notification_template_id' => $template?->id,
            'scheduled_for' => $scheduledFor,
            'due_at' => $scheduledFor->lessThanOrEqualTo(now()) ? now() : null,
            'translation_params_json' => $context['translation_params_json'] ?? null,
            'action_type' => $context['action_type'] ?? $template?->default_action_type,
            'action_url' => $context['action_url'] ?? null,
        ]);
    }

    public function cancelReminder(NotificationReminder $reminder, ?string $reason = null): NotificationReminder
    {
        $reminder->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return $reminder->refresh();
    }

    public function markDue(NotificationReminder $reminder): NotificationReminder
    {
        $reminder->update([
            'status' => 'due',
            'due_at' => now(),
        ]);

        return $reminder->refresh();
    }

    public function processReminder(NotificationReminder $reminder): ?Notification
    {
        if (! in_array($reminder->status, ['scheduled', 'due'], true)) {
            return null;
        }

        if ($reminder->scheduled_for->isFuture()) {
            return null;
        }

        $templateKey = $this->templateKeyForReminder($reminder->reminder_type);
        $notification = $this->notifications->createForUser($reminder->user, $templateKey, [
            'booking_id' => $reminder->booking_id,
            'property_id' => $reminder->property_id,
            'room_id' => $reminder->room_id,
            'sleeping_place_id' => $reminder->sleeping_place_id,
            'recipient_type' => $reminder->recipient_type,
            'priority' => $reminder->priority,
            'notification_type' => 'reminder',
            'translation_params_json' => $reminder->translation_params_json ?? [],
            'action_type' => $reminder->action_type,
            'action_url' => $reminder->action_url,
        ]);

        $reminder->update([
            'status' => 'processed',
            'due_at' => $reminder->due_at ?: now(),
            'processed_at' => now(),
        ]);

        return $notification;
    }

    public function expireReminder(NotificationReminder $reminder): NotificationReminder
    {
        $reminder->update([
            'status' => 'expired',
            'expired_at' => now(),
        ]);

        return $reminder->refresh();
    }

    /**
     * @return Collection<int, NotificationReminder>
     */
    public function getDueReminders(?User $user = null): Collection
    {
        return NotificationReminder::query()
            ->whereIn('status', ['scheduled', 'due'])
            ->where('scheduled_for', '<=', now())
            ->when($user, fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('scheduled_for')
            ->limit(100)
            ->get();
    }

    private function templateKeyForReminder(string $reminderType): string
    {
        return match ($reminderType) {
            'payment_deadline' => 'payment_required',
            'check_in_today' => 'check_in_today',
            'check_in_soon' => 'check_in_soon',
            'checkout_today' => 'checkout_today',
            'checkout_soon' => 'checkout_soon',
            'deposit_guest_response_due' => 'deposit_deduction_requested',
            'deposit_return_due' => 'deposit_review_started',
            'cleaning_due' => 'cleaning_due',
            'inspection_due' => 'inspection_due',
            'maintenance_due' => 'maintenance_reported',
            'saved_search_digest' => 'saved_search_new_results',
            'favorite_price_check' => 'favorite_price_dropped',
            'waitlist_offer_expires' => 'waitlist_place_available',
            default => 'booking_started',
        };
    }
}
