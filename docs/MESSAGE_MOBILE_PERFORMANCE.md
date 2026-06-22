# Message Mobile Performance

The messaging UI is designed for old Android devices and slow 3G.

Rules:

- load the newest 20-30 messages first
- use cursor pagination or load-more for older history
- keep Livewire public properties to IDs, filters, and short input state
- avoid storing full message history or full attachment lists in public state
- use thumbnails for images
- avoid heavy realtime JavaScript libraries in the MVP
- use cautious `wire:poll.visible.10s` only on focused message surfaces
- do not render large hidden template/filter DOM blocks

Composer fields use `wire:model.blur` for normal text and `wire:model.live.debounce.500ms` only for search.
