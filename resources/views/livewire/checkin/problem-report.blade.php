<div class="mx-auto max-w-3xl space-y-5 px-4 py-4 pb-32 sm:px-6">
    <section class="space-y-3">
        <flux:badge color="amber">{{ __('booking.problem_report.eyebrow') }}</flux:badge>
        <div class="space-y-2">
            <flux:heading size="xl" level="1">{{ __('booking.problem_report.title') }}</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                {{ __('booking.problem_report.helper') }}
            </flux:text>
        </div>
    </section>

    <flux:card class="space-y-2">
        <flux:heading size="lg">{{ $trip['title'] }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $trip['dates'] }}</flux:text>
    </flux:card>

    <form wire:submit="submit" class="space-y-4">
        <flux:card class="space-y-4">
            <flux:textarea
                wire:model.blur="problemDescription"
                label="{{ __('booking.problem_report.description') }}"
                rows="5"
                :error="$errors->first('problemDescription')"
            />

            <div class="space-y-2">
                <flux:file-upload
                    wire:model="photos"
                    multiple
                    :label="__('booking.problem_report.photos')"
                    :description="__('booking.problem_report.photos_helper')"
                    :error="$errors->first('photos')"
                >
                    <flux:file-upload.dropzone
                        :heading="__('booking.problem_report.photos')"
                        :text="__('booking.problem_report.photos_helper')"
                        with-progress
                        inline
                    />
                </flux:file-upload>
                <div wire:loading wire:target="photos" class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('booking.problem_report.photos_loading') }}
                </div>
                @error('photos.*')
                    <flux:text size="sm" class="text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                @enderror
            </div>
        </flux:card>

        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-zinc-200 bg-white/95 px-4 py-3 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/95 sm:static sm:rounded-lg sm:border sm:backdrop-blur-none">
            <div class="mx-auto grid max-w-3xl grid-cols-2 gap-2">
                <flux:button href="{{ route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="ghost" class="w-full">
                    {{ __('app.actions.back') }}
                </flux:button>
                <flux:button type="submit" wire:loading.attr="disabled" data-loading variant="primary" class="w-full">
                    <span wire:loading.remove>{{ __('booking.problem_report.submit') }}</span>
                    <span wire:loading>{{ __('booking.problem_report.submitting') }}</span>
                </flux:button>
            </div>
        </div>
    </form>
</div>
