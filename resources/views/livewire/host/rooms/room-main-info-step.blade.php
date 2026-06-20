<form wire:submit="save" class="space-y-5">
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">{{ __('room.steps.main.title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('room.steps.main.helper') }}</flux:text>
        </div>

        @if($wasSaved)
            <flux:callout color="emerald" icon="check-circle">
                <flux:callout.text>{{ __('room.messages.saved') }}</flux:callout.text>
            </flux:callout>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach(['titleEn', 'titleRu', 'shortDescriptionEn', 'shortDescriptionRu'] as $field)
                <flux:field>
                    <flux:label>{{ __('room.fields.'.\Illuminate\Support\Str::snake($field)) }}</flux:label>
                    <flux:input wire:model.blur="{{ $field }}" />
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach(['fullDescriptionEn', 'fullDescriptionRu'] as $field)
                <flux:field>
                    <flux:label>{{ __('room.fields.'.\Illuminate\Support\Str::snake($field)) }}</flux:label>
                    <flux:textarea rows="4" wire:model.blur="{{ $field }}" />
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach(['roomNumber', 'internalName'] as $field)
                <flux:field>
                    <flux:label>{{ __('room.fields.'.\Illuminate\Support\Str::snake($field)) }}</flux:label>
                    <flux:input wire:model.blur="{{ $field }}" />
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <flux:field>
                <flux:label>{{ __('room.fields.room_type') }}</flux:label>
                <flux:select wire:model.change="roomType">
                    @foreach(\App\Enums\RoomType::cases() as $type)
                        <flux:select.option value="{{ $type->value }}">{{ __('room.room_types.'.$type->value) }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="roomType" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('room.fields.gender_policy') }}</flux:label>
                <flux:select wire:model.change="genderPolicy">
                    @foreach(\App\Enums\GenderType::cases() as $gender)
                        <flux:select.option value="{{ $gender->value }}">{{ __('room.gender_policies.'.$gender->value) }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="genderPolicy" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('room.fields.living_format') }}</flux:label>
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
                <flux:label>{{ __('room.fields.sleeping_places_count') }}</flux:label>
                <flux:input type="number" inputmode="numeric" wire:model.blur="sleepingPlacesCount" />
                <flux:error name="sleepingPlacesCount" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('room.fields.max_guests') }}</flux:label>
                <flux:input type="number" inputmode="numeric" wire:model.blur="maxGuests" />
                <flux:error name="maxGuests" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('room.fields.status') }}</flux:label>
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
                <flux:checkbox wire:model.change="{{ $field }}" label="{{ __('room.fields.'.\Illuminate\Support\Str::snake($field)) }}" />
            @endforeach
        </div>
    </flux:card>

    <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled">
        <span wire:loading.remove wire:target="save">{{ __('room.actions.save_step') }}</span>
        <span wire:loading wire:target="save">{{ __('room.messages.saving') }}</span>
    </flux:button>
</form>
