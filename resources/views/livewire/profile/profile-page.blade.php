<div class="mx-auto flex min-h-screen w-full max-w-xl flex-col gap-4 px-4 pb-24 pt-6">
    <div class="space-y-2">
        <flux:heading size="lg">{{ __('profiles.title') }}</flux:heading>
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
</div>
