# No-show Disputes

A guest can dispute no-show when they believe the report is wrong.

Common dispute reasons:

- guest arrived but host did not answer
- address or instructions were unavailable
- access code did not work
- guest warned about late arrival
- host mistakenly reported no-show

Disputing a no-show sets the no-show status to `dispute_opened`, marks the booking as dispute opened, and stores `future_support_review_required` as hidden future-ready data.

Normal guest and host views must not show `future_support_comment`. The privacy service filters future support fields and enforces media visibility rules.
