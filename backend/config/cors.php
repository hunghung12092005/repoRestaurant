<?php

return [
    'paths' => ['*'], // Thêm cả hai đường dẫn
    'allowed_methods' => ['*'],
    // 'allowed_origins' => ['*'], // Chỉ cho phép từ nguồn này
    'allowed_origins' => ['http://localhost:5173'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
