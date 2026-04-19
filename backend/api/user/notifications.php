<?php
// ═══════════════════════════════════════════════════════════════
// API: Thông báo người dùng (Học viên)
// ═══════════════════════════════════════════════════════════════
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

$authUser = JwtHelper::requireAuth();
$studentId = (int)($authUser['sub'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Lấy 10 thông báo gần nhất
        $stmt = $pdo->prepare("
            SELECT id, title, message, type, link, is_read, created_at
            FROM notifications
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$studentId]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Đếm số thông báo chưa đọc
        $stmtUnread = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmtUnread->execute([$studentId]);
        $unreadCount = (int)$stmtUnread->fetchColumn();

        echo json_encode([
            'status' => 'success',
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        // Đánh dấu tất cả là đã đọc
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$studentId]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Đã đánh dấu tất cả thông báo là đã đọc.'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
