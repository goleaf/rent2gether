<flux:card class="space-y-6">
    <div>
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="information-circle" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('auth.login.heading') }}</span>
            </span>
        </flux:heading>
        <flux:text class="mt-1 text-zinc-500">{{ __('auth.login.helper') }}</flux:text>
    </div>

    <form wire:submit="login" class="space-y-4">
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="bell" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('auth.email') }}</span>
    </span>
</flux:label>
            <flux:input type="email" wire:model.blur="email" autocomplete="email" icon="envelope" autofocus />
            <flux:error name="email" />
        </flux:field>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('auth.password_label') }}</span>
    </span>
</flux:label>
            <flux:input type="password" wire:model.blur="password" autocomplete="current-password" icon="lock-closed" />
            <flux:error name="password" />
        </flux:field>

        <div class="flex items-center justify-between gap-3">
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="remember" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('auth.login.remember') }}</span>
                    </span>
                </flux:label>
                <flux:error name="remember" />
            </flux:field>
            <flux:link href="{{ route('auth.forgot-password') }}" wire:navigate class="text-sm">
                {{ __('auth.login.forgot_password') }}
            </flux:link>
        </div>

        <flux:button type="submit" variant="primary" class="w-full data-loading:opacity-70" icon="arrow-right-end-on-rectangle">
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
