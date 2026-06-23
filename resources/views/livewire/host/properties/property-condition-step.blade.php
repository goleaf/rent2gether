<form wire:submit="save" class="space-y-5">
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="wrench-screwdriver" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('property.steps.condition.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('property.steps.condition.helper') }}</flux:text>
        </div>

        @if($wasSaved)
            <flux:callout color="emerald" icon="chat-bubble-left-right">
                <flux:callout.text>{{ __('property.messages.saved') }}</flux:callout.text>
            </flux:callout>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach(['repairState', 'cleanlinessLevel', 'smellLevel', 'humidityLevel', 'winterTemperatureLevel', 'summerTemperatureLevel', 'indoorNoiseLevel', 'lightLevel'] as $field)
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="wrench-screwdriver" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('property.fields.'.\Illuminate\Support\Str::snake($field)) }}</span>
    </span>
</flux:label>
                    <flux:select wire:model.change="{{ $field }}">
                        <flux:select.option value="">{{ __('property.options.not_specified') }}</flux:select.option>
                        @foreach(['low', 'moderate', 'good', 'high', 'normal'] as $level)
                            <flux:select.option value="{{ $level }}">{{ __('property.levels.'.$level) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>

        <div class="space-y-3">
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="hasHeating" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.has_heating') }}</span>
                    </span>
                </flux:label>
                <flux:error name="hasHeating" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="hasAirConditioning" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.has_air_conditioning') }}</span>
                    </span>
                </flux:label>
                <flux:error name="hasAirConditioning" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="hasHotWater" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.has_hot_water') }}</span>
                    </span>
                </flux:label>
                <flux:error name="hasHotWater" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="hasInsects" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.has_insects') }}</span>
                    </span>
                </flux:label>
                <flux:error name="hasInsects" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="hasMold" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.has_mold') }}</span>
                    </span>
                </flux:label>
                <flux:error name="hasMold" />
            </flux:field>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach(['furnitureCondition', 'plumbingCondition', 'kitchenCondition', 'floorCondition', 'wallsCondition'] as $field)
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="wrench-screwdriver" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('property.fields.'.\Illuminate\Support\Str::snake($field)) }}</span>
    </span>
</flux:label>
                    <flux:input wire:model.blur="{{ $field }}" icon="pencil-square" />
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach(['lastCleanedAt', 'lastRepairedAt', 'lastCheckedAt'] as $field)
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="wrench-screwdriver" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('property.fields.'.\Illuminate\Support\Str::snake($field)) }}</span>
    </span>
</flux:label>
                    <flux:input type="date" wire:model.change="{{ $field }}" icon="calendar-days" />
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>
    </flux:card>

    <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled" icon="chat-bubble-left-right">
        <span wire:loading.remove wire:target="save">{{ __('property.actions.save_step') }}</span>
        <span wire:loading wire:target="save">{{ __('property.messages.saving') }}</span>
    </flux:button>
</form>
