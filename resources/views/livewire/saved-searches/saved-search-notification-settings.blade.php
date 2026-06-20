<div class="fixed inset-0 z-50">
    <flux:button type="button" variant="ghost" class="absolute inset-0 h-auto w-full rounded-none bg-zinc-950/50 p-0 hover:bg-zinc-950/50 dark:hover:bg-zinc-950/50" wire:click="$dispatch('saved-search-updated')" aria-label="{{ __('saved_searches.close') }}" />

    <section class="absolute inset-x-0 bottom-0 max-h-[88vh] overflow-y-auto rounded-t-xl bg-white p-4 shadow-2xl dark:bg-zinc-950 sm:bottom-4 sm:left-auto sm:right-4 sm:top-4 sm:w-full sm:max-w-md sm:rounded-xl">
        <div class="space-y-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">{{ __('saved_searches.notification_settings') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('saved_searches.notification_settings_helper') }}</flux:text>
                </div>
                <flux:button type="button" variant="ghost" size="sm" wire:click="$dispatch('saved-search-updated')">
                    {{ __('saved_searches.close') }}
                </flux:button>
            </div>

            <div class="space-y-3">
                <flux:checkbox wire:model.change="notifyNewMatches" label="{{ __('saved_searches.notify_new_matches') }}" />
                <flux:checkbox wire:model.change="notifyPriceDrops" label="{{ __('saved_searches.notify_price_drops') }}" />
                <flux:checkbox wire:model.change="notifyAvailableAgain" label="{{ __('saved_searches.notify_available_again') }}" />
                <flux:checkbox wire:model.change="notifyBetterMatch" label="{{ __('saved_searches.notify_better_match') }}" />
            </div>

            <flux:field>
                <flux:label>{{ __('saved_searches.notification_frequency') }}</flux:label>
                <flux:select wire:model.change="notificationFrequency">
                    @foreach(['on_visit', 'instant', 'daily', 'weekly', 'important_only'] as $frequency)
                        <flux:select.option value="{{ $frequency }}">{{ __('saved_searches.frequency.'.$frequency) }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <div class="space-y-3">
                <flux:checkbox wire:model.change="quietHoursEnabled" label="{{ __('saved_searches.quiet_hours_enabled') }}" />
                <div class="grid grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>{{ __('saved_searches.quiet_hours_start') }}</flux:label>
                        <flux:input type="time" wire:model.change="quietHoursStart" />
                        <flux:error name="quietHoursStart" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('saved_searches.quiet_hours_end') }}</flux:label>
                        <flux:input type="time" wire:model.change="quietHoursEnd" />
                        <flux:error name="quietHoursEnd" />
                    </flux:field>
                </div>
            </div>

            <div class="sticky bottom-0 -mx-4 border-t border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
                <flux:button type="button" variant="primary" class="w-full data-loading:opacity-70" wire:click="save">
                    <span wire:loading.remove wire:target="save">{{ __('saved_searches.save_changes') }}</span>
                    <span wire:loading wire:target="save">{{ __('saved_searches.saving') }}</span>
                </flux:button>
            </div>
        </div>
    </section>
</div>
