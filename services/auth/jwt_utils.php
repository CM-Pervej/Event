<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/secret.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function generateAccessToken($userId) {
    return JWT::encode([
        'iss' => 'event-system',
        'sub' => $userId,
        'exp' => time() + (15 * 60)
    ], JWT_SECRET, 'HS256');
}

function generateRefreshToken($userId) {
    return JWT::encode([
        'iss' => 'event-system',
        'sub' => $userId,
        'exp' => time() + (7 * 24 * 60 * 60)
    ], JWT_SECRET, 'HS256');
}

function verifyToken($token) {
    try {
        return JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
    } catch (Exception $e) {
        return null;
    }
}
