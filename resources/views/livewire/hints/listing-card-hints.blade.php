<div>
    @if($hints)
        <div class="flex flex-wrap gap-1.5" aria-label="{{ __('guest_hints.title') }}">
            @forelse($hints as $hint)
                <flux:badge
                    size="sm"
                    color="{{ ($hint['type'] ?? '') === 'warning' || ($hint['type'] ?? '') === 'urgent' ? 'amber' : (($hint['type'] ?? '') === 'positive' || ($hint['type'] ?? '') === 'discount' ? 'emerald' : 'zinc') }}"
                >
                    {{ $hint['text'] }}
                </flux:badge>
            @empty
            @endforelse
        </div>
    @endif
</div>
