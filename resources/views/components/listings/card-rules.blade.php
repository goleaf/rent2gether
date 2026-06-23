@props(['rules' => []])

@if($rules !== [])
    <div class="flex flex-wrap gap-1.5" aria-label="{{ __('listing_card.rules_label') }}">
        @foreach(array_slice($rules, 0, 3) as $rule)
            <flux:badge size="sm" color="zinc" icon="home-modern">{{ $rule }}</flux:badge>
        @endforeach
    </div>
@endif
