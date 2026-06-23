@props(['variant' => 'subtle'])

<flux:button
    variant="{{ $variant }}"
    icon="exclamation-triangle"
    {{ $attributes->class([
        'bg-amber-50! text-amber-700! ring-1! ring-inset! ring-amber-200! hover:bg-amber-100! hover:text-amber-800! dark:bg-amber-400/10! dark:text-amber-200! dark:ring-amber-300/20! dark:hover:bg-amber-400/15! dark:hover:text-amber-100!',
    ]) }}
>
    {{ $slot }}
</flux:button>
