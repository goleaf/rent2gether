<flux:card class="space-y-4">
    <flux:heading size="sm">{{ __('pricing.sections.promo_codes') }}</flux:heading>

    <div class="grid gap-3 sm:grid-cols-3">
        <flux:field>
            <flux:label>{{ __('pricing.fields.promo_code') }}</flux:label>
            <flux:input wire:model.blur="code" />
            <flux:error name="code" />
        </flux:field>
        <flux:field>
            <flux:label>{{ __('pricing.fields.name') }}</flux:label>
            <flux:input wire:model.blur="name" />
            <flux:error name="name" />
        </flux:field>
        <flux:field>
            <flux:label>{{ __('pricing.fields.discount_amount') }}</flux:label>
            <flux:input type="number" step="0.01" wire:model.blur="value" />
            <flux:error name="value" />
        </flux:field>
    </div>

    <div class="flex justify-end">
        <flux:button variant="primary" wire:click="save" wire:loading.attr="disabled">
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
                <flux:badge size="sm">{{ $code['status'] }}</flux:badge>
            </div>
        @empty
            <flux:callout color="zinc">
                <flux:callout.heading>{{ __('pricing.empty.promo_codes') }}</flux:callout.heading>
            </flux:callout>
        @endforelse
    </div>

    @if ($savedMessageKey)
        <flux:callout color="green">
            <flux:callout.heading>{{ __($savedMessageKey) }}</flux:callout.heading>
        </flux:callout>
    @endif
</flux:card>
