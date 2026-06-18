<x-layouts.app :title="$bed->title">

    <div class="max-w-5xl mx-auto space-y-8">

        {{-- Breadcrumb --}}
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('home', ['locale' => app()->getLocale()]) }}">{{ __('app.nav.home') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('search.index', ['locale' => app()->getLocale()]) }}">{{ __('app.nav.search') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $bed->title }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Left column: details --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Image placeholder --}}
                <div class="h-72 bg-zinc-100 dark:bg-zinc-800 rounded-2xl flex items-center justify-center border border-zinc-200 dark:border-zinc-700">
                    <flux:icon name="home" class="size-16 text-zinc-300 dark:text-zinc-600" />
                </div>

                {{-- Title & location --}}
                <div>
                    <flux:heading size="xl">{{ $bed->title }}</flux:heading>

                    <div class="flex items-center gap-2 mt-1 text-zinc-500 dark:text-zinc-400">
                        <flux:icon name="map-pin" class="size-4 shrink-0" />
                        <flux:text>
                            {{ $bed->room->property->city }}
                            @if($bed->room->property->district), {{ $bed->room->property->district }}@endif
                            @if($bed->room->property->country), {{ $bed->room->property->country }}@endif
                        </flux:text>
                    </div>
                </div>

                <flux:separator />

                {{-- Room & property info --}}
                <div class="space-y-2">
                    <flux:heading size="sm">{{ __('app.bed.room_property') }}</flux:heading>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-3">
                            <flux:text size="xs" class="text-zinc-400 uppercase tracking-wide">{{ __('app.bed.bed_type') }}</flux:text>
                            <flux:text class="font-medium mt-0.5">{{ $bed->type->label() }}</flux:text>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-3">
                            <flux:text size="xs" class="text-zinc-400 uppercase tracking-wide">{{ __('app.bed.room_gender') }}</flux:text>
                            <flux:text class="font-medium mt-0.5">{{ $bed->room->gender_type->label() }}</flux:text>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-3">
                            <flux:text size="xs" class="text-zinc-400 uppercase tracking-wide">{{ __('app.bed.room_capacity') }}</flux:text>
                            <flux:text class="font-medium mt-0.5">{{ $bed->room->capacity }} {{ __('app.bed.beds') }}</flux:text>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-3">
                            <flux:text size="xs" class="text-zinc-400 uppercase tracking-wide">{{ __('app.bed.min_stay') }}</flux:text>
                            <flux:text class="font-medium mt-0.5">{{ $bed->min_nights }} night{{ $bed->min_nights !== 1 ? 's' : '' }}</flux:text>
                        </div>
                        @if($bed->max_nights)
                            <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-3">
                                <flux:text size="xs" class="text-zinc-400 uppercase tracking-wide">{{ __('app.bed.max_stay') }}</flux:text>
                                <flux:text class="font-medium mt-0.5">{{ $bed->max_nights }} nights</flux:text>
                            </div>
                        @endif
                        <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-3">
                            <flux:text size="xs" class="text-zinc-400 uppercase tracking-wide">{{ __('app.bed.cancellation') }}</flux:text>
                            <flux:text class="font-medium mt-0.5">{{ $bed->cancellation_policy->label() }}</flux:text>
                        </div>
                    </div>
                </div>

                <flux:separator />

                {{-- Amenities --}}
                <div class="space-y-3">
                    <flux:heading size="sm">{{ __('app.bed.offers') }}</flux:heading>
                    <div class="flex flex-wrap gap-2">
                        @if($bed->has_locker)   <flux:badge icon="lock-closed"      color="zinc">{{ __('app.bed.personal_locker') }}</flux:badge>   @endif
                        @if($bed->has_outlet)   <flux:badge icon="bolt"             color="zinc">{{ __('app.bed.power_outlet') }}</flux:badge>      @endif
                        @if($bed->has_lamp)     <flux:badge icon="light-bulb"       color="zinc">{{ __('app.bed.reading_lamp') }}</flux:badge>      @endif
                        @if($bed->has_curtain)  <flux:badge icon="eye-slash"        color="zinc">{{ __('app.bed.privacy_curtain') }}</flux:badge>   @endif
                        @if($bed->has_shelf)    <flux:badge icon="square-3-stack-3d" color="zinc">{{ __('app.bed.shelf') }}</flux:badge>            @endif
                        @if($bed->has_luggage_space) <flux:badge icon="briefcase"   color="zinc">{{ __('app.bed.luggage_space') }}</flux:badge>    @endif
                        @if($bed->has_linen)    <flux:badge icon="sparkles"         color="zinc">{{ __('app.bed.linen_included') }}</flux:badge>   @endif
                        @if($bed->has_towel)    <flux:badge icon="sparkles"         color="zinc">{{ __('app.bed.towel_included') }}</flux:badge>   @endif
                        @if($bed->instant_book) <flux:badge icon="bolt"             color="green">{{ __('app.bed.instant_book') }}</flux:badge>    @endif
                    </div>
                </div>

                @if($bed->description)
                    <flux:separator />
                    <div class="space-y-2">
                        <flux:heading size="sm">{{ __('app.bed.description') }}</flux:heading>
                        <flux:text class="whitespace-pre-line text-zinc-700 dark:text-zinc-300">{{ $bed->description }}</flux:text>
                    </div>
                @endif

                {{-- Property amenities --}}
                @if($bed->room->property->amenities)
                    <flux:separator />
                    <div class="space-y-3">
                        <flux:heading size="sm">{{ __('app.bed.property_amenities') }}</flux:heading>
                        <div class="flex flex-wrap gap-2">
                            @foreach($bed->room->property->amenities as $amenity)
                                <flux:badge color="blue" size="sm">{{ Str::headline($amenity) }}</flux:badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Other beds in the same room --}}
                @if($bed->room->beds->isNotEmpty())
                    <flux:separator />
                    <div class="space-y-3">
                        <flux:heading size="sm">{{ __('app.bed.other_beds') }}</flux:heading>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($bed->room->beds as $sibling)
                                <a href="{{ route('beds.show', ['locale' => app()->getLocale(), 'bed' => $sibling]) }}"
                                   class="flex items-center justify-between bg-zinc-50 dark:bg-zinc-800 rounded-lg px-4 py-3 border border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 transition-colors">
                                    <div>
                                        <flux:text class="font-medium">{{ $sibling->title }}</flux:text>
                                        <flux:text size="sm" class="text-zinc-500">{{ $sibling->type->label() }}</flux:text>
                                    </div>
                                    <flux:text class="font-semibold text-zinc-900 dark:text-white">
                                        €{{ number_format($sibling->price_per_night, 0) }}/night
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
                        <span class="text-zinc-500 dark:text-zinc-400 pb-0.5">/{{ __('app.bed.nightly_rate') }}</span>
                    </div>

                    @if($bed->discount_weekly > 0 || $bed->discount_monthly > 0)
                        <div class="flex flex-wrap gap-2">
                            @if($bed->discount_weekly > 0)
                                <flux:badge color="green" size="sm">{{ __('app.bed.weekly_discount', ['percent' => $bed->discount_weekly]) }}</flux:badge>
                            @endif
                            @if($bed->discount_monthly > 0)
                                <flux:badge color="green" size="sm">{{ __('app.bed.monthly_discount', ['percent' => $bed->discount_monthly]) }}</flux:badge>
                            @endif
                        </div>
                    @endif

                    <flux:separator />

                    <div class="space-y-3">
                        <flux:field>
                            <flux:label>{{ __('app.search.check_in') }}</flux:label>
                            <flux:input type="date" id="check_in" :min="now()->toDateString()" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('app.search.check_out') }}</flux:label>
                            <flux:input type="date" id="check_out" :min="now()->addDay()->toDateString()" />
                        </flux:field>
                    </div>

                    <flux:button variant="primary" class="w-full" href="{{ route('search.index', ['locale' => app()->getLocale()]) }}">
                        {{ $bed->instant_book ? __('app.bed.book_instantly') : __('app.bed.request_to_book') }}
                    </flux:button>

                    <flux:text size="sm" class="text-center text-zinc-400">
                        {{ $bed->instant_book ? __('app.bed.instant_text') : __('app.bed.request_text') }}
                    </flux:text>

                    @if($bed->cleaning_fee)
                        <flux:separator />
                        <div class="space-y-1.5">
                            <div class="flex justify-between text-sm">
                                <flux:text class="text-zinc-500">{{ __('app.bed.cleaning_fee') }}</flux:text>
                                <flux:text>€{{ number_format($bed->cleaning_fee, 0) }}</flux:text>
                            </div>
                            @if($bed->deposit)
                                <div class="flex justify-between text-sm">
                                    <flux:text class="text-zinc-500">{{ __('app.bed.security_deposit') }}</flux:text>
                                    <flux:text>€{{ number_format($bed->deposit, 0) }}</flux:text>
                                </div>
                            @endif
                            <div class="flex justify-between text-sm">
                                <flux:text class="text-zinc-500">{{ __('app.bed.service_fee') }}</flux:text>
                                <flux:text>5% of subtotal</flux:text>
                            </div>
                        </div>
                    @endif

                    <flux:separator />

                    <div class="text-center">
                        <flux:text size="sm" class="text-zinc-400">
                            {{ __('app.bed.hosted_by') }} <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $bed->room->property->host->name }}</span>
                        </flux:text>
                    </div>

                </div>
            </div>

        </div>

    </div>

</x-layouts.app>
