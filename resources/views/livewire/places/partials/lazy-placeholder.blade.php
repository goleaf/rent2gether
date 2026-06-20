<flux:card class="space-y-3">
    <div class="flex items-center gap-3">
        <flux:skeleton class="size-10 rounded-full" />
        <div class="flex-1 space-y-2">
            <flux:skeleton class="h-4 w-2/3" />
            <flux:skeleton class="h-3 w-1/2" />
        </div>
    </div>
    <flux:text size="sm" class="text-zinc-500">{{ $label }}</flux:text>
</flux:card>
