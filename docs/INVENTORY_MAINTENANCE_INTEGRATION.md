# Inventory Maintenance Integration

Broken or damaged inventory can start a maintenance flow.

Examples:

- broken lamp;
- damaged locker lock;
- broken bed frame;
- router failure;
- damaged mattress.

The inventory issue records the broken item and links the maintenance request identifier when one exists. When maintenance is completed, the item can be marked available, repaired, retired, or replaced.

The current inventory module keeps maintenance integration as a service seam so the repair module can own its future tables and workflows.

