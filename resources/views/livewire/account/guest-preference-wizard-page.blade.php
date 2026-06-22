<x-ui.page>
    <section class="space-y-2">
        <flux:heading size="xl" level="1">{{ __('preferences.wizard.heading') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('preferences.wizard.helper') }}</flux:text>
    </section>

    <flux:card class="space-y-4">
        <div class="flex flex-wrap gap-2">
            @foreach(range(1, 4) as $number)
                <flux:badge :color="$step === $number ? 'green' : 'zinc'" size="sm">
                    {{ __('preferences.wizard.steps.'.$number) }}
                </flux:badge>
            @endforeach
        </div>

        @include('livewire.account.partials.guest-preference-section', [
            'section' => match ($step) {
                1 => 'budget',
                2 => 'place',
                3 => 'comfort',
                default => 'lifestyle',
            },
        ])
    </flux:card>

    <div class="sticky bottom-20 z-10 rounded-xl border border-zinc-200 bg-white/95 p-3 shadow-lg backdrop-blur dark:border-zinc-700 dark:bg-zinc-950/95 lg:static lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
        <div class="grid grid-cols-2 gap-3">
            <flux:button type="button" variant="ghost" wire:click="previousStep" :disabled="$step === 1">
                {{ __('preferences.actions.back') }}
            </flux:button>

            @if($step < 4)
                <flux:button type="button" variant="primary" wire:click="nextStep" class="data-loading:opacity-70">
                    <span wire:loading.remove wire:target="nextStep">{{ __('preferences.actions.next') }}</span>
                    <span wire:loading wire:target="nextStep">{{ __('account.actions.saving') }}</span>
                </flux:button>
            @else
                <flux:button type="button" variant="primary" wire:click="save" class="data-loading:opacity-70">
                    <span wire:loading.remove wire:target="save">{{ __('preferences.actions.save') }}</span>
                    <span wire:loading wire:target="save">{{ __('account.actions.saving') }}</span>
                </flux:button>
            @endif
        </div>
    </div>
</x-ui.page>
