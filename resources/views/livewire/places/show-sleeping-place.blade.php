<div class="mx-auto max-w-6xl space-y-5 px-4 py-4 pb-24 sm:px-6 lg:py-6">
    <flux:button
        variant="ghost"
        size="sm"
        icon="arrow-left"
        href="{{ route('search.index', ['locale' => app()->getLocale()]) }}"
        wire:navigate
    >
        {{ __('listing.detail.actions.back_to_search') }}
    </flux:button>

    <section aria-labelledby="place-gallery-title" class="space-y-3">
        <flux:heading id="place-gallery-title" size="lg">{{ __('listing.detail.gallery.title') }}</flux:heading>

        @if($primaryImage)
            <div class="overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-900">
                <img
                    src="{{ $primaryImage['url'] }}"
                    alt="{{ $primaryImage['alt'] }}"
                    width="960"
                    height="640"
                    decoding="async"
                    class="aspect-[4/3] w-full object-cover sm:aspect-[16/10]"
                />
            </div>

            @if(count($gallery) > 1)
                <div class="flex gap-2 overflow-x-auto pb-1">
                    @forelse(array_slice($gallery, 1) as $image)
                        <img
                            src="{{ $image['thumb_url'] }}"
                            alt="{{ $image['alt'] }}"
                            width="112"
                            height="84"
                            loading="lazy"
                            decoding="async"
                            class="h-20 w-28 shrink-0 rounded-lg bg-zinc-100 object-cover dark:bg-zinc-900"
                        />
                    @empty
                    @endforelse
                </div>
            @endif
        @else
            <div class="flex aspect-[4/3] w-full items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-900">
                <div class="space-y-2 text-center text-zinc-500">
                    <flux:icon name="photo" class="mx-auto size-10 text-zinc-300 dark:text-zinc-700" />
                    <flux:text>{{ __('listing.detail.gallery.empty') }}</flux:text>
                </div>
            </div>
        @endif
    </section>

    <section class="space-y-3" aria-labelledby="listing-title">
        <div class="space-y-2">
            <flux:heading id="listing-title" size="xl" level="1">{{ $title }}</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('listing.detail.helper') }}</flux:text>
        </div>

        <flux:card class="space-y-4">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('listing.detail.summary.title') }}</flux:heading>
                <div class="flex flex-wrap gap-2">
                    <flux:badge>{{ $summary['property_type'] }}</flux:badge>
                    <flux:badge>{{ $summary['room_type'] }}</flux:badge>
                    <flux:badge>{{ $summary['sleeping_place_type'] }}</flux:badge>
                </div>
            </div>

            <div class="grid gap-3 text-sm sm:grid-cols-2">
                <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                    <div class="text-zinc-500">{{ __('listing.detail.summary.location') }}</div>
                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $summary['location'] }}</div>
                </div>
                <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                    <div class="text-zinc-500">{{ __('listing.detail.summary.rating') }}</div>
                    <div class="font-medium text-zinc-900 dark:text-zinc-100">
                        @if($summary['rating'])
                            {{ __('listing.detail.summary.rating_value', ['rating' => $summary['rating']]) }}
                            <span aria-hidden="true">.</span>
                            {{ trans_choice('listing.detail.summary.reviews_count', $summary['reviews_count'], ['count' => $summary['reviews_count']]) }}
                        @else
                            {{ __('listing.detail.summary.no_reviews') }}
                        @endif
                    </div>
                </div>
            </div>
        </flux:card>

        <livewire:hints.listing-detail-hints
            :sleeping-place-id="$place->id"
            :check-in="$checkIn"
            :check-out="$checkOut"
            :key="'detail-hints-'.$place->id.'-'.$checkIn.'-'.$checkOut"
            lazy
        />

        <flux:card class="space-y-3">
            <div>
                <flux:heading size="lg">{{ __('listing.detail.flow.title') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing.detail.flow.helper') }}</flux:text>
            </div>
            <div class="grid gap-2 sm:grid-cols-3">
                @forelse($decisionFlow as $step)
                    <div class="min-h-16 rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                        <div class="text-xs text-zinc-500">{{ $step['label'] }}</div>
                        <div class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $step['value'] }}</div>
                    </div>
                @empty
                    <flux:text class="text-zinc-500">{{ __('listing.detail.summary.no_reviews') }}</flux:text>
                @endforelse
            </div>
        </flux:card>
    </section>

    <section class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start">
        <aside class="space-y-4 lg:sticky lg:top-4 lg:order-2">
            <flux:card class="space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <flux:heading size="lg">{{ __('listing.detail.booking.title') }}</flux:heading>
                        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing.detail.booking.helper') }}</flux:text>
                    </div>
                    <flux:button
                        type="button"
                        variant="ghost"
                        icon="heart"
                        wire:click="toggleFavorite"
                        wire:loading.attr="disabled"
                        wire:target="toggleFavorite"
                        aria-label="{{ $isFavorited ? __('listing.detail.favorite.remove') : __('listing.detail.favorite.add') }}"
                    />
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                    <flux:field>
                        <flux:label>{{ __('listing.detail.booking.check_in') }}</flux:label>
                        <flux:input type="date" min="{{ now()->toDateString() }}" wire:model.change="checkIn" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('listing.detail.booking.check_out') }}</flux:label>
                        <flux:input type="date" min="{{ $checkIn ?: now()->toDateString() }}" wire:model.change="checkOut" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>{{ __('listing.detail.booking.guests') }}</flux:label>
                    <flux:input type="number" min="1" inputmode="numeric" wire:model.change="guestsCount" />
                </flux:field>

                <div wire:loading.delay wire:target="checkIn,checkOut,guestsCount,refreshQuote" class="rounded-lg border border-zinc-200 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                    {{ __('listing.detail.booking.updating') }}
                </div>

                @if($availabilityWarning)
                    <flux:callout color="amber" icon="exclamation-triangle">
                        <flux:callout.heading>{{ __('listing.detail.booking.warning_heading') }}</flux:callout.heading>
                        <flux:callout.text>{{ $availabilityWarning }}</flux:callout.text>
                    </flux:callout>

                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-500/30 dark:bg-amber-500/10">
                        <div class="space-y-2">
                            <flux:text size="sm" class="text-amber-900 dark:text-amber-100">{{ __('waitlist.messages.detail_unavailable') }}</flux:text>
                            <livewire:waitlist.join-waitlist-button
                                :sleeping-place-id="$place->id"
                                :check-in="$checkIn"
                                :check-out="$checkOut"
                                :guests-count="$guestsCount"
                                source="listing_detail"
                                :key="'detail-waitlist-'.$place->id.'-'.$checkIn.'-'.$checkOut.'-'.$guestsCount"
                            />
                        </div>
                    </div>

                    @if($unavailableDates)
                        <div class="flex flex-wrap gap-2">
                            @forelse($unavailableDates as $date)
                                <flux:badge size="sm">{{ \Carbon\CarbonImmutable::parse($date)->translatedFormat('d M') }}</flux:badge>
                            @empty
                            @endforelse
                        </div>
                    @endif
                @endif

                <div data-detail-section="price-breakdown" class="space-y-3 rounded-lg bg-zinc-50 px-3 py-3 text-sm dark:bg-zinc-900">
                    <div>
                        <flux:heading size="sm">{{ __('listing.detail.booking.price_breakdown_title') }}</flux:heading>
                        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                            {{ $priceBreakdown['has_quote'] ? __('listing.detail.booking.price_breakdown_helper') : $priceBreakdown['summary'] }}
                        </flux:text>
                    </div>

                    @if($priceBreakdown['date_prices'])
                        <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @forelse($priceBreakdown['date_prices'] as $datePrice)
                                <div class="flex items-center justify-between gap-3 py-2">
                                    <div>
                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $datePrice['label'] }}</div>
                                        <div class="text-xs text-zinc-500">{{ $datePrice['weekday'] }}. {{ $datePrice['source'] }}</div>
                                    </div>
                                    <div class="font-medium">{{ $datePrice['amount'] }}</div>
                                </div>
                            @empty
                            @endforelse
                        </div>

                        @if($priceBreakdown['remaining_dates_count'] > 0)
                            <flux:text size="sm" class="text-zinc-500">
                                {{ trans_choice('listing.detail.booking.more_date_prices', $priceBreakdown['remaining_dates_count'], ['count' => $priceBreakdown['remaining_dates_count']]) }}
                            </flux:text>
                        @endif
                    @endif

                    <div class="space-y-2">
                        @forelse($priceBreakdown['lines'] as $line)
                            <div class="flex justify-between gap-3">
                                <span>{{ $line['label'] }}</span>
                                <span class="font-medium">{{ $line['amount'] }}</span>
                            </div>
                        @empty
                            <div class="text-zinc-500">{{ __('listing.detail.booking.choose_dates') }}</div>
                        @endforelse
                    </div>

                    @if($priceBreakdown['total'])
                        <div class="border-t border-zinc-200 pt-3 dark:border-zinc-800">
                            <div class="flex justify-between gap-3 text-base">
                                <span class="font-medium">{{ __('listing.detail.booking.total') }}</span>
                                <span class="font-semibold">{{ $priceBreakdown['total'] }}</span>
                            </div>
                            <div class="mt-2 space-y-1 text-xs text-zinc-600 dark:text-zinc-400">
                                <p>{{ __('listing.detail.booking.refundable_note', ['amount' => $priceBreakdown['refundable']]) }}</p>
                                <p>{{ __('listing.detail.booking.non_refundable_note', ['amount' => $priceBreakdown['non_refundable']]) }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <flux:button
                    variant="primary"
                    class="w-full"
                    href="{{ route('places.book', array_filter([
                        'locale' => app()->getLocale(),
                        'sleepingPlace' => $place,
                        'in' => $checkIn ?: null,
                        'out' => $checkOut ?: null,
                        'guests' => $guestsCount ?: null,
                    ])) }}"
                    wire:navigate
                >
                    {{ $place->instant_booking_enabled ? __('listing.detail.booking.instant_action') : __('listing.detail.booking.request_action') }}
                </flux:button>

                <flux:button type="button" variant="ghost" class="w-full data-loading:opacity-70" wire:click="openContact" wire:loading.attr="disabled" wire:target="openContact">
                    {{ __('listing.detail.contact.open') }}
                </flux:button>
            </flux:card>

            <livewire:listings.detail.compatibility-summary-section
                :sleeping-place-id="$place->id"
                :check-in="$checkIn"
                :check-out="$checkOut"
                :key="'detail-compatibility-summary-'.$place->id.'-'.$checkIn.'-'.$checkOut"
                lazy
            />

            <livewire:listings.detail.compatibility-details-sheet
                :sleeping-place-id="$place->id"
                :check-in="$checkIn"
                :check-out="$checkOut"
                :key="'detail-compatibility-details-'.$place->id.'-'.$checkIn.'-'.$checkOut"
                lazy
            />

            @if(session('listing-contact-status'))
                <flux:callout color="green" icon="check-circle">
                    <flux:callout.text>{{ session('listing-contact-status') }}</flux:callout.text>
                </flux:callout>
            @endif

            @if($contactOpen)
                <flux:card class="space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <flux:heading size="sm">{{ __('listing.detail.contact.title') }}</flux:heading>
                            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing.detail.contact.helper') }}</flux:text>
                        </div>
                        <flux:button type="button" variant="ghost" size="sm" icon="x-mark" wire:click="closeContact" aria-label="{{ __('listing.detail.contact.close') }}" />
                    </div>

                    <flux:field>
                        <flux:label>{{ __('listing.detail.contact.message_label') }}</flux:label>
                        <flux:textarea rows="4" wire:model.blur="messageBody" />
                        <flux:error name="messageBody" />
                    </flux:field>

                    <flux:button type="button" variant="primary" class="w-full data-loading:opacity-70" wire:click="sendMessage" wire:loading.attr="disabled" wire:target="sendMessage">
                        {{ __('listing.detail.contact.send') }}
                    </flux:button>
                </flux:card>
            @endif
        </aside>

        <div class="space-y-5 lg:order-1">
            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('listing.detail.exact.title') }}</flux:heading>
                <dl class="grid gap-3 sm:grid-cols-2">
                    @forelse($exactFeatures as $feature)
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                            <dt class="text-zinc-500">{{ $feature['label'] }}</dt>
                            <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $feature['value'] }}</dd>
                        </div>
                    @empty
                        <div class="text-sm text-zinc-500">{{ __('listing.detail.values.not_set') }}</div>
                    @endforelse
                </dl>
            </flux:card>

            <flux:card data-detail-section="sleeping-place-details" class="space-y-4">
                <div class="space-y-2">
                    <flux:heading size="lg">{{ $sleepingPlaceProfile['title'] }}</flux:heading>
                    @if($sleepingPlaceProfile['badges'])
                        <div class="flex flex-wrap gap-2">
                            @forelse($sleepingPlaceProfile['badges'] as $badge)
                                <flux:badge size="sm">{{ $badge }}</flux:badge>
                            @empty
                            @endforelse
                        </div>
                    @endif
                </div>

                @if($sleepingPlaceProfile['sections'])
                    <flux:accordion transition>
                        @foreach($sleepingPlaceProfile['sections'] as $section)
                            <flux:accordion.item :expanded="$section['open']">
                                <flux:accordion.heading>{{ $section['title'] }}</flux:accordion.heading>

                                <flux:accordion.content>
                                    <div class="grid gap-2 text-sm sm:grid-cols-2">
                                        @forelse($section['items'] as $item)
                                            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                                                <div class="text-xs text-zinc-500">{{ $item['label'] }}</div>
                                                <div class="font-medium text-zinc-800 dark:text-zinc-100">{{ $item['value'] }}</div>
                                            </div>
                                        @empty
                                        @endforelse
                                    </div>

                                    @if($section['warnings'])
                                        <div class="mt-3 space-y-2">
                                            @forelse($section['warnings'] as $warning)
                                                <flux:callout color="amber" icon="exclamation-triangle">
                                                    <flux:callout.text>{{ $warning }}</flux:callout.text>
                                                </flux:callout>
                                            @empty
                                            @endforelse
                                        </div>
                                    @endif
                                </flux:accordion.content>
                            </flux:accordion.item>
                        @endforeach
                    </flux:accordion>
                @else
                    <flux:text class="text-zinc-500">{{ __('listing.detail.values.not_set') }}</flux:text>
                @endif
            </flux:card>

            <flux:card data-detail-section="room-details" class="space-y-4">
                <flux:heading size="lg">{{ __('listing.detail.room.title') }}</flux:heading>
                <div class="grid gap-3 text-sm sm:grid-cols-2">
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                        <div class="text-zinc-500">{{ __('listing.detail.room.people_on_dates') }}</div>
                        <div class="font-medium">{{ trans_choice('listing.detail.room.people_count', $roomDetails['people_on_dates'], ['count' => $roomDetails['people_on_dates']]) }}</div>
                    </div>
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                        <div class="text-zinc-500">{{ __('listing.detail.room.total_places') }}</div>
                        <div class="font-medium">{{ $roomDetails['total_places'] }}</div>
                    </div>
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                        <div class="text-zinc-500">{{ __('listing.detail.room.occupied_places') }}</div>
                        <div class="font-medium">{{ $roomDetails['occupied_places'] }}</div>
                    </div>
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                        <div class="text-zinc-500">{{ __('listing.detail.room.gender_policy') }}</div>
                        <div class="font-medium">{{ $roomDetails['gender_policy'] }}</div>
                    </div>
                </div>

                <div class="space-y-2">
                    <flux:heading size="sm">{{ __('listing.detail.room.quiet_rules') }}</flux:heading>
                    @if($roomDetails['quiet_rules'])
                        <div class="flex flex-wrap gap-2">
                            @forelse($roomDetails['quiet_rules'] as $rule)
                                <flux:badge size="sm">{{ $rule }}</flux:badge>
                            @empty
                            @endforelse
                        </div>
                    @else
                        <flux:text size="sm" class="text-zinc-500">{{ __('listing.detail.room.no_quiet_rules') }}</flux:text>
                    @endif
                </div>

                <div class="space-y-3 border-t border-zinc-200 pt-4 dark:border-zinc-800">
                    <div class="space-y-2">
                        <flux:heading size="sm">{{ $roomDetails['profile']['title'] }}</flux:heading>
                        @if($roomDetails['profile']['badges'])
                            <div class="flex flex-wrap gap-2">
                                @forelse($roomDetails['profile']['badges'] as $badge)
                                    <flux:badge size="sm">{{ $badge }}</flux:badge>
                                @empty
                                @endforelse
                            </div>
                        @endif
                    </div>

                    @if($roomDetails['profile']['sections'])
                        <flux:accordion transition>
                            @foreach($roomDetails['profile']['sections'] as $section)
                                <flux:accordion.item :expanded="$section['open']">
                                    <flux:accordion.heading>{{ $section['title'] }}</flux:accordion.heading>

                                    <flux:accordion.content>
                                        <div class="grid gap-2 text-sm sm:grid-cols-2">
                                            @forelse($section['items'] as $item)
                                                <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                                                    <div class="text-xs text-zinc-500">{{ $item['label'] }}</div>
                                                    <div class="font-medium text-zinc-800 dark:text-zinc-100">{{ $item['value'] }}</div>
                                                </div>
                                            @empty
                                            @endforelse
                                        </div>

                                        @if($section['warnings'])
                                            <div class="mt-3 space-y-2">
                                                @forelse($section['warnings'] as $warning)
                                                    <flux:callout color="amber" icon="exclamation-triangle">
                                                        <flux:callout.text>{{ $warning }}</flux:callout.text>
                                                    </flux:callout>
                                                @empty
                                                @endforelse
                                            </div>
                                        @endif
                                    </flux:accordion.content>
                                </flux:accordion.item>
                            @endforeach
                        </flux:accordion>
                    @endif
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('occupants.title') }}</flux:heading>
                <div class="rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                    <div class="font-medium">{{ trans_choice('listing.detail.nearby.count', $nearbySummary['count'], ['count' => $nearbySummary['count']]) }}</div>
                    <div class="mt-1 text-zinc-600 dark:text-zinc-400">{{ $nearbySummary['summary'] }}</div>
                </div>
                @if($nearbySummary['messages'])
                    <div class="space-y-2 text-sm text-zinc-700 dark:text-zinc-300">
                        @forelse($nearbySummary['messages'] as $message)
                            <p>{{ $message }}</p>
                        @empty
                        @endforelse
                    </div>
                @endif
                @if($nearbySummary['badges'])
                    <div class="flex flex-wrap gap-2">
                        @forelse($nearbySummary['badges'] as $badge)
                            <flux:badge size="sm">{{ $badge }}</flux:badge>
                        @empty
                        @endforelse
                    </div>
                @endif
                @if($nearbySummary['warnings'])
                    <div class="space-y-2">
                        @forelse($nearbySummary['warnings'] as $warning)
                            <flux:badge color="amber">{{ $warning['message'] }}</flux:badge>
                        @empty
                        @endforelse
                    </div>
                @endif
                <flux:callout icon="shield-check">
                    <flux:callout.text>{{ $nearbySummary['privacy'] }}</flux:callout.text>
                </flux:callout>
                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $nearbySummary['privacy_note'] }}</flux:text>
            </flux:card>

            <x-listings.detail.description-sections :sections="$extendedContent['sections']" />

            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('listing.detail.property.title') }}</flux:heading>
                <div class="space-y-3 text-sm text-zinc-700 dark:text-zinc-300">
                    <p>{{ $propertyDetails['description'] }}</p>
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">{{ $propertyDetails['address'] }}</div>
                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $propertyDetails['address_note'] }}</flux:text>
                    @if($propertyDetails['check_in_instructions'])
                        <div class="rounded-lg bg-emerald-50 px-3 py-2 dark:bg-emerald-400/10">
                            <div class="text-xs font-medium text-emerald-800 dark:text-emerald-200">{{ __('listing.detail.property.check_in_instructions') }}</div>
                            <div class="mt-1 whitespace-pre-line text-zinc-700 dark:text-zinc-200">{{ $propertyDetails['check_in_instructions'] }}</div>
                        </div>
                    @endif
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">{{ $propertyDetails['transport'] }}</div>
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">{{ $propertyDetails['kitchen_bathroom'] }}</div>
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">{{ $propertyDetails['safety'] }}</div>
                </div>

                <div class="space-y-3 border-t border-zinc-200 pt-4 dark:border-zinc-800">
                    <flux:heading size="sm">{{ __('property.public.title') }}</flux:heading>
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                        {{ $propertyDetails['profile']['address']['public'] }}
                    </div>

                    @if($propertyDetails['profile']['sections'])
                        <flux:accordion transition>
                            @foreach($propertyDetails['profile']['sections'] as $section)
                                <flux:accordion.item :expanded="$loop->first">
                                    <flux:accordion.heading>{{ $section['title'] }}</flux:accordion.heading>

                                    <flux:accordion.content>
                                        <div class="grid gap-2 text-sm sm:grid-cols-2">
                                            @forelse($section['items'] as $item)
                                                <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                                                    <div class="text-xs text-zinc-500">{{ $item['label'] }}</div>
                                                    <div class="font-medium text-zinc-800 dark:text-zinc-100">{{ $item['value'] }}</div>
                                                </div>
                                            @empty
                                                <flux:text size="sm" class="text-zinc-500">{{ __('listing.detail.property.no_description') }}</flux:text>
                                            @endforelse
                                        </div>
                                    </flux:accordion.content>
                                </flux:accordion.item>
                            @endforeach
                        </flux:accordion>
                    @endif
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('listing.detail.amenities.title') }}</flux:heading>
                @if($amenityGroups)
                    <flux:accordion transition>
                        @foreach($amenityGroups as $group)
                            <flux:accordion.item :expanded="$loop->first">
                                <flux:accordion.heading>{{ $group['title'] }}</flux:accordion.heading>

                                <flux:accordion.content>
                                    @if($group['items'])
                                        <div class="flex flex-wrap gap-2">
                                            @forelse($group['items'] as $amenity)
                                                <span class="rounded-md bg-zinc-100 px-2 py-1 text-xs text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $amenity }}</span>
                                            @empty
                                            @endforelse
                                        </div>
                                    @else
                                        <flux:text size="sm" class="text-zinc-500">{{ __('listing.detail.amenities.empty') }}</flux:text>
                                    @endif
                                </flux:accordion.content>
                            </flux:accordion.item>
                        @endforeach
                    </flux:accordion>
                @else
                    <flux:text class="text-zinc-500">{{ __('listing.detail.amenities.empty') }}</flux:text>
                @endif
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('listing.detail.rules.title') }}</flux:heading>
                @if($rulesByGroup)
                    <flux:accordion transition>
                        @foreach($rulesByGroup as $category => $rules)
                            <flux:accordion.item :expanded="$loop->first">
                                <flux:accordion.heading>{{ __('listing.detail.rules.categories.'.$category) }}</flux:accordion.heading>

                                <flux:accordion.content>
                                    <div class="grid gap-2">
                                        @forelse($rules as $rule)
                                            <div class="rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">{{ $rule }}</div>
                                        @empty
                                        @endforelse
                                    </div>
                                </flux:accordion.content>
                            </flux:accordion.item>
                        @endforeach
                    </flux:accordion>
                @else
                    <flux:text class="text-zinc-500">{{ __('listing.detail.rules.empty') }}</flux:text>
                @endif
            </flux:card>

            <flux:card class="space-y-4">
                <div>
                    <flux:heading size="lg">{{ __('listing.detail.calendar.title') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing.detail.calendar.helper') }}</flux:text>
                </div>
                <div class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $calendarPreview['range_label'] }}</div>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @forelse($calendarPreview['days'] as $day)
                        <div @class([
                            'min-h-24 rounded-lg border px-3 py-2 text-sm',
                            'border-emerald-300 bg-emerald-50 dark:border-emerald-400/40 dark:bg-emerald-400/10' => $day['is_selected'],
                            'border-rose-200 bg-rose-50 dark:border-rose-400/30 dark:bg-rose-400/10' => $day['is_blocked'] && ! $day['is_selected'],
                            'border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900' => ! $day['is_selected'] && ! $day['is_blocked'],
                        ])>
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="text-xs text-zinc-500">{{ $day['weekday'] }}</div>
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $day['label'] }}</div>
                                </div>
                                @if($day['is_selected'])
                                    <span class="rounded-md bg-emerald-100 px-1.5 py-0.5 text-[0.65rem] font-medium text-emerald-800 dark:bg-emerald-300/20 dark:text-emerald-100">{{ __('listing.detail.calendar.selected') }}</span>
                                @endif
                            </div>
                            <div class="mt-2 text-xs text-zinc-600 dark:text-zinc-400">{{ $day['status_label'] }}</div>
                            @if($day['price'])
                                <div class="mt-1 text-xs font-medium">{{ $day['price'] }}</div>
                            @endif
                            @if(! $day['check_in_allowed'])
                                <div class="mt-1 text-xs text-amber-700 dark:text-amber-200">{{ __('listing.detail.calendar.check_in_closed') }}</div>
                            @endif
                            @if(! $day['check_out_allowed'])
                                <div class="mt-1 text-xs text-amber-700 dark:text-amber-200">{{ __('listing.detail.calendar.check_out_closed') }}</div>
                            @endif
                        </div>
                    @empty
                        <flux:text class="text-zinc-500">{{ __('listing.detail.calendar.fallback') }}</flux:text>
                    @endforelse
                </div>
                <flux:callout icon="calendar-days">
                    <flux:callout.text>{{ $calendarPreview['fallback'] }}</flux:callout.text>
                </flux:callout>
            </flux:card>

            <flux:card class="space-y-4">
                <div>
                    <flux:heading size="lg">{{ __('listing.detail.map.title') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing.detail.map.helper') }}</flux:text>
                </div>
                <div class="flex aspect-[16/9] items-center justify-center rounded-lg bg-zinc-100 text-center text-sm text-zinc-500 dark:bg-zinc-900 dark:text-zinc-400">
                    <div class="space-y-2 px-4">
                        <flux:icon name="map-pin" variant="outline" class="mx-auto size-8 text-zinc-400" />
                        <div>{{ __('listing.detail.map.placeholder') }}</div>
                    </div>
                </div>
                <dl class="grid gap-3 text-sm sm:grid-cols-3">
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                        <dt class="text-zinc-500">{{ __('listing.detail.map.area') }}</dt>
                        <dd class="font-medium">{{ $mapDetails['area'] }}</dd>
                    </div>
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                        <dt class="text-zinc-500">{{ __('listing.detail.map.transport') }}</dt>
                        <dd class="font-medium">{{ $mapDetails['transport'] }}</dd>
                    </div>
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                        <dt class="text-zinc-500">{{ __('listing.detail.map.distance') }}</dt>
                        <dd class="font-medium">{{ $mapDetails['distance'] }}</dd>
                    </div>
                </dl>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $mapDetails['description'] }}</flux:text>
                <flux:callout icon="shield-check">
                    <flux:callout.text>{{ $mapDetails['privacy'] }}</flux:callout.text>
                </flux:callout>
            </flux:card>

            <section aria-labelledby="host-card-title" class="space-y-3">
                <flux:heading id="host-card-title" size="lg">{{ __('listing.detail.host.title') }}</flux:heading>
                <x-host.public-card :host="$place->property?->host" />
            </section>

            <section aria-labelledby="reviews-title" class="space-y-3">
                <flux:heading id="reviews-title" size="lg">{{ __('listing.detail.reviews.title') }}</flux:heading>
                <livewire:places.sleeping-place-reviews :sleeping-place-id="$place->id" lazy />
            </section>

            <flux:card class="space-y-4">
                <div>
                    <flux:heading size="lg">{{ __('listing.detail.safety.title') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing.detail.safety.helper') }}</flux:text>
                </div>
                <dl class="grid gap-3 sm:grid-cols-2">
                    @forelse($safetyDetails['rows'] as $row)
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                            <dt class="text-zinc-500">{{ $row['label'] }}</dt>
                            <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $row['value'] }}</dd>
                        </div>
                    @empty
                        <flux:text class="text-zinc-500">{{ __('listing.detail.property.safety_missing') }}</flux:text>
                    @endforelse
                </dl>
                <flux:callout icon="shield-check">
                    <flux:callout.text>{{ $safetyDetails['callout'] }}</flux:callout.text>
                </flux:callout>
            </flux:card>

            <flux:card class="space-y-4">
                <div>
                    <flux:heading size="lg">{{ __('listing.detail.cancellation.title') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing.detail.cancellation.helper') }}</flux:text>
                </div>
                <dl class="grid gap-3 sm:grid-cols-2">
                    @forelse($cancellationDetails['rows'] as $row)
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                            <dt class="text-zinc-500">{{ $row['label'] }}</dt>
                            <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $row['value'] }}</dd>
                        </div>
                    @empty
                        <flux:text class="text-zinc-500">{{ __('listing.detail.cancellation.choose_dates') }}</flux:text>
                    @endforelse
                </dl>
            </flux:card>

            <section aria-labelledby="similar-title" class="space-y-3">
                <flux:heading id="similar-title" size="lg">{{ __('listing.detail.similar.title') }}</flux:heading>
                <livewire:places.similar-sleeping-places :sleeping-place-id="$place->id" lazy />
            </section>

            <flux:card class="space-y-3">
                <flux:heading size="lg">{{ __('listing.detail.faq.title') }}</flux:heading>
                @if($faqItems)
                    <flux:accordion transition exclusive>
                        @foreach($faqItems as $item)
                            <flux:accordion.item :expanded="$loop->first">
                                <flux:accordion.heading>{{ $item['question'] }}</flux:accordion.heading>
                                <flux:accordion.content>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $item['answer'] }}</p>
                                </flux:accordion.content>
                            </flux:accordion.item>
                        @endforeach
                    </flux:accordion>
                @else
                    <flux:text class="text-zinc-500">{{ __('listing.detail.values.not_set') }}</flux:text>
                @endif
            </flux:card>
        </div>
    </section>
</div>
