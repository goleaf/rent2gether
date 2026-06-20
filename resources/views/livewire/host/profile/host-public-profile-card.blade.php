<article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
    <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('host_profile.public.title') }}</flux:text>
    <flux:heading size="md">{{ $profile['name'] }}</flux:heading>
    @if ($profile['verified_host'])
        <flux:text class="text-emerald-700 dark:text-emerald-300">{{ __('host_profile.public.verified') }}</flux:text>
    @endif
</article>
