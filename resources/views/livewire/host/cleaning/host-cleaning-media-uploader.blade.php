<section class="space-y-3">
    <flux:card class="space-y-2">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="photo" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('cleaning.sections.photo_uploader') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ __('cleaning.helpers.photo_uploader') }}
        </flux:text>
    </flux:card>

    <div class="grid grid-cols-2 gap-2">
        <flux:button variant="ghost" wire:loading.attr="disabled" icon="camera">{{ __('cleaning.actions.upload_before_photo') }}</flux:button>
        <flux:button variant="primary" wire:loading.attr="disabled" icon="camera">{{ __('cleaning.actions.upload_after_photo') }}</flux:button>
    </div>
</section>
