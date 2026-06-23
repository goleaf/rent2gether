<x-ui.page>
    <section class="space-y-2">
        <flux:badge color="emerald" icon="check-circle">{{ __('host.profile.onboarding.eyebrow') }}</flux:badge>
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="cog-6-tooth" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('host.profile.onboarding.heading') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('host.profile.onboarding.helper') }}</flux:text>
    </section>

    @if(session('success'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ session('success') }}</flux:callout.text>
        </flux:callout>
    @endif

    <div class="flex flex-wrap gap-2">
        @foreach(range(1, 3) as $number)
            <flux:badge :color="$step === $number ? 'green' : 'zinc'" size="sm" icon="check-circle">
                {{ __('host.profile.onboarding.steps.'.$number) }}
            </flux:badge>
        @endforeach
    </div>

    <form wire:submit="save" class="space-y-5">
        <flux:card class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host.profile.sections.'.match ($step) {
                        1 => 'identity',
                        2 => 'style',
                        default => 'defaults',
                    }) }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-500">
                    {{ __('host.profile.onboarding.step_helpers.'.$step) }}
                </flux:text>
            </div>

            @include('livewire.host.partials.host-profile-section', [
                'section' => match ($step) {
                    1 => 'identity',
                    2 => 'style',
                    default => 'defaults',
                },
            ])
        </flux:card>

        @if($step === 3)
            <flux:card class="space-y-3">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="wrench-screwdriver" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host.profile.checklist.title') }}</span>
                    </span>
                </flux:heading>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach($this->readinessChecklist() as $item)
                        <div class="flex items-center justify-between gap-2 rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                            <span>{{ $item['label'] }}</span>
                            <flux:badge :color="$item['done'] ? 'green' : 'zinc'" size="sm" icon="check-circle">
                                {{ $item['done'] ? __('host.profile.checklist.done') : __('host.profile.checklist.later') }}
                            </flux:badge>
                        </div>
                    @endforeach
                </div>
                <flux:text size="sm" class="text-zinc-500">{{ __('host.profile.checklist.payout_helper') }}</flux:text>
            </flux:card>
        @endif

        <div class="sticky bottom-20 z-10 rounded-xl border border-zinc-200 bg-white/95 p-3 shadow-lg backdrop-blur dark:border-zinc-700 dark:bg-zinc-950/95 lg:static lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
            <div class="grid grid-cols-2 gap-3">
                <flux:button type="button" variant="ghost" wire:click="previousStep" :disabled="$step === 1" icon="arrow-left">
                    {{ __('host.profile.actions.back') }}
                </flux:button>

                @if($step < 3)
                    <flux:button type="button" variant="primary" wire:click="nextStep" class="data-loading:opacity-70" icon="arrow-right">
                        <span wire:loading.remove wire:target="nextStep">{{ __('host.profile.actions.next') }}</span>
                        <span wire:loading wire:target="nextStep">{{ __('account.actions.saving') }}</span>
                    </flux:button>
                @else
                    <flux:button type="submit" variant="primary" class="data-loading:opacity-70" icon="check">
                        <span wire:loading.remove wire:target="save">{{ __('host.profile.actions.finish') }}</span>
                        <span wire:loading wire:target="save">{{ __('account.actions.saving') }}</span>
                    </flux:button>
                @endif
            </div>
        </div>
    </form>
</x-ui.page>
