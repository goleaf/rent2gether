<div class="mx-auto max-w-2xl space-y-5 px-4 py-4 pb-28 sm:px-6">
    <section class="space-y-3">
        <flux:badge color="emerald">{{ __('booking.review.eyebrow') }}</flux:badge>
        <div class="space-y-2">
            <flux:heading size="xl" level="1">
                {{ $isHostReview ? __('booking.review.host_title') : __('booking.review.guest_title') }}
            </flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                {{ $isHostReview ? __('booking.review.host_helper') : __('booking.review.guest_helper') }}
            </flux:text>
        </div>
    </section>

    <flux:card class="space-y-2">
        <flux:heading size="sm">{{ $placeTitle }}</flux:heading>
        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
            {{ $booking->check_in_date?->translatedFormat('d M Y') }} - {{ $booking->check_out_date?->translatedFormat('d M Y') }}
        </flux:text>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ __('booking.review.visibility_helper') }}
        </flux:text>
    </flux:card>

    @error('booking')
        <flux:callout color="amber" icon="exclamation-triangle">
            <flux:callout.heading>{{ __('booking.review.warning_title') }}</flux:callout.heading>
            <flux:callout.text>{{ $message }}</flux:callout.text>
        </flux:callout>
    @enderror

    @error('review')
        <flux:callout color="amber" icon="exclamation-triangle">
            <flux:callout.heading>{{ __('booking.review.warning_title') }}</flux:callout.heading>
            <flux:callout.text>{{ $message }}</flux:callout.text>
        </flux:callout>
    @enderror

    <form wire:submit="submit" class="space-y-5">
        <flux:card class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">{{ __('booking.review.ratings') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                    {{ __('booking.review.rating_helper') }}
                </flux:text>
            </div>

            @foreach($isHostReview ? $hostRatings : $guestRatings as $property => $labelKey)
                <flux:field>
                    <div class="flex items-center justify-between gap-3">
                        <flux:label>{{ __('booking.review.fields.'.$labelKey) }}</flux:label>
                        <flux:select wire:model.change="{{ $property }}" class="w-24">
                            @foreach($ratingOptions as $option)
                                <flux:select.option value="{{ $option }}">{{ $option }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    <flux:error name="{{ $property }}" />
                </flux:field>
            @endforeach
        </flux:card>

        @if($isHostReview)
            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('booking.review.host_comment_title') }}</flux:heading>

                <flux:field>
                    <flux:label>{{ __('booking.review.fields.host_comment') }}</flux:label>
                    <flux:textarea wire:model.blur="hostComment" rows="4" />
                    <flux:error name="hostComment" />
                </flux:field>

                <flux:checkbox wire:model.change="recommendGuest" label="{{ __('booking.review.fields.recommend_guest') }}" />
            </flux:card>
        @else
            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('booking.review.comments') }}</flux:heading>

                <flux:field>
                    <flux:label>{{ __('booking.review.fields.liked_text') }}</flux:label>
                    <flux:textarea wire:model.blur="likedText" rows="3" />
                    <flux:error name="likedText" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('booking.review.fields.improvement_text') }}</flux:label>
                    <flux:textarea wire:model.blur="improvementText" rows="3" />
                    <flux:error name="improvementText" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('booking.review.fields.advice_text') }}</flux:label>
                    <flux:textarea wire:model.blur="adviceText" rows="3" />
                    <flux:error name="adviceText" />
                </flux:field>

                <flux:checkbox wire:model.change="recommend" label="{{ __('booking.review.fields.recommend') }}" />
            </flux:card>

            <flux:card class="space-y-3">
                <flux:file-upload
                    wire:model="photos"
                    multiple
                    :label="__('booking.review.fields.photos')"
                    :description="__('booking.review.photos_helper')"
                    :error="$errors->first('photos')"
                >
                    <flux:file-upload.dropzone
                        :heading="__('booking.review.fields.photos')"
                        :text="__('booking.review.photos_helper')"
                        with-progress
                        inline
                    />
                </flux:file-upload>

                <flux:error name="photos.*" />

                <flux:text wire:loading wire:target="photos" size="sm" class="text-zinc-500">
                    {{ __('booking.review.uploading') }}
                </flux:text>
            </flux:card>
        @endif

        <div class="fixed inset-x-0 bottom-0 z-20 border-t border-zinc-200 bg-white/95 px-4 py-3 backdrop-blur dark:border-white/10 dark:bg-zinc-950/95 sm:static sm:border-0 sm:bg-transparent sm:px-0 sm:py-0">
            <div class="mx-auto flex max-w-2xl gap-2">
                <flux:button type="submit" variant="primary" class="w-full data-loading:opacity-70" wire:target="submit,photos">
                    <span wire:loading.remove wire:target="submit">{{ __('booking.review.submit') }}</span>
                    <span wire:loading wire:target="submit">{{ __('booking.review.submitting') }}</span>
                </flux:button>

                @if($isHostReview)
                    <flux:button href="{{ route('host.bookings.manage', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="ghost" class="w-full">
                        {{ __('app.actions.back') }}
                    </flux:button>
                @else
                    <flux:button href="{{ route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="ghost" class="w-full">
                        {{ __('app.actions.back') }}
                    </flux:button>
                @endif
            </div>
        </div>
    </form>
</div>
