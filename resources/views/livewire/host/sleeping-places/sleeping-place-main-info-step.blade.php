<form wire:submit="save" class="space-y-5">
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('sleeping_place.steps.main.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('sleeping_place.steps.main.helper') }}</flux:text>
        </div>

        @if($wasSaved)
            <flux:callout color="emerald" icon="chat-bubble-left-right">
                <flux:callout.text>{{ __('sleeping_place.messages.saved') }}</flux:callout.text>
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
                    <flux:label>{{ __('sleeping_place.translation_fields.title', ['language' => $locale['name']]) }}</flux:label>
                    <flux:input wire:model.blur="translations.{{ $locale['code'] }}.title" icon="language" />
                    <flux:error name="translations.{{ $locale['code'] }}.title" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('sleeping_place.translation_fields.short_description', ['language' => $locale['name']]) }}</flux:label>
                    <flux:textarea rows="3" wire:model.blur="translations.{{ $locale['code'] }}.short_description" />
                    <flux:error name="translations.{{ $locale['code'] }}.short_description" />
                </flux:field>
            </div>
        @endforeach

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach(['placeNumber', 'internalName'] as $field)
                <flux:field>
                    <flux:label>{{ __('sleeping_place.fields.'.\Illuminate\Support\Str::snake($field)) }}</flux:label>
                    <flux:input wire:model.blur="{{ $field }}" icon="pencil-square" />
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <flux:field>
                <flux:label>{{ __('sleeping_place.fields.sleeping_place_type') }}</flux:label>
                <flux:select wire:model.change="sleepingPlaceType">
                    @foreach(\App\Enums\SleepingPlaceType::cases() as $type)
                        <flux:select.option value="{{ $type->value }}">{{ $type->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="sleepingPlaceType" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('sleeping_place.fields.sleeping_place_subtype') }}</flux:label>
                <flux:input wire:model.blur="sleepingPlaceSubtype" icon="home-modern" />
                <flux:error name="sleepingPlaceSubtype" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('sleeping_place.fields.bunk_level') }}</flux:label>
                <flux:select wire:model.change="bunkLevel">
                    <flux:select.option value="">{{ __('sleeping_place.options.not_specified') }}</flux:select.option>
                    @foreach(['top', 'middle', 'bottom'] as $level)
                        <flux:select.option value="{{ $level }}">{{ __('sleeping_place.bunk_levels.'.$level) }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="bunkLevel" />
            </flux:field>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            @foreach(['isTopBunk', 'isBottomBunk', 'isSingle', 'isDouble', 'isForOnePerson', 'isForCouple'] as $field)
                <flux:checkbox wire:model.change="{{ $field }}" label="{{ __('sleeping_place.fields.'.\Illuminate\Support\Str::snake($field)) }}" />
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach(['maxGuests', 'minGuestAge', 'maxGuestAge'] as $field)
                <flux:field>
                    <flux:label>{{ __('sleeping_place.fields.'.\Illuminate\Support\Str::snake($field)) }}</flux:label>
                    <flux:input type="number" inputmode="numeric" wire:model.blur="{{ $field }}" icon="numbered-list" />
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach

            <flux:field>
                <flux:label>{{ __('sleeping_place.fields.status') }}</flux:label>
                <flux:select wire:model.change="status">
                    @foreach(\App\Enums\SleepingPlaceStatus::cases() as $status)
                        <flux:select.option value="{{ $status->value }}">{{ $status->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="status" />
            </flux:field>
        </div>
    </flux:card>

    <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled" icon="chat-bubble-left-right">
        <span wire:loading.remove wire:target="save">{{ __('sleeping_place.actions.save_step') }}</span>
        <span wire:loading wire:target="save">{{ __('sleeping_place.messages.saving') }}</span>
    </flux:button>
</form>
