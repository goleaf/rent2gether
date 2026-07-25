<div class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 space-y-1">
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $roomTitle }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing_wizard.sleeping_places.auto_create_helper') }}</flux:text>
        </div>
        <div class="flex shrink-0 flex-col gap-2 sm:flex-row">
            <flux:button size="sm" type="button" wire:click="autoCreate" wire:loading.attr="disabled" wire:target="autoCreate" icon="sparkles">
                {{ __('listing_wizard.sleeping_places.auto_create') }}
            </flux:button>
            <flux:button size="sm" type="button" variant="ghost" wire:click="addPlace" wire:loading.attr="disabled" wire:target="addPlace" icon="plus">
                {{ __('listing_wizard.sleeping_places.add_place') }}
            </flux:button>
        </div>
    </div>

    @forelse($places as $index => $place)
        <flux:card class="space-y-4" wire:key="sleeping-place-editor-{{ $place['id'] }}">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 space-y-1">
                    <flux:heading size="md">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="rectangle-stack" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing_wizard.defaults.sleeping_place_name', ['number' => $place['place_number']]) }}</span>
                        </span>
                    </flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                        {{ $availabilityOptions[$place['availability']] ?? $place['availability'] }}
                    </flux:text>
                </div>
                <flux:badge size="sm" color="zinc" icon="check-circle">{{ $statusOptions[$place['status']] ?? $place['status'] }}</flux:badge>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="hashtag" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing_wizard.sleeping_places.place_number') }}</span>
                        </span>
                    </flux:label>
                    <flux:input wire:model.blur="places.{{ $index }}.place_number" maxlength="40" icon="hashtag" />
                    <flux:error name="places.{{ $index }}.place_number" />
                </flux:field>

                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="rectangle-stack" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing_wizard.sleeping_places.type') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model.change="places.{{ $index }}.type">
                        @foreach($typeOptions as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="places.{{ $index }}.type" />
                </flux:field>

                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing_wizard.sleeping_places.price') }}</span>
                        </span>
                    </flux:label>
                    <flux:input type="number" min="0.01" max="100000" step="0.01" wire:model.blur="places.{{ $index }}.base_price_per_night" icon="banknotes" />
                    <flux:error name="places.{{ $index }}.base_price_per_night" />
                </flux:field>

                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing_wizard.sleeping_places.availability') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model.change="places.{{ $index }}.availability">
                        @foreach($availabilityOptions as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="places.{{ $index }}.availability" />
                </flux:field>

                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="check-circle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing_wizard.sleeping_places.status') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model.change="places.{{ $index }}.status">
                        @foreach($statusOptions as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="places.{{ $index }}.status" />
                </flux:field>
            </div>

            <div class="space-y-2">
                <flux:text size="sm" class="font-medium">{{ __('listing_wizard.sleeping_places.features') }}</flux:text>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach($featureOptions as $value => $label)
                        <flux:field variant="inline" wire:key="place-{{ $place['id'] }}-feature-{{ $value }}">
                            <flux:checkbox wire:model.change="places.{{ $index }}.features" value="{{ $value }}" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ $label }}</span>
                                </span>
                            </flux:label>
                        </flux:field>
                    @endforeach
                </div>
                <flux:error name="places.{{ $index }}.features" />
            </div>

            <flux:accordion>
                <flux:accordion.item>
                    <flux:accordion.heading>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="photo" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">
                                {{ __('listing_wizard.sleeping_places.photos') }}
                                {{ __('listing_wizard.media_count', ['count' => $place['media_count']]) }}
                            </span>
                        </span>
                    </flux:accordion.heading>
                    <flux:accordion.content>
                    <livewire:media.manage-media
                        owner-type="sleeping_place"
                        :owner-id="$place['id']"
                        collection="exact_place"
                        :max-items="8"
                        :key="'sleeping-place-media-'.$place['id']"
                    />
                    </flux:accordion.content>
                </flux:accordion.item>
            </flux:accordion>

            <flux:button type="button" variant="primary" wire:click="savePlace({{ $index }})" wire:loading.attr="disabled" wire:target="savePlace" icon="bookmark" class="w-full sm:w-auto">
                <span wire:loading.remove wire:target="savePlace">{{ __('listing_wizard.save_draft') }}</span>
                <span wire:loading wire:target="savePlace">{{ __('listing_wizard.actions.saving') }}</span>
            </flux:button>
        </flux:card>
    @empty
        <flux:callout variant="secondary" icon="information-circle" :text="__('listing_wizard.sleeping_places.empty')" />
    @endforelse
</div>
