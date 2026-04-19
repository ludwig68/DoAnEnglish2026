<?php

$secret = trim((string) getenv('JWT_SECRET'));

if ($secret === '') {
    $localConfigPath = __DIR__ . '/jwt.local.php';
    if (is_file($localConfigPath)) {
        $localConfig = require $localConfigPath;
        if (is_array($localConfig)) {
            $secret = trim((string) ($localConfig['secret'] ?? ''));
        } elseif (is_string($localConfig)) {
            $secret = trim($localConfig);
        }
    }
}

if ($secret === '') {
    $secretFile = __DIR__ . '/.jwt_secret';

    if (is_file($secretFile)) {
        $secret = trim((string) file_get_contents($secretFile));
    }

    if ($secret === '') {
        $secret = rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=');
        @file_put_contents($secretFile, $secret);
        @chmod($secretFile, 0600);
    }
}

return [
    'secret' => $secret,
    'issuer' => 'doanenglish2026',
    'ttl' => 60 * 60 * 24,
];
