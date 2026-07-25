<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('listing_wizard.rooms.created') }}</span>
            </span>
        </flux:heading>
        <flux:button size="sm" type="button" wire:click="addRoom" wire:loading.attr="disabled" wire:target="addRoom" icon="plus">
            <span wire:loading.remove wire:target="addRoom">{{ __('listing_wizard.rooms.add_room') }}</span>
            <span wire:loading wire:target="addRoom">{{ __('listing_wizard.actions.saving') }}</span>
        </flux:button>
    </div>

    @forelse($rooms as $index => $room)
        <flux:card class="space-y-4" wire:key="room-editor-{{ $room['id'] }}">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 space-y-1">
                    <flux:heading size="md">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="rectangle-stack" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ $room['title'] }}</span>
                        </span>
                    </flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                        {{ __('listing_wizard.rooms.created_sleeping_places', ['count' => $room['sleeping_places_total']]) }}
                    </flux:text>
                </div>
                <flux:badge size="sm" color="zinc" icon="check-circle">{{ $statusOptions[$room['status']] ?? $room['status'] }}</flux:badge>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="tag" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing_wizard.rooms.name') }}</span>
                        </span>
                    </flux:label>
                    <flux:input wire:model.blur="rooms.{{ $index }}.title" maxlength="120" icon="tag" />
                    <flux:error name="rooms.{{ $index }}.title" />
                </flux:field>

                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="rectangle-stack" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing_wizard.rooms.type') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model.change="rooms.{{ $index }}.type">
                        @foreach($roomTypeOptions as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="rooms.{{ $index }}.type" />
                </flux:field>

                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="numbered-list" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing_wizard.rooms.sleeping_places_count') }}</span>
                        </span>
                    </flux:label>
                    <flux:input type="number" min="1" max="40" wire:model.blur="rooms.{{ $index }}.sleeping_places_count" icon="numbered-list" />
                    <flux:error name="rooms.{{ $index }}.sleeping_places_count" />
                </flux:field>

                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="users" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing_wizard.rooms.living_format') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model.change="rooms.{{ $index }}.living_format">
                        @foreach($livingFormatOptions as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="rooms.{{ $index }}.living_format" />
                </flux:field>

                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="users" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing_wizard.rooms.gender_policy') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model.change="rooms.{{ $index }}.gender_policy">
                        @foreach($genderPolicyOptions as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="rooms.{{ $index }}.gender_policy" />
                </flux:field>

                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="check-circle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing_wizard.rooms.status') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model.change="rooms.{{ $index }}.status">
                        @foreach($statusOptions as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="rooms.{{ $index }}.status" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="document-text" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('listing_wizard.rooms.description') }}</span>
                    </span>
                </flux:label>
                <flux:textarea rows="3" wire:model.blur="rooms.{{ $index }}.description" maxlength="1500" />
                <flux:error name="rooms.{{ $index }}.description" />
            </flux:field>

            <div class="space-y-2">
                <flux:text size="sm" class="font-medium">{{ __('listing_wizard.rooms.rules') }}</flux:text>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach($ruleOptions as $value => $label)
                        <flux:field variant="inline" wire:key="room-{{ $room['id'] }}-rule-{{ $value }}">
                            <flux:checkbox wire:model.change="rooms.{{ $index }}.rules" value="{{ $value }}" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ $label }}</span>
                                </span>
                            </flux:label>
                        </flux:field>
                    @endforeach
                </div>
                <flux:error name="rooms.{{ $index }}.rules" />
            </div>

            <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="document-text" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('listing_wizard.rooms.rules_note') }}</span>
                    </span>
                </flux:label>
                <flux:textarea rows="3" wire:model.blur="rooms.{{ $index }}.room_rules_text" maxlength="1200" />
                <flux:error name="rooms.{{ $index }}.room_rules_text" />
            </flux:field>

            <flux:accordion>
                <flux:accordion.item>
                    <flux:accordion.heading>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="photo" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">
                                {{ __('listing_wizard.rooms.photos') }}
                                {{ __('listing_wizard.media_count', ['count' => $room['media_count']]) }}
                            </span>
                        </span>
                    </flux:accordion.heading>
                    <flux:accordion.content>
                    <livewire:media.manage-media
                        owner-type="room"
                        :owner-id="$room['id']"
                        collection="gallery"
                        :max-items="8"
                        :key="'room-media-'.$room['id']"
                    />
                    </flux:accordion.content>
                </flux:accordion.item>
            </flux:accordion>

            <flux:button type="button" variant="primary" wire:click="saveRoom({{ $index }})" wire:loading.attr="disabled" wire:target="saveRoom" icon="bookmark" class="w-full sm:w-auto">
                <span wire:loading.remove wire:target="saveRoom">{{ __('listing_wizard.save_draft') }}</span>
                <span wire:loading wire:target="saveRoom">{{ __('listing_wizard.actions.saving') }}</span>
            </flux:button>
        </flux:card>
    @empty
        <flux:callout variant="secondary" icon="information-circle" :text="__('listing_wizard.rooms.empty')" />
    @endforelse
</div>
