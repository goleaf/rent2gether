@if($notification)
    <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-950">
        <flux:heading size="sm">{{ $notification->title() }}</flux:heading>
        <flux:text size="sm">{{ $notification->body() }}</flux:text>
    </div>
@endif
