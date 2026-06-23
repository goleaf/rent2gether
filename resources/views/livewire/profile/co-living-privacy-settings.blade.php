<div class="space-y-4">
    @if(session('co_living_status'))
        <flux:badge color="green" icon="check-circle">{{ session('co_living_status') }}</flux:badge>
    @endif

    <form wire:submit="save" class="space-y-4">
        <flux:card class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="shield-check" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('occupants.privacy.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('occupants.privacy.helper') }}</flux:text>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showPublicAlias" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.privacy.show_public_alias') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showPublicAlias" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showRealFirstName" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.privacy.show_real_first_name') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showRealFirstName" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showAvatar" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.privacy.show_avatar') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showAvatar" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showAgeRange" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.privacy.show_age_range') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showAgeRange" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showGenderIfRoomPolicy" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.privacy.show_gender_if_room_policy') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showGenderIfRoomPolicy" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showLanguages" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.privacy.show_languages') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showLanguages" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showStayPurpose" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.privacy.show_stay_purpose') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showStayPurpose" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showGuestType" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.privacy.show_guest_type') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showGuestType" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showSleepSchedule" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.privacy.show_sleep_schedule') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showSleepSchedule" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showHomePresence" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.privacy.show_home_presence') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showHomePresence" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showSmokingStatus" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.privacy.show_smoking_status') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showSmokingStatus" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showPetStatus" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.privacy.show_pet_status') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showPetStatus" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showSocialLevel" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.privacy.show_social_level') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showSocialLevel" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showQuietPreference" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.privacy.show_quiet_preference') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showQuietPreference" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showRoommateRating" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.privacy.show_roommate_rating') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showRoommateRating" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showCheckoutDateToFutureRoommates" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.privacy.show_checkout_date_to_future_roommates') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showCheckoutDateToFutureRoommates" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="allowProfileInPrebookingSummary" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.privacy.allow_profile_in_prebooking_summary') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="allowProfileInPrebookingSummary" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="allowProfileAfterConfirmedBooking" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('occupants.privacy.allow_profile_after_confirmed_booking') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="allowProfileAfterConfirmedBooking" />
                </flux:field>
            </div>
        </flux:card>

        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" icon="check">
            <span wire:loading.remove>{{ __('occupants.actions.save_privacy') }}</span>
            <span wire:loading>{{ __('occupants.actions.saving') }}</span>
        </flux:button>
    </form>
</div>
