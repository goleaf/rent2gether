@props([
    'host' => null,
    'hostProfile' => null,
])

@php
    $loadedProfile = $host?->relationLoaded('hostProfile') ? $host->hostProfile : null;
    $profile = $hostProfile ?: $loadedProfile;
    $displayName = $profile?->display_name ?: $host?->name;
    $avatarPath = $profile?->avatar_path ?: $host?->avatar;
    $avatarUrl = $avatarPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($avatarPath) : null;
    $languages = collect($profile?->languages_json ?: $host?->languages ?: [])
        ->filter()
        ->take(4)
        ->map(fn (string $language): string => \Illuminate\Support\Facades\Lang::has('navigation.languages.'.(string) $language)
            ? \Illuminate\Support\Facades\Lang::get('navigation.languages.'.(string) $language)
            : strtoupper($language))
        ->join(', ');
    $responseMinutes = $profile?->response_time_minutes;
    $responseLabel = null;

    if ($responseMinutes) {
        $responseLabel = $responseMinutes < 60
            ? __('host.profile.public_card.response_time_minutes', ['count' => $responseMinutes])
            : __('host.profile.public_card.response_time_hours', ['count' => (int) ceil($responseMinutes / 60)]);
    }
@endphp

<flux:card class="space-y-4">
    <div class="flex items-start gap-3">
        <div class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-emerald-50 text-lg font-semibold text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
            @if($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="{{ __('host.profile.public_card.photo_alt', ['name' => $displayName]) }}" loading="lazy" decoding="async" class="size-full object-cover">
            @else
                {{ \Illuminate\Support\Str::of((string) $displayName)->substr(0, 1)->upper() }}
            @endif
        </div>

        <div class="min-w-0 flex-1 space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <flux:heading size="sm">{{ $displayName }}</flux:heading>
                <flux:badge :color="$profile?->verified_at ? 'green' : 'zinc'" size="sm">
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
