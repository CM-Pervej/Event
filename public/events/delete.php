<?php
include $_SERVER['DOCUMENT_ROOT'] . '/event/middleware/token_protect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/event/config/db.php';

$userId = $GLOBALS['AUTH_USER_ID'];
$eventId = $_POST['id'] ?? null;

if (!$eventId) {
    die("Missing event ID.");
}

// Get event with image path
$stmt = $pdo->prepare("SELECT image_path FROM events WHERE id = ? AND user_id = ?");
$stmt->execute([$eventId, $userId]);
$event = $stmt->fetch();

if (!$event) {
    die("Unauthorized or event not found.");
}

// Delete the image file if it exists
if (!empty($event['image_path'])) {
    $filePath = '../../' . $event['image_path'];
    if (file_exists($filePath)) {
        unlink($filePath); // Deletes the image
    }
}

// Delete the event from the database
$stmt = $pdo->prepare("DELETE FROM events WHERE id = ? AND user_id = ?");
$stmt->execute([$eventId, $userId]);

header("Location: event_list.php");
exit;
