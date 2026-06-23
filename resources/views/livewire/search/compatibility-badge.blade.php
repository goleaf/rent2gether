<div>
    @if($result)
        <flux:badge size="sm" color="{{ $result['fit_status'] === 'not_suitable' ? 'red' : ($result['warning_reasons'] ? 'yellow' : 'green') }}" icon="exclamation-triangle">
            {{ __('compatibility.title') }} · {{ __('compatibility.badge_short', ['score' => $result['score']]) }}
        </flux:badge>
    @else
        <flux:badge size="sm" icon="magnifying-glass">{{ __('compatibility.title') }}</flux:badge>
    @endif
</div>
