<flux:card class="space-y-1">
    <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('profiles.public.guest_title') }}</flux:text>
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="user" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ $profile['public_name'] }}</span>
        </span>
    </flux:heading>
    @if ($profile['identity_verified'])
        <flux:badge color="green" size="sm" icon="check-circle">{{ __('profiles.public.identity_verified') }}</flux:badge>
    @endif
</flux:card>
