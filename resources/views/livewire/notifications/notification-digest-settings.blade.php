<flux:select wire:model.change="digestType" label="{{ __('notifications.settings.digest') }}">
    @foreach(['none', 'daily', 'weekly', 'important_only'] as $type)
        <flux:select.option value="{{ $type }}">{{ __('notifications.digest_types.'.$type) }}</flux:select.option>
    @endforeach
</flux:select>
