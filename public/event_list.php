<?php
require_once '../middleware/token_protect.php';
require_once '../config/db.php';

$userId = $GLOBALS['AUTH_USER_ID'];

$stmt = $pdo->prepare("SELECT * FROM events WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Events</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-5xl mx-auto bg-white p-6 rounded-xl shadow">
    <h1 class="text-2xl font-bold mb-4">My Events</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <?php foreach ($events as $event): ?>
        <div class="card bg-base-100 shadow-md">
          <figure>
            <?php if ($event['image_path']): ?>
              <img src="../<?= htmlspecialchars($event['image_path']) ?>" alt="Event Banner" class="w-full h-48 object-cover">
            <?php else: ?>
              <div class="w-full h-48 bg-gray-300 flex items-center justify-center">No Image</div>
            <?php endif; ?>
          </figure>
          <div class="card-body">
            <h2 class="card-title"><?= htmlspecialchars($event['name']) ?></h2>
            <p><?= htmlspecialchars($event['description']) ?></p>
            <p class="mt-2">
              <span class="badge <?= $event['status'] === 'published' ? 'badge-success' : 'badge-warning' ?>">
                <?= ucfirst($event['status']) ?>
              </span>
            </p>
            <div class="text-sm text-gray-500 mt-2">
              <p><?= htmlspecialchars($event['category']) ?> > <?= htmlspecialchars($event['subcategory']) ?></p>
              <p><?= date('F j, Y g:i A', strtotime($event['event_date'])) ?></p>
              <p>📍 <?= htmlspecialchars($event['location']) ?></p>
            </div>

            <div class="mt-3 space-x-2">
              <a href="eventsite.php?event=<?= urlencode($event['slug']) ?>" class="btn btn-outline btn-sm">🔗 View Event Site</a>
              <a href="speaker_add.php?event_id=<?= $event['id'] ?>" class="btn btn-outline btn-sm">🎤 Add Speaker</a>
              <a href="session_add.php?event_id=<?= $event['id'] ?>" class="btn btn-outline btn-sm">🗓️ Add Session</a>
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
              <a href="eventsite.php?event=<?= urlencode($event['slug']) ?>" class="btn btn-sm btn-outline">🔗 View</a>
              <a href="event_edit.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-info">✏️ Edit</a>
              <form action="event_delete.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this event?');">
                <input type="hidden" name="id" value="<?= $event['id'] ?>">
                <button type="submit" class="btn btn-sm btn-error">🗑️ Delete</button>
              </form>
            </div>
            <a href="event_clone.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-warning">🧬 Clone</a>
            <a href="feedback_submit.php?event_id=<?= $event['id'] ?>" class="btn btn-outline btn-sm"> Leave Feedback </a>
            <?php if (strtotime($event['event_date']) < time()): ?>
              <a href="feedback_submit.php?event_id=<?= $event['id'] ?>" class="btn btn-outline btn-sm mt-2">
                📝 Leave Feedback
              </a>
            <?php endif; ?>
            <a href="feedback_view.php?event_id=<?= $event['id'] ?>" class="btn btn-secondary btn-sm">
              📊 View Feedback
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mt-6">
      <a href="event_create.php" class="btn btn-primary">➕ Create Another Event</a>
    </div>
  </div>
</body>
</html>
