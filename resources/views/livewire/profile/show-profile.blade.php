<x-ui.page class="space-y-6">
    <div class="flex items-center gap-4">
        <div class="size-20 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center">
            @if($this->profileVisibility['avatar_path'])
                <img src="{{ asset('storage/'.$this->profileVisibility['avatar_path']) }}" alt="{{ __('app.profile.avatar_alt', ['name' => $this->profileVisibility['display_name']]) }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
            @else
                <flux:icon name="user" class="size-10 text-zinc-400" />
            @endif
        </div>
        <div>
            <flux:heading size="xl">{{ $this->profileVisibility['display_name'] }}</flux:heading>
            @if($this->profileVisibility['full_name'])
                <flux:text size="sm" class="text-zinc-500">{{ $this->profileVisibility['full_name'] }}</flux:text>
            @endif
            <flux:text class="text-zinc-500">
                {{ __('app.profile.member_since') }} {{ $user->created_at->translatedFormat('M Y') }}
                @if($this->profileVisibility['city']) &middot; {{ $this->profileVisibility['city'] }}@endif
                @if($this->profileVisibility['age_range']) &middot; {{ __('app.profile.age_range', ['range' => $this->profileVisibility['age_range']]) }}@endif
            </flux:text>
            @if($user->is_host)
                <flux:badge color="blue" size="sm" class="mt-1">{{ __('app.profile.host') }}</flux:badge>
            @endif
        </div>
    </div>

    @if($user->bio)
        <flux:card>
            <flux:heading size="sm">{{ __('app.profile.about') }}</flux:heading>
            <flux:text>{{ $user->bio }}</flux:text>
        </flux:card>
    @endif

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <flux:card class="text-center">
            <div class="text-2xl font-bold">{{ number_format($user->rating_as_guest ?? 0, 1) }}</div>
            <flux:text size="sm" class="text-zinc-500">{{ __('app.profile.guest_rating') }}</flux:text>
        </flux:card>
        <flux:card class="text-center">
            <div class="text-2xl font-bold">{{ $user->completed_stays_count ?? 0 }}</div>
            <flux:text size="sm" class="text-zinc-500">{{ __('app.profile.stays') }}</flux:text>
        </flux:card>
        @if($user->is_host)
            <flux:card class="text-center">
                <div class="text-2xl font-bold">{{ number_format($user->rating_as_host ?? 0, 1) }}</div>
                <flux:text size="sm" class="text-zinc-500">{{ __('app.profile.host_rating') }}</flux:text>
            </flux:card>
            <flux:card class="text-center">
                <div class="text-2xl font-bold">{{ $user->hosted_stays_count ?? 0 }}</div>
                <flux:text size="sm" class="text-zinc-500">{{ __('app.profile.hosted') }}</flux:text>
            </flux:card>
        @endif
    </div>

    @if($this->profileVisibility['show_reviews'])
    <div class="space-y-4">
        <flux:heading size="lg">{{ __('app.profile.review_summary') }}</flux:heading>

        @foreach([
            'guest' => $this->reviewsAsGuest,
            'host' => $this->reviewsAsHost,
        ] as $group => $reviews)
            <section class="space-y-3">
                <flux:heading size="sm">{{ __('app.profile.reviews_as_'.$group) }}</flux:heading>

                @forelse($reviews as $review)
                    <flux:card class="space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <flux:text class="font-medium">{{ $review->reviewer?->name ?: __('listing.detail.reviews.guest') }}</flux:text>
                            <flux:badge>{{ __('listing.detail.reviews.rating', ['rating' => number_format((float) $review->overall_rating, 1)]) }}</flux:badge>
                        </div>
                        <flux:text size="sm" class="text-zinc-500">{{ $review->created_at?->diffForHumans() }}</flux:text>

                        @if($review->liked_text ?: $review->comment ?: $review->positive_comment)
                            <flux:text>{{ $review->liked_text ?: $review->comment ?: $review->positive_comment }}</flux:text>
                        @endif
                    </flux:card>
                @empty
                    <flux:card>
                        <flux:text class="text-zinc-500">{{ __('app.profile.no_reviews_yet') }}</flux:text>
                    </flux:card>
                @endforelse
            </section>
        @endforeach
    </div>
    @else
        <flux:card>
            <flux:text class="text-zinc-500">{{ __('app.profile.reviews_hidden') }}</flux:text>
        </flux:card>
    @endif
</x-ui.page>
