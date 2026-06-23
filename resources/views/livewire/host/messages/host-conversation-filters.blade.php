<section class="flex gap-2 overflow-x-auto pb-1">
    @forelse(['active', 'waiting_host_response', 'closed', 'archived'] as $filterStatus)
        <flux:button
            type="button"
            size="sm"
            variant="{{ $status === $filterStatus ? 'primary' : 'ghost' }}"
            wire:click="setStatus('{{ $filterStatus }}')"
            class="shrink-0"
         icon="funnel">
            {{ __('messages.statuses.'.$filterStatus) }}
        </flux:button>
    @empty
        <flux:text size="sm">{{ __('messages.empty_states.conversations') }}</flux:text>
    @endforelse
</section>
