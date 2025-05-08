<?php
include $_SERVER['DOCUMENT_ROOT'] . '/event/middleware/token_protect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/event/config/db.php';

$userId = $GLOBALS['AUTH_USER_ID'];
$originalId = $_GET['id'] ?? null;

if (!$originalId) { die("Missing event ID."); }

// Fetch original event
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
$stmt->execute([$originalId, $userId]);
$original = $stmt->fetch();

if (!$original) { die("Event not found or access denied."); }

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

$newName = $original['name'] . ' (Copy)';
$newImagePath = $original['image_path'];

// Clone image with new name if exists
if (!empty($original['image_path']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/event/' . $original['image_path'])) {
    $pathInfo = pathinfo($original['image_path']);
    $newFilename = uniqid('clone_') . '.' . $pathInfo['extension'];
    $source = $_SERVER['DOCUMENT_ROOT'] . '/event/' . $original['image_path'];
    $destination = $_SERVER['DOCUMENT_ROOT'] . '/event/uploads/events/' . $newFilename;

    if (copy($source, $destination)) {
        $newImagePath = 'uploads/events/' . $newFilename;
    }
}

// Clone event only
$stmt = $pdo->prepare("INSERT INTO events (
  user_id, name, description, category_id, subcategory_id, event_date,
  location, image_path, status, slug
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->execute([
  $userId,
  $newName,
  $original['description'],
  $original['category_id'],
  $original['subcategory_id'],
  $original['event_date'],
  $original['location'],
  $newImagePath,
  $original['status'],
  $slug
]);

$newEventId = $pdo->lastInsertId();

header("Location: edit.php?id=$newEventId");
exit;
