@props(['size' => 'base'])

@php
    $classes = $size === 'sm' ? 'size-6 text-[0.625rem]' : 'size-8 text-xs';
@endphp

<span
    {{ $attributes->class([
        $classes,
        'inline-grid shrink-0 place-items-center rounded-lg bg-accent font-semibold tracking-normal text-accent-foreground shadow-xs',
    ]) }}
>
    r2
</span>
