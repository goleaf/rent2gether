# Notification Mobile Performance

Notification UI is optimized for old phones and slow 3G.

Rules:

- cursor pagination
- load the newest 20 notifications first
- avoid delivery logs in the normal UI
- keep Livewire public state small
- use action buttons instead of heavy modal stacks
- refresh urgent panels cautiously with `wire:poll.visible.30s`
- update the notification center after user actions rather than polling every second

Notification lists should show compact translated summaries and link to full protected pages for detailed context.
