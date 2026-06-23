@props(['amenities' => []])

@if($amenities !== [])
    <div class="flex flex-wrap gap-1.5" aria-label="{{ __('listing_card.amenities_label') }}">
        @foreach(array_slice($amenities, 0, 4) as $amenity)
            <flux:badge size="sm" color="zinc" icon="home-modern">{{ $amenity }}</flux:badge>
        @endforeach
    </div>
@endif
