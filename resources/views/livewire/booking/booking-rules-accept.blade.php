<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="lg">{{ __('booking.flow.rules.title') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('booking.flow.rules.helper') }}</flux:text>
    </div>

    @forelse($rulesByGroup as $category => $rules)
        <div class="space-y-2">
            <flux:heading size="sm">{{ __('listing.detail.rules.categories.'.$category) }}</flux:heading>
            <div class="grid gap-2">
                @foreach($rules as $rule)
                    <div class="rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                        {{ $rule }}
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="rounded-lg bg-zinc-50 px-3 py-3 text-sm text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400">
            {{ __('booking.flow.rules.empty') }}
        </div>
    @endforelse

    <flux:checkbox wire:model.change="accepted" label="{{ __('booking.flow.fields.rules_accepted') }}" />
</flux:card>
