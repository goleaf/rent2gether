# Check-out Deposit Integration

Check-out starts the deposit review after the guest leaves and host checks the place.

If there are no issues, `BookingCheckOutDepositIntegrationService::startDepositReturnIfNoIssues()` delegates to the existing deposit decision service and prepares a full return.

If there is damage, lost inventory, extra dirt, or another deposit-related issue, the check-out can request a deduction. The request stores:

- amount;
- currency;
- reason;
- related checkout issue;
- status/event history.

The checkout module does not implement the full dispute workflow. It records the handoff fields and keeps dispute states future-ready without creating support, staff, or finance panels.

