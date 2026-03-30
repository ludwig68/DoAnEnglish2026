<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../utils/JwtHelper.php';

JwtHelper::requireAuth('admin');

http_response_code(404);
echo json_encode([
    'status' => 'error',
    'message' => 'Statistics endpoint is not in use.',
]);
