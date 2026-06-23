<flux:card class="space-y-1">
    <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('host_profile.public.title') }}</flux:text>
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ $profile['name'] }}</span>
        </span>
    </flux:heading>
</flux:card>
