<form wire:submit="save" class="space-y-4">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="language" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('profiles.sections.languages') }}</span>
        </span>
    </flux:heading>

        <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('profiles.fields.language_code') }}</span>
            </span>
        </flux:label>
        <flux:input wire:model.blur="languageCode" icon="language" />
        <flux:error name="languageCode" />
    </flux:field>
        <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('profiles.fields.language_level') }}</span>
            </span>
        </flux:label>
        <flux:select wire:model.change="level">
        <flux:select.option value="native">{{ __('profiles.language_levels.native') }}</flux:select.option>
        <flux:select.option value="fluent">{{ __('profiles.language_levels.fluent') }}</flux:select.option>
        <flux:select.option value="intermediate">{{ __('profiles.language_levels.intermediate') }}</flux:select.option>
        <flux:select.option value="basic">{{ __('profiles.language_levels.basic') }}</flux:select.option>
    </flux:select>
        <flux:error name="level" />
    </flux:field>
        <flux:field variant="inline">
        <flux:checkbox wire:model.change="isPrimary" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('profiles.fields.is_primary_language') }}</span>
            </span>
        </flux:label>
        <flux:error name="isPrimary" />
    </flux:field>

    <flux:button type="submit" variant="primary" class="w-full" wire:loading.class="opacity-50" icon="language">
        {{ __('common.actions.save') }}
    </flux:button>
</form>
