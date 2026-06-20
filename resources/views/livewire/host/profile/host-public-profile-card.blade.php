<flux:card class="space-y-1">
    <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('host_profile.public.title') }}</flux:text>
    <flux:heading size="md">{{ $profile['name'] }}</flux:heading>
    @if ($profile['verified_host'])
        <flux:badge color="green" size="sm">{{ __('host_profile.public.verified') }}</flux:badge>
    @endif
</flux:card>
