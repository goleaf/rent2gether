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
                <flux:checkbox wire:model.change="showDisplayNamePublicly" label="{{ __('account.privacy.guest_profile.show_display_name_publicly') }}" />
                <flux:checkbox wire:model.change="showAvatar" label="{{ __('account.privacy.guest_profile.show_avatar') }}" />
                <flux:checkbox wire:model.change="showAge" label="{{ __('account.privacy.guest_profile.show_age') }}" />
                <flux:checkbox wire:model.change="showAgeRange" label="{{ __('account.privacy.guest_profile.show_age_range') }}" />
                <flux:checkbox wire:model.change="showCity" label="{{ __('account.privacy.guest_profile.show_city') }}" />
                <flux:checkbox wire:model.change="showLanguages" label="{{ __('account.privacy.guest_profile.show_languages') }}" />
                <flux:checkbox wire:model.change="showOccupation" label="{{ __('account.privacy.guest_profile.show_occupation') }}" />
                <flux:checkbox wire:model.change="showReviews" label="{{ __('account.privacy.guest_profile.show_reviews') }}" />
                <flux:checkbox wire:model.change="showVerificationStatus" label="{{ __('account.privacy.guest_profile.show_verification_status') }}" />
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
                <flux:checkbox wire:model.change="showFullNameToConfirmedHostsOnly" label="{{ __('account.privacy.guest_contact.show_full_name_to_confirmed_hosts_only') }}" />
                <flux:checkbox wire:model.change="showPhoneAfterConfirmedBooking" label="{{ __('account.privacy.guest_contact.show_phone_after_confirmed_booking') }}" />
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
                <flux:checkbox wire:model.change="showExactAddressBeforeBooking" label="{{ __('account.privacy.host_listing.show_exact_address_before_booking') }}" />
                <flux:checkbox wire:model.change="showApproximateAreaBeforeBooking" label="{{ __('account.privacy.host_listing.show_approximate_area_before_booking') }}" />
                <flux:checkbox wire:model.change="showHostPhoneAfterConfirmedBooking" label="{{ __('account.privacy.host_listing.show_phone_after_confirmed_booking') }}" />
                <flux:checkbox wire:model.change="showCheckInInstructionsAfterConfirmation" label="{{ __('account.privacy.host_listing.show_checkin_instructions_after_confirmation') }}" />
                <flux:checkbox wire:model.change="hideSensitivePublicListingInfo" label="{{ __('account.privacy.host_listing.hide_sensitive_public_listing_info') }}" />
            </div>
        </flux:card>

        <flux:callout icon="shield-check">
            <flux:callout.heading>{{ __('account.privacy.note.title') }}</flux:callout.heading>
            <flux:callout.text>{{ __('account.privacy.note.text') }}</flux:callout.text>
        </flux:callout>

        <div class="sticky bottom-20 z-10 rounded-xl border border-zinc-200 bg-white/95 p-3 shadow-lg backdrop-blur dark:border-zinc-700 dark:bg-zinc-950/95 lg:static lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
            <flux:button type="submit" variant="primary" class="w-full data-loading:opacity-70" icon="check">
                <span wire:loading.remove wire:target="save">{{ __('account.privacy.save') }}</span>
                <span wire:loading wire:target="save">{{ __('account.actions.saving') }}</span>
            </flux:button>
        </div>
    </form>
</x-ui.page>
