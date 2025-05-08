<?php
require_once '../../config/db.php';
require_once '../../middleware/token_protect.php';

$userId = $GLOBALS['AUTH_USER_ID'];

function generateSlug($text) {
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower($text));
    return trim($slug, '-');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $eventDate = $_POST['event_date'];
    $location = trim($_POST['location']);
    $status = 'draft';  // Automatically set status to draft
    $imagePath = null;

    // Handle categories
    $categoryId = $_POST['category_id'] ?? null;
    $newCategory = trim($_POST['new_category']);
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

    // Handle subcategories
    $subcategoryId = $_POST['subcategory_id'] ?? null;
    $newSubcategory = trim($_POST['new_subcategory']);
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

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = '../../uploads/events/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = 'uploads/events/' . $filename;
        }
    }

    // Slug generation
    $slug = generateSlug($name);
    $checkSlug = $pdo->prepare("SELECT COUNT(*) FROM events WHERE slug = ?");
    $checkSlug->execute([$slug]);
    if ($checkSlug->fetchColumn() > 0) {
        $slug .= '-' . substr(uniqid(), 0, 5);
    }

    // Insert event
    $stmt = $pdo->prepare("INSERT INTO events (user_id, name, description, category_id, subcategory_id, event_date, location, image_path, status, slug) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $userId,
        $name,
        $description,
        $categoryId,
        $subcategoryId,
        $eventDate,
        $location,
        $imagePath,
        $status,  // Always set to 'draft'
        $slug
    ]);

    header("Location: ../../public/events/event_list.php");
    exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$subcategories = $pdo->query("SELECT * FROM subcategories ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Create Event</title>
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
            <main class="flex-1 px-6 overflow-y-auto">
                <div class="max-w-3xl mx-auto bg-white p-6 mt-5 rounded-xl shadow">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-bold mb-4">Create New Event</h1>
                        <a href="event_list.php" class="link link-primary">View My Events</a>
                    </div>
            
                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($userId) ?>">

                        <input type="text" name="name" placeholder="Event Name" class="input input-bordered w-full" required>
                        <textarea name="description" placeholder="Event Description" class="textarea textarea-bordered w-full" required></textarea>
            
                        <!-- Category -->
                        <div class="grid grid-cols-2 gap-4">
                            <select name="category_id" class="select select-bordered w-full">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="new_category" placeholder="Or add new category" class="input input-bordered w-full">
                        </div>
            
                        <!-- Subcategory -->
                        <div class="grid grid-cols-2 gap-4">
                            <select name="subcategory_id" class="select select-bordered w-full">
                                <option value="">Select Subcategory</option>
                                <?php foreach ($subcategories as $sub): ?>
                                    <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="new_subcategory" placeholder="Or add new subcategory" class="input input-bordered w-full">
                        </div>
            
                        <div class="grid grid-cols-2 gap-4">
                            <input type="datetime-local" name="event_date" class="input input-bordered w-full" required>
                            <input type="text" name="location" placeholder="Location" class="input input-bordered w-full" required>
                        </div>
            
                        <label class="form-control w-full">
                            <span class="label-text">Upload Event Banner</span>
                            <input type="file" name="image" class="file-input file-input-bordered w-full" />
                        </label>

                        <!-- The status field is completely removed -->

                        <button type="submit" class="btn btn-primary w-full">Create Event</button>
                    </form>
                </div>
            </main>
        </div>
    </section>
</body>
</html>
