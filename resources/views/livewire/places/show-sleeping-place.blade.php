@php
    $money = static fn (float|int|string $amount, string $currency): string => \Illuminate\Support\Number::currency((float) $amount, $currency, app()->getLocale());
    $primaryImage = $gallery[0] ?? null;
@endphp

<div class="mx-auto max-w-6xl space-y-5 px-4 py-4 pb-24 sm:px-6 lg:py-6">
    <div class="space-y-2">
        <flux:button
            variant="ghost"
            size="sm"
            icon="arrow-left"
            href="{{ route('search.index', ['locale' => app()->getLocale()]) }}"
            wire:navigate
        >
            {{ __('listing.detail.actions.back_to_search') }}
        </flux:button>

        <flux:heading size="xl" level="1">{{ $title }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('listing.detail.helper') }}</flux:text>
    </div>

    <section class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start">
        <div class="space-y-5">
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
                            @foreach(array_slice($gallery, 1) as $image)
                                <img
                                    src="{{ $image['thumb_url'] }}"
                                    alt="{{ $image['alt'] }}"
                                    width="112"
                                    height="84"
                                    loading="lazy"
                                    decoding="async"
                                    class="h-20 w-28 shrink-0 rounded-lg bg-zinc-100 object-cover dark:bg-zinc-900"
                                />
                            @endforeach
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
                                <span aria-hidden="true">·</span>
                                {{ trans_choice('listing.detail.summary.reviews_count', $summary['reviews_count'], ['count' => $summary['reviews_count']]) }}
                            @else
                                {{ __('listing.detail.summary.no_reviews') }}
                            @endif
                        </div>
                    </div>
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('listing.detail.exact.title') }}</flux:heading>
                <dl class="grid gap-3 sm:grid-cols-2">
                    @foreach($exactFeatures as $feature)
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                            <dt class="text-zinc-500">{{ $feature['label'] }}</dt>
                            <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $feature['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </flux:card>

            <flux:card class="space-y-4">
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
                            @foreach($roomDetails['quiet_rules'] as $rule)
                                <flux:badge size="sm">{{ $rule }}</flux:badge>
                            @endforeach
                        </div>
                    @else
                        <flux:text size="sm" class="text-zinc-500">{{ __('listing.detail.room.no_quiet_rules') }}</flux:text>
                    @endif
                </div>

                <div class="space-y-2">
                    <flux:heading size="sm">{{ __('listing.detail.room.amenities') }}</flux:heading>
                    @if($roomDetails['amenities'])
                        <div class="flex flex-wrap gap-2">
                            @foreach($roomDetails['amenities'] as $amenity)
                                <span class="rounded-md bg-zinc-100 px-2 py-1 text-xs text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $amenity }}</span>
                            @endforeach
                        </div>
                    @else
                        <flux:text size="sm" class="text-zinc-500">{{ __('listing.detail.room.no_amenities') }}</flux:text>
                    @endif
                </div>
            </flux:card>

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
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('listing.detail.nearby.title') }}</flux:heading>
                <div class="rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                    <div class="font-medium">{{ trans_choice('listing.detail.nearby.count', $nearbySummary['count'], ['count' => $nearbySummary['count']]) }}</div>
                    <div class="mt-1 text-zinc-600 dark:text-zinc-400">{{ $nearbySummary['summary'] }}</div>
                </div>
                <flux:callout icon="shield-check">
                    <flux:callout.text>{{ $nearbySummary['privacy'] }}</flux:callout.text>
                </flux:callout>
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('listing.detail.rules.title') }}</flux:heading>
                @forelse($rulesByGroup as $category => $rules)
                    <div class="space-y-2">
                        <flux:heading size="sm">{{ __('listing.detail.rules.categories.'.$category) }}</flux:heading>
                        <div class="grid gap-2">
                            @foreach($rules as $rule)
                                <div class="rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">{{ $rule }}</div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <flux:text class="text-zinc-500">{{ __('listing.detail.rules.empty') }}</flux:text>
                @endforelse
            </flux:card>

            <section aria-labelledby="host-card-title" class="space-y-3">
                <flux:heading id="host-card-title" size="lg">{{ __('listing.detail.host.title') }}</flux:heading>
                <x-host.public-card :host="$place->property?->host" />
            </section>

            <section aria-labelledby="reviews-title" class="space-y-3">
                <flux:heading id="reviews-title" size="lg">{{ __('listing.detail.reviews.title') }}</flux:heading>
                <livewire:places.sleeping-place-reviews :sleeping-place-id="$place->id" lazy />
            </section>

            <section aria-labelledby="similar-title" class="space-y-3">
                <flux:heading id="similar-title" size="lg">{{ __('listing.detail.similar.title') }}</flux:heading>
                <livewire:places.similar-sleeping-places :sleeping-place-id="$place->id" lazy />
            </section>

            <flux:card class="space-y-3">
                <flux:heading size="lg">{{ __('listing.detail.faq.title') }}</flux:heading>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @foreach($faqItems as $item)
                        <details class="group py-3">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                <span>{{ $item['question'] }}</span>
                                <flux:icon name="chevron-down" class="size-4 text-zinc-400 transition group-open:rotate-180" />
                            </summary>
                            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $item['answer'] }}</p>
                        </details>
                    @endforeach
                </div>
            </flux:card>
        </div>

        <aside class="space-y-4 lg:sticky lg:top-4">
            <flux:card class="space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <flux:heading size="lg">{{ __('listing.detail.booking.title') }}</flux:heading>
                        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing.detail.booking.helper') }}</flux:text>
                    </div>
                    <flux:button
                        type="button"
                        variant="ghost"
                        icon="{{ $isFavorited ? 'heart' : 'heart' }}"
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

                    @if($unavailableDates)
                        <div class="flex flex-wrap gap-2">
                            @foreach($unavailableDates as $date)
                                <flux:badge size="sm">{{ \Carbon\CarbonImmutable::parse($date)->translatedFormat('d M') }}</flux:badge>
                            @endforeach
                        </div>
                    @endif
                @endif

                @if($quote)
                    <div class="space-y-2 rounded-lg bg-zinc-50 px-3 py-3 text-sm dark:bg-zinc-900">
                        <div class="flex justify-between gap-3">
                            <span>{{ __('listing.detail.booking.nights') }}</span>
                            <span class="font-medium">{{ trans_choice('listing.detail.booking.nights_count', $quote['nights_count'], ['count' => $quote['nights_count']]) }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span>{{ __('listing.detail.booking.total') }}</span>
                            <span class="font-semibold">{{ $money($quote['total_amount'], $quote['currency']) }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span>{{ __('listing.detail.booking.deposit') }}</span>
                            <span class="font-medium">{{ $money($quote['deposit_amount'], $quote['currency']) }}</span>
                        </div>
                    </div>

                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                        {{ __('listing.detail.booking.deposit_note') }}
                    </flux:text>
                @else
                    <div class="rounded-lg bg-zinc-50 px-3 py-3 text-sm text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400">
                        {{ __('listing.detail.booking.choose_dates') }}
                    </div>
                @endif

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
    </section>
</div>
