@props(['amenities' => []])

@if($amenities !== [])
    <div class="flex flex-wrap gap-1.5" aria-label="{{ __('listing_card.amenities_label') }}">
        @foreach(array_slice($amenities, 0, 4) as $amenity)
            <span class="rounded-md bg-zinc-100 px-2 py-1 text-xs text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $amenity }}</span>
        @endforeach
    </div>
@endif
