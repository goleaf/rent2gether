<form wire:submit="save" class="space-y-5">
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">{{ __('sleeping_place.steps.storage.title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('sleeping_place.steps.storage.helper') }}</flux:text>
        </div>

        @if($wasSaved)
            <flux:callout color="emerald" icon="check-circle">
                <flux:callout.text>{{ __('sleeping_place.messages.saved') }}</flux:callout.text>
            </flux:callout>
        @endif

        <flux:field>
            <flux:label>{{ __('sleeping_place.fields.locker_size') }}</flux:label>
            <flux:input wire:model.blur="lockerSize" />
            <flux:error name="lockerSize" />
        </flux:field>

        <div class="grid gap-3 sm:grid-cols-2">
            @foreach(['hasShoeSpace', 'hasLuggageSpace', 'hasBackpackSpace', 'hasPersonalLocker', 'lockerHasLock', 'lockProvided', 'guestShouldBringLock', 'canStoreValuables', 'canStoreDocuments', 'canStoreLaptop'] as $field)
                <flux:checkbox wire:model.change="{{ $field }}" label="{{ __('sleeping_place.fields.'.\Illuminate\Support\Str::snake($field)) }}" />
            @endforeach
        </div>
    </flux:card>

    <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled">
        <span wire:loading.remove wire:target="save">{{ __('sleeping_place.actions.save_step') }}</span>
        <span wire:loading wire:target="save">{{ __('sleeping_place.messages.saving') }}</span>
    </flux:button>
</form>
