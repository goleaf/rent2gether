<form wire:submit="save" class="space-y-5">
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('room.steps.access_storage.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('room.steps.access_storage.helper') }}</flux:text>
        </div>

        @if($wasSaved)
            <flux:callout color="emerald" icon="chat-bubble-left-right">
                <flux:callout.text>{{ __('room.messages.saved') }}</flux:callout.text>
            </flux:callout>
        @endif

        <div class="grid gap-3 sm:grid-cols-2">
            @foreach(['hasDoor', 'hasLock', 'hasKey', 'keyGivenToGuest', 'canLockFromInside', 'canLockFromOutside', 'hasWardrobe', 'hasSharedWardrobe', 'hasPersonalLockers', 'lockersHaveLocks', 'hasLuggageSpace', 'hasDesk', 'hasChairs', 'hasMirror', 'canStoreFood'] as $field)
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="{{ $field }}" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('room.fields.'.\Illuminate\Support\Str::snake($field)) }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cube" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('room.fields.personal_lockers_count') }}</span>
    </span>
</flux:label>
                <flux:input type="number" inputmode="numeric" wire:model.blur="personalLockersCount" icon="numbered-list" />
                <flux:error name="personalLockersCount" />
            </flux:field>
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('room.fields.chairs_count') }}</span>
    </span>
</flux:label>
                <flux:input type="number" inputmode="numeric" wire:model.blur="chairsCount" icon="numbered-list" />
                <flux:error name="chairsCount" />
            </flux:field>
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('room.fields.privacy_level') }}</span>
    </span>
</flux:label>
                <flux:select wire:model.change="privacyLevel">
                    <flux:select.option value="">{{ __('room.options.not_specified') }}</flux:select.option>
                    @foreach(['shared', 'moderate', 'private'] as $level)
                        <flux:select.option value="{{ $level }}">{{ __('room.levels.'.$level) }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="privacyLevel" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('room.fields.food_storage_allowed_type') }}</span>
    </span>
</flux:label>
            <flux:select wire:model.change="foodStorageAllowedType">
                <flux:select.option value="">{{ __('room.options.not_specified') }}</flux:select.option>
                @foreach(['none', 'dry_food_only', 'kitchen_only', 'small_snacks'] as $type)
                    <flux:select.option value="{{ $type }}">{{ __('room.food_storage.'.$type) }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="foodStorageAllowedType" />
        </flux:field>
    </flux:card>

    <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled" icon="chat-bubble-left-right">
        <span wire:loading.remove wire:target="save">{{ __('room.actions.save_step') }}</span>
        <span wire:loading wire:target="save">{{ __('room.messages.saving') }}</span>
    </flux:button>
</form>
