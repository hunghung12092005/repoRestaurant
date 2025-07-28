<?php
    return [
        'paths' => ['api/*', '*'], // Giới hạn chỉ API hoặc giữ '*'
        'allowed_methods' => ['*'],
        // 'allowed_origins' => ['https://hxhhotel.online'],
        'allowed_origins' => ['*'],
        // 'allowed_origins' => ['https://hxhhotel.online', 'https://socket.hxhhotel.online'],
        'allowed_origins_patterns' => [],
        'allowed_headers' => ['*'],
        'exposed_headers' => [],
        'max_age' => 0,
        'supports_credentials' => true,
    ];
?>