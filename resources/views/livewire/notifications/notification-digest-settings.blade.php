<flux:field>
    <flux:label>
        <span class="inline-flex min-w-0 items-center gap-1.5">
            <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('notifications.settings.digest') }}</span>
        </span>
    </flux:label>
    <flux:select wire:model.change="digestType">
    @foreach(['none', 'daily', 'weekly', 'important_only'] as $type)
        <flux:select.option value="{{ $type }}">{{ __('notifications.digest_types.'.$type) }}</flux:select.option>
    @endforeach
</flux:select>
    <flux:error name="digestType" />
</flux:field>
