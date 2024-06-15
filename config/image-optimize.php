<?php

return [
    // Set the default resize width in pixels
    'default_resize_width' => env('IMAGE_OPTIMIZE_WIDTH', 1600),

    // Set the default resize height in pixels
    'default_resize_height' => env('IMAGE_OPTIMIZE_HEIGHT', 1600),

    'default_quality' => env('IMAGE_OPTIMIZE_QUALITY', 90),

    'only_if_smaller' => env('IMAGE_OPTIMIZE_ONLY_IF_SMALLER', false),

    // Set the default queue name
    'default_queue_name' => env('IMAGE_OPTIMIZE_QUEUE_NAME', 'default'),

    // Set the default queue connection
    'default_queue_connection' => env('IMAGE_OPTIMIZE_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'sync')),

    // The following mime types will be used to optimize images
    'mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],

    'events' => [
        \Statamic\Events\AssetUploaded::class,
        \Statamic\Events\AssetReuploaded::class,
    ],

    'listener' => \JustBetter\ImageOptimize\Listeners\AssetUploadedListener::class,
];