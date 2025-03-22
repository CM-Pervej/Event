<?php
require_once '../config/db.php';

$verified = false;
$message = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_token = ?");
    $stmt->execute([$token]);

    if ($stmt->rowCount() > 0) {
        $update = $pdo->prepare("UPDATE users SET email_verified = 1, verification_token = NULL WHERE verification_token = ?");
        $update->execute([$token]);
        $verified = true;
        $message = "✅ Your email has been successfully verified!";
    } else {
        $message = "❌ Invalid or expired verification link.";
    }
} else {
    $message = "❌ No verification token provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
    <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-md text-center">
        <h2 class="text-2xl font-bold mb-4">Email Verification</h2>
        <p class="<?= $verified ? 'text-green-600' : 'text-red-600' ?>"><?= $message ?></p>
        <a href="login.php" class="btn btn-primary mt-6">Go to Login</a>
    </div>
</body>
</html>
