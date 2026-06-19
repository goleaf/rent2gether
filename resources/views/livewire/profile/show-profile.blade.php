<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <div class="size-20 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center">
            <flux:icon name="user" class="size-10 text-zinc-400" />
        </div>
        <div>
            <flux:heading size="xl">{{ $user->name }}</flux:heading>
            <flux:text class="text-zinc-500">
                {{ __('app.profile.member_since') }} {{ $user->created_at->translatedFormat('M Y') }}
                @if($user->city) &middot; {{ $user->city }}@endif
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

    @if($this->reviews->isNotEmpty())
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('app.profile.reviews') }}</flux:heading>
            @foreach($this->reviews as $review)
                <flux:card class="space-y-2">
                    <div class="flex items-center justify-between">
                        <flux:text class="font-medium">{{ $review->reviewer->name }}</flux:text>
                        <flux:text size="sm" class="text-zinc-500">{{ $review->created_at->diffForHumans() }}</flux:text>
                    </div>
                    <div class="flex items-center gap-1">
                        @for($i = 1; $i <= 5; $i++)
                            <flux:icon name="star" class="size-4 {{ $i <= $review->rating_overall ? 'text-yellow-500' : 'text-zinc-300' }}" />
                        @endfor
                    </div>
                    @if($review->comment_overall)
                        <flux:text>{{ $review->comment_overall }}</flux:text>
                    @endif
                </flux:card>
            @endforeach
        </div>
    @endif
</div>
