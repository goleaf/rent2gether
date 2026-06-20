<?php

return [
    'default_caption' => 'Photo',
    'flash' => [
        'uploaded' => 'Saved',
        'deleted' => 'Photo deleted',
        'primary_set' => 'Primary photo updated',
    ],
    'validation' => [
        'max_items' => 'You can add up to :count photos here. Delete one photo before adding another.',
    ],
    'validation_attributes' => [
        'photo' => 'photo',
        'captionEn' => 'English caption',
        'captionRu' => 'Russian caption',
    ],
    'manager' => [
        'title' => 'Photos',
        'helper' => 'Add clear photos one by one. We create smaller versions for phones automatically.',
        'file_label' => 'Add photo',
        'file_helper' => 'JPG, PNG, or WebP. Up to 4 MB.',
        'uploading' => 'Uploading',
        'preview_alt' => 'Selected photo preview',
        'caption_en' => 'Caption in English',
        'caption_ru' => 'Caption in Russian',
        'warning' => 'Use clear photos that show the real place. We will use smaller images on mobile cards.',
        'primary' => 'Primary',
        'dimensions' => ':width x :height px',
        'optimized' => 'Optimized for mobile',
        'empty' => 'No photos yet. Add one clear photo to help guests understand the place.',
        'delete_confirm' => 'Delete this photo?',
        'actions' => [
            'save' => 'Save photo',
            'saving' => 'Saving...',
            'up' => 'Up',
            'down' => 'Down',
            'primary' => 'Set primary',
            'delete' => 'Delete',
        ],
    ],
];
