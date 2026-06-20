# User verification rules

Verification statuses are stored in `user_verifications` with:

- verification type
- status
- provider
- verified and expiry timestamps
- rejection reason
- private metadata

Hosts can see verification status only, such as phone verified, email verified, and identity verified.

Hosts must never see document file paths, document scans, ID numbers, passport details, or private verification metadata. `user_documents` exists for future-ready document checks and must remain system-private.
