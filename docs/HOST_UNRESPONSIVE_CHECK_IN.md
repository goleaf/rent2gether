# Host Unresponsive and Check-in

Host unresponsive usually starts from a check-in screen or from a check-in problem report.

When the case is created, the check-in record is marked as `host_unresponsive`, with the problem status set to `reported`. The guest can see allowed instructions through the check-in access disclosure service.

If access is resolved, check-in status becomes `check_in_continued`. If the case is confirmed unresolved, check-in is marked `failed` and the booking can move to `host_unresponsive`.

Access disclosures are logged through `booking_check_in_access_disclosures`; sensitive codes should only be disclosed when the check-in privacy rules allow them.
