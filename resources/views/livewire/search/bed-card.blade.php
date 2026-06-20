<a href="{{ route('beds.show', ['locale' => app()->getLocale(), 'bed' => $bed]) }}" class="group block bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden hover:shadow-md hover:border-zinc-300 dark:hover:border-zinc-600 transition-all">

    @php($media = $bed->room->property->cardMedia)
    @if($media)
        <img
            src="{{ $media->imageUrl('mobile') }}"
            alt="{{ $media->localizedCaption() ?: __('listing.media.primary_alt', ['title' => $bed->title]) }}"
            loading="lazy"
            decoding="async"
            width="720"
            height="480"
            class="h-44 w-full bg-zinc-100 object-cover dark:bg-zinc-700"
        />
    @else
        <div class="h-44 bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center">
            <flux:icon name="home" class="size-12 text-zinc-300 dark:text-zinc-600" />
        </div>
    @endif

    <div class="p-4 space-y-2">

        {{-- City & property --}}
        <div class="flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400 truncate">
            <flux:icon name="map-pin" variant="mini" class="size-3.5 shrink-0" />
            <span class="truncate">{{ $bed->room->property->city }}{{ $bed->room->property->district ? ', '.$bed->room->property->district : '' }}</span>
        </div>

        {{-- Bed title --}}
        <flux:heading size="sm" class="truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
            {{ $bed->title }}
        </flux:heading>

        {{-- Room info --}}
        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 truncate">
            {{ $bed->room->title }} &middot; {{ $bed->type->label() }} &middot; {{ $bed->room->gender_type->label() }}
        </flux:text>

        {{-- Badges --}}
        <div class="flex flex-wrap gap-1.5">
            @if($bed->instant_book)
                <flux:badge color="green" size="sm" icon="bolt">{{ __('listing.bed.instant_book') }}</flux:badge>
            @endif
            @if($bed->has_locker)
                <flux:badge color="zinc" size="sm" icon="lock-closed">{{ __('listing.bed.personal_locker') }}</flux:badge>
            @endif
            @if($bed->cancellation_policy->value === 'flexible')
                <flux:badge color="blue" size="sm">{{ __('listing.cancellation_policy.flexible') }}</flux:badge>
            @endif
        </div>

        {{-- Price --}}
        <div class="flex items-end justify-between pt-1">
            <div>
                <span class="text-lg font-semibold text-zinc-900 dark:text-white">
                    €{{ number_format($bed->price_per_night, 0) }}
                </span>
                <span class="text-sm text-zinc-500 dark:text-zinc-400">/{{ __('listing.bed.nightly_rate') }}</span>
            </div>

            @if($nights > 0)
                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                    €{{ number_format($this->priceSummary['total'] ?? 0, 0) }} {{ __('search.price_total') }}
                </flux:text>
            @endif
        </div>

    </div>
</a>
