<flux:card class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('auth.login.heading') }}</flux:heading>
        <flux:text class="mt-1 text-zinc-500">{{ __('auth.login.helper') }}</flux:text>
    </div>

    <form wire:submit="login" class="space-y-4">
        <flux:field>
            <flux:label>{{ __('auth.email') }}</flux:label>
            <flux:input type="email" wire:model.blur="email" autocomplete="email" icon="envelope" autofocus />
            <flux:error name="email" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('auth.password_label') }}</flux:label>
            <flux:input type="password" wire:model.blur="password" autocomplete="current-password" icon="lock-closed" />
            <flux:error name="password" />
        </flux:field>

        <div class="flex items-center justify-between gap-3">
            <flux:checkbox wire:model.change="remember" label="{{ __('auth.login.remember') }}" />
            <flux:link href="{{ route('auth.forgot-password') }}" wire:navigate class="text-sm">
                {{ __('auth.login.forgot_password') }}
            </flux:link>
        </div>

        <flux:button type="submit" variant="primary" class="w-full data-loading:opacity-70">
            <span wire:loading.remove wire:target="login">{{ __('auth.login.submit') }}</span>
            <span wire:loading wire:target="login">{{ __('account.actions.signing_in') }}</span>
        </flux:button>
    </form>

    <flux:separator :text="__('auth.or')" />

    <flux:text class="text-center text-sm text-zinc-500">
        {{ __('auth.login.no_account') }}
        <flux:link href="{{ route('auth.register') }}" wire:navigate class="font-medium">{{ __('auth.login.create_account') }}</flux:link>
    </flux:text>
</flux:card>
