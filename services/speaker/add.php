<?php
require_once '../../config/db.php';
require_once '../../middleware/token_protect.php';

$eventId = $_POST['event_id'];
$name = trim($_POST['name']);
$bio = trim($_POST['bio']);
$imagePath = null;

if ($_FILES['image']['error'] === 0) {
    $uploadDir = '../../uploads/speakers/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $filename = uniqid() . '_' . basename($_FILES['image']['name']);
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $imagePath = 'uploads/speakers/' . $filename;
    }
}

$stmt = $pdo->prepare("INSERT INTO speakers (event_id, name, bio, image_path) VALUES (?, ?, ?, ?)");
$stmt->execute([$eventId, $name, $bio, $imagePath]);

header("Location: ../../public/event_list.php");
exit;
