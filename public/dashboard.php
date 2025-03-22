<?php
require_once '../middleware/token_protect.php';
$userId = $GLOBALS['AUTH_USER_ID'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow">
        <h1 class="text-2xl font-bold mb-4">Welcome to the Dashboard</h1>
        <p class="mb-4">You are logged in as user ID: <strong><?= htmlspecialchars($userId) ?></strong></p>
        <div class="space-x-4">
            <a href="user_list.php" class="btn btn-secondary">View User List</a>
            <a href="logout.php" class="btn btn-error">Logout</a>
        </div>
    </div>
</body>
</html>
