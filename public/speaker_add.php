<?php
require_once '../middleware/token_protect.php';
require_once '../config/db.php';

$eventId = $_GET['event_id'] ?? null;
if (!$eventId) {
    echo "Missing event ID."; exit;
}

// ✅ Fetch existing speakers for the event
$stmt = $pdo->prepare("SELECT * FROM speakers WHERE event_id = ?");
$stmt->execute([$eventId]);
$speakers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Speaker</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-xl mx-auto bg-white p-6 rounded-xl shadow">
    <h2 class="text-xl font-bold mb-4">Add Speaker to Event #<?= htmlspecialchars($eventId) ?></h2>

    <form action="../services/speaker/add.php" method="POST" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="event_id" value="<?= htmlspecialchars($eventId) ?>">

      <input type="text" name="name" placeholder="Speaker Name" class="input input-bordered w-full" required>
      <textarea name="bio" placeholder="Biography" class="textarea textarea-bordered w-full" required></textarea>

      <label class="form-control w-full">
        <span class="label-text">Upload Profile Picture</span>
        <input type="file" name="image" class="file-input file-input-bordered w-full" />
      </label>

      <button type="submit" class="btn btn-primary w-full">Add Speaker</button>
    </form>

    <hr class="my-6">
    <h3 class="text-lg font-bold mb-2">Speakers</h3>

    <div class="space-y-2">
      <?php foreach ($speakers as $sp): ?>
        <div class="flex justify-between items-center bg-gray-100 p-3 rounded">
          <span><?= htmlspecialchars($sp['name']) ?></span>
          <div class="space-x-2">
            <a href="speaker_edit.php?id=<?= $sp['id'] ?>" class="btn btn-xs btn-info">✏️ Edit</a>
            <form action="speaker_delete.php" method="POST" onsubmit="return confirm('Delete this speaker?');" class="inline">
              <input type="hidden" name="id" value="<?= $sp['id'] ?>">
              <button type="submit" class="btn btn-xs btn-error">🗑️ Delete</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
