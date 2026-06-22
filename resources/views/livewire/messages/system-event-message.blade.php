<div class="rounded-lg bg-zinc-50 px-3 py-2 text-center dark:bg-zinc-900">
    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-300">
        {{ $translationKey !== '' ? __($translationKey, $params) : __('messages.message_types.system_event') }}
    </flux:text>
</div>
