@props(['section'])

<flux:card {{ $attributes->merge(['class' => 'space-y-3']) }}>
    @if($section)
        <flux:heading size="lg">{{ $section['title'] }}</flux:heading>

        <div class="grid gap-2 text-sm sm:grid-cols-2">
            @forelse($section['items'] as $item)
                <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                    <div class="text-xs text-zinc-500">{{ $item['label'] }}</div>
                    <div class="font-medium text-zinc-800 dark:text-zinc-100">{{ $item['value'] }}</div>
                </div>
            @empty
            @endforelse
        </div>

        @if($section['warnings'])
            <div class="space-y-2">
                @forelse($section['warnings'] as $warning)
                    <flux:callout color="amber" icon="exclamation-triangle">
                        <flux:callout.text>{{ $warning }}</flux:callout.text>
                    </flux:callout>
                @empty
                @endforelse
            </div>
        @endif
    @else
        <flux:text class="text-zinc-500">{{ __('sleeping_place.public.empty_section') }}</flux:text>
    @endif
</flux:card>
