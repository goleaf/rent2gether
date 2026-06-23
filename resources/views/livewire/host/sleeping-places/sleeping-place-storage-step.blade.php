<form wire:submit="save" class="space-y-5">
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('sleeping_place.steps.storage.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('sleeping_place.steps.storage.helper') }}</flux:text>
        </div>

        @if($wasSaved)
            <flux:callout color="emerald" icon="chat-bubble-left-right">
                <flux:callout.text>{{ __('sleeping_place.messages.saved') }}</flux:callout.text>
            </flux:callout>
        @endif

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('sleeping_place.fields.locker_size') }}</span>
    </span>
</flux:label>
            <flux:input wire:model.blur="lockerSize" icon="pencil-square" />
            <flux:error name="lockerSize" />
        </flux:field>

        <div class="grid gap-3 sm:grid-cols-2">
            @foreach(['hasShoeSpace', 'hasLuggageSpace', 'hasBackpackSpace', 'hasPersonalLocker', 'lockerHasLock', 'lockProvided', 'guestShouldBringLock', 'canStoreValuables', 'canStoreDocuments', 'canStoreLaptop'] as $field)
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="{{ $field }}" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('sleeping_place.fields.'.\Illuminate\Support\Str::snake($field)) }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>
    </flux:card>

    <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled" icon="chat-bubble-left-right">
        <span wire:loading.remove wire:target="save">{{ __('sleeping_place.actions.save_step') }}</span>
        <span wire:loading wire:target="save">{{ __('sleeping_place.messages.saving') }}</span>
    </flux:button>
</form>
