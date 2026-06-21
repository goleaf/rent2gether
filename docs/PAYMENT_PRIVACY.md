# Payment Privacy

Guests can view their own payment records and refunds.

Hosts can view payment status and booking payment context for their own bookings. Hosts must not see private payment provider details.

Hidden from normal UI:

- provider payload JSON;
- provider payment IDs;
- provider attempt IDs;
- private provider errors;
- card data;
- CVV;
- full card numbers.

The application currently stores only internal payment state, amount, currency, attempts, deadlines, allocations, logs, basic refunds, and future-ready receipt data.

