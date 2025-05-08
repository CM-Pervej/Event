<?php
// File: public/logout.php

require_once '../config/db.php';

header('Content-Type: application/json');

// Start session in case we're using session-stored refresh token
session_start();

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
$deviceInfo = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

// Determine refresh token from header or session
if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
    $refreshToken = trim(str_replace('Bearer', '', $authHeader));
} elseif (isset($_SESSION['refresh_token'])) {
    $refreshToken = $_SESSION['refresh_token'];
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid Authorization header or session token']);
    exit;
}

// Delete refresh token from DB
$stmt = $pdo->prepare("DELETE FROM user_tokens WHERE refresh_token = ? AND device_info = ?");
$stmt->execute([$refreshToken, $deviceInfo]);

// Destroy session if exists
session_unset();
session_destroy();

// Redirect to login page if called from browser
if (!(isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
    header("Location: ../index.php");
    exit;
}

// JSON response for API
if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Token not found or already removed.']);
}