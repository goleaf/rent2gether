<div class="space-y-2">
    @forelse($hints as $hint)
        <div class="rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $hint['text'] ?? '' }}</div>
            @if(! empty($hint['category']))
                <div class="mt-1 text-xs text-zinc-500">{{ __('guest_hints.categories.'.$hint['category']) }}</div>
            @endif
        </div>
    @empty
        <div class="rounded-lg bg-zinc-50 px-3 py-3 text-sm text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400">
            {{ __('guest_hints.empty') }}
        </div>
    @endforelse
</div>
