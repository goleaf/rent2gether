<flux:card class="space-y-6">
    <div>
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="shield-check" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('auth.forgot.heading') }}</span>
            </span>
        </flux:heading>
        <flux:text class="mt-1 text-zinc-500">{{ __('auth.forgot.helper') }}</flux:text>
    </div>

    @if($statusMessage)
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ $statusMessage }}</flux:callout.text>
        </flux:callout>
    @endif

    <form wire:submit="sendResetLink" class="space-y-4">
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

        <flux:button type="submit" variant="primary" class="w-full data-loading:opacity-70" icon="paper-airplane">
            <span wire:loading.remove wire:target="sendResetLink">{{ __('auth.forgot.submit') }}</span>
            <span wire:loading wire:target="sendResetLink">{{ __('account.actions.sending') }}</span>
        </flux:button>
    </form>

    <flux:text class="text-center text-sm text-zinc-500">
        <flux:link href="{{ route('auth.login') }}" wire:navigate class="font-medium">{{ __('auth.forgot.back_to_login') }}</flux:link>
    </flux:text>
</flux:card>
