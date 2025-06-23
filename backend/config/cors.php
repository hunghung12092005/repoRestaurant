<?php
    return [
        'paths' => ['api/*', '*'], // Giới hạn chỉ API hoặc giữ '*'
        'allowed_methods' => ['*'],
        // 'allowed_origins' => ['http://127.0.0.1:5173'],
        'allowed_origins' => ['*'],
        'allowed_origins_patterns' => [],
        'allowed_headers' => ['*'],
        'exposed_headers' => [],
        'max_age' => 0,
        'supports_credentials' => true,
    ];
?>
