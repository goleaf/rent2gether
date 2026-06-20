@props(['badges' => []])

@foreach($badges as $badge)
    <flux:badge
        size="sm"
        color="{{ $badge['tone'] ?? 'zinc' }}"
        :icon="$badge['icon'] ?? null"
    >
        {{ $badge['label'] }}
    </flux:badge>
@endforeach
