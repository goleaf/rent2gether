# Basic Refund Records

Refunds are stored in `booking_refunds`.

The MVP creates internal records only. Real provider refund handling can be added later using provider fields.

Refund types:

- `full_refund`
- `partial_refund`
- `deposit_refund`
- `cleaning_fee_refund`
- `service_fee_refund`
- `cancellation_refund`
- `relocation_refund`
- `overpayment_refund`
- `manual_future`

Refund statuses:

- `pending`
- `approved`
- `processing`
- `completed`
- `failed`
- `cancelled`

Each refund keeps booking, guest, host, property, room, and sleeping place context.

