<?php
require_once '../middleware/token_protect.php';
require_once '../config/db.php';

$id = $_GET['id'] ?? null;
if (!$id) exit("Speaker ID missing.");

$stmt = $pdo->prepare("SELECT * FROM speakers WHERE id = ?");
$stmt->execute([$id]);
$speaker = $stmt->fetch();
if (!$speaker) exit("Speaker not found.");
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Edit Speaker</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6 bg-gray-100">
  <div class="max-w-xl mx-auto bg-white p-6 rounded-xl shadow">
    <h1 class="text-xl font-bold mb-4">Edit Speaker</h1>
    <form action="../services/speaker/update.php" method="POST" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="id" value="<?= $speaker['id'] ?>">
      <input type="text" name="name" value="<?= htmlspecialchars($speaker['name']) ?>" class="input input-bordered w-full" required>
      <textarea name="bio" class="textarea textarea-bordered w-full" required><?= htmlspecialchars($speaker['bio']) ?></textarea>
      <input type="file" name="image" class="file-input file-input-bordered w-full">
      <button class="btn btn-primary w-full">Update Speaker</button>
    </form>
  </div>
</body>
</html>
