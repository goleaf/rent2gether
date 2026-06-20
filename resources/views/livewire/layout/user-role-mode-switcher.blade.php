<div class="flex flex-wrap gap-2" aria-label="{{ __('common.role_mode_switcher.label') }}">
    @forelse($modes as $roleMode)
        <flux:button
            type="button"
            size="sm"
            variant="{{ $mode === $roleMode->value ? 'primary' : 'ghost' }}"
            wire:click="switchMode('{{ $roleMode->value }}')"
            wire:loading.attr="disabled"
        >
            {{ $roleMode->label() }}
        </flux:button>
    @empty
        <flux:text>{{ __('common.role_mode_switcher.empty') }}</flux:text>
    @endforelse
</div>
