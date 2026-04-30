<?php
/**
 * SEC-01: Centralized CORS helper
 * Thay thế header("Access-Control-Allow-Origin: *") ở tất cả API
 * Chỉ cho phép các origin đã được khai báo.
 */

function applyCors(): void
{
    // Danh sách origin được phép — thêm domain production vào đây khi deploy
    $allowedOrigins = [
        'http://localhost:5173',   // Vite dev server
        'http://localhost:3000',   // Alternative dev port
        'http://127.0.0.1:5173',
    ];

    // Đọc thêm từ biến môi trường nếu có (dùng khi deploy production)
    $envOrigin = getenv('APP_ALLOWED_ORIGIN');
    if ($envOrigin) {
        $allowedOrigins[] = rtrim($envOrigin, '/');
    }

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if (in_array($origin, $allowedOrigins, true)) {
        header("Access-Control-Allow-Origin: $origin");
        header("Vary: Origin");
    } else {
        // Fallback: cho phép localhost khi không có Origin header (Postman, curl, v.v.)
        header("Access-Control-Allow-Origin: http://localhost:5173");
    }

    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");
}
