@if($notification?->safe_action_url)
    <flux:button href="{{ $notification->safe_action_url }}" wire:navigate variant="primary" icon="bell">
        {{ __('notifications.actions.'.($notification->action_type ?: 'open')) }}
    </flux:button>
@endif
