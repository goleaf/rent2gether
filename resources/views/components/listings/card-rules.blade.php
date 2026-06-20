@props(['rules' => []])

@if($rules !== [])
    <div class="flex flex-wrap gap-1.5" aria-label="{{ __('listing_card.rules_label') }}">
        @foreach(array_slice($rules, 0, 3) as $rule)
            <span class="rounded-md border border-zinc-200 px-2 py-1 text-xs text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">{{ $rule }}</span>
        @endforeach
    </div>
@endif
