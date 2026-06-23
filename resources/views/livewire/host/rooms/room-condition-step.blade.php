<form wire:submit="save" class="space-y-5">
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="wrench-screwdriver" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('room.steps.condition.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('room.steps.condition.helper') }}</flux:text>
        </div>

        @if($wasSaved)
            <flux:callout color="emerald" icon="chat-bubble-left-right">
                <flux:callout.text>{{ __('room.messages.saved') }}</flux:callout.text>
            </flux:callout>
        @endif

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach(['conditionState', 'repairState', 'cleanlinessLevel', 'floorCondition', 'wallsCondition', 'ceilingCondition', 'windowCondition', 'doorCondition', 'lockCondition', 'furnitureCondition'] as $field)
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="wrench-screwdriver" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('room.fields.'.\Illuminate\Support\Str::snake($field)) }}</span>
    </span>
</flux:label>
                    <flux:select wire:model.change="{{ $field }}">
                        <flux:select.option value="">{{ __('room.options.not_specified') }}</flux:select.option>
                        @foreach(['bad', 'basic', 'normal', 'good', 'high', 'needs_update'] as $level)
                            <flux:select.option value="{{ $level }}">{{ __('room.levels.'.$level) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            @foreach(['hasDust', 'hasBadSmell', 'hasDampMarks', 'hasMold', 'hasInsects', 'hasDamage', 'needsRepair', 'recentlyRenovated'] as $field)
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="{{ $field }}" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('room.fields.'.\Illuminate\Support\Str::snake($field)) }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach(['lastCleanedAt', 'lastCheckedAt', 'lastRepairedAt'] as $field)
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="wrench-screwdriver" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('room.fields.'.\Illuminate\Support\Str::snake($field)) }}</span>
    </span>
</flux:label>
                    <flux:input type="date" wire:model.change="{{ $field }}" icon="calendar-days" />
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>
    </flux:card>

    <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled" icon="chat-bubble-left-right">
        <span wire:loading.remove wire:target="save">{{ __('room.actions.save_step') }}</span>
        <span wire:loading wire:target="save">{{ __('room.messages.saving') }}</span>
    </flux:button>
</form>
