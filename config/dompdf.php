<?php

return [
    'show_warnings'   => false,
    'orientation'     => 'portrait',
    'defines'         => [
        'font_dir'     => storage_path('fonts/'),
        'font_cache'   => storage_path('fonts/'),
        'temp_dir'     => sys_get_temp_dir(),
        'chroot'       => realpath(base_path()),
        'allowed_protocols' => [
            'file://'  => ['rules' => []],
            'http://'  => ['rules' => []],
            'https://' => ['rules' => []],
        ],
        'enable_html5_parser' => true,
        'enable_remote'       => true,
        'default_media_type'  => 'screen',
        'default_paper_size'  => 'A4',
        'default_font'        => 'serif',
        'dpi'                 => 96,
        'enable_php'          => false,
        'enable_javascript'   => true,
        'is_javascript_enabled' => true,
    ],
];
