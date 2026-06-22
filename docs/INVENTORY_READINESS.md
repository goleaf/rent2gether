# Inventory Readiness

Inventory affects `PlaceReadinessCheck.inventory_ready`.

A sleeping place is not ready when a required inventory item is missing, lost, damaged, retired, disposed, or needs replacement.

Common readiness-required items:

- key or access method;
- bedding when included;
- towel when included;
- mattress;
- lamp;
- locker or locker lock when promised.

`InventoryReadinessIntegrationService` checks required items and can mark readiness as `waiting_inventory` with `required_inventory_missing`.

