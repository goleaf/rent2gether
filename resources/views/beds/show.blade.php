<x-layouts.app :title="$bed->title">

    <div class="max-w-5xl mx-auto space-y-8">

        {{-- Breadcrumb --}}
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('home', ['locale' => app()->getLocale()]) }}">{{ __('navigation.home') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('search.index', ['locale' => app()->getLocale()]) }}">{{ __('navigation.search') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $bed->title }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Left column: details --}}
            <div class="lg:col-span-2 space-y-6">

                @if($media)
                    <img
                        src="{{ $media->imageUrl('mobile') }}"
                        alt="{{ $media->localizedCaption() ?: __('listing.media.primary_alt', ['title' => $bed->title]) }}"
                        loading="lazy"
                        width="720"
                        height="480"
                        class="h-72 w-full rounded-2xl border border-zinc-200 bg-zinc-100 object-cover dark:border-zinc-700 dark:bg-zinc-800"
                    />
                @else
                    <div class="h-72 bg-zinc-100 dark:bg-zinc-800 rounded-2xl flex items-center justify-center border border-zinc-200 dark:border-zinc-700">
                        <flux:icon name="home" class="size-16 text-zinc-300 dark:text-zinc-600" />
                    </div>
                @endif

                {{-- Title & location --}}
                <div>
                    <flux:heading size="xl">{{ $bed->title }}</flux:heading>

                    <div class="flex items-center gap-2 mt-1 text-zinc-500 dark:text-zinc-400">
                        <flux:icon name="map-pin" variant="mini" class="size-4 shrink-0" />
                        <flux:text>
                            {{ $bed->room->property->city }}
                            @if($bed->room->property->district), {{ $bed->room->property->district }}@endif
                            @if($bed->room->property->country), {{ $bed->room->property->country }}@endif
                        </flux:text>
                    </div>
                </div>

                @if($compatibilityResult)
                    <flux:card class="space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1">
                                <flux:heading size="sm">{{ __('compatibility.listing.title') }}</flux:heading>
                                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                                    {{ __('compatibility.listing.helper') }}
                                </flux:text>
                            </div>
                            <div class="shrink-0 text-right">
                                <div class="text-2xl font-semibold text-zinc-950 dark:text-white">{{ $compatibilityResult['score'] }}%</div>
                                <flux:badge size="sm" color="{{ $compatibilityResult['score'] >= 70 ? 'green' : ($compatibilityResult['score'] >= 45 ? 'yellow' : 'red') }}">
                                    {{ __('compatibility.fit_levels.'.$compatibilityResult['fit_level']) }}
                                </flux:badge>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="space-y-2">
                                <flux:heading size="xs">{{ __('compatibility.listing.why_fits') }}</flux:heading>
                                @forelse($compatibilityResult['positive_reasons'] as $reason)
                                    <div class="flex gap-2 rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-950 dark:bg-emerald-400/10 dark:text-emerald-100">
                                        <flux:icon name="check-circle" class="mt-0.5 size-4 shrink-0" />
                                        <span>{{ $reason }}</span>
                                    </div>
                                @empty
                                    <flux:text size="sm" class="text-zinc-500">{{ __('compatibility.listing.no_positive') }}</flux:text>
                                @endforelse
                            </div>

                            <div class="space-y-2">
                                <flux:heading size="xs">{{ __('compatibility.listing.pay_attention') }}</flux:heading>
                                @forelse($compatibilityResult['warning_reasons'] as $reason)
                                    <div class="flex gap-2 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-950 dark:bg-amber-400/10 dark:text-amber-100">
                                        <flux:icon name="exclamation-triangle" class="mt-0.5 size-4 shrink-0" />
                                        <span>{{ $reason }}</span>
                                    </div>
                                @empty
                                    <flux:text size="sm" class="text-zinc-500">{{ __('compatibility.listing.no_warnings') }}</flux:text>
                                @endforelse
                            </div>
                        </div>
                    </flux:card>
                @endif

                <flux:separator />

                {{-- Room & property info --}}
                <div class="space-y-2">
                    <flux:heading size="sm">{{ __('listing.bed.room_property') }}</flux:heading>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-3">
                            <flux:text size="xs" class="text-zinc-400 uppercase tracking-wide">{{ __('listing.bed.bed_type') }}</flux:text>
                            <flux:text class="font-medium mt-0.5">{{ $bed->type->label() }}</flux:text>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-3">
                            <flux:text size="xs" class="text-zinc-400 uppercase tracking-wide">{{ __('listing.bed.room_gender') }}</flux:text>
                            <flux:text class="font-medium mt-0.5">{{ $bed->room->gender_type->label() }}</flux:text>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-3">
                            <flux:text size="xs" class="text-zinc-400 uppercase tracking-wide">{{ __('listing.bed.room_capacity') }}</flux:text>
                            <flux:text class="font-medium mt-0.5">{{ $bed->room->capacity }} {{ __('listing.bed.beds') }}</flux:text>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-3">
                            <flux:text size="xs" class="text-zinc-400 uppercase tracking-wide">{{ __('listing.bed.min_stay') }}</flux:text>
                            <flux:text class="font-medium mt-0.5">{{ trans_choice('booking.nights_count', $bed->min_nights, ['count' => $bed->min_nights]) }}</flux:text>
                        </div>
                        @if($bed->max_nights)
                            <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-3">
                                <flux:text size="xs" class="text-zinc-400 uppercase tracking-wide">{{ __('listing.bed.max_stay') }}</flux:text>
                                <flux:text class="font-medium mt-0.5">{{ trans_choice('booking.nights_count', $bed->max_nights, ['count' => $bed->max_nights]) }}</flux:text>
                            </div>
                        @endif
                        <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-3">
                            <flux:text size="xs" class="text-zinc-400 uppercase tracking-wide">{{ __('listing.bed.cancellation') }}</flux:text>
                            <flux:text class="font-medium mt-0.5">{{ $bed->cancellation_policy->label() }}</flux:text>
                        </div>
                    </div>
                </div>

                <flux:separator />

                {{-- Amenities --}}
                <div class="space-y-3">
                    <flux:heading size="sm">{{ __('listing.bed.offers') }}</flux:heading>
                    <div class="flex flex-wrap gap-2">
                        @if($bed->has_locker)   <flux:badge icon="lock-closed"      color="zinc">{{ __('listing.bed.personal_locker') }}</flux:badge>   @endif
                        @if($bed->has_outlet)   <flux:badge icon="bolt"             color="zinc">{{ __('listing.bed.power_outlet') }}</flux:badge>      @endif
                        @if($bed->has_lamp)     <flux:badge icon="light-bulb"       color="zinc">{{ __('listing.bed.reading_lamp') }}</flux:badge>      @endif
                        @if($bed->has_curtain)  <flux:badge icon="eye-slash"        color="zinc">{{ __('listing.bed.privacy_curtain') }}</flux:badge>   @endif
                        @if($bed->has_shelf)    <flux:badge icon="square-3-stack-3d" color="zinc">{{ __('listing.bed.shelf') }}</flux:badge>            @endif
                        @if($bed->has_luggage_space) <flux:badge icon="briefcase"   color="zinc">{{ __('listing.bed.luggage_space') }}</flux:badge>    @endif
                        @if($bed->has_linen)    <flux:badge icon="sparkles"         color="zinc">{{ __('listing.bed.linen_included') }}</flux:badge>   @endif
                        @if($bed->has_towel)    <flux:badge icon="sparkles"         color="zinc">{{ __('listing.bed.towel_included') }}</flux:badge>   @endif
                        @if($bed->instant_book) <flux:badge icon="bolt"             color="green">{{ __('listing.bed.instant_book') }}</flux:badge>    @endif
                    </div>
                </div>

                @if($bed->description)
                    <flux:separator />
                    <div class="space-y-2">
                        <flux:heading size="sm">{{ __('listing.bed.description') }}</flux:heading>
                        <flux:text class="whitespace-pre-line text-zinc-700 dark:text-zinc-300">{{ $bed->description }}</flux:text>
                    </div>
                @endif

                {{-- Property amenities --}}
                @if($propertyAmenityLabels)
                    <flux:separator />
                    <div class="space-y-3">
                        <flux:heading size="sm">{{ __('listing.bed.property_amenities') }}</flux:heading>
                        <div class="flex flex-wrap gap-2">
                            @foreach($propertyAmenityLabels as $amenityLabel)
                                <flux:badge color="blue" size="sm">
                                    {{ $amenityLabel }}
                                </flux:badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Other beds in the same room --}}
                @if($bed->room->beds->isNotEmpty())
                    <flux:separator />
                    <div class="space-y-3">
                        <flux:heading size="sm">{{ __('listing.bed.other_beds') }}</flux:heading>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($bed->room->beds as $sibling)
                                <a href="{{ route('beds.show', ['locale' => app()->getLocale(), 'bed' => $sibling]) }}"
                                   class="flex items-center justify-between bg-zinc-50 dark:bg-zinc-800 rounded-lg px-4 py-3 border border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 transition-colors">
                                    <div>
                                        <flux:text class="font-medium">{{ $sibling->title }}</flux:text>
                                        <flux:text size="sm" class="text-zinc-500">{{ $sibling->type->label() }}</flux:text>
                                    </div>
                                    <flux:text class="font-semibold text-zinc-900 dark:text-white">
                                        €{{ number_format($sibling->price_per_night, 0) }}/{{ __('listing.bed.nightly_rate') }}
                                    </flux:text>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            {{-- Right column: booking card --}}
            <div class="lg:col-span-1">
                <div class="sticky top-24 bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm p-5 space-y-4">

                    <div class="flex items-end gap-1">
                        <span class="text-2xl font-bold text-zinc-900 dark:text-white">€{{ number_format($bed->price_per_night, 0) }}</span>
                        <span class="text-zinc-500 dark:text-zinc-400 pb-0.5">/{{ __('listing.bed.nightly_rate') }}</span>
                    </div>

                    @if($bed->discount_weekly > 0 || $bed->discount_monthly > 0)
                        <div class="flex flex-wrap gap-2">
                            @if($bed->discount_weekly > 0)
                                <flux:badge color="green" size="sm">{{ __('listing.bed.weekly_discount', ['percent' => $bed->discount_weekly]) }}</flux:badge>
                            @endif
                            @if($bed->discount_monthly > 0)
                                <flux:badge color="green" size="sm">{{ __('listing.bed.monthly_discount', ['percent' => $bed->discount_monthly]) }}</flux:badge>
                            @endif
                        </div>
                    @endif

                    <flux:separator />

                    <div class="space-y-3">
                        <flux:field>
                            <flux:label>{{ __('search.check_in') }}</flux:label>
                            <flux:input type="date" id="check_in" :min="now()->toDateString()" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('search.check_out') }}</flux:label>
                            <flux:input type="date" id="check_out" :min="now()->addDay()->toDateString()" />
                        </flux:field>
                    </div>

                    <flux:button variant="primary" class="w-full" href="{{ route('search.index', ['locale' => app()->getLocale()]) }}">
                        {{ $bed->instant_book ? __('listing.bed.book_instantly') : __('listing.bed.request_to_book') }}
                    </flux:button>

                    <flux:text size="sm" class="text-center text-zinc-400">
                        {{ $bed->instant_book ? __('listing.bed.instant_text') : __('listing.bed.request_text') }}
                    </flux:text>

                    @if($bed->cleaning_fee)
                        <flux:separator />
                        <div class="space-y-1.5">
                            <div class="flex justify-between text-sm">
                                <flux:text class="text-zinc-500">{{ __('listing.bed.cleaning_fee') }}</flux:text>
                                <flux:text>€{{ number_format($bed->cleaning_fee, 0) }}</flux:text>
                            </div>
                            @if($bed->deposit)
                                <div class="flex justify-between text-sm">
                                    <flux:text class="text-zinc-500">{{ __('listing.bed.security_deposit') }}</flux:text>
                                    <flux:text>€{{ number_format($bed->deposit, 0) }}</flux:text>
                                </div>
                            @endif
                            <div class="flex justify-between text-sm">
                                <flux:text class="text-zinc-500">{{ __('listing.bed.service_fee') }}</flux:text>
                                <flux:text>{{ __('listing.bed.service_fee_detail', ['percent' => 5]) }}</flux:text>
                            </div>
                        </div>
                    @endif

                    <flux:separator />

                    <x-host.public-card
                        :host="$bed->room->property->host"
                        :host-profile="$bed->room->property->host->hostProfile"
                    />

                </div>
            </div>

        </div>

    </div>

</x-layouts.app>
