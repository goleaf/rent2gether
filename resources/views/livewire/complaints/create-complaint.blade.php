<div class="mx-auto max-w-3xl space-y-5 px-4 py-4 pb-32 sm:px-6">
    <section class="space-y-3">
        <flux:badge color="amber">{{ __('booking.complaint.eyebrow') }}</flux:badge>
        <div class="space-y-2">
            <flux:heading size="xl" level="1">{{ __('booking.complaint.title') }}</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                {{ $reporterRole === 'host' ? __('booking.complaint.host_helper') : __('booking.complaint.guest_helper') }}
            </flux:text>
        </div>
    </section>

    <flux:card class="space-y-2">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <flux:heading size="lg">{{ $summaryTitle }}</flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ __('booking.complaint.booking_reference', ['reference' => $booking->reference]) }}
                </flux:text>
            </div>
            <flux:badge color="zinc">{{ $booking->status->label() }}</flux:badge>
        </div>
    </flux:card>

    <form wire:submit="submit" class="space-y-4">
        <flux:card class="space-y-4">
            <flux:select wire:model.change="type" label="{{ __('booking.complaint.fields.type') }}" :error="$errors->first('type')">
                <flux:select.option value="">{{ __('booking.complaint.select_type') }}</flux:select.option>
                @foreach($this->complaintTypes() as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.change="priority" label="{{ __('booking.complaint.fields.priority') }}" :error="$errors->first('priority')">
                <flux:select.option value="low">{{ __('booking.complaint.priority.low') }}</flux:select.option>
                <flux:select.option value="normal">{{ __('booking.complaint.priority.normal') }}</flux:select.option>
                <flux:select.option value="high">{{ __('booking.complaint.priority.high') }}</flux:select.option>
                <flux:select.option value="critical">{{ __('booking.complaint.priority.critical') }}</flux:select.option>
            </flux:select>

            <flux:textarea
                wire:model.blur="description"
                label="{{ __('booking.complaint.fields.description') }}"
                rows="5"
                :error="$errors->first('description')"
            />

            <flux:textarea
                wire:model.blur="desiredResolution"
                label="{{ __('booking.complaint.fields.desired_resolution') }}"
                rows="3"
                :error="$errors->first('desiredResolution')"
            />
        </flux:card>

        <flux:card class="space-y-4">
            <div class="space-y-3">
                <flux:heading size="lg">{{ __('booking.complaint.resolution_title') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ __('booking.complaint.resolution_helper') }}
                </flux:text>
            </div>

            <div class="space-y-3">
                <flux:checkbox wire:model.change="refundRequested" label="{{ __('booking.complaint.fields.refund_requested') }}" />
                <flux:checkbox wire:model.change="depositHoldRequested" label="{{ __('booking.complaint.fields.deposit_hold_requested') }}" />
            </div>
        </flux:card>

        <flux:card class="space-y-3">
            <div class="space-y-1">
                <flux:heading size="lg">{{ __('booking.complaint.fields.media') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ __('booking.complaint.media_helper') }}
                </flux:text>
            </div>

            <flux:file-upload
                wire:model="media"
                multiple
                :label="__('booking.complaint.fields.media')"
                :description="__('booking.complaint.media_helper')"
                :error="$errors->first('media')"
            >
                <flux:file-upload.dropzone
                    :heading="__('booking.complaint.fields.media')"
                    :text="__('booking.complaint.media_helper')"
                    with-progress
                    inline
                />
            </flux:file-upload>

            <div wire:loading wire:target="media" class="text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('booking.complaint.media_loading') }}
            </div>

            @error('media.*')
                <flux:text size="sm" class="text-red-600 dark:text-red-400">{{ $message }}</flux:text>
            @enderror
        </flux:card>

        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-zinc-200 bg-white/95 px-4 py-3 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/95 sm:static sm:rounded-lg sm:border sm:backdrop-blur-none">
            <div class="mx-auto grid max-w-3xl grid-cols-2 gap-2">
                <flux:button href="{{ $reporterRole === 'host' ? route('host.bookings.manage', ['locale' => app()->getLocale(), 'booking' => $booking]) : route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="ghost" class="w-full">
                    {{ __('app.actions.back') }}
                </flux:button>
                <flux:button type="submit" wire:loading.attr="disabled" wire:target="submit,media" data-loading variant="primary" class="w-full">
                    <span wire:loading.remove wire:target="submit">{{ __('booking.complaint.submit') }}</span>
                    <span wire:loading wire:target="submit">{{ __('booking.complaint.submitting') }}</span>
                </flux:button>
            </div>
        </div>
    </form>
</div>
