<?php

return [
    'title' => 'Disputes',
    'host_title' => 'Host disputes',

    'fields' => [
        'dispute_number' => 'Dispute number',
        'complaint' => 'Complaint',
        'booking' => 'Booking',
        'opened_by' => 'Opened by',
        'dispute_type' => 'Dispute type',
        'severity' => 'Severity',
        'status' => 'Status',
        'amount_disputed' => 'Disputed amount',
        'proposed_resolution' => 'Proposed resolution',
        'final_resolution' => 'Final resolution',
        'booking_frozen' => 'Booking frozen',
        'refund_frozen' => 'Refund frozen',
        'deposit_frozen' => 'Deposit frozen',
        'host_payout_frozen' => 'Host payout frozen',
    ],

    'types' => [
        'refund_dispute' => 'Refund dispute',
        'deposit_dispute' => 'Deposit dispute',
        'damage_dispute' => 'Damage dispute',
        'cancellation_dispute' => 'Cancellation dispute',
        'no_show_dispute' => 'No-show dispute',
        'host_unresponsive_dispute' => 'Host unresponsive dispute',
        'listing_mismatch_dispute' => 'Listing mismatch dispute',
        'payment_dispute' => 'Payment dispute',
        'relocation_dispute' => 'Relocation dispute',
        'review_dispute_future' => 'Review dispute later',
        'safety_dispute' => 'Safety dispute',
        'other' => 'Other',
    ],

    'statuses' => [
        'draft' => 'Draft',
        'opened' => 'Opened',
        'evidence_requested' => 'Evidence requested',
        'evidence_submitted' => 'Evidence submitted',
        'waiting_guest_response' => 'Waiting for guest',
        'waiting_host_response' => 'Waiting for host',
        'negotiation' => 'Negotiation',
        'resolution_proposed' => 'Resolution proposed',
        'resolution_accepted' => 'Resolution accepted',
        'resolution_rejected' => 'Resolution rejected',
        'decision_pending_future' => 'Future decision pending',
        'decision_recorded_future' => 'Future decision recorded',
        'refund_pending' => 'Refund pending',
        'refund_completed' => 'Refund completed',
        'deposit_action_pending' => 'Deposit action pending',
        'deposit_action_completed' => 'Deposit action completed',
        'booking_frozen' => 'Booking frozen',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
        'cancelled' => 'Cancelled',
    ],

    'resolution_types' => [
        'guest_refund_full' => 'Full refund to guest',
        'guest_refund_partial' => 'Partial refund to guest',
        'no_refund' => 'No refund',
        'deposit_return_full' => 'Full deposit return',
        'deposit_return_partial' => 'Partial deposit return',
        'deposit_deduction_approved' => 'Deposit deduction approved',
        'deposit_deduction_rejected' => 'Deposit deduction rejected',
        'host_payout_full' => 'Full host payout',
        'host_payout_partial' => 'Partial host payout',
        'relocation_required' => 'Relocation required',
        'cancellation_confirmed' => 'Cancellation confirmed',
        'complaint_rejected' => 'Complaint rejected',
        'complaint_confirmed' => 'Complaint confirmed',
        'no_action' => 'No action',
        'future_manual_decision' => 'Future manual decision',
    ],

    'freeze_labels' => [
        'booking_frozen' => 'Booking',
        'refund_frozen' => 'Refund',
        'deposit_frozen' => 'Deposit',
        'host_payout_frozen' => 'Host payout',
        'rating_impact_frozen' => 'Rating impact',
    ],

    'proposal_statuses' => [
        'offered' => 'Offered',
        'accepted_by_guest' => 'Accepted by guest',
        'accepted_by_host' => 'Accepted by host',
        'accepted_by_both' => 'Accepted by both',
        'rejected' => 'Rejected',
        'expired' => 'Expired',
        'cancelled' => 'Cancelled',
        'applied' => 'Applied',
    ],

    'decision_types' => [
        'mutual_agreement' => 'Mutual agreement',
        'system_rule' => 'System rule',
        'future_reviewer' => 'Future reviewer',
    ],

    'actions' => [
        'open_dispute' => 'Open dispute',
        'add_evidence' => 'Add evidence',
        'send_message' => 'Send message',
        'create_proposal' => 'Create proposal',
        'accept_proposal' => 'Accept proposal',
        'reject_proposal' => 'Reject proposal',
        'close_dispute' => 'Close dispute',
    ],

    'messages' => [
        'dispute_opened' => 'Dispute opened.',
        'some_actions_frozen' => 'Some actions may be paused until the dispute is resolved.',
        'proposal_sent' => 'Proposal sent.',
        'proposal_accepted' => 'Proposal accepted.',
        'dispute_resolved' => 'Dispute resolved.',
    ],

    'notifications' => [
        'opened' => ['title' => 'Dispute opened', 'body' => 'A dispute was opened for this booking.'],
        'evidence_requested' => ['title' => 'Evidence requested', 'body' => 'More dispute evidence was requested.'],
        'evidence_submitted' => ['title' => 'Evidence submitted', 'body' => 'New dispute evidence was added.'],
        'proposal_created' => ['title' => 'Proposal created', 'body' => 'A dispute resolution proposal was created.'],
        'proposal_accepted' => ['title' => 'Proposal accepted', 'body' => 'A dispute proposal was accepted.'],
        'proposal_rejected' => ['title' => 'Proposal rejected', 'body' => 'A dispute proposal was rejected.'],
        'resolved' => ['title' => 'Dispute resolved', 'body' => 'The dispute was resolved.'],
        'closed' => ['title' => 'Dispute closed', 'body' => 'The dispute was closed.'],
    ],

    'validation' => [
        'cannot_upload_evidence' => 'You cannot add evidence to this dispute.',
        'cannot_message' => 'You cannot message in this dispute.',
    ],

    'empty' => [
        'no_disputes' => 'No disputes yet.',
    ],
];
