# Inventory

Inventory tracks host-owned items at the `Property -> Room -> SleepingPlace -> Booking -> Guest` levels.

Examples include keys, access cards, bedding, towels, lockers, lamps, routers, furniture, cleaning supplies, and promised listing items.

Core tables:

- `inventory_categories` stores translated category keys.
- `inventory_items` stores the main item record and its property/room/place context.
- `inventory_item_units` stores individually tracked units such as one key from a set.
- `inventory_movements` stores location history.
- `inventory_events` stores timeline events.

Important rules:

- Every item must belong to a property.
- Room and sleeping-place context are nullable because some items are property-level.
- Guests can see only issued items and public listing-visible items.
- Purchase prices, host notes, internal notes, and private operational details stay host-only.
- Required inventory can block place readiness.
- Promised inventory can trigger a listing mismatch when missing.

