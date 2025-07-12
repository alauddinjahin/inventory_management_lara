<?php

return [
    'disk_name' => env('MEDIA_DISK', 'public'),
    'max_file_size' => 1024 * 1024 * 10, // 10MB
    'queue_connection_name' => env('QUEUE_CONNECTION', 'sync'),
    'queue_name' => '',
    'queue_conversions_by_default' => env('QUEUE_CONVERSIONS_BY_DEFAULT', true),
    'media_model' => Spatie\MediaLibrary\MediaCollections\Models\Media::class,
    'remote' => [
        'extra_headers' => [
            'CacheControl' => 'max-age=604800',
        ],
    ],
    'responsive_images' => [
        'width_calculator' => Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\FileSizeOptimizedWidthCalculator::class,
        'use_tiny_placeholders' => true,
        'tiny_placeholder_generator' => Spatie\MediaLibrary\ResponsiveImages\TinyPlaceholderGenerator\Blurred::class,
    ],
    'enable_vapor_uploads' => env('ENABLE_VAPOR_UPLOADS', false),
    'generate_thumbnails_for_temporary_uploads' => true,
    'temporary_upload_path_generator' => null,
];