<?php
include $_SERVER['DOCUMENT_ROOT'] . '/event/config/db.php';

$slug = $_GET['event'] ?? null;
if (!$slug) { echo "Event not found."; exit; }

// Get event
$stmt = $pdo->prepare("SELECT * FROM events WHERE slug = ?");
$stmt->execute([$slug]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$event) { echo "Invalid event link."; exit; }

// Get category & subcategory names
$categoryStmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
$categoryStmt->execute([$event['category_id']]);
$categoryName = $categoryStmt->fetchColumn() ?? 'N/A';

$subcategoryStmt = $pdo->prepare("SELECT name FROM subcategories WHERE id = ?");
$subcategoryStmt->execute([$event['subcategory_id']]);
$subcategoryName = $subcategoryStmt->fetchColumn() ?? 'N/A';

// Get linked speakers
$speakerStmt = $pdo->prepare("
  SELECT s.* FROM speakers s
  JOIN event_speaker es ON es.speaker_id = s.id
  WHERE es.event_id = ?
");
$speakerStmt->execute([$event['id']]);
$speakers = $speakerStmt->fetchAll();

// Get linked sessions
$sessionStmt = $pdo->prepare("
  SELECT sess.*, sp.name AS speaker_name
  FROM sessions sess
  JOIN event_session es ON es.session_id = sess.id
  LEFT JOIN speakers sp ON sess.speaker_id = sp.id
  WHERE es.event_id = ?
  ORDER BY sess.session_date, sess.session_time
");
$sessionStmt->execute([$event['id']]);
$sessions = $sessionStmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($event['name']) ?> | AmarEvent</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-900">
  <!-- Hero -->
  <div class="hero min-h-screen" style="background-image: url('/<?= htmlspecialchars($event['image_path']) ?>'); background-size: cover;">
    <div class="hero-overlay bg-black bg-opacity-50"></div>
    <div class="hero-content text-center text-neutral-content">
      <div class="max-w-2xl">
        <h1 class="mb-4 text-5xl font-bold"><?= htmlspecialchars($event['name']) ?></h1>
        <p class="mb-4"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
        <p class="mb-2 text-sm">📍 <?= htmlspecialchars($event['location']) ?></p>
        <p class="mb-2 text-sm">🕒 <?= date('F j, Y g:i A', strtotime($event['event_date'])) ?></p>
        <p class="mb-4 text-sm">📂 <?= htmlspecialchars($categoryName) ?> > <?= htmlspecialchars($subcategoryName) ?></p>
        <a href="feedback_submit.php?event_id=<?= $event['id'] ?>" class="btn btn-primary">📝 Leave Feedback</a>
      </div>
    </div>
  </div>

  <!-- Speakers -->
  <?php if (count($speakers) > 0): ?>
    <section class="max-w-5xl mx-auto py-12 px-4">
      <h2 class="text-2xl font-bold mb-4">🎤 Speakers</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach ($speakers as $sp): ?>
          <div class="bg-white border rounded-xl p-4 shadow-md flex gap-4">
            <?php if ($sp['image_path']): ?>
              <img src="/<?= htmlspecialchars($sp['image_path']) ?>" alt="<?= htmlspecialchars($sp['name']) ?>" class="w-24 h-24 rounded-full object-cover">
            <?php endif; ?>
            <div>
              <h3 class="font-bold text-lg"><?= htmlspecialchars($sp['name']) ?></h3>
              <p class="text-sm text-gray-600"><?= nl2br(htmlspecialchars($sp['bio'])) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- Sessions -->
  <?php if (count($sessions) > 0): ?>
    <section class="max-w-5xl mx-auto py-12 px-4">
      <h2 class="text-2xl font-bold mb-4">🗓️ Sessions</h2>
      <ul class="space-y-4">
        <?php foreach ($sessions as $sess): ?>
          <li class="border p-4 rounded-lg shadow-sm">
            <h4 class="text-lg font-semibold"><?= htmlspecialchars($sess['title']) ?></h4>
            <p class="text-sm text-gray-700"><?= date('F j, Y', strtotime($sess['session_date'])) ?> at <?= date('g:i A', strtotime($sess['session_time'])) ?></p>
            <p class="text-sm text-gray-500">🎤 <?= htmlspecialchars($sess['speaker_name'] ?? 'TBA') ?></p>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>
  <?php endif; ?>

  <!-- Feedback Button -->
  <?php if (strtotime($event['event_date']) < time()): ?>
    <div class="text-center my-8">
      <a href="feedback_submit.php?event_id=<?= $event['id'] ?>" class="btn btn-outline btn-primary">📝 Leave Feedback</a>
      <a href="feedback_view.php?event_id=<?= $event['id'] ?>" class="btn btn-secondary ml-2">📊 View Feedback</a>
    </div>
  <?php endif; ?>
</body>
</html>
