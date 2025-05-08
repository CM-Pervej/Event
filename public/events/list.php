<?php
// users.php - Public page listing all users who are organizers

require_once $_SERVER['DOCUMENT_ROOT'] . '/event/config/db.php'; // Database connection

// Fetch all users with role = 2 (organizers)
$stmt = $pdo->prepare("SELECT * FROM users WHERE role != 1 ORDER BY full_name ASC");
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Organizers – AmarEvent</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.0.0/dist/full.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
</head>
<body class="bg-base-200">

<div class="flex h-screen">
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/event/sidebar.php'; ?> <!-- Optional sidebar -->

  <div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-blue-50 shadow flex items-center w-full">
      <?php include $_SERVER['DOCUMENT_ROOT'] . '/event/topbar.php'; ?> <!-- Optional top bar -->
    </header>

    <main class="flex-1 px-6 overflow-y-auto">
      <div class="mt-8 flex justify-between items-center px-6">
        <h1 class="text-2xl font-bold">Event Organizers</h1>
      </div>

      <div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow mt-5">
        <?php if (count($users) === 0): ?>
          <p class="text-center text-gray-500">No organizers available.</p>
        <?php else: ?>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($users as $user): ?>
              <div class="card bg-base-100 shadow-md" data-user-id="<?= $user['id'] ?>">
                <div class="card-body">
                  <h2 class="card-title"><?= htmlspecialchars($user['full_name']) ?></h2>
                  <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                  <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></p>
                  <a href="events.php?user_id=<?= $user['id'] ?>" class="btn btn-sm btn-outline mt-4">View Events</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</div>

</body>
</html>
