<div class="mx-auto max-w-2xl space-y-5">
    <section class="space-y-2">
        <flux:heading size="xl" level="1">{{ __('account.security.heading') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('account.security.helper') }}</flux:text>
    </section>

    @if(session('success'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ session('success') }}</flux:callout.text>
        </flux:callout>
    @endif

    <flux:card class="space-y-4">
        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
            <flux:heading size="sm">{{ __('account.security.empty_title') }}</flux:heading>
            <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-400">
                {{ __('account.security.empty_text') }}
            </flux:text>
        </div>

        <form wire:submit="save" class="space-y-4">
            <flux:field>
                <flux:label>{{ __('account.security.current_password') }}</flux:label>
                <flux:input type="password" wire:model.blur="currentPassword" autocomplete="current-password" />
                <flux:error name="currentPassword" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('account.security.new_password') }}</flux:label>
                <flux:input type="password" wire:model.blur="password" autocomplete="new-password" />
                <flux:error name="password" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('account.security.confirm_password') }}</flux:label>
                <flux:input type="password" wire:model.blur="passwordConfirmation" autocomplete="new-password" />
                <flux:error name="passwordConfirmation" />
            </flux:field>

            <flux:button type="submit" variant="primary" class="w-full data-loading:opacity-70">
                <span wire:loading.remove wire:target="save">{{ __('account.security.save') }}</span>
                <span wire:loading wire:target="save">{{ __('account.actions.saving') }}</span>
            </flux:button>
        </form>
    </flux:card>
</div>
