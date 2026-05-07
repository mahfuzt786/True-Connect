<?php

return [
    'app' => [
        'name'       => env('APP_NAME', 'True Commerce'),
        'url'        => env('APP_URL', 'http://localhost/mahfuz/crm/public'),
        'env'        => env('APP_ENV', 'local'),
        'debug'      => (bool)env('APP_DEBUG', true),
        'key'        => env('APP_KEY', 'base64:changethis32characterkeyimmediately!!'),
        'jwt_secret' => env('JWT_SECRET', 'change_this_jwt_secret_in_production'),
        'timezone'   => env('APP_TIMEZONE', 'UTC'),
        'locale'     => env('APP_LOCALE', 'en'),
        'currency'   => env('APP_CURRENCY', 'USD'),
        'version'    => '1.0.0',
    ],

    'session' => [
        'name'     => 'saas_session',
        'lifetime' => 7200,
        'secure'   => false,
        'domain'   => '',
    ],

    'cache' => [
        'driver' => env('CACHE_DRIVER', 'file'),
        'path'   => STORAGE_PATH . '/cache',
        'redis'  => [
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'port'     => (int)env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', null),
            'database' => (int)env('REDIS_DB', 0),
        ],
    ],

    'queue' => [
        'driver'     => env('QUEUE_DRIVER', 'database'),
        'connection' => 'default',
    ],

    'storage' => [
        'max_upload_size' => 10240, // KB (10MB)
        'allowed_images'  => ['jpg','jpeg','png','gif','webp'],
        'allowed_docs'    => ['pdf','doc','docx'],
    ],

    'pagination' => [
        'per_page' => 20,
    ],

    'features' => [
        'registration'   => true,
        'social_login'   => true,
        'two_factor'     => true,
        'api'            => true,
        'marketplace'    => true,
    ],

    'platforms' => [
        'google_client_id'     => env('GOOGLE_CLIENT_ID', ''),
        'google_client_secret' => env('GOOGLE_CLIENT_SECRET', ''),
        'facebook_app_id'      => env('FACEBOOK_APP_ID', ''),
        'facebook_app_secret'  => env('FACEBOOK_APP_SECRET', ''),
    ],
];
