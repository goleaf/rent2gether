# No-show Waiting Period

No-show cannot be confirmed immediately after the host reports the guest absent.

The waiting period starts from the no-show watch or host report and is stored as `waiting_until`. Confirmation is allowed only when `waiting_until` has passed or when the guest explicitly accepts no-show / says they will not arrive.

Guest responses can extend or stop the waiting period:

- `i_am_on_the_way` keeps the waiting period active.
- `i_am_late` stores `new_arrival_time` and extends `waiting_until`.
- `i_arrived` blocks no-show confirmation.
- `i_have_check_in_problem` blocks confirmation and marks check-in as problem reported.
- `host_not_answering` converts the flow away from no-show into host unresponsive.

This protects guests from a premature no-show decision and protects hosts by preserving all contact attempts and responses.
