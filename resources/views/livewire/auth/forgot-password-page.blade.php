<flux:card class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('auth.forgot.heading') }}</flux:heading>
        <flux:text class="mt-1 text-zinc-500">{{ __('auth.forgot.helper') }}</flux:text>
    </div>

    @if($statusMessage)
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ $statusMessage }}</flux:callout.text>
        </flux:callout>
    @endif

    <form wire:submit="sendResetLink" class="space-y-4">
        <flux:field>
            <flux:label>{{ __('auth.email') }}</flux:label>
            <flux:input type="email" wire:model.blur="email" autocomplete="email" icon="envelope" autofocus />
            <flux:error name="email" />
        </flux:field>

        <flux:button type="submit" variant="primary" class="w-full data-loading:opacity-70">
            <span wire:loading.remove wire:target="sendResetLink">{{ __('auth.forgot.submit') }}</span>
            <span wire:loading wire:target="sendResetLink">{{ __('account.actions.sending') }}</span>
        </flux:button>
    </form>

    <flux:text class="text-center text-sm text-zinc-500">
        <flux:link href="{{ route('auth.login') }}" wire:navigate class="font-medium">{{ __('auth.forgot.back_to_login') }}</flux:link>
    </flux:text>
</flux:card>
