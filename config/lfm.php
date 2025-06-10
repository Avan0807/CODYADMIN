<?php

return [
    'use_package_routes'       => false,

    /*
    |--------------------------------------------------------------------------
    | OPTION 1: Chỉ dùng shared folder (access root bucket)
    |--------------------------------------------------------------------------
     */
    
    'allow_private_folder'     => false,  // Tắt private
    'private_folder_name'      => '',
    
    'allow_shared_folder'      => true,   // Bật shared
    'shared_folder_name'       => 'allimage', // Root bucket

    'folder_categories' => [
        'file' => [
            'folder_name' => '',          // Root bucket
            'startup_view' => 'grid',
            'max_size' => 50000,
            'valid_mime' => [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/svg+xml',
                'image/webp',
                'application/pdf',
                'text/plain',
            ],
        ],
        'image' => [
            'folder_name' => '',          // Root bucket
            'startup_view' => 'list',
            'max_size' => 50000,
            'valid_mime' => [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/svg+xml',
                'image/webp',
                'application/pdf',
                'text/plain',
            ],
        ],
    ],

    'disk' => 's3',
    'url' => env('AWS_URL'),
    
    // ... rest of config same
    'paginator' => ['perPage' => 30],
    'rename_file' => false,
    'alphanumeric_filename' => false,
    'alphanumeric_directory' => false,
    'should_validate_size' => false,
    'should_validate_mime' => false,
    'over_write_on_duplicate' => false,
    'create_thumb_img' => true,  // Fix key name
    'thumb_folder_name' => 'thumbs',
    'raster_mimetypes' => ['image/jpeg', 'image/pjpeg', 'image/png'],
    'thumb_img_width' => 200,
    'thumb_img_height' => 200,
    'file_type_array' => [
        'pdf' => 'Adobe Acrobat',
        'doc' => 'Microsoft Word',
        'docx' => 'Microsoft Word',
        'xls' => 'Microsoft Excel',
        'xlsx' => 'Microsoft Excel',
        'zip' => 'Archive',
        'gif' => 'GIF Image',
        'jpg' => 'JPEG Image',
        'jpeg' => 'JPEG Image',
        'png' => 'PNG Image',
        'ppt' => 'Microsoft PowerPoint',
        'pptx' => 'Microsoft PowerPoint',
    ],
    'php_ini_overrides' => ['memory_limit' => '256M'],
    'lang' => 'vi',
];