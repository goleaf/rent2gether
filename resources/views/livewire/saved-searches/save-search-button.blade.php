<div>
    <flux:button type="button" variant="primary" icon="bookmark" class="w-full sm:w-auto" wire:click="open">
        {{ __('saved_searches.save_search') }}
    </flux:button>

    @if($saved)
        <div class="mt-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200">
            {{ __('saved_searches.messages.saved') }}
        </div>
    @endif

    @if($openSheet)
        <div class="fixed inset-0 z-50">
            <button type="button" class="absolute inset-0 bg-zinc-950/50" wire:click="$set('openSheet', false)" aria-label="{{ __('saved_searches.close') }}"></button>

            <section class="absolute inset-x-0 bottom-0 max-h-[88vh] overflow-y-auto rounded-t-xl bg-white p-4 shadow-2xl dark:bg-zinc-950 sm:bottom-4 sm:left-auto sm:right-4 sm:top-4 sm:w-full sm:max-w-md sm:rounded-xl">
                <div class="space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <flux:heading size="lg">{{ __('saved_searches.create_title') }}</flux:heading>
                            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('saved_searches.create_helper') }}</flux:text>
                        </div>
                        <flux:button type="button" variant="ghost" size="sm" wire:click="$set('openSheet', false)">
                            {{ __('saved_searches.close') }}
                        </flux:button>
                    </div>

                    <flux:field>
                        <flux:label>{{ __('saved_searches.search_name') }}</flux:label>
                        <flux:input wire:model.blur="title" />
                        <flux:error name="title" />
                    </flux:field>

                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 text-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ __('saved_searches.summary.title') }}</div>
                        <div class="mt-2 space-y-1 text-zinc-600 dark:text-zinc-300">
                            @if($cityName !== '')
                                <div>{{ __('saved_searches.summary.city', ['city' => $cityName]) }}</div>
                            @endif
                            @if($checkIn !== '' && $checkOut !== '')
                                <div>{{ __('saved_searches.summary.dates', ['check_in' => $checkIn, 'check_out' => $checkOut]) }}</div>
                            @endif
                            @if($priceMax !== '')
                                <div>{{ __('saved_searches.summary.budget', ['amount' => $priceMax, 'currency' => $currency]) }}</div>
                            @endif
                            @if($instantBooking)
                                <div>{{ __('saved_searches.instant_booking_only') }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-3">
                        <flux:checkbox wire:model.change="notifyNewMatches" label="{{ __('saved_searches.notify_new_matches') }}" />
                        <flux:checkbox wire:model.change="notifyPriceDrops" label="{{ __('saved_searches.notify_price_drops') }}" />
                        <flux:checkbox wire:model.change="notifyAvailableAgain" label="{{ __('saved_searches.notify_available_again') }}" />

                        <flux:field>
                            <flux:label>{{ __('saved_searches.notification_frequency') }}</flux:label>
                            <flux:select wire:model.change="notificationFrequency">
                                @foreach(['on_visit', 'instant', 'daily', 'weekly', 'important_only'] as $frequency)
                                    <flux:select.option value="{{ $frequency }}">{{ __('saved_searches.frequency.'.$frequency) }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                    </div>

                    <div class="sticky bottom-0 -mx-4 border-t border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
                        <flux:button type="button" variant="primary" class="w-full data-loading:opacity-70" wire:click="save">
                            <span wire:loading.remove wire:target="save">{{ __('saved_searches.save_search') }}</span>
                            <span wire:loading wire:target="save">{{ __('saved_searches.saving') }}</span>
                        </flux:button>
                    </div>
                </div>
            </section>
        </div>
    @endif
</div>
