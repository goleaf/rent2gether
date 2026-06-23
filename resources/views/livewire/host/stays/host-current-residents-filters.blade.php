<div class="flex gap-2 overflow-x-auto pb-1">
    @foreach ($filters as $key => $label)
        <flux:badge color="{{ $activeFilter === $key ? 'emerald' : 'zinc' }}" icon="check-circle">{{ $label }}</flux:badge>
    @endforeach
</div>
