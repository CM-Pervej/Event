<?php
require_once '../../config/db.php';
require_once '../../middleware/token_protect.php';

$userId = $GLOBALS['AUTH_USER_ID'];
$errors = [];

function generateSlug($text) {
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower($text));
    return trim($slug, '-');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $subcategory = trim($_POST['subcategory']);
    $eventDate = $_POST['event_date'];
    $location = trim($_POST['location']);
    $imagePath = null;
    $status = $_POST['status'] ?? 'draft';

    // Generate and ensure unique slug
    $slug = generateSlug($name);
    $check = $pdo->prepare("SELECT COUNT(*) FROM events WHERE slug = ?");
    $check->execute([$slug]);
    if ($check->fetchColumn() > 0) {
        $slug .= '-' . substr(uniqid(), 0, 5);
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

    try {
        $stmt = $pdo->prepare("INSERT INTO events (user_id, name, description, category, subcategory, event_date, location, image_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $name, $description, $category, $subcategory, $eventDate, $location, $imagePath, $status]);

        header("Location: ../../public/event_list.php");
        exit;

    } catch (PDOException $e) {
        $errors[] = $e->getMessage();
    }
} else {
    header("Location: ../../public/event_create.php");
    exit;
}
