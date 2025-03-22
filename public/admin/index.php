<?php
// File: public/admin/index.php
require_once '../../middleware/admin_only.php';
$userId = $GLOBALS['AUTH_USER_ID'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.0.0/dist/full.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-4xl mx-auto bg-white p-6 rounded-xl shadow-lg">
    <h1 class="text-3xl font-bold mb-4 text-center text-primary">Admin Dashboard</h1>
    <p class="text-center mb-6">Welcome, Admin (User ID: <strong><?= htmlspecialchars($userId) ?></strong>)</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
      <a href="users.php" class="card bg-base-100 shadow-md hover:shadow-xl transition">
        <div class="card-body">
          <h2 class="card-title">Manage Users</h2>
          <p>View, update, and promote users.</p>
        </div>
      </a>

      <a href="events.php" class="card bg-base-100 shadow-md hover:shadow-xl transition">
        <div class="card-body">
          <h2 class="card-title">Manage Events</h2>
          <p>Approve or edit user-submitted events.</p>
        </div>
      </a>

      <a href="tickets.php" class="card bg-base-100 shadow-md hover:shadow-xl transition">
        <div class="card-body">
          <h2 class="card-title">Ticket Reports</h2>
          <p>See sales, refunds, and ticket types.</p>
        </div>
      </a>

      <a href="../dashboard.php" class="card bg-base-100 shadow-md hover:shadow-xl transition">
        <div class="card-body">
          <h2 class="card-title">Back to User Panel</h2>
          <p>Switch back to normal user dashboard.</p>
        </div>
      </a>
    </div>
  </div>
</body>
</html>
