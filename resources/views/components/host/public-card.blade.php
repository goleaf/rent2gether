<flux:card class="space-y-4">
    <div class="flex items-start gap-3">
        <flux:avatar
            :name="$displayName"
            :src="$avatarUrl"
            :initials="$initial"
            alt="{{ __('host.profile.public_card.photo_alt', ['name' => $displayName]) }}"
            color="auto"
            color:seed="{{ $displayName }}"
            circle
            size="lg"
        />

        <div class="min-w-0 flex-1 space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ $displayName }}</span>
                    </span>
                </flux:heading>
                <flux:badge :color="$profile?->verified_at ? 'green' : 'zinc'" size="sm" icon="check-circle">
                    {{ $profile?->verified_at ? __('host.profile.public_card.verified') : __('host.profile.public_card.not_verified') }}
                </flux:badge>
            </div>

            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                @if((int) ($profile?->reviews_count ?? 0) > 0)
                    {{ __('host.profile.public_card.rating', ['rating' => number_format((float) $profile->rating_average, 1)]) }}
                    <span aria-hidden="true">&middot;</span>
                    {{ trans_choice('host.profile.public_card.reviews_count', (int) $profile->reviews_count, ['count' => (int) $profile->reviews_count]) }}
                @else
                    {{ __('host.profile.public_card.new_host') }}
                @endif
            </flux:text>
        </div>
    </div>

    <div class="grid gap-2 text-sm text-zinc-700 dark:text-zinc-300">
        @if($responseLabel)
            <div class="flex items-center gap-2">
                <flux:icon name="clock" class="size-4 text-zinc-400" />
                <span>{{ __('host.profile.public_card.response_time', ['time' => $responseLabel]) }}</span>
            </div>
        @endif

        @if($languages)
            <div class="flex items-center gap-2">
                <flux:icon name="language" class="size-4 text-zinc-400" />
                <span>{{ __('host.profile.public_card.languages', ['languages' => $languages]) }}</span>
            </div>
        @endif
    </div>
</flux:card>
