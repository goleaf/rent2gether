# Check-out Inventory

Inventory checks are stored in `booking_check_out_inventory_checks`.

The MVP checklist covers:

- key;
- access card;
- locker;
- bedding;
- towel.

Each item can be returned, lost, damaged, or marked as needing replacement.

Lost or damaged items can request a deposit deduction with amount and currency. This updates the parent check-out flags:

- `has_inventory_issue`;
- `has_lost_items`;
- `has_lost_key`;
- `deposit_review_required`;
- `deposit_deduction_requested`.

