# Mobile First Rules

Every primary screen must work first at 320px to 430px wide.

Use:

- cards instead of large tables
- step-by-step forms for host setup
- sticky bottom primary actions
- bottom sheets or drawers for secondary filters
- accordions for long details
- skeleton and loading states for network actions
- cursor pagination or load-more behavior for large lists
- compact Livewire public state with scalar IDs and short strings

Avoid:

- huge hidden DOM sections
- full country or city select lists
- loading maps on the first search screen
- rendering full calendars for many sleeping places at once
- loading all chat messages or all search results at once
- live updates for long textareas

Use `wire:model.blur` for ordinary text fields, `wire:model.change` for selects and toggles, and debounced live models only for search/autocomplete.

Public cards and host dashboard cards should query selected columns and return compact arrays or DTOs.
