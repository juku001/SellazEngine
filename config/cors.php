<?php



return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',
        'http://localhost:3001',
        'http://localhost:3039',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:3039',
    ],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];
