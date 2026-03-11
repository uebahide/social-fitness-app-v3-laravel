<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'https://social-fitness-app-laravel-docker.onrender.com'
    ],

    'allowed_origins_patterns' => [
        "'#^https://social-fitness-app-v3-next-(?:git-[a-z0-9-]+|[a-z0-9]+)-hidekazu-7805s-projects\.vercel\.app$#',"
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];