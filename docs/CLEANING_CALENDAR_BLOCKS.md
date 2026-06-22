# Cleaning Calendar Blocks

Cleaning and inspection can block sleeping-place availability.

Blocking rules:

- required cleaning blocks the affected sleeping-place date as `cleaning`;
- inspection blocks the affected date as `waiting_inspection`;
- repair or safety issues keep the date blocked as `repair`;
- completed cleaning can release the block only when inspection, repair, and issue flags allow it;
- passed inspection can release the inspection block;
- readiness can open the calendar after all requirements pass.

The services only block the affected sleeping place unless the task scope explicitly belongs to a broader room or property context.
