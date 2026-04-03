<?php
/**
 * Chuyển đổi image_url từ DB thành URL đúng với domain hiện tại.
 *
 * Giải quyết vấn đề: ảnh upload trên localhost lưu URL "http://localhost/...",
 * khi deploy lên host, URL đó không truy cập được.
 *
 * Logic:
 *   1. Nếu URL là external (placehold.co, imgur...) → giữ nguyên.
 *   2. Nếu URL chứa "/backend/uploads/" → build lại với domain hiện tại.
 *   3. Nếu URL là relative path (bắt đầu "/backend/") → build full URL.
 */
function resolveImageUrl(?string $imageUrl): ?string
{
    if ($imageUrl === null || trim($imageUrl) === '') {
        return null;
    }

    $imageUrl = trim($imageUrl);

    // Nếu là relative path: /backend/uploads/...
    if (str_starts_with($imageUrl, '/backend/uploads/')) {
        return buildBaseUrl() . $imageUrl;
    }

    // Nếu là full URL chứa /backend/uploads/ — rebuild với domain hiện tại
    if (preg_match('#/backend/uploads/(.+)$#', $imageUrl, $matches)) {
        return buildBaseUrl() . '/backend/uploads/' . $matches[1];
    }

    // URL bên ngoài (placehold.co, etc.) — giữ nguyên
    return $imageUrl;
}

/**
 * Build base URL (scheme + host + project base path).
 * Ví dụ:
 *   localhost → http://localhost/DoAnEnglish2026
 *   host     → https://tttn375.cnttstu.online
 */
function buildBaseUrl(): string
{
    static $baseUrl = null;
    if ($baseUrl !== null) {
        return $baseUrl;
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Xác định project base path từ SCRIPT_NAME
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

    // Tìm vị trí "/backend/" trong script path để lấy phần trước đó
    $backendPos = strpos($scriptName, '/backend/');
    if ($backendPos !== false) {
        $projectBase = substr($scriptName, 0, $backendPos);
    } else {
        $projectBase = '';
    }

    $baseUrl = $scheme . '://' . $host . $projectBase;
    return $baseUrl;
}
