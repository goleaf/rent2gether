<?php

return [
    'title' => 'Publication',
    'helper' => 'Automatic checks show whether guests can already see this listing.',
    'checklist_title' => 'Before publishing',
    'checklist_helper' => 'Required items block publication. Suggestions can be improved later.',
    'blocking_issues' => 'Required before publishing',
    'recommended_issues' => 'Recommended improvements',
    'actions' => [
        'save_draft' => 'Save draft',
        'fix_issues' => 'Fix issues',
        'publish' => 'Publish',
        'request_review' => 'Send to review',
    ],
    'review_statuses' => [
        'not_required' => 'Review not required',
        'not_requested' => 'Not requested',
        'pending' => 'Pending review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'auto_approved' => 'Automatically approved',
        'auto_rejected' => 'Automatically rejected',
    ],
];
