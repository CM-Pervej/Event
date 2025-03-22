<?php
require_once '../../middleware/token_protect.php';
require_once '../../config/db.php';

$userId = $GLOBALS['AUTH_USER_ID'];
$eventId = $_POST['event_id'] ?? null;
$rating = (int) ($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if (!$eventId || $rating < 1 || $rating > 5) {
    echo "Invalid submission.";
    exit;
}

// Prevent duplicate feedback
$stmt = $pdo->prepare("SELECT id FROM feedback WHERE user_id = ? AND event_id = ?");
$stmt->execute([$userId, $eventId]);
if ($stmt->rowCount() > 0) {
    echo "Feedback already exists.";
    exit;
}

// Insert feedback
$stmt = $pdo->prepare("INSERT INTO feedback (user_id, event_id, rating, comment) VALUES (?, ?, ?, ?)");
$stmt->execute([$userId, $eventId, $rating, $comment]);

header("Location: ../../public/event_list.php");
exit;
