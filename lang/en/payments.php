<?php

return [
    'title' => 'Payment',

    'fields' => [
        'payment_number' => 'Payment number',
        'refund_number' => 'Refund number',
        'booking' => 'Booking',
        'amount' => 'Amount',
        'currency' => 'Currency',
        'payment_type' => 'Payment type',
        'payment_method' => 'Payment method',
        'payment_status' => 'Payment status',
        'payment_deadline' => 'Payment deadline',
        'paid_at' => 'Paid at',
        'failed_at' => 'Failed at',
        'refundable_amount' => 'Refundable amount',
        'non_refundable_amount' => 'Non-refundable amount',
        'remaining_amount' => 'Remaining amount',
        'attempt_number' => 'Attempt',
    ],

    'cards' => [
        'breakdown' => 'Payment breakdown',
        'attempts' => 'Payment attempts',
        'receipt' => 'Receipt',
        'refund' => 'Refund',
    ],

    'host' => [
        'status_title' => 'Guest payment',
        'summary_title' => 'Payment summary',
        'refund_title' => 'Refund status',
    ],

    'statuses' => [
        'unpaid' => 'Unpaid',
        'waiting_payment' => 'Waiting for payment',
        'payment_started' => 'Payment started',
        'pending' => 'Processing',
        'partially_paid' => 'Partially paid',
        'paid' => 'Paid',
        'failed' => 'Payment failed',
        'expired' => 'Payment expired',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
        'partially_refunded' => 'Partially refunded',
        'disputed' => 'Payment dispute',
    ],

    'attempt_statuses' => [
        'created' => 'Created',
        'started' => 'Started',
        'requires_action' => 'Action required',
        'processing' => 'Processing',
        'succeeded' => 'Succeeded',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled',
        'expired' => 'Expired',
        'provider_redirect_required' => 'Provider redirect required',
        'provider_webhook_pending' => 'Provider confirmation pending',
        'provider_confirmed' => 'Provider confirmed',
        'provider_failed' => 'Provider failed',
    ],

    'methods' => [
        'internal_test' => 'Test payment',
        'card_future' => 'Bank card',
        'bank_transfer_future' => 'Bank transfer',
        'cash_future' => 'Cash',
        'manual_confirmation_future' => 'Manual confirmation',
        'wallet_future' => 'Wallet',
        'promo_credit_future' => 'Promo credit',
    ],

    'payment_types' => [
        'full_payment' => 'Full payment',
        'partial_payment' => 'Partial payment',
        'deposit_only' => 'Deposit only',
        'remaining_balance' => 'Remaining balance',
        'extension_payment' => 'Extension payment',
        'relocation_difference' => 'Relocation difference',
        'manual_future' => 'Manual payment',
    ],

    'payment_purposes' => [
        'booking_payment' => 'Booking payment',
        'deposit_payment' => 'Deposit payment',
        'extension_payment' => 'Extension payment',
        'relocation_payment' => 'Relocation payment',
        'price_difference_payment' => 'Price difference payment',
        'cleaning_fee_payment' => 'Cleaning fee payment',
        'service_fee_payment' => 'Service fee payment',
        'manual_adjustment_future' => 'Manual adjustment',
    ],

    'allocation_types' => [
        'accommodation' => 'Accommodation',
        'cleaning_fee' => 'Cleaning fee',
        'guest_service_fee' => 'Service fee',
        'deposit' => 'Deposit',
        'tax_future' => 'Tax',
        'city_fee_future' => 'City fee',
        'extra_guest_fee' => 'Extra guest',
        'early_check_in_fee' => 'Early check-in',
        'late_checkout_fee' => 'Late checkout',
        'extension_amount' => 'Extension',
        'relocation_difference' => 'Relocation difference',
        'other' => 'Other',
    ],

    'refund_types' => [
        'full_refund' => 'Full refund',
        'partial_refund' => 'Partial refund',
        'deposit_refund' => 'Deposit refund',
        'cleaning_fee_refund' => 'Cleaning fee refund',
        'service_fee_refund' => 'Service fee refund',
        'cancellation_refund' => 'Cancellation refund',
        'relocation_refund' => 'Relocation refund',
        'overpayment_refund' => 'Overpayment refund',
        'manual_future' => 'Manual refund',
    ],

    'refund_statuses' => [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled',
    ],

    'refund_reasons' => [
        'guest_cancelled' => 'Guest cancellation',
        'partial_adjustment' => 'Partial adjustment',
        'deposit_return' => 'Deposit return',
    ],

    'deadline_types' => [
        'initial_payment' => 'Initial payment',
        'remaining_balance' => 'Remaining balance',
        'deposit_payment' => 'Deposit payment',
        'extension_payment' => 'Extension payment',
        'relocation_payment' => 'Relocation payment',
        'manual_future' => 'Manual deadline',
    ],

    'deadline_statuses' => [
        'pending' => 'Pending',
        'completed' => 'Completed',
        'expired' => 'Expired',
        'cancelled' => 'Cancelled',
        'extended' => 'Extended',
    ],

    'receipt_statuses' => [
        'draft' => 'Draft',
        'issued' => 'Issued',
        'cancelled' => 'Cancelled',
        'failed' => 'Failed',
    ],

    'actions' => [
        'pay' => 'Pay',
        'retry_payment' => 'Try again',
        'cancel_payment' => 'Cancel payment',
        'open_receipt' => 'Open receipt',
        'change_payment_method' => 'Save payment method',
    ],

    'messages' => [
        'payment_required' => 'Payment is required to confirm this booking.',
        'payment_succeeded' => 'Payment was successful.',
        'payment_failed' => 'Payment did not go through.',
        'payment_expired' => 'Payment deadline has expired.',
        'locks_released' => 'The dates are no longer held.',
        'deposit_refundable' => 'This part is refundable after checkout if there are no issues.',
    ],

    'validation' => [
        'not_allowed' => 'You cannot manage this payment.',
        'deadline_expired' => 'The payment deadline has expired.',
    ],

    'empty_states' => [
        'no_allocations' => 'No payment lines yet.',
        'no_deadline' => 'No payment deadline is set.',
        'no_attempts' => 'No payment attempts yet.',
        'no_receipt' => 'No receipt has been issued yet.',
        'no_payment_methods' => 'No payment methods are available yet.',
        'no_refunds' => 'No refunds yet.',
    ],
];
