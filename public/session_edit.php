<?php
require_once '../middleware/token_protect.php';
require_once '../config/db.php';

$id = $_GET['id'] ?? null;
if (!$id) exit("Session ID missing.");

// Fetch session
$stmt = $pdo->prepare("SELECT * FROM sessions WHERE id = ?");
$stmt->execute([$id]);
$session = $stmt->fetch();
if (!$session) exit("Session not found.");

$eventId = $session['event_id'];

// Get speakers
$stmt = $pdo->prepare("SELECT id, name FROM speakers WHERE event_id = ?");
$stmt->execute([$eventId]);
$speakers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Edit Session</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-xl mx-auto bg-white p-6 rounded-xl shadow">
    <h1 class="text-xl font-bold mb-4">Edit Session</h1>
    <form action="../services/session/update.php" method="POST" class="space-y-4">
      <input type="hidden" name="id" value="<?= $session['id'] ?>">

      <input type="text" name="title" value="<?= htmlspecialchars($session['title']) ?>" class="input input-bordered w-full" required>

      <div class="grid grid-cols-2 gap-4">
        <input type="date" name="session_date" value="<?= $session['session_date'] ?>" class="input input-bordered w-full" required>
        <input type="time" name="session_time" value="<?= $session['session_time'] ?>" class="input input-bordered w-full" required>
      </div>

      <select name="speaker_id" class="select select-bordered w-full">
        <option value="">Select Speaker</option>
        <?php foreach ($speakers as $s): ?>
          <option value="<?= $s['id'] ?>" <?= ($s['id'] == $session['speaker_id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($s['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button class="btn btn-primary w-full">Update Session</button>
    </form>
  </div>
</body>
</html>
