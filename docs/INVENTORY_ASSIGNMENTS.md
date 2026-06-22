# Inventory Assignments

`BookingInventoryAssignment` records items issued to a guest for a booking.

The assignment stores:

- booking, guest, host, property, room, and sleeping-place context;
- the issued item and optional unit;
- issue time and issuer;
- whether return is expected;
- guest and host confirmations;
- condition at issue and return.

Flow:

1. Host issues item during check-in, stay, relocation, or replacement.
2. Guest may confirm receipt.
3. Host or guest may mark return.
4. If the item is not returned or returned damaged, an `InventoryIssue` is created.

Returnable items must set `expected_return = true`. Lost or damaged assignments must not deduct deposit automatically.

