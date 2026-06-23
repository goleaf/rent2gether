<flux:card class="space-y-4">
    <flux:heading size="sm">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('pricing.sections.promo_codes') }}</span>
        </span>
    </flux:heading>

    <div class="grid gap-3 sm:grid-cols-3">
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('pricing.fields.promo_code') }}</span>
    </span>
</flux:label>
            <flux:input wire:model.blur="code" icon="tag" />
            <flux:error name="code" />
        </flux:field>
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('pricing.fields.name') }}</span>
    </span>
</flux:label>
            <flux:input wire:model.blur="name" icon="user" />
            <flux:error name="name" />
        </flux:field>
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('pricing.fields.discount_amount') }}</span>
    </span>
</flux:label>
            <flux:input type="number" step="0.01" wire:model.blur="value" icon="numbered-list" />
            <flux:error name="value" />
        </flux:field>
    </div>

    <div class="flex justify-end">
        <flux:button variant="primary" wire:click="save" wire:loading.attr="disabled" icon="check">
            {{ __('pricing.actions.create_promo') }}
        </flux:button>
    </div>

    <div class="space-y-2">
        @forelse ($codes as $code)
            <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div>
                    <flux:text size="sm">{{ $code['code'] }}</flux:text>
                    <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400">{{ $code['name'] }}</flux:text>
                </div>
                <flux:badge size="sm" icon="user">{{ $code['status'] }}</flux:badge>
            </div>
        @empty
            <flux:callout color="zinc" icon="information-circle">
                <flux:callout.heading icon="banknotes" icon:variant="mini">{{ __('pricing.empty.promo_codes') }}</flux:callout.heading>
            </flux:callout>
        @endforelse
    </div>

    @if ($savedMessageKey)
        <flux:callout color="green" icon="check-circle">
            <flux:callout.heading icon="check-circle" icon:variant="mini">{{ __($savedMessageKey) }}</flux:callout.heading>
        </flux:callout>
    @endif
</flux:card>
