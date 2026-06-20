<form wire:submit="save" class="space-y-5">
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">{{ __('room.steps.comfort.title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('room.steps.comfort.helper') }}</flux:text>
        </div>

        @if($wasSaved)
            <flux:callout color="emerald" icon="check-circle">
                <flux:callout.text>{{ __('room.messages.saved') }}</flux:callout.text>
            </flux:callout>
        @endif

        <div class="grid gap-3 sm:grid-cols-2">
            @foreach(['hasHeating', 'hasAirConditioning', 'hasFan', 'canOpenWindow', 'canCloseWindow', 'hasCurtains', 'hasBlackoutCurtains', 'canTurnLightAtNight', 'canUsePersonalLampAtNight', 'quietHoursEnabled'] as $field)
                <flux:checkbox wire:model.change="{{ $field }}" label="{{ __('room.fields.'.\Illuminate\Support\Str::snake($field)) }}" />
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach(['winterTemperatureLevel', 'summerTemperatureLevel', 'ventilationLevel', 'lightLevel', 'noiseLevel', 'soundproofingLevel'] as $field)
                <flux:field>
                    <flux:label>{{ __('room.fields.'.\Illuminate\Support\Str::snake($field)) }}</flux:label>
                    <flux:select wire:model.change="{{ $field }}">
                        <flux:select.option value="">{{ __('room.options.not_specified') }}</flux:select.option>
                        @foreach(['none', 'low', 'moderate', 'normal', 'good', 'high', 'quiet', 'bright', 'warm'] as $level)
                            <flux:select.option value="{{ $level }}">{{ __('room.levels.'.$level) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach(['quietHoursStart', 'quietHoursEnd'] as $field)
                <flux:field>
                    <flux:label>{{ __('room.fields.'.\Illuminate\Support\Str::snake($field)) }}</flux:label>
                    <flux:input type="time" wire:model.change="{{ $field }}" />
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
