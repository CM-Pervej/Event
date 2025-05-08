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
        
                // Set user ID in session
                $_SESSION['user_id'] = $userId;
        
                // Generate access and refresh tokens
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
                    echo "<script>window.top.location.href = 'index.php';</script>";
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
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
</head>
<body class="bg-base-200 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-8 bg-white rounded-2xl shadow-lg">
        <div class="mb-6 text-center">
            <h1 class="text-3xl font-bold text-primary">Welcome Back</h1>
            <p class="text-gray-500 text-sm mt-2">Login to your AmarEvent account</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error mb-4">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4"  onsubmit="return checkBeforeSubmit()" autocomplete="off">
            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text font-medium">Email Address</span>
                </label>
                <input type="email" name="email" placeholder="name@example.com" class="input input-bordered w-full" required>
            </div>
            <!-- Password Field with Toggle and Validation -->
            <div class="form-control mb-2">
                <label class="label justify-between">
                    <span class="label-text font-medium">Password</span>
                    <a href="#" class="label-text-alt link link-hover text-sm text-primary">Forgot Password?</a>
                </label>
                <div class="relative">
                    <input id="passwordInput" type="password" name="password" placeholder="••••••••" class="input input-bordered w-full pr-12" required oninput="validatePassword()" />
                    <button type="button" onclick="togglePassword()" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-primary">
                        <i id="eyeIcon" class="fa-regular fa-eye"></i>
                    </button>
                </div>
            </div>
            <input type="hidden" name="device" value="browser">
            <div class="form-control mt-6">
                <button type="submit" class="btn btn-primary w-full text-white">Sign In</button>
            </div>
        </form>
        <div class="divider my-6 text-sm text-gray-400">OR</div>
        <div class="flex flex-col gap-3">
            <button class="btn btn-outline w-full">
                <i class="fa-brands fa-google mr-2"></i> Sign in with Google
            </button>
            <button class="btn btn-outline w-full">
                <i class="fa-brands fa-linkedin mr-2"></i> Sign in with LinkedIn
            </button>
        </div>
        <p class="mt-6 text-center text-sm text-gray-600"> Don’t have an account? <a href="register.php" class="link text-primary font-medium">Create one</a> </p>
    </div>

  <!-- Toggle Password Script -->
  <script>
        function togglePassword() {
        const input = document.getElementById("passwordInput");
        const icon = document.getElementById("eyeIcon");
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
        }
    </script>
</body>
</html>
