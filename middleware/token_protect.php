<?php
// File: middleware/token_protect.php

require_once __DIR__ . '/../services/auth/jwt_utils.php';
require_once __DIR__ . '/../config/db.php';
session_start();

function getAuthorizationHeader() {
    if (isset($_SERVER['Authorization'])) {
        return trim($_SERVER['Authorization']);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return trim($_SERVER['HTTP_AUTHORIZATION']);
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

$accessToken = null;
$authHeader = getAuthorizationHeader();

if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
    $accessToken = trim(str_replace('Bearer', '', $authHeader));
} elseif (isset($_SESSION['access_token'])) {
    $accessToken = $_SESSION['access_token'];
}

if (!$accessToken) {
    http_response_code(401);
    // echo "Access denied. Please login.";

    // // Delay before redirecting (optional, gives user a chance to read the message)
    // sleep(20); // Adjust the time delay (20 seconds)

    // // Redirect the user to the index page after the message is shown
    header("Location: /event/index.php");
    exit;
}

$decoded = verifyToken($accessToken);

if (!$decoded) {
    http_response_code(401);
    echo "Invalid or expired access token.";
    exit;
}

$userId = $decoded->sub;

$stmt = $pdo->prepare("SELECT id FROM token_blacklist WHERE token = ?");
$stmt->execute([$accessToken]);
if ($stmt->rowCount() > 0) {
    http_response_code(401);
    echo "This token has been revoked. Please login again.";
    exit;
}

$GLOBALS['AUTH_USER_ID'] = $userId;
