@props([
    'sections' => [],
])

@if($sections !== [])
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">{{ __('listing_detail.extended.title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                {{ __('listing_detail.extended.helper') }}
            </flux:text>
        </div>

        <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
            @forelse($sections as $section)
                <details @if($section['open_by_default']) open @endif class="group py-3">
                    <summary class="flex min-h-12 cursor-pointer list-none items-center justify-between gap-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                        <span>{{ __($section['title_key']) }}</span>
                        <flux:icon name="chevron-down" class="size-4 text-zinc-400 transition group-open:rotate-180" />
                    </summary>

                    <div class="space-y-3 pb-1 pt-2">
                        @forelse($section['items'] as $item)
                            <div class="rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                                <div class="text-xs font-medium text-zinc-500">{{ __($item['label_key']) }}</div>
                                <p class="mt-1 whitespace-pre-line text-zinc-700 dark:text-zinc-300">{{ $item['text'] }}</p>
                            </div>
                        @empty
                            <flux:text size="sm" class="text-zinc-500">{{ __('listing_detail.empty.not_provided') }}</flux:text>
                        @endforelse
                    </div>
                </details>
            @empty
                <flux:text class="text-zinc-500">{{ __('listing_detail.empty.not_provided') }}</flux:text>
            @endforelse
        </div>
    </flux:card>
@endif
