<form wire:submit="save" class="space-y-5">
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('room.steps.main.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('room.steps.main.helper') }}</flux:text>
        </div>

        @if($wasSaved)
            <flux:callout color="emerald" icon="chat-bubble-left-right">
                <flux:callout.text>{{ __('room.messages.saved') }}</flux:callout.text>
            </flux:callout>
        @endif

        @foreach($this->contentLocales() as $locale)
            <div class="space-y-4 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="language" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ $locale['name'] }}</span>
                    </span>
                </flux:heading>
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="language" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('room.translation_fields.title', ['language' => $locale['name']]) }}</span>
    </span>
</flux:label>
                    <flux:input wire:model.blur="translations.{{ $locale['code'] }}.title" icon="language" />
                    <flux:error name="translations.{{ $locale['code'] }}.title" />
                </flux:field>
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="language" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('room.translation_fields.short_description', ['language' => $locale['name']]) }}</span>
    </span>
</flux:label>
                    <flux:input wire:model.blur="translations.{{ $locale['code'] }}.short_description" icon="language" />
                    <flux:error name="translations.{{ $locale['code'] }}.short_description" />
                </flux:field>
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="language" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('room.translation_fields.full_description', ['language' => $locale['name']]) }}</span>
    </span>
</flux:label>
                    <flux:textarea rows="4" wire:model.blur="translations.{{ $locale['code'] }}.full_description" />
                    <flux:error name="translations.{{ $locale['code'] }}.full_description" />
                </flux:field>
            </div>
        @endforeach

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach(['roomNumber', 'internalName'] as $field)
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('room.fields.'.\Illuminate\Support\Str::snake($field)) }}</span>
    </span>
</flux:label>
                    <flux:input wire:model.blur="{{ $field }}" icon="pencil-square" />
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('room.fields.room_type') }}</span>
    </span>
</flux:label>
                <flux:select wire:model.change="roomType">
                    @foreach(\App\Enums\RoomType::cases() as $type)
                        <flux:select.option value="{{ $type->value }}">{{ __('room.room_types.'.$type->value) }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="roomType" />
            </flux:field>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('room.fields.gender_policy') }}</span>
    </span>
</flux:label>
                <flux:select wire:model.change="genderPolicy">
                    @foreach(\App\Enums\GenderType::cases() as $gender)
                        <flux:select.option value="{{ $gender->value }}">{{ __('room.gender_policies.'.$gender->value) }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="genderPolicy" />
            </flux:field>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('room.fields.living_format') }}</span>
    </span>
</flux:label>
                <flux:select wire:model.change="livingFormat">
                    <flux:select.option value="">{{ __('room.options.not_specified') }}</flux:select.option>
                    @foreach(['long_stay', 'short_stay', 'student', 'worker', 'tourist', 'remote_work'] as $format)
                        <flux:select.option value="{{ $format }}">{{ __('room.living_formats.'.$format) }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="livingFormat" />
            </flux:field>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('room.fields.sleeping_places_count') }}</span>
    </span>
</flux:label>
                <flux:input type="number" inputmode="numeric" wire:model.blur="sleepingPlacesCount" icon="home-modern" />
                <flux:error name="sleepingPlacesCount" />
            </flux:field>
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('room.fields.max_guests') }}</span>
    </span>
</flux:label>
                <flux:input type="number" inputmode="numeric" wire:model.blur="maxGuests" icon="users" />
                <flux:error name="maxGuests" />
            </flux:field>
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('room.fields.status') }}</span>
    </span>
</flux:label>
                <flux:select wire:model.change="status">
                    @foreach(\App\Enums\RoomStatus::cases() as $roomStatus)
                        <flux:select.option value="{{ $roomStatus->value }}">{{ __('room.statuses.'.$roomStatus->value) }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="status" />
            </flux:field>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            @foreach(['isPrivate', 'isShared', 'isPassThrough', 'isForOnePerson', 'isForCouples', 'isForGroups', 'isForLongStay', 'isForShortStay', 'canBookEntireRoom', 'canBookIndividualPlaces'] as $field)
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
    </flux:card>

    <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled" icon="chat-bubble-left-right">
        <span wire:loading.remove wire:target="save">{{ __('room.actions.save_step') }}</span>
        <span wire:loading wire:target="save">{{ __('room.messages.saving') }}</span>
    </flux:button>
</form>
