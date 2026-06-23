<x-ui.page class="space-y-0 flex min-h-screen flex-col gap-4">
    <div class="space-y-2">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="user" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('profiles.title') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-300">{{ __('profiles.helper') }}</flux:text>
    </div>

    <div class="grid gap-3">
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:text class="font-medium">{{ __('profiles.sections.basic') }}</flux:text>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:text class="font-medium">{{ __('profiles.sections.photo') }}</flux:text>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:text class="font-medium">{{ __('profiles.sections.languages') }}</flux:text>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:text class="font-medium">{{ __('profiles.sections.privacy') }}</flux:text>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:text class="font-medium">{{ __('profiles.sections.guest_profile') }}</flux:text>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:text class="font-medium">{{ __('profiles.sections.host_profile') }}</flux:text>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:text class="font-medium">{{ __('profiles.sections.verifications') }}</flux:text>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:text class="font-medium">{{ __('profiles.sections.notifications') }}</flux:text>
        </div>
    </div>
</x-ui.page>
