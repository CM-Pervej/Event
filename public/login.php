<?php
// File: public/login.php

require_once '../config/db.php';
require_once '../services/auth/jwt_utils.php';
session_start();

$errors = [];

$isJsonRequest = (
    isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
) || (
    isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isJsonRequest) {
    $input = json_decode(file_get_contents('php://input'), true);
    $_POST = $input ?? [];
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';
$deviceInfo = $_POST['device'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($email) || empty($password)) {
        $errors[] = "Email and password are required.";
    } else {
        $stmt = $pdo->prepare("SELECT id, password, email_verified FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch();

            if (!$user['email_verified']) {
                $errors[] = "Please verify your email first.";
            } elseif (password_verify($password, $user['password'])) {
                $userId = $user['id'];
                $accessToken = generateAccessToken($userId);
                $refreshToken = generateRefreshToken($userId);

                // Remove existing token for same device
                $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE user_id = ? AND device_info = ?");
                $stmt->execute([$userId, $deviceInfo]);

                // Store refresh token for this device
                $stmt = $pdo->prepare("INSERT INTO user_tokens (user_id, refresh_token, device_info) VALUES (?, ?, ?)");
                $stmt->execute([$userId, $refreshToken, $deviceInfo]);

                if ($isJsonRequest) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'access_token' => $accessToken,
                        'refresh_token' => $refreshToken
                    ]);
                    exit;
                } else {
                    $_SESSION['access_token'] = $accessToken;
                    $_SESSION['refresh_token'] = $refreshToken;
                    header("Location: dashboard.php");
                    exit;
                }
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "User not found.";
        }
    }

    if ($isJsonRequest) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'errors' => $errors
        ]);
        exit;
    }
}
?>

<!-- Browser HTML Login Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Event Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.0.0/dist/full.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-6 bg-white rounded-xl shadow-lg">
        <h2 class="text-2xl font-bold mb-4 text-center">Login</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error mb-4">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="email" name="email" placeholder="Email" class="input input-bordered w-full" required>
            <input type="password" name="password" placeholder="Password" class="input input-bordered w-full" required>
            <input type="hidden" name="device" value="browser">
            <button type="submit" class="btn btn-primary w-full">Login</button>
        </form>

        <p class="mt-4 text-center">
            Don't have an account? <a href="register.php" class="link link-primary">Register here</a>.
        </p>
    </div>
</body>
</html>
