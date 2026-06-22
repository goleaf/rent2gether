<?php

return [
    'title' => 'Reviews',

    'fields' => [
        'review' => 'Review',
        'overall_rating' => 'Overall rating',
        'public_comment' => 'Public comment',
        'private_comment' => 'Private comment',
        'what_liked' => 'What did you like?',
        'what_disliked' => 'What could be better?',
        'advice_to_future_guests' => 'Advice for future guests',
        'recommend' => 'Recommend',
        'wants_to_return' => 'Would return',
        'status' => 'Status',
        'submitted_at' => 'Submitted',
        'published_at' => 'Published',
        'due_at' => 'Due by',
    ],

    'request_types' => [
        'guest_reviews_place' => 'Guest reviews the place',
        'host_reviews_guest' => 'Host reviews the guest',
        'guest_reviews_roommates' => 'Guest reviews roommates',
        'guest_reviews_check_in' => 'Guest reviews check-in',
        'guest_reviews_check_out' => 'Guest reviews checkout',
        'guest_reviews_problem_resolution' => 'Guest reviews problem resolution',
    ],

    'subject_types' => [
        'property' => 'Property',
        'room' => 'Room',
        'sleeping_place' => 'Sleeping place',
        'host' => 'Host',
        'guest' => 'Guest',
        'roommates' => 'Roommates',
        'check_in' => 'Check-in',
        'check_out' => 'Checkout',
        'problem_resolution' => 'Problem resolution',
        'overall_booking' => 'Overall booking',
    ],

    'statuses' => [
        'draft' => 'Draft',
        'pending' => 'Pending',
        'submitted' => 'Submitted',
        'waiting_other_party' => 'Waiting for the other side',
        'pending_publish' => 'Pending publication',
        'published' => 'Published',
        'hidden' => 'Hidden',
        'flagged' => 'Flagged',
        'expired' => 'Expired',
        'cancelled' => 'Cancelled',
        'removed_future' => 'Removed in future',
        'disputed_future' => 'Disputed in future',
        'closed' => 'Closed',
    ],

    'request_statuses' => [
        'created' => 'Created',
        'sent' => 'Sent',
        'opened' => 'Opened',
        'started' => 'Started',
        'submitted' => 'Submitted',
        'expired' => 'Expired',
        'cancelled' => 'Cancelled',
        'closed' => 'Closed',
    ],

    'roommates' => [
        'title' => 'Roommate experience',
    ],

    'roommate_fields' => [
        'quiet_roommates' => 'Roommates were quiet',
        'clean_roommates' => 'Roommates kept things clean',
        'friendly_roommates' => 'Roommates were friendly',
        'roommates_disturbed_sleep' => 'Roommates disturbed sleep',
        'roommates_broke_rules' => 'Roommates broke rules',
        'conflict_happened' => 'There was a conflict',
    ],

    'filters' => [
        'all' => 'All',
        'cleanliness' => 'Cleanliness',
        'noise' => 'Noise',
        'internet' => 'Internet',
        'roommates' => 'Roommates',
        'photos' => 'Photos',
        'long_stay' => 'Long stay',
        'short_stay' => 'Short stay',
    ],

    'actions' => [
        'leave_review' => 'Leave a review',
        'review_guest' => 'Review the guest',
        'submit_review' => 'Submit review',
        'edit_review' => 'Edit review',
        'respond_to_review' => 'Respond to review',
        'publish_response' => 'Publish response',
        'upload_photo' => 'Add photo',
        'publish' => 'Publish',
        'view_reviews' => 'View reviews',
    ],

    'messages' => [
        'review_requested' => 'Please leave a review about this stay.',
        'double_blind_notice' => 'Your review will be published when both sides review or when the review window closes.',
        'submitted' => 'Review submitted.',
        'published' => 'Review published.',
        'expired' => 'The review window has expired.',
        'no_reviews_yet' => 'No reviews yet.',
        'unconfirmed_no_rating_impact' => 'Unconfirmed issues do not automatically affect ratings.',
        'roommate_privacy_notice' => 'Roommate feedback is shown only as a privacy-safe summary.',
        'photo_privacy_notice' => 'Photos with sensitive details should not be published.',
        'public_review_helper' => 'Published review details are visible according to privacy rules.',
        'host_review_helper' => 'Rate the guest after a real completed stay.',
        'host_reviews_helper' => 'Review requests and reputation are based on completed stays.',
    ],

    'validation' => [
        'not_author' => 'Only the assigned reviewer can submit this review.',
        'published_not_editable' => 'Published reviews cannot be edited.',
        'edit_window_closed' => 'The edit window has closed.',
        'request_not_open' => 'This review request is no longer open.',
        'request_expired' => 'The review request has expired.',
        'wrong_request_type' => 'This review form does not match the request.',
        'overall_required' => 'Please choose an overall rating from 1 to 5.',
        'already_submitted' => 'A review for this booking was already submitted.',
        'response_not_allowed' => 'You cannot respond to this review.',
        'score_between' => 'Ratings must be between 1 and 5.',
    ],

    'empty_states' => [
        'requests' => 'There are no review requests right now.',
        'reviews' => 'Reviews will appear here after publication.',
    ],
];
