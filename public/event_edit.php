<?php
require_once '../middleware/token_protect.php';
require_once '../config/db.php';

$userId = $GLOBALS['AUTH_USER_ID'];
$eventId = $_GET['id'] ?? null;

if (!$eventId) {
    echo "Missing event ID."; exit;
}

$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
$stmt->execute([$eventId, $userId]);
$event = $stmt->fetch();

if (!$event) {
    echo "Event not found or access denied."; exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Event</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-3xl mx-auto bg-white p-6 rounded-xl shadow">
    <h1 class="text-2xl font-bold mb-4">Edit Event</h1>

    <form action="../services/event/update.php" method="POST" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="id" value="<?= $event['id'] ?>">

      <input type="text" name="name" value="<?= htmlspecialchars($event['name']) ?>" class="input input-bordered w-full" required>

      <textarea name="description" class="textarea textarea-bordered w-full"><?= htmlspecialchars($event['description']) ?></textarea>

      <div class="grid grid-cols-2 gap-4">
        <input type="text" name="category" value="<?= htmlspecialchars($event['category']) ?>" class="input input-bordered w-full">
        <input type="text" name="subcategory" value="<?= htmlspecialchars($event['subcategory']) ?>" class="input input-bordered w-full">
      </div>

      <div class="grid grid-cols-2 gap-4">
        <input type="datetime-local" name="event_date" value="<?= date('Y-m-d\TH:i', strtotime($event['event_date'])) ?>" class="input input-bordered w-full">
        <input type="text" name="location" value="<?= htmlspecialchars($event['location']) ?>" class="input input-bordered w-full">
      </div>

      <label class="form-control w-full">
        <span class="label-text">Replace Event Banner (optional)</span>
        <input type="file" name="image" class="file-input file-input-bordered w-full" />
      </label>

      <select name="status" class="select select-bordered w-full" required>
        <option value="draft" <?= $event['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
        <option value="published" <?= $event['status'] === 'published' ? 'selected' : '' ?>>Published</option>
      </select>

      <button type="submit" class="btn btn-primary w-full">Update Event</button>
    </form>
  </div>
</body>
</html>
