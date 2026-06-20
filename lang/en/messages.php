<?php

return [
    'inbox' => [
        'eyebrow' => 'Messages',
        'title' => 'Messages',
        'helper' => 'Talk with hosts and guests about availability, booking details, arrival, and current stays.',
        'empty_title' => 'No conversations yet',
        'empty_text' => 'Messages will appear here after you contact a host or receive a booking question.',
        'empty_action' => 'Find a place',
        'unknown_user' => 'Conversation',
        'no_messages' => 'No messages yet.',
    ],
    'thread' => [
        'title' => 'Conversation',
        'back' => 'Back',
        'address_note' => 'Exact address stays hidden until booking rules allow it.',
        'empty' => 'Send a short message when you are ready.',
        'read' => 'Read',
        'important' => 'Important',
        'attachment' => 'Attachment',
        'fields' => [
            'body' => 'Message',
            'attachments' => 'Attachments',
            'important' => 'Mark important',
        ],
        'placeholders' => [
            'body' => 'Write a clear, short message...',
        ],
        'attachments_helper' => 'Add up to 3 images or PDF documents, 5 MB each.',
        'actions' => [
            'send' => 'Send message',
            'sending' => 'Sending...',
        ],
    ],
    'templates' => [
        'guest' => [
            'available' => 'Is this place available?',
            'late_checkin' => 'Can I check in late?',
            'keys' => 'Where do I get the keys?',
            'arriving' => 'I am arriving soon.',
            'extend' => 'Can I extend my stay?',
            'problem' => 'There is a problem.',
        ],
        'host' => [
            'confirmed' => 'Your booking is confirmed.',
            'instructions' => 'Here are check-in instructions.',
            'arrival_time' => 'Please confirm arrival time.',
            'rules' => 'Please read the rules.',
            'thanks' => 'Thank you for staying.',
        ],
    ],
    'validation_attributes' => [
        'body' => 'message',
        'uploads' => 'attachments',
        'uploads.*' => 'attachment',
        'important' => 'important marker',
    ],
    'errors' => [
        'empty_message' => 'Write a message or add an attachment before sending.',
        'thread_unavailable' => 'This conversation is not available right now.',
        'recipient_missing' => 'We could not find who should receive this message.',
        'not_participant' => 'This conversation belongs to other people.',
        'address_hidden' => 'Exact address can be shared only after booking rules allow it. Send general arrival details for now.',
    ],
];
