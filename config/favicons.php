<?php

return [
    'path' => base_path('content/addons/favicons.yaml'),
    'assetPath' => null,
    'payload' => [
        'favicon_generation' => [
            'master_picture' => [
                'type' => 'url',
            ],
            'files_location' => [
                'type' => 'path',
            ],
            'favicon_design' => [
                'desktop_browser' => [],

                // 'ios' => [],

                // 'windows' => [],

                // 'firefox_app' => [
                //     'manifest' => [
                //         'app_name' => config('app.name')
                //     ]
                // ],

'android_chrome' => [
    'manifest' => [
        'name' => config('app.name'),
        'short_name' => config('app.name'),
        'start_url' => '/',
        'display' => 'standalone',
        'declared' => true
    ],
    'picture_aspect' => 'no_change',
    'theme_color' => '#ffffff',
    'assets' => [
        'android_chrome_use_existing' => false,
        'android_chrome_192' => true,
        'android_chrome_512' => true
    ]
],

                // 'safari_pinned_tab' => [],

                // 'coast' => [],

                // 'open_graph' => [],

                // 'yandex_browser' => [
                //     'manifest' => [
                //         'show_title' => true,
                //         'version' => '1.0'
                //     ]
                // ]
            ],
            'settings' => [
                'compression' => '3',
                'scaling_algorithm' => 'Mitchell',
                'error_on_image_too_small' => true,
                'readme_file' => false,
                'html_code_file' => false,
                'use_path_as_is' => false,
            ],
            'versioning' => [
                'param_name' => 'v',
            ],
        ],
    ]
];