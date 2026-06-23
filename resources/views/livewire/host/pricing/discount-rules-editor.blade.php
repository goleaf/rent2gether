<flux:card class="space-y-4">
    <flux:heading size="sm">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('pricing.sections.discount_rules') }}</span>
        </span>
    </flux:heading>

    <div class="grid gap-3 sm:grid-cols-4">
        <flux:field>
            <flux:label>{{ __('pricing.fields.discount_type') }}</flux:label>
            <flux:input wire:model.blur="discountType" icon="pencil-square" />
            <flux:error name="discountType" />
        </flux:field>
        <flux:field>
            <flux:label>{{ __('pricing.fields.name') }}</flux:label>
            <flux:input wire:model.blur="name" icon="user" />
            <flux:error name="name" />
        </flux:field>
        <flux:field>
            <flux:label>{{ __('pricing.fields.discount_amount') }}</flux:label>
            <flux:input type="number" step="0.01" wire:model.blur="value" icon="numbered-list" />
            <flux:error name="value" />
        </flux:field>
        <flux:field>
            <flux:label>{{ __('pricing.fields.min_nights') }}</flux:label>
            <flux:input type="number" wire:model.blur="minNights" icon="numbered-list" />
            <flux:error name="minNights" />
        </flux:field>
    </div>

    <flux:switch wire:model.change="allowStacking" label="{{ __('pricing.fields.allow_stacking') }}" />

    <div class="flex justify-end">
        <flux:button variant="primary" wire:click="save" wire:loading.attr="disabled" icon="check">
            {{ __('pricing.actions.add_discount_rule') }}
        </flux:button>
    </div>

    <div class="space-y-2">
        @forelse ($rules as $rule)
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-3">
                    <flux:text size="sm">{{ $rule['name'] }}</flux:text>
                    <flux:badge size="sm" icon="user">{{ $rule['type'] }}</flux:badge>
                </div>
                <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400">
                    {{ __('pricing.messages.discount_rule_summary', ['value' => $rule['value'], 'nights' => $rule['min_nights']]) }}
                </flux:text>
            </div>
        @empty
            <flux:callout color="zinc" icon="information-circle">
                <flux:callout.heading>{{ __('pricing.empty.discount_rules') }}</flux:callout.heading>
            </flux:callout>
        @endforelse
    </div>

    @if ($savedMessageKey)
        <flux:callout color="green" icon="check-circle">
            <flux:callout.heading>{{ __($savedMessageKey) }}</flux:callout.heading>
        </flux:callout>
    @endif
</flux:card>
