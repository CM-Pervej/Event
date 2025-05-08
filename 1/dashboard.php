<?php
  include $_SERVER['DOCUMENT_ROOT'] . '/event/config/db.php';

  $slug = $_GET['event'] ?? null;
  if (!$slug) { echo "Event not found."; exit; }

  $stmt = $pdo->prepare("SELECT * FROM events WHERE slug = ?");
  $stmt->execute([$slug]);
  $event = $stmt->fetch();

  if (!$event) { echo "Invalid event link."; exit; }

  $eventId = $event['id'];

  $speakers = $pdo->prepare("SELECT * FROM speakers WHERE event_id = ?");
  $speakers->execute([$eventId]);

  $sessions = $pdo->prepare("SELECT s.*, sp.name AS speaker_name FROM sessions s
      LEFT JOIN speakers sp ON s.speaker_id = sp.id WHERE s.event_id = ?");
  $sessions->execute([$eventId]);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($event['name']) ?> | Event Site</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.0.0/dist/full.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
</head>
<body class="bg-base-200">

  <!-- Layout -->
  <div class="flex h-screen">
    <?php include '../sidebar.php' ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">

      <!-- Topbar -->
      <header class="bg-blue-50 shadow flex items-center w-full">
        <h1 class="text-xl font-bold">Dashboard</h1>
        <span class="w-full">
          <?php include '../topbar.php' ?>
        </span>
      </header>

      <!-- Content -->
      <main class="flex-1 p-6 overflow-y-auto">
        <div class="max-w-5xl mx-auto p-6">
          <h1 class="text-3xl font-bold mb-2"><?= htmlspecialchars($event['name']) ?></h1>
          <p class="mb-4 text-gray-600"><?= htmlspecialchars($event['description']) ?></p>

          <div class="mb-6">
            <h2 class="text-xl font-semibold">🎤 Speakers</h2>
            <div class="grid grid-cols-2 gap-4 mt-2">
              <?php foreach ($speakers as $s): ?>
                <div class="border p-3 rounded shadow">
                  <h3 class="font-bold"><?= htmlspecialchars($s['name']) ?></h3>
                  <p class="text-sm text-gray-600"><?= nl2br(htmlspecialchars($s['bio'])) ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div>
            <h2 class="text-xl font-semibold">🗓️ Sessions</h2>
            <ul class="list-disc pl-5 mt-2">
              <?php foreach ($sessions as $sess): ?>
                <li>
                  <?= htmlspecialchars($sess['title']) ?> - <?= date('M j, Y', strtotime($sess['session_date'])) ?> at <?= date('g:i A', strtotime($sess['session_time'])) ?>
                  (Speaker: <?= htmlspecialchars($sess['speaker_name'] ?? 'TBA') ?>)
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <a href="feedback_submit.php?event_id=<?= $event['id'] ?>" class="btn btn-outline btn-sm"> Leave Feedback </a>
          <?php if (strtotime($event['event_date']) < time()): ?>
            <div class="mt-6">
              <a href="feedback_submit.php?event_id=<?= $event['id'] ?>" class="btn btn-outline btn-primary">
                📝 Leave Feedback
              </a>
            </div>
          <?php endif; ?>
          <a href="feedback_view.php?event_id=<?= $event['id'] ?>" class="btn btn-secondary btn-sm">
            📊 View Feedback
          </a>
        </div>
      </main>
    </div>
  </div>

</body>
</html>
