<?php
require_once '../middleware/token_protect.php';
require_once '../config/db.php';

$eventId = $_GET['event_id'] ?? null;
if (!$eventId) {
    echo "Missing event ID.";
    exit;
}

// Fetch speakers
$stmt = $pdo->prepare("SELECT id, name FROM speakers WHERE event_id = ?");
$stmt->execute([$eventId]);
$speakers = $stmt->fetchAll();

// Fetch sessions
$stmt = $pdo->prepare("SELECT s.*, sp.name AS speaker_name 
                       FROM sessions s 
                       LEFT JOIN speakers sp ON s.speaker_id = sp.id 
                       WHERE s.event_id = ?");
$stmt->execute([$eventId]);
$sessions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Session</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-xl mx-auto bg-white p-6 rounded-xl shadow">
    <h2 class="text-xl font-bold mb-4">Add Session to Event #<?= htmlspecialchars($eventId) ?></h2>

    <form action="../services/session/add.php" method="POST" class="space-y-4">
      <input type="hidden" name="event_id" value="<?= htmlspecialchars($eventId) ?>">

      <input type="text" name="title" placeholder="Session Title" class="input input-bordered w-full" required>

      <div class="grid grid-cols-2 gap-4">
        <input type="date" name="session_date" class="input input-bordered w-full" required>
        <input type="time" name="session_time" class="input input-bordered w-full" required>
      </div>

      <select name="speaker_id" class="select select-bordered w-full">
        <option value="">Select Speaker</option>
        <?php foreach ($speakers as $s): ?>
          <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <button type="submit" class="btn btn-primary w-full">Add Session</button>
    </form>

    <hr class="my-6">
    <h3 class="text-lg font-bold mb-2">Sessions</h3>

    <?php foreach ($sessions as $sess): ?>
      <div class="flex justify-between items-center bg-gray-100 p-3 rounded mb-2">
        <span><?= htmlspecialchars($sess['title']) ?> - <?= $sess['speaker_name'] ?? 'TBA' ?></span>
        <div class="space-x-2">
          <a href="session_edit.php?id=<?= $sess['id'] ?>" class="btn btn-xs btn-info">✏️ Edit</a>
          <form action="session_delete.php" method="POST" onsubmit="return confirm('Delete this session?');" class="inline">
            <input type="hidden" name="id" value="<?= $sess['id'] ?>">
            <button type="submit" class="btn btn-xs btn-error">🗑️ Delete</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
