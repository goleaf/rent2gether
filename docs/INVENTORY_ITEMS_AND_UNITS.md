# Inventory Items And Units

`InventoryItem` is the host-facing catalog item. It records type, scope, status, condition, quantity, replacement cost, readiness flags, listing promise flags, and current location.

`InventoryItemUnit` is used when a host wants to track individual physical units, for example:

- four keys;
- ten towels;
- six bedding sets;
- several access cards.

Use item-level tracking for stable assets and small quantities. Use unit-level tracking when the exact item issued to a guest matters.

Statuses:

- `available` means the item can be used.
- `issued_to_guest` means it is currently with a guest.
- `needs_washing`, `needs_cleaning`, and `needs_repair` keep the item out of normal readiness until resolved.
- `lost`, `missing`, `damaged`, `retired`, and `disposed` are not ready states.

