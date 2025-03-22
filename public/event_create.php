<?php
require_once '../middleware/token_protect.php';
$userId = $GLOBALS['AUTH_USER_ID'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Event</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.0.0/dist/full.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-3xl mx-auto bg-white p-6 rounded-xl shadow">
    <h1 class="text-2xl font-bold mb-4">Create New Event</h1>

    <form action="../services/event/create.php" method="POST" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="user_id" value="<?= htmlspecialchars($userId) ?>">

      <input type="text" name="name" placeholder="Event Name" class="input input-bordered w-full" required>
      
      <textarea name="description" placeholder="Event Description" class="textarea textarea-bordered w-full" required></textarea>
      
      <div class="grid grid-cols-2 gap-4">
        <input type="text" name="category" placeholder="Category (e.g., Conference)" class="input input-bordered w-full">
        <input type="text" name="subcategory" placeholder="Subcategory (e.g., Workshop)" class="input input-bordered w-full">
      </div>

      <div class="grid grid-cols-2 gap-4">
        <input type="datetime-local" name="event_date" class="input input-bordered w-full" required>
        <input type="text" name="location" placeholder="Location" class="input input-bordered w-full" required>
      </div>

      <label class="form-control w-full">
        <span class="label-text">Upload Event Banner</span>
        <input type="file" name="image" class="file-input file-input-bordered w-full" />
      </label>

    <select name="status" class="select select-bordered w-full" required>
      <option value="draft">Draft</option>
      <option value="published">Published</option>
    </select>

      <button type="submit" class="btn btn-primary w-full">Create Event</button>
    </form>

    <div class="mt-4">
      <a href="event_list.php" class="link link-primary">View My Events</a>
    </div>
  </div>
</body>
</html>
