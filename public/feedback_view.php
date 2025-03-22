<?php
require_once '../middleware/admin_only.php';
require_once '../config/db.php';

$eventId = $_GET['event_id'] ?? null;
if (!$eventId) {
    echo "Missing event ID.";
    exit;
}

// Get event name
$stmt = $pdo->prepare("SELECT name FROM events WHERE id = ?");
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    echo "Event not found.";
    exit;
}

// Get feedback
$stmt = $pdo->prepare("SELECT f.*, u.full_name FROM feedback f
    JOIN users u ON f.user_id = u.id WHERE f.event_id = ?");
$stmt->execute([$eventId]);
$feedbacks = $stmt->fetchAll();

// Average rating
$stmt = $pdo->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS total FROM feedback WHERE event_id = ?");
$stmt->execute([$eventId]);
$summary = $stmt->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Event Feedback</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-4xl mx-auto bg-white p-6 rounded-xl shadow">
    <h1 class="text-2xl font-bold mb-4">Feedback for <?= htmlspecialchars($event['name']) ?></h1>

    <p class="mb-4 text-gray-700">
      Average Rating: <strong><?= round($summary['avg_rating'], 1) ?>/5</strong>  
      (<?= $summary['total'] ?> total)
    </p>

    <?php foreach ($feedbacks as $fb): ?>
      <div class="border-b py-3">
        <p class="font-semibold"><?= htmlspecialchars($fb['full_name']) ?> - ⭐ <?= $fb['rating'] ?>/5</p>
        <?php if ($fb['comment']): ?>
          <p class="text-gray-700"><?= nl2br(htmlspecialchars($fb['comment'])) ?></p>
        <?php endif; ?>
        <p class="text-sm text-gray-500"><?= $fb['submitted_at'] ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
