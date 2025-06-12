<?php



return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:3009'], // or '*' if no credentials
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];
