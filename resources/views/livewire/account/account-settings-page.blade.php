<x-ui.page>
    <section class="space-y-2">
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="cog-6-tooth" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('account.settings.heading') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('account.settings.helper') }}</flux:text>
    </section>

    @if(session('success'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ session('success') }}</flux:callout.text>
        </flux:callout>
    @endif

    <form wire:submit="save" class="space-y-5">
        <flux:card class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('account.settings.locale') }}</flux:label>
                    <flux:select wire:model.change="locale">
                        @foreach(config('localization.supported_locales') as $supportedLocale)
                            <flux:select.option value="{{ $supportedLocale }}">
                                {{ __('navigation.languages.'.$supportedLocale) }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="locale" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('account.settings.currency') }}</flux:label>
                    <flux:select wire:model.change="currency">
                        <flux:select.option value="EUR">EUR</flux:select.option>
                        <flux:select.option value="USD">USD</flux:select.option>
                    </flux:select>
                    <flux:error name="currency" />
                </flux:field>
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="bell" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('account.settings.notifications') }}</span>
                </span>
            </flux:heading>
            <div class="grid gap-3">
                <flux:checkbox wire:model.change="emailMessages" label="{{ __('account.settings.email_messages') }}" />
                <flux:checkbox wire:model.change="emailBookings" label="{{ __('account.settings.email_bookings') }}" />
                <flux:checkbox wire:model.change="productUpdates" label="{{ __('account.settings.product_updates') }}" />
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('account.settings.privacy') }}</span>
                </span>
            </flux:heading>
            <div class="grid gap-3">
                <flux:checkbox wire:model.change="showProfile" label="{{ __('account.settings.show_profile') }}" />
                <flux:checkbox wire:model.change="showLanguages" label="{{ __('account.settings.show_languages') }}" />
                <flux:checkbox wire:model.change="showReviews" label="{{ __('account.settings.show_reviews') }}" />
            </div>
        </flux:card>

        <div class="sticky bottom-20 z-10 rounded-xl border border-zinc-200 bg-white/95 p-3 shadow-lg backdrop-blur dark:border-zinc-700 dark:bg-zinc-950/95 lg:static lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
            <flux:button type="submit" variant="primary" class="w-full data-loading:opacity-70" icon="check">
                <span wire:loading.remove wire:target="save">{{ __('account.settings.save') }}</span>
                <span wire:loading wire:target="save">{{ __('account.actions.saving') }}</span>
            </flux:button>
        </div>
    </form>
</x-ui.page>
