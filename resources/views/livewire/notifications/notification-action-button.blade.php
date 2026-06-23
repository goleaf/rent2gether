@if($notification?->action_url)
    <flux:button href="{{ $notification->action_url }}" wire:navigate variant="primary" icon="bell">
        {{ __('notifications.actions.'.($notification->action_type ?: 'open')) }}
    </flux:button>
@endif
