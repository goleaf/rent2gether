<x-ui.page>
    <section class="space-y-2">
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="shield-check" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('account.security.heading') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('account.security.helper') }}</flux:text>
    </section>

    @if(session('success'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ session('success') }}</flux:callout.text>
        </flux:callout>
    @endif

    <flux:card class="space-y-4">
        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('account.security.empty_title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-400">
                {{ __('account.security.empty_text') }}
            </flux:text>
        </div>

        <form wire:submit="save" class="space-y-4">
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('account.security.current_password') }}</span>
    </span>
</flux:label>
                <flux:input type="password" wire:model.blur="currentPassword" autocomplete="current-password" icon="lock-closed" />
                <flux:error name="currentPassword" />
            </flux:field>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('account.security.new_password') }}</span>
    </span>
</flux:label>
                <flux:input type="password" wire:model.blur="password" autocomplete="new-password" icon="lock-closed" />
                <flux:error name="password" />
            </flux:field>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('account.security.confirm_password') }}</span>
    </span>
</flux:label>
                <flux:input type="password" wire:model.blur="passwordConfirmation" autocomplete="new-password" icon="lock-closed" />
                <flux:error name="passwordConfirmation" />
            </flux:field>

            <flux:button type="submit" variant="primary" class="w-full data-loading:opacity-70" icon="check">
                <span wire:loading.remove wire:target="save">{{ __('account.security.save') }}</span>
                <span wire:loading wire:target="save">{{ __('account.actions.saving') }}</span>
            </flux:button>
        </form>
    </flux:card>
</x-ui.page>
