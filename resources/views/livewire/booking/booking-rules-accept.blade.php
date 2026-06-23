<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('booking.flow.rules.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('booking.flow.rules.helper') }}</flux:text>
    </div>

    @forelse($rulesByGroup as $category => $rules)
        <div class="space-y-2">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('listing.detail.rules.categories.'.$category) }}</span>
                </span>
            </flux:heading>
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

        <flux:field variant="inline">
        <flux:checkbox wire:model.change="accepted" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('booking.flow.fields.rules_accepted') }}</span>
            </span>
        </flux:label>
        <flux:error name="accepted" />
    </flux:field>
</flux:card>
