<?php

namespace App\Services\Notifications;

use App\Models\NotificationTemplate;
use Illuminate\Support\Collection;

class NotificationTemplateService
{
    /**
     * @return Collection<int, NotificationTemplate>
     */
    public function seedDefaultTemplates(): Collection
    {
        return collect($this->defaults())
            ->map(fn (array $data, string $key): NotificationTemplate => NotificationTemplate::query()->updateOrCreate(
                ['template_key' => $key],
                $data + [
                    'title_translation_key' => 'notifications.events.'.$key,
                    'body_translation_key' => 'notifications.events.'.$key,
                    'short_body_translation_key' => null,
                    'supports_in_app' => true,
                    'supports_email' => in_array($data['notification_category'], ['booking', 'payment', 'deposit', 'security'], true),
                    'supports_sms_future' => false,
                    'supports_push_future' => false,
                    'supports_conversation_event' => in_array($data['notification_category'], ['booking', 'payment', 'check_in', 'check_out', 'deposit', 'complaint', 'dispute', 'maintenance', 'inventory', 'message'], true),
                    'requires_booking' => in_array($data['notification_category'], ['booking', 'payment', 'check_in', 'check_out', 'deposit', 'message'], true),
                    'requires_action' => in_array($data['default_priority'], ['high', 'urgent', 'critical'], true),
                    'active' => true,
                ],
            ))
            ->values();
    }

    public function getByKey(string $templateKey): ?NotificationTemplate
    {
        return NotificationTemplate::query()
            ->where('template_key', $templateKey)
            ->where('active', true)
            ->first();
    }

    /**
     * @return Collection<int, NotificationTemplate>
     */
    public function getForCategory(string $category): Collection
    {
        return NotificationTemplate::query()
            ->where('notification_category', $category)
            ->where('active', true)
            ->orderBy('template_key')
            ->get();
    }

    public function isCritical(string $templateKey): bool
    {
        return (bool) $this->getByKey($templateKey)?->is_critical;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function defaults(): array
    {
        return [
            'booking_started' => ['notification_category' => 'booking', 'default_priority' => 'normal', 'default_action_type' => 'open_booking', 'is_critical' => false],
            'payment_required' => ['notification_category' => 'payment', 'default_priority' => 'high', 'default_action_type' => 'open_payment', 'is_critical' => false],
            'booking_confirmed' => ['notification_category' => 'booking', 'default_priority' => 'normal', 'default_action_type' => 'open_booking', 'is_critical' => false],
            'booking_rejected' => ['notification_category' => 'booking', 'default_priority' => 'normal', 'default_action_type' => 'open_booking', 'is_critical' => false],
            'host_sent_message' => ['notification_category' => 'message', 'default_priority' => 'normal', 'default_action_type' => 'open_conversation', 'is_critical' => false],
            'guest_sent_message' => ['notification_category' => 'message', 'default_priority' => 'normal', 'default_action_type' => 'open_conversation', 'is_critical' => false],
            'check_in_soon' => ['notification_category' => 'check_in', 'default_priority' => 'high', 'default_action_type' => 'open_check_in', 'is_critical' => false],
            'check_in_today' => ['notification_category' => 'check_in', 'default_priority' => 'urgent', 'default_action_type' => 'open_check_in', 'is_critical' => false],
            'check_in_instruction_available' => ['notification_category' => 'check_in', 'default_priority' => 'high', 'default_action_type' => 'open_check_in', 'is_critical' => false],
            'guest_arrived' => ['notification_category' => 'check_in', 'default_priority' => 'urgent', 'default_action_type' => 'open_check_in', 'is_critical' => false],
            'guest_confirmed_check_in' => ['notification_category' => 'check_in', 'default_priority' => 'high', 'default_action_type' => 'open_check_in', 'is_critical' => false],
            'host_confirmed_check_in' => ['notification_category' => 'check_in', 'default_priority' => 'high', 'default_action_type' => 'open_check_in', 'is_critical' => false],
            'check_in_problem' => ['notification_category' => 'check_in', 'default_priority' => 'critical', 'default_action_type' => 'open_check_in', 'is_critical' => true],
            'host_unresponsive_reported' => ['notification_category' => 'host_unresponsive', 'default_priority' => 'critical', 'default_action_type' => 'open_host_unresponsive', 'is_critical' => true],
            'checkout_soon' => ['notification_category' => 'check_out', 'default_priority' => 'high', 'default_action_type' => 'open_check_out', 'is_critical' => false],
            'checkout_today' => ['notification_category' => 'check_out', 'default_priority' => 'urgent', 'default_action_type' => 'open_check_out', 'is_critical' => false],
            'guest_checked_out' => ['notification_category' => 'check_out', 'default_priority' => 'high', 'default_action_type' => 'open_check_out', 'is_critical' => false],
            'extension_requested' => ['notification_category' => 'extension', 'default_priority' => 'high', 'default_action_type' => 'open_extension', 'is_critical' => false],
            'relocation_requested' => ['notification_category' => 'relocation', 'default_priority' => 'high', 'default_action_type' => 'open_relocation', 'is_critical' => false],
            'cancellation_created' => ['notification_category' => 'cancellation', 'default_priority' => 'high', 'default_action_type' => 'open_cancellation', 'is_critical' => false],
            'no_show_reported' => ['notification_category' => 'no_show', 'default_priority' => 'urgent', 'default_action_type' => 'open_no_show', 'is_critical' => false],
            'deposit_review_started' => ['notification_category' => 'deposit', 'default_priority' => 'normal', 'default_action_type' => 'open_deposit', 'is_critical' => false],
            'deposit_deduction_requested' => ['notification_category' => 'deposit', 'default_priority' => 'high', 'default_action_type' => 'open_deposit', 'is_critical' => true],
            'deposit_returned' => ['notification_category' => 'deposit', 'default_priority' => 'normal', 'default_action_type' => 'open_deposit', 'is_critical' => false],
            'complaint_opened' => ['notification_category' => 'complaint', 'default_priority' => 'high', 'default_action_type' => 'open_complaint', 'is_critical' => false],
            'dispute_opened' => ['notification_category' => 'dispute', 'default_priority' => 'high', 'default_action_type' => 'open_dispute', 'is_critical' => true],
            'maintenance_reported' => ['notification_category' => 'maintenance', 'default_priority' => 'high', 'default_action_type' => 'open_maintenance', 'is_critical' => false],
            'cleaning_due' => ['notification_category' => 'cleaning', 'default_priority' => 'high', 'default_action_type' => 'open_cleaning', 'is_critical' => false],
            'inspection_due' => ['notification_category' => 'inspection', 'default_priority' => 'high', 'default_action_type' => 'open_inspection', 'is_critical' => false],
            'place_ready' => ['notification_category' => 'readiness', 'default_priority' => 'normal', 'default_action_type' => 'open_readiness', 'is_critical' => false],
            'favorite_available' => ['notification_category' => 'favorite', 'default_priority' => 'normal', 'default_action_type' => 'open_favorite', 'is_critical' => false],
            'favorite_price_dropped' => ['notification_category' => 'favorite', 'default_priority' => 'normal', 'default_action_type' => 'open_favorite', 'is_critical' => false],
            'saved_search_new_results' => ['notification_category' => 'saved_search', 'default_priority' => 'low', 'default_action_type' => 'open_saved_search', 'is_critical' => false],
            'waitlist_place_available' => ['notification_category' => 'waitlist', 'default_priority' => 'high', 'default_action_type' => 'open_waitlist', 'is_critical' => false],
        ];
    }
}
