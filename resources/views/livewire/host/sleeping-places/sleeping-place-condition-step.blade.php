<form wire:submit="save" class="space-y-5">
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">{{ __('sleeping_place.steps.condition.title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('sleeping_place.steps.condition.helper') }}</flux:text>
        </div>

        @if($wasSaved)
            <flux:callout color="emerald" icon="check-circle">
                <flux:callout.text>{{ __('sleeping_place.messages.saved') }}</flux:callout.text>
            </flux:callout>
        @endif

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach(['conditionState', 'frameCondition', 'mattressCondition', 'beddingCondition', 'curtainCondition', 'lampCondition', 'socketCondition', 'lockerCondition'] as $field)
                <flux:field>
                    <flux:label>{{ __('sleeping_place.fields.'.\Illuminate\Support\Str::snake($field)) }}</flux:label>
                    <flux:input wire:model.blur="{{ $field }}" />
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach(['lastCleanedAt', 'lastBeddingChangedAt', 'lastCheckedAt'] as $field)
                <flux:field>
                    <flux:label>{{ __('sleeping_place.fields.'.\Illuminate\Support\Str::snake($field)) }}</flux:label>
                    <flux:input type="date" wire:model.change="{{ $field }}" />
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            @foreach(['hasDamage', 'hasStains', 'hasSmell', 'squeaks', 'needsRepair', 'needsMattressReplacement'] as $field)
                <flux:checkbox wire:model.change="{{ $field }}" label="{{ __('sleeping_place.fields.'.\Illuminate\Support\Str::snake($field)) }}" />
            @endforeach
        </div>

        <flux:field>
            <flux:label>{{ __('sleeping_place.fields.host_condition_note') }}</flux:label>
            <flux:textarea rows="3" wire:model.blur="hostConditionNote" />
            <flux:error name="hostConditionNote" />
        </flux:field>
    </flux:card>

    <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled">
        <span wire:loading.remove wire:target="save">{{ __('sleeping_place.actions.save_step') }}</span>
        <span wire:loading wire:target="save">{{ __('sleeping_place.messages.saving') }}</span>
    </flux:button>
</form>
