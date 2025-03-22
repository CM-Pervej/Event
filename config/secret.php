<?php
require_once __DIR__ . '/env.php';

$envPath = __DIR__ . '/../.env';
loadEnv($envPath);

// ✅ Regenerate or fix the secret if empty or missing
if (empty($_ENV['JWT_SECRET']) || trim($_ENV['JWT_SECRET']) === '') {
    $generatedKey = bin2hex(random_bytes(32));

    // Load current .env content
    $envContent = file_exists($envPath) ? file_get_contents($envPath) : '';

    // ✅ Replace existing JWT_SECRET line or add one
    if (str_contains($envContent, 'JWT_SECRET=')) {
        $envContent = preg_replace('/^JWT_SECRET=.*$/m', "JWT_SECRET=$generatedKey", $envContent);
    } else {
        $envContent .= "\nJWT_SECRET=$generatedKey";
    }

    file_put_contents($envPath, trim($envContent) . "\n"); // Clean up & save
    $_ENV['JWT_SECRET'] = $generatedKey;
}

define('JWT_SECRET', $_ENV['JWT_SECRET']);
