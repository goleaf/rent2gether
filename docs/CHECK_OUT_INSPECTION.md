# Check-out Inspection

Inspection records whether the exact room and sleeping place are ready after a guest leaves.

Host-side inspection can mark:

- room checked;
- property checked;
- sleeping place cleared;
- damage;
- extra dirt;
- repair required;
- cleaning required.

`BookingCheckOutInspectionService::startInspection()` creates or reuses a host inspection task and moves the check-out to `inspection_in_progress`.

`completeInspection()` records the result and creates a `inspection_completed` event. If damage or extra dirt exists, the flow stays in a problem/deposit path instead of opening availability immediately.

