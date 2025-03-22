<?php
require_once '../../config/db.php';
$id = $_POST['id'];
$name = trim($_POST['name']);
$bio = trim($_POST['bio']);
$imagePath = null;

// Get event_id for redirection
$stmt = $pdo->prepare("SELECT * FROM speakers WHERE id = ?");
$stmt->execute([$id]);
$speaker = $stmt->fetch();
$eventId = $speaker['event_id'] ?? null;
$imagePath = $speaker['image_path'];

// Optional image upload
if ($_FILES['image']['error'] === 0) {
    $uploadDir = '../../uploads/speakers/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $filename = uniqid() . '_' . basename($_FILES['image']['name']);
    $targetPath = $uploadDir . $filename;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $imagePath = 'uploads/speakers/' . $filename;
    }
}

$stmt = $pdo->prepare("UPDATE speakers SET name = ?, bio = ?, image_path = ? WHERE id = ?");
$stmt->execute([$name, $bio, $imagePath, $id]);

header("Location: ../../public/speaker_add.php?event_id=$eventId");
exit;
