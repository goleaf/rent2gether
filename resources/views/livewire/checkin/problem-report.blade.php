<x-ui.page>
    <section class="space-y-3">
        <flux:badge color="amber" icon="exclamation-triangle">{{ __('booking.problem_report.eyebrow') }}</flux:badge>
        <div class="space-y-2">
            <flux:heading size="xl" level="1">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="exclamation-triangle" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.problem_report.title') }}</span>
                </span>
            </flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                {{ __('booking.problem_report.helper') }}
            </flux:text>
        </div>
    </section>

    <flux:card class="space-y-2">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="exclamation-triangle" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ $trip['title'] }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $trip['dates'] }}</flux:text>
    </flux:card>

    <form wire:submit="submit" class="space-y-4">
        <flux:card class="space-y-4">
                        <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('booking.problem_report.description') }}</span>
                    </span>
                </flux:label>
                <flux:textarea
                    wire:model.blur="problemDescription"
                    rows="5"
                    :error="$errors->first('problemDescription')" />
                <flux:error name="problemDescription" />
            </flux:field>

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
            <div class="mx-auto grid w-full max-w-5xl grid-cols-2 gap-2">
                <flux:button href="{{ route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="ghost" class="w-full" icon="arrow-left">
                    {{ __('app.actions.back') }}
                </flux:button>
                <flux:button type="submit" wire:loading.attr="disabled" data-loading variant="primary" class="w-full" icon="calendar-days">
                    <span wire:loading.remove>{{ __('booking.problem_report.submit') }}</span>
                    <span wire:loading>{{ __('booking.problem_report.submitting') }}</span>
                </flux:button>
            </div>
        </div>
    </form>
</x-ui.page>
