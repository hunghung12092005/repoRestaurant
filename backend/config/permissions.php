<?php
// File: config/permissions.php

return [
    /*
    |--------------------------------------------------------------------------
    | Định nghĩa các quyền hạn (Abilities) của ứng dụng
    |--------------------------------------------------------------------------
    | Đây là danh sách các "slug" cho từng quyền.
    | Tên quyền nên rõ ràng và thể hiện đúng hành động.
    | Ví dụ: 'manage-users', 'manage-news', 'view-dashboard'.
    */
    'abilities' => [
        // Quản lý người dùng
        'manage-users',
        
        // Quản lý Phòng & Giá
        'manage-room-types',
        'manage-rooms',
        'manage-prices',

        // Quản lý Dịch vụ & Tiện nghi
        'manage-services',
        'manage-amenities',

        // Quản lý Đặt phòng
        'manage-bookings',
        'view-booking-history',

        // Quản lý Nội dung
        'manage-news',
        'manage-contacts',
        'manage-comments',

        // Các quyền khác
        'view-dashboard',
        'train-ai',
    ],

    /*
    |--------------------------------------------------------------------------
    | Gán quyền cho từng Vai trò (Role)
    |--------------------------------------------------------------------------
    | 'admin' có quyền '*' (tất cả các quyền được định nghĩa ở trên).
    | Các vai trò khác được gán một mảng các quyền cụ thể.
    */
    'roles' => [
        'admin' => ['*'], // Admin có tất cả các quyền

        'staff' => [
            'view-dashboard',
            'manage-bookings',
            'manage-news',       // Cho phép staff quản lý tin tức
            'manage-contacts',   // Cho phép staff quản lý liên hệ
            'manage-comments',   // Cho phép staff quản lý bình luận
            'manage-services',   // Cho phép staff xem/sửa dịch vụ
            'manage-amenities',  // Cho phép staff xem/sửa tiện nghi
        ],

        'client' => [
            // Client không có quyền truy cập vào trang admin
        ],
    ],
];

?>