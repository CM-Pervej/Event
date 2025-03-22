<?php
// File: middleware/admin_only.php

require_once __DIR__ . '/token_protect.php'; // Ensures token is valid first
require_once __DIR__ . '/../config/db.php';

$userId = $GLOBALS['AUTH_USER_ID'] ?? null;

if (!$userId) {
    http_response_code(401);
    echo "Unauthorized: No user ID found.";
    exit;
}

// Check if the user is an admin
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    echo "Access denied: Admins only.";
    exit;
}

// ✅ Allow access
