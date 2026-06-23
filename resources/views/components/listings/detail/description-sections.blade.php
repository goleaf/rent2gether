@props([
    'sections' => [],
])

@if($sections !== [])
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('listing_detail.extended.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                {{ __('listing_detail.extended.helper') }}
            </flux:text>
        </div>

        @if(count($sections) > 0)
            <flux:accordion transition>
                @foreach($sections as $section)
                    <flux:accordion.item :expanded="$section['open_by_default']">
                        <flux:accordion.heading>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cube" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __($section['title_key']) }}</span>
    </span>
</flux:accordion.heading>

                        <flux:accordion.content>
                            <div class="space-y-3">
                                @forelse($section['items'] as $item)
                                    <div class="rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                                        <div class="text-xs font-medium text-zinc-500">{{ __($item['label_key']) }}</div>
                                        <p class="mt-1 whitespace-pre-line text-zinc-700 dark:text-zinc-300">{{ $item['text'] }}</p>
                                    </div>
                                @empty
                                    <flux:text size="sm" class="text-zinc-500">{{ __('listing_detail.empty.not_provided') }}</flux:text>
                                @endforelse
                            </div>
                        </flux:accordion.content>
                    </flux:accordion.item>
                @endforeach
            </flux:accordion>
        @else
            <flux:text class="text-zinc-500">{{ __('listing_detail.empty.not_provided') }}</flux:text>
        @endif
    </flux:card>
@endif
