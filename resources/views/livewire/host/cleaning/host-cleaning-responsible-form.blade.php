<section class="space-y-3">
    <flux:card class="space-y-2">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="wrench-screwdriver" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('cleaning.sections.responsible_sheet') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ __('cleaning.helpers.responsible_sheet') }}
        </flux:text>
    </flux:card>

    <div class="grid grid-cols-2 gap-2">
        @foreach (['host', 'host_representative', 'external_person', 'not_assigned'] as $type)
            <flux:button size="sm" variant="ghost" wire:click="$set('responsibleType', '{{ $type }}')" wire:loading.attr="disabled" icon="paint-brush">
                {{ __('cleaning.responsible_types.'.$type) }}
            </flux:button>
        @endforeach
    </div>
</section>
