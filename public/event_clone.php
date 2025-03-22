<?php
require_once '../middleware/token_protect.php';
require_once '../config/db.php';

$userId = $GLOBALS['AUTH_USER_ID'];
$originalId = $_GET['id'] ?? null;

if (!$originalId) {
    die("Missing event ID.");
}

// Fetch original event
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
$stmt->execute([$originalId, $userId]);
$original = $stmt->fetch();

if (!$original) {
    die("Event not found or access denied.");
}

// Slug generation
function generateSlug($text) {
    return trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($text)), '-');
}

$baseSlug = generateSlug($original['name'] . '-copy');
$slug = $baseSlug;
$check = $pdo->prepare("SELECT COUNT(*) FROM events WHERE slug = ?");
$check->execute([$slug]);
if ($check->fetchColumn() > 0) {
    $slug .= '-' . substr(uniqid(), 0, 5);
}

// Insert cloned event
$newName = $original['name'] . ' (Copy)';
$stmt = $pdo->prepare("INSERT INTO events (
    user_id, name, description, category, subcategory, event_date,
    location, image_path, status, slug
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([
    $userId,
    $newName,
    $original['description'],
    $original['category'],
    $original['subcategory'],
    $original['event_date'],
    $original['location'],
    $original['image_path'],
    $original['status'],
    $slug
]);

$newEventId = $pdo->lastInsertId();


// ✅ Clone speakers (if any)
$speakerMap = []; // map old speaker_id => new speaker_id
$speakers = $pdo->prepare("SELECT * FROM speakers WHERE event_id = ?");
$speakers->execute([$originalId]);
foreach ($speakers as $s) {
    $insert = $pdo->prepare("INSERT INTO speakers (event_id, name, bio, image_path) VALUES (?, ?, ?, ?)");
    $insert->execute([$newEventId, $s['name'], $s['bio'], $s['image_path']]);
    $speakerMap[$s['id']] = $pdo->lastInsertId();
}

// ✅ Clone sessions (if any)
$sessions = $pdo->prepare("SELECT * FROM sessions WHERE event_id = ?");
$sessions->execute([$originalId]);
foreach ($sessions as $sess) {
    $newSpeakerId = $sess['speaker_id'] ? ($speakerMap[$sess['speaker_id']] ?? null) : null;
    $insert = $pdo->prepare("INSERT INTO sessions (event_id, title, session_date, session_time, speaker_id) VALUES (?, ?, ?, ?, ?)");
    $insert->execute([
        $newEventId,
        $sess['title'],
        $sess['session_date'],
        $sess['session_time'],
        $newSpeakerId
    ]);
}

header("Location: event_edit.php?id=$newEventId");
exit;
