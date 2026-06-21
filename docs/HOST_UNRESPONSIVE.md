# Host Unresponsive Flow

Host unresponsive starts when a guest cannot get check-in help or access details from the host. The flow belongs to a booking and sleeping place, and usually links to a check-in record.

The guest reports the problem, the platform creates a `booking_host_unresponsive_cases` record, contacts the host, contacts a representative if one is available, shows only allowed check-in instructions, and starts a response deadline.

An active case blocks automatic no-show confirmation. If the host or representative responds and access is resolved, check-in continues. If the deadline expires and nobody responds, the case can be confirmed as unresolved and moved to cancellation, relocation, complaint, refund, or dispute flows.

Representatives are contact records or future users. They are not managers, staff, support users, or a new role.
