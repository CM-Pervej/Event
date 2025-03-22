<?php
// File: public/refresh.php

require_once '../config/db.php';
require_once '../services/auth/jwt_utils.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

function getAuthorizationHeader() {
    if (isset($_SERVER['Authorization'])) {
        return trim($_SERVER["Authorization"]);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        foreach ($requestHeaders as $key => $value) {
            if (strtolower($key) === 'authorization') {
                return trim($value);
            }
        }
    }
    return null;
}

$authHeader = getAuthorizationHeader();

if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid Authorization header']);
    exit;
}

$refreshToken = trim(str_replace('Bearer', '', $authHeader));
$decoded = verifyToken($refreshToken);

if (!$decoded) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid or expired refresh token']);
    exit;
}

$userId = $decoded->sub;

// Verify refresh token exists in DB
$stmt = $pdo->prepare("SELECT id FROM user_tokens WHERE user_id = ? AND refresh_token = ?");
$stmt->execute([$userId, $refreshToken]);

if ($stmt->rowCount() === 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Refresh token not found in database']);
    exit;
}

$newAccessToken = generateAccessToken($userId);

echo json_encode([
    'success' => true,
    'access_token' => $newAccessToken
]);
