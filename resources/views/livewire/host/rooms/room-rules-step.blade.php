<form wire:submit="save" class="space-y-5">
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">{{ __('room.steps.rules.title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('room.steps.rules.helper') }}</flux:text>
        </div>

        @if($wasSaved)
            <flux:callout color="emerald" icon="check-circle">
                <flux:callout.text>{{ __('room.messages.saved') }}</flux:callout.text>
            </flux:callout>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach(['roomRulesTextEn', 'roomRulesTextRu', 'quietHoursTextEn', 'quietHoursTextRu', 'foodRulesTextEn', 'foodRulesTextRu', 'conflictInstructionsEn', 'conflictInstructionsRu', 'specialNotesEn', 'specialNotesRu'] as $field)
                <flux:field>
                    <flux:label>{{ __('room.fields.'.\Illuminate\Support\Str::snake($field)) }}</flux:label>
                    <flux:textarea rows="4" wire:model.blur="{{ $field }}" />
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>
    </flux:card>

    <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled">
        <span wire:loading.remove wire:target="save">{{ __('room.actions.save_step') }}</span>
        <span wire:loading wire:target="save">{{ __('room.messages.saving') }}</span>
    </flux:button>
</form>
