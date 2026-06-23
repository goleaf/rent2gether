<div class="flex flex-wrap gap-2" aria-label="{{ __('common.language_switcher.label') }}">
    @forelse($locales as $supportedLocale)
        <flux:button
            type="button"
            size="sm"
            variant="{{ $locale === $supportedLocale ? 'primary' : 'ghost' }}"
            wire:click="switchLocale('{{ $supportedLocale }}')"
            wire:loading.attr="disabled"
         icon="language">
            {{ __('navigation.languages.'.$supportedLocale) }}
        </flux:button>
    @empty
        <flux:text>{{ __('common.language_switcher.empty') }}</flux:text>
    @endforelse
</div>
