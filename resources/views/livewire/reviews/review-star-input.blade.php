<div class="space-y-2">
    <flux:text size="sm">{{ __('ratings.metrics.'.$scoreKey) }}</flux:text>

    <div class="grid grid-cols-5 gap-2" role="group" aria-label="{{ __('ratings.metrics.'.$scoreKey) }}">
        @foreach([1, 2, 3, 4, 5] as $rating)
            <flux:button type="button" size="sm" :variant="$rating <= $value ? 'primary' : 'ghost'" wire:click="setValue({{ $rating }})">
                {{ $rating }}
            </flux:button>
        @endforeach
    </div>
</div>
