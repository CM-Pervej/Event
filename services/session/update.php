<?php
require_once '../../config/db.php';

$id = $_POST['id'];
$title = trim($_POST['title']);
$date = $_POST['session_date'];
$time = $_POST['session_time'];
$speakerId = $_POST['speaker_id'] ?: null;

// Get event_id for redirect
$stmt = $pdo->prepare("SELECT event_id FROM sessions WHERE id = ?");
$stmt->execute([$id]);
$session = $stmt->fetch();
$eventId = $session['event_id'] ?? null;

$stmt = $pdo->prepare("UPDATE sessions SET title = ?, session_date = ?, session_time = ?, speaker_id = ? WHERE id = ?");
$stmt->execute([$title, $date, $time, $speakerId, $id]);

header("Location: ../../public/session_add.php?event_id=$eventId");
exit;
