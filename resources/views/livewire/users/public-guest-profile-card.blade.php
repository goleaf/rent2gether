<flux:card class="space-y-1">
    <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('profiles.public.guest_title') }}</flux:text>
    <flux:heading size="md">{{ $profile['public_name'] }}</flux:heading>
    @if ($profile['identity_verified'])
        <flux:badge color="green" size="sm">{{ __('profiles.public.identity_verified') }}</flux:badge>
    @endif
</flux:card>
