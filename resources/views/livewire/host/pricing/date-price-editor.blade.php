<flux:card class="space-y-4">
    <flux:heading size="sm">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('pricing.sections.date_prices') }}</span>
        </span>
    </flux:heading>

    <div class="grid gap-3 sm:grid-cols-3">
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('pricing.fields.date') }}</span>
    </span>
</flux:label>
            <flux:input type="date" wire:model.blur="date" icon="calendar-days" />
            <flux:error name="date" />
        </flux:field>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('pricing.fields.date_override_price') }}</span>
    </span>
</flux:label>
            <flux:input type="number" step="0.01" wire:model.blur="price" icon="banknotes" />
            <flux:error name="price" />
        </flux:field>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('pricing.fields.price_type') }}</span>
    </span>
</flux:label>
            <flux:input wire:model.blur="priceType" icon="banknotes" />
            <flux:error name="priceType" />
        </flux:field>
    </div>

    <div class="flex justify-end">
        <flux:button variant="primary" wire:click="save" wire:loading.attr="disabled" icon="calendar-days">
            {{ __('pricing.actions.add_date_price') }}
        </flux:button>
    </div>

    <div class="space-y-2">
        @forelse ($prices as $price)
            <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div>
                    <flux:text size="sm">{{ $price['date'] }}</flux:text>
                    <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400">{{ $price['type'] }}</flux:text>
                </div>
                <flux:text size="sm" class="font-medium">{{ $price['price'] }}</flux:text>
            </div>
        @empty
            <flux:callout color="zinc" icon="information-circle">
                <flux:callout.heading icon="banknotes" icon:variant="mini">{{ __('pricing.empty.date_prices') }}</flux:callout.heading>
            </flux:callout>
        @endforelse
    </div>

    @if ($savedMessageKey)
        <flux:callout color="green" icon="check-circle">
            <flux:callout.heading icon="check-circle" icon:variant="mini">{{ __($savedMessageKey) }}</flux:callout.heading>
        </flux:callout>
    @endif
</flux:card>
