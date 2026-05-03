<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shop Theme Configuration
    |--------------------------------------------------------------------------
    |
    | All the configurations are related to the shop themes.
    |
    */

    'shop-default' => 'kun',

    'shop' => [
        'default' => [
            'name' => 'Default',
            'assets_path' => 'public/themes/shop/default',
            'views_path' => 'resources/themes/default/views',

            'vite' => [
                'hot_file' => 'shop-default-vite.hot',
                'build_directory' => 'themes/shop/default/build',
                'package_assets_directory' => 'src/Resources/assets',
            ],
        ],

        'kun' => [
            'name'        => 'KUN Arabian',
            'assets_path' => 'public/themes/shop/kun',
            'views_path'  => 'resources/themes/kun/views',

            'vite' => [
                'hot_file'                 => 'shop-kun-vite.hot',
                'build_directory'          => 'themes/shop/kun/build',
                'package_assets_directory' => 'src/Resources/assets',
            ],

            'parent' => 'default',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Theme Configuration
    |--------------------------------------------------------------------------
    |
    | All the configurations are related to the admin themes.
    |
    */

    'admin-default' => 'default',

    'admin' => [
        'default' => [
            'name' => 'Default',
            'assets_path' => 'public/themes/admin/default',
            'views_path' => 'resources/admin-themes/default/views',

            'vite' => [
                'hot_file' => 'admin-default-vite.hot',
                'build_directory' => 'themes/admin/default/build',
                'package_assets_directory' => 'src/Resources/assets',
            ],
        ],
    ],
];