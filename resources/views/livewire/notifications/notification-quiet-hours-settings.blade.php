<div class="space-y-3">
    <flux:checkbox wire:model.change="enabled" label="{{ __('notifications.settings.quiet_hours') }}" />
    <div class="grid grid-cols-2 gap-3">
        <flux:input wire:model.blur="start" label="{{ __('notifications.fields.quiet_hours_start') }}" />
        <flux:input wire:model.blur="end" label="{{ __('notifications.fields.quiet_hours_end') }}" />
    </div>
</div>
