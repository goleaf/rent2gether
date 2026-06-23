<x-ui.page>
    <header class="space-y-1">
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="bell" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('notifications.settings.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('notifications.settings.helper') }}</flux:text>
    </header>

    <section class="space-y-4">
        <flux:card class="space-y-3">
            <flux:checkbox wire:model.change="inAppEnabled" label="{{ __('notifications.settings.in_app_enabled') }}" />
            <flux:checkbox wire:model.change="emailEnabled" label="{{ __('notifications.settings.email_enabled') }}" />
            <flux:checkbox wire:model.change="smsFutureEnabled" label="{{ __('notifications.settings.sms_future_enabled') }}" />
            <flux:checkbox wire:model.change="pushFutureEnabled" label="{{ __('notifications.settings.push_future_enabled') }}" />
        </flux:card>

        <flux:card class="space-y-3">
            <flux:checkbox wire:model.change="quietHoursEnabled" label="{{ __('notifications.settings.quiet_hours') }}" />
            <div class="grid grid-cols-2 gap-3">
                <flux:input wire:model.blur="quietHoursStart" label="{{ __('notifications.fields.quiet_hours_start') }}" icon="clock" />
                <flux:input wire:model.blur="quietHoursEnd" label="{{ __('notifications.fields.quiet_hours_end') }}" icon="clock" />
            </div>
        </flux:card>

        <flux:card class="space-y-3">
            <flux:select wire:model.change="digestType" label="{{ __('notifications.settings.digest') }}">
                @foreach(['none', 'daily', 'weekly', 'important_only'] as $type)
                    <flux:select.option value="{{ $type }}">{{ __('notifications.digest_types.'.$type) }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.change="languageLocale" label="{{ __('notifications.settings.language') }}">
                @foreach(['en', 'ru'] as $locale)
                    <flux:select.option value="{{ $locale }}">{{ __('notifications.locales.'.$locale) }}</flux:select.option>
                @endforeach
            </flux:select>
        </flux:card>

        <flux:button type="button" wire:click="save" variant="primary" class="w-full data-loading:opacity-70" icon="bell">
            {{ __('notifications.actions.save_settings') }}
        </flux:button>
    </section>
</x-ui.page>
