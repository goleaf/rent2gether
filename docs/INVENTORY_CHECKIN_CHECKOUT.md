# Inventory Check-In And Checkout

Check-in can issue default guest items:

- keys;
- access cards or codes;
- bedding;
- towels;
- locker or locker lock.

Checkout creates an inventory return checklist for expected returnable items. The checklist is stored in:

- `inventory_checks`;
- `inventory_check_items`.

Checkout flags such as keys returned, bedding returned, towel returned, locker cleared, and inventory issue found are synced from assignments and check items.

If a check item is missing, damaged, or requires repair or replacement, the failed row creates an `InventoryIssue`.

