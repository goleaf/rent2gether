<x-ui.page>
    <section class="space-y-2">
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="shield-check" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('account.privacy.heading') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('account.privacy.helper') }}</flux:text>
    </section>

    @if(session('success'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ session('success') }}</flux:callout.text>
        </flux:callout>
    @endif

    <form wire:submit="save" class="space-y-5">
        <flux:card class="space-y-4">
            <div>
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('account.privacy.guest_profile.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('account.privacy.guest_profile.helper') }}</flux:text>
            </div>

            <div class="grid gap-3">
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showDisplayNamePublicly" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('account.privacy.guest_profile.show_display_name_publicly') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showDisplayNamePublicly" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showAvatar" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('account.privacy.guest_profile.show_avatar') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showAvatar" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showAge" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('account.privacy.guest_profile.show_age') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showAge" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showAgeRange" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('account.privacy.guest_profile.show_age_range') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showAgeRange" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showCity" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('account.privacy.guest_profile.show_city') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showCity" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showLanguages" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('account.privacy.guest_profile.show_languages') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showLanguages" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showOccupation" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('account.privacy.guest_profile.show_occupation') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showOccupation" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showReviews" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('account.privacy.guest_profile.show_reviews') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showReviews" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showVerificationStatus" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('account.privacy.guest_profile.show_verification_status') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showVerificationStatus" />
                </flux:field>
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <div>
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('account.privacy.guest_contact.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('account.privacy.guest_contact.helper') }}</flux:text>
            </div>

            <div class="grid gap-3">
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showFullNameToConfirmedHostsOnly" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('account.privacy.guest_contact.show_full_name_to_confirmed_hosts_only') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showFullNameToConfirmedHostsOnly" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showPhoneAfterConfirmedBooking" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('account.privacy.guest_contact.show_phone_after_confirmed_booking') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showPhoneAfterConfirmedBooking" />
                </flux:field>
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <div>
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('account.privacy.host_listing.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('account.privacy.host_listing.helper') }}</flux:text>
            </div>

            <div class="grid gap-3">
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showExactAddressBeforeBooking" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('account.privacy.host_listing.show_exact_address_before_booking') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showExactAddressBeforeBooking" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showApproximateAreaBeforeBooking" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('account.privacy.host_listing.show_approximate_area_before_booking') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showApproximateAreaBeforeBooking" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showHostPhoneAfterConfirmedBooking" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('account.privacy.host_listing.show_phone_after_confirmed_booking') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showHostPhoneAfterConfirmedBooking" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="showCheckInInstructionsAfterConfirmation" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('account.privacy.host_listing.show_checkin_instructions_after_confirmation') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="showCheckInInstructionsAfterConfirmation" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="hideSensitivePublicListingInfo" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('account.privacy.host_listing.hide_sensitive_public_listing_info') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="hideSensitivePublicListingInfo" />
                </flux:field>
            </div>
        </flux:card>

        <flux:callout icon="shield-check">
            <flux:callout.heading icon="shield-check" icon:variant="mini">{{ __('account.privacy.note.title') }}</flux:callout.heading>
            <flux:callout.text>{{ __('account.privacy.note.text') }}</flux:callout.text>
        </flux:callout>

        <div class="sticky bottom-20 z-10 rounded-xl border border-zinc-200 bg-white/95 p-3 shadow-lg backdrop-blur dark:border-zinc-700 dark:bg-zinc-950/95 lg:static lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
            <flux:button type="submit" variant="primary" class="w-full data-loading:opacity-70" icon="check">
                <span wire:loading.remove wire:target="save">{{ __('account.privacy.save') }}</span>
                <span wire:loading wire:target="save">{{ __('account.actions.saving') }}</span>
            </flux:button>
        </div>
    </form>

    <livewire:profile.guest-compatibility-privacy-settings />
</x-ui.page>
