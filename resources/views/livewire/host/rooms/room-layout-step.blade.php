<form wire:submit="save" class="space-y-5">
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">{{ __('room.steps.layout.title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('room.steps.layout.helper') }}</flux:text>
        </div>

        @if($wasSaved)
            <flux:callout color="emerald" icon="check-circle">
                <flux:callout.text>{{ __('room.messages.saved') }}</flux:callout.text>
            </flux:callout>
        @endif

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach(['area', 'lengthMeters', 'widthMeters', 'ceilingHeightMeters', 'windowsCount'] as $field)
                <flux:field>
                    <flux:label>{{ __('room.fields.'.\Illuminate\Support\Str::snake($field)) }}</flux:label>
                    <flux:input type="number" step="0.01" inputmode="decimal" wire:model.blur="{{ $field }}" />
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <flux:field>
                <flux:label>{{ __('room.fields.window_size') }}</flux:label>
                <flux:input wire:model.blur="windowSize" />
                <flux:error name="windowSize" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('room.fields.window_view') }}</flux:label>
                <flux:input wire:model.blur="windowView" />
                <flux:error name="windowView" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('room.fields.cardinal_direction') }}</flux:label>
                <flux:select wire:model.change="cardinalDirection">
                    <flux:select.option value="">{{ __('room.options.not_specified') }}</flux:select.option>
                    @foreach(['north', 'south', 'east', 'west'] as $direction)
                        <flux:select.option value="{{ $direction }}">{{ __('room.directions.'.$direction) }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="cardinalDirection" />
            </flux:field>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            @foreach(['hasBalcony', 'balconyAccessible', 'hasFreePassageSpace', 'narrowPassages'] as $field)
                <flux:checkbox wire:model.change="{{ $field }}" label="{{ __('room.fields.'.\Illuminate\Support\Str::snake($field)) }}" />
            @endforeach
        </div>
    </flux:card>

    <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled">
        <span wire:loading.remove wire:target="save">{{ __('room.actions.save_step') }}</span>
        <span wire:loading wire:target="save">{{ __('room.messages.saving') }}</span>
    </flux:button>
</form>
