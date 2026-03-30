<?php

class JwtHelper
{
    private static ?array $config = null;

    private static function getConfig(): array
    {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../config/jwt.php';
        }

        return self::$config;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        $padding = strlen($data) % 4;
        if ($padding > 0) {
            $data .= str_repeat('=', 4 - $padding);
        }

        return base64_decode(strtr($data, '-_', '+/'));
    }

    public static function generate(array $claims): string
    {
        $config = self::getConfig();
        $issuedAt = time();

        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $payload = array_merge($claims, [
            'iss' => $config['issuer'],
            'iat' => $issuedAt,
            'exp' => $issuedAt + (int) $config['ttl'],
        ]);

        $encodedHeader = self::base64UrlEncode(json_encode($header));
        $encodedPayload = self::base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $config['secret'], true);
        $encodedSignature = self::base64UrlEncode($signature);

        return $encodedHeader . '.' . $encodedPayload . '.' . $encodedSignature;
    }

    public static function decode(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new InvalidArgumentException('Token khong hop le.');
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
        $config = self::getConfig();

        $expectedSignature = self::base64UrlEncode(
            hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $config['secret'], true)
        );

        if (!hash_equals($expectedSignature, $encodedSignature)) {
            throw new InvalidArgumentException('Token khong hop le.');
        }

        $payload = json_decode(self::base64UrlDecode($encodedPayload), true);

        if (!is_array($payload)) {
            throw new InvalidArgumentException('Token khong hop le.');
        }

        if (!empty($payload['exp']) && time() >= (int) $payload['exp']) {
            throw new InvalidArgumentException('Phien dang nhap da het han.');
        }

        return $payload;
    }

    public static function getBearerToken(): ?string
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['Authorization'] ?? null;

        if (!$authHeader && function_exists('getallheaders')) {
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        }

        if ($authHeader && preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    public static function requireAuth(?string $requiredRole = null): array
    {
        $token = self::getBearerToken();

        if (!$token) {
            self::abort(401, 'Ban can dang nhap de tiep tuc.');
        }

        try {
            $payload = self::decode($token);
        } catch (InvalidArgumentException $exception) {
            self::abort(401, $exception->getMessage());
        }

        $userId = (int) ($payload['sub'] ?? 0);
        if ($userId <= 0) {
            self::abort(401, 'Token khong hop le.');
        }

        require __DIR__ . '/../config/db.php';

        $stmt = $pdo->prepare("
            SELECT id, full_name, email, role, status
            FROM users
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            self::abort(401, 'Tai khoan khong con ton tai.');
        }

        if (($user['status'] ?? 'blocked') !== 'active') {
            self::abort(403, 'Tai khoan cua ban hien dang bi khoa.');
        }

        if ($requiredRole !== null && (($user['role'] ?? null) !== $requiredRole)) {
            self::abort(403, 'Ban khong co quyen truy cap tai nguyen nay.');
        }

        return array_merge($payload, [
            'sub' => (int) $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'status' => $user['status'],
        ]);
    }

    private static function abort(int $statusCode, string $message): void
    {
        http_response_code($statusCode);
        echo json_encode([
            'status' => 'error',
            'message' => $message,
        ]);
        exit;
    }
}
