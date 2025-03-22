<?php
require_once '../config/db.php';

$id = $_POST['id'] ?? null;
if (!$id) {
    echo "Missing speaker ID.";
    exit;
}

// Step 1: Get speaker info (image path and event_id)
$stmt = $pdo->prepare("SELECT event_id, image_path FROM speakers WHERE id = ?");
$stmt->execute([$id]);
$speaker = $stmt->fetch();

if (!$speaker) {
    echo "Speaker not found.";
    exit;
}

$eventId = $speaker['event_id'];
$imagePath = $speaker['image_path'] ?? null;

// Step 2: Delete the image from disk
if ($imagePath) {
    $fullPath = __DIR__ . '/../' . $imagePath;
    if (file_exists($fullPath)) {
        unlink($fullPath); // 🧨 Delete image
    }
}

// Step 3: Delete speaker from database
$stmt = $pdo->prepare("DELETE FROM speakers WHERE id = ?");
$stmt->execute([$id]);

// Step 4: Redirect back to speaker_add
header("Location: speaker_add.php?event_id=$eventId");
exit;
