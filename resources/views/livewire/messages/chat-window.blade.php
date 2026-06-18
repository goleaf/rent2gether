<div class="max-w-3xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <flux:button size="sm" variant="ghost" href="{{ route('messages.index', ['locale' => app()->getLocale()]) }}" icon="arrow-left" />
            <flux:heading size="lg">{{ $otherUser->name }}</flux:heading>
        </div>
        @if($conversation->bed)
            <flux:badge size="sm">{{ $conversation->bed->title }}</flux:badge>
        @endif
    </div>

    <div class="border rounded-xl dark:border-zinc-700 h-[500px] overflow-y-auto p-4 space-y-3" wire:poll.5s>
        @foreach($this->messages as $message)
            <div class="{{ $message->sender_id === auth()->id() ? 'ml-auto' : 'mr-auto' }} max-w-[75%]">
                @if($message->is_system_message)
                    <div class="text-center">
                        <flux:text size="sm" class="text-zinc-400 italic">{{ $message->body }}</flux:text>
                    </div>
                @else
                    <div class="{{ $message->sender_id === auth()->id() ? 'bg-blue-500 text-white' : 'bg-zinc-100 dark:bg-zinc-800' }} rounded-xl px-4 py-2">
                        <flux:text>{{ $message->body }}</flux:text>
                    </div>
                    <flux:text size="sm" class="text-zinc-400 mt-0.5 {{ $message->sender_id === auth()->id() ? 'text-right' : '' }}">
                        {{ $message->created_at->format('H:i') }}
                        @if($message->sender_id === auth()->id() && $message->read_at)
                            &middot; {{ __('Read') }}
                        @endif
                    </flux:text>
                @endif
            </div>
        @endforeach
    </div>

    <form wire:submit="send" class="flex gap-2">
        <flux:input wire:model="body" placeholder="{{ __('Type a message...') }}" class="flex-1" />
        <flux:button type="submit" variant="primary" icon="paper-airplane" />
    </form>
</div>
