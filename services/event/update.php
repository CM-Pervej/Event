<?php
require_once '../../config/db.php';
require_once '../../middleware/token_protect.php';

$userId = $GLOBALS['AUTH_USER_ID'];
$eventId = $_POST['id'] ?? null;

if (!$eventId) {
    die("Invalid request.");
}

$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
$stmt->execute([$eventId, $userId]);
$event = $stmt->fetch();

if (!$event) {
    die("Unauthorized or event not found.");
}

$name = trim($_POST['name']);
$description = trim($_POST['description']);
$category = trim($_POST['category']);
$subcategory = trim($_POST['subcategory']);
$eventDate = $_POST['event_date'];
$location = trim($_POST['location']);
$imagePath = $event['image_path'];
$status = $_POST['status'] ?? 'draft';

// Optional image upload
if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../../uploads/events/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $filename = uniqid() . '_' . basename($_FILES['image']['name']);
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $imagePath = 'uploads/events/' . $filename;
    }
}

$stmt = $pdo->prepare("UPDATE events SET name = ?, description = ?, category = ?, subcategory = ?, event_date = ?, location = ?, image_path = ?, status = ? WHERE id = ? AND user_id = ?");
$stmt->execute([$name, $description, $category, $subcategory, $eventDate, $location, $imagePath, $status, $eventId, $userId]);

header("Location: ../../public/event_list.php");
exit;
