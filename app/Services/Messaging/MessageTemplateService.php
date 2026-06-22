<?php

namespace App\Services\Messaging;

use App\Models\Conversation;
use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Support\Collection;

class MessageTemplateService
{
    public function seedDefaultTemplates(): Collection
    {
        return collect($this->defaultTemplates())
            ->map(fn (array $template): MessageTemplate => MessageTemplate::query()->updateOrCreate([
                'template_key' => $template['template_key'],
            ], $template));
    }

    public function getAvailableForUser(User $user, Conversation $conversation): Collection
    {
        return MessageTemplate::query()
            ->where('active', true)
            ->where(function ($query) use ($conversation): void {
                $query->whereNull('conversation_type')
                    ->orWhere('conversation_type', $conversation->conversation_type);
            })
            ->when((int) $conversation->guest_user_id === (int) $user->id, fn ($query) => $query->where('visible_to_guest', true))
            ->when((int) $conversation->host_user_id === (int) $user->id, fn ($query) => $query->where('visible_to_host', true))
            ->orderBy('sort_order')
            ->orderBy('template_key')
            ->get();
    }

    public function getByKey(string $templateKey): ?MessageTemplate
    {
        return MessageTemplate::query()
            ->where('template_key', $templateKey)
            ->first();
    }

    public function canUseTemplate(User $user, Conversation $conversation, MessageTemplate $template): bool
    {
        return app(ConversationPrivacyService::class)->canUseTemplate($user, $conversation, $template);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function defaultTemplates(): array
    {
        $guestTemplates = [
            ['i_will_arrive_soon', 'check_in', 'none'],
            ['i_am_late', 'check_in', 'none'],
            ['where_are_keys', 'access', 'none'],
            ['cannot_find_address', 'check_in', 'open_check_in_problem'],
            ['check_in_problem', 'check_in', 'open_check_in_problem'],
            ['host_not_answering', 'check_in', 'open_host_unresponsive'],
            ['can_extend_stay', 'extension', 'open_extension'],
            ['can_late_checkout', 'check_out', 'open_checkout'],
            ['room_problem', 'complaint', 'open_complaint'],
            ['neighbors_noisy', 'complaint', 'open_complaint'],
            ['wifi_not_working', 'maintenance', 'open_maintenance'],
            ['no_hot_water', 'maintenance', 'open_maintenance'],
            ['need_towel', 'inventory', 'none'],
            ['need_bedding', 'inventory', 'none'],
            ['i_checked_out', 'check_out', 'open_checkout'],
            ['i_returned_keys', 'check_out', 'open_checkout'],
            ['forgot_item', 'check_out', 'none'],
        ];

        $hostTemplates = [
            ['booking_confirmed', 'booking', 'none'],
            ['thanks_for_request', 'polite_reply', 'none'],
            ['place_unavailable', 'booking', 'none'],
            ['check_in_instruction', 'access', 'none'],
            ['door_code', 'access', 'none'],
            ['where_bed_is', 'access', 'none'],
            ['house_rules', 'rules', 'none'],
            ['i_will_reply_soon', 'polite_reply', 'none'],
            ['self_check_in_available', 'check_in', 'none'],
            ['representative_will_help', 'check_in', 'none'],
            ['checkout_reminder', 'check_out', 'none'],
            ['return_keys', 'check_out', 'none'],
            ['clear_locker', 'check_out', 'none'],
            ['leave_review', 'booking', 'none'],
            ['deposit_will_be_checked', 'deposit', 'none'],
            ['deposit_returned', 'deposit', 'none'],
            ['need_details', 'polite_reply', 'none'],
        ];

        $templates = [];

        foreach ($guestTemplates as $index => [$key, $category, $action]) {
            $templates[] = $this->templateRow($key, $category, 'guest', $action, $index);
        }

        foreach ($hostTemplates as $index => [$key, $category, $action]) {
            $templates[] = $this->templateRow($key, $category, 'host', $action, $index + 100);
        }

        return $templates;
    }

    /**
     * @return array<string, mixed>
     */
    private function templateRow(string $key, string $category, string $senderType, string $action, int $sortOrder): array
    {
        return [
            'template_key' => $key,
            'template_category' => $category,
            'sender_type' => $senderType,
            'conversation_type' => null,
            'title_translation_key' => "messages.templates_{$senderType}.{$key}",
            'body_translation_key' => "messages.templates_{$senderType}.{$key}",
            'visible_to_guest' => $senderType === 'guest',
            'visible_to_host' => $senderType === 'host',
            'requires_booking' => true,
            'requires_check_in' => $category === 'check_in',
            'requires_check_out' => $category === 'check_out',
            'requires_active_stay' => in_array($category, ['stay', 'extension'], true),
            'creates_action' => $action !== 'none',
            'action_type' => $action,
            'sort_order' => $sortOrder,
            'active' => true,
        ];
    }
}
