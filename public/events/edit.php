<?php
include $_SERVER['DOCUMENT_ROOT'] . '/event/middleware/token_protect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/event/config/db.php';

$userId = $GLOBALS['AUTH_USER_ID'];
$eventId = $_GET['id'] ?? null;

if (!$eventId) {
    die("Missing event ID.");
}

// Fetch the event for editing
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
$stmt->execute([$eventId, $userId]);
$event = $stmt->fetch();

if (!$event) {
    die("Event not found or access denied.");
}

// Fetch categories and subcategories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$subcategories = $pdo->query("SELECT * FROM subcategories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve form data
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $eventDate = $_POST['event_date'];
    $location = trim($_POST['location']);
    $status = $_POST['status'] ?? 'draft';
    $imagePath = $event['image_path']; // Retain the current image if not replaced

    // Handle category and subcategory updates
    $categoryId = $_POST['category_id'] ?? null;
    $newCategory = trim($_POST['new_category']);
    $subcategoryId = $_POST['subcategory_id'] ?? null;
    $newSubcategory = trim($_POST['new_subcategory']);

    // Handle new category if provided
    if ($newCategory) {
        $checkCat = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $checkCat->execute([$newCategory]);
        $existingCatId = $checkCat->fetchColumn();
        if ($existingCatId) {
            $categoryId = $existingCatId;
        } else {
            $insertCat = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $insertCat->execute([$newCategory]);
            $categoryId = $pdo->lastInsertId();
        }
    }

    // Handle new subcategory if provided
    if ($newSubcategory) {
        $checkSub = $pdo->prepare("SELECT id FROM subcategories WHERE name = ?");
        $checkSub->execute([$newSubcategory]);
        $existingSubId = $checkSub->fetchColumn();
        if ($existingSubId) {
            $subcategoryId = $existingSubId;
        } else {
            $insertSub = $pdo->prepare("INSERT INTO subcategories (name) VALUES (?)");
            $insertSub->execute([$newSubcategory]);
            $subcategoryId = $pdo->lastInsertId();
        }
    }

    // Handle image replacement (optional)
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/events/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = 'uploads/events/' . $filename;
        }
    }

    // Update the event in the database
    $stmt = $pdo->prepare("UPDATE events 
                           SET name = ?, description = ?, category_id = ?, subcategory_id = ?, event_date = ?, location = ?, image_path = ?, status = ? 
                           WHERE id = ? AND user_id = ?");
    $stmt->execute([
        $name,
        $description,
        $categoryId,
        $subcategoryId,
        $eventDate,
        $location,
        $imagePath,
        $status,
        $eventId,
        $userId
    ]);

    // Redirect to the event list after successful update
    header("Location: /event/public/events/event_list.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.0.0/dist/full.css" rel="stylesheet" />
</head>
<body class="bg-base-200">
    <section class="flex h-screen">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/event/sidebar.php'; ?>
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-blue-50 shadow flex items-center w-ful">
                <?php include $_SERVER['DOCUMENT_ROOT'] . '/event/topbar.php'; ?>
            </header>
            <main class="flex-1 p-6 overflow-y-auto">
                <div class="max-w-3xl mx-auto bg-white p-6 rounded-xl shadow">
                    <h1 class="text-2xl font-bold ">Edit Event: <span class="text-blue-600"><?= htmlspecialchars($event['name']) ?></span></h1>
            
                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="id" value="<?= $event['id'] ?>">
            
                        <input type="text" name="name" value="<?= htmlspecialchars($event['name']) ?>" class="input input-bordered w-full" placeholder="Event Name" required>
                        <textarea name="description" class="textarea textarea-bordered w-full" placeholder="Event Description" required><?= htmlspecialchars($event['description']) ?></textarea>
            
                        <!-- Category -->
                        <div class="grid grid-cols-2 gap-4">
                            <select name="category_id" class="select select-bordered w-full">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $event['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="new_category" placeholder="Or add new category" class="input input-bordered w-full">
                        </div>
            
                        <!-- Subcategory -->
                        <div class="grid grid-cols-2 gap-4">
                            <select name="subcategory_id" class="select select-bordered w-full">
                                <option value="">Select Subcategory</option>
                                <?php foreach ($subcategories as $sub): ?>
                                    <option value="<?= $sub['id'] ?>" <?= $sub['id'] == $event['subcategory_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($sub['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="new_subcategory" placeholder="Or add new subcategory" class="input input-bordered w-full">
                        </div>
            
                        <div class="grid grid-cols-2 gap-4">
                            <input type="datetime-local" name="event_date" value="<?= date('Y-m-d\TH:i', strtotime($event['event_date'])) ?>" class="input input-bordered w-full" required>
                            <input type="text" name="location" value="<?= htmlspecialchars($event['location']) ?>" class="input input-bordered w-full" placeholder="Location" required>
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
            </main>
        </div>
    </section>
</body>
</html>
