<?php
require_once '../../config/db.php';
require_once '../../middleware/token_protect.php';

$eventId = $_POST['event_id'];
$title = trim($_POST['title']);
$date = $_POST['session_date'];
$time = $_POST['session_time'];
$speakerId = $_POST['speaker_id'] ?: null;

$stmt = $pdo->prepare("INSERT INTO sessions (event_id, title, session_date, session_time, speaker_id) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$eventId, $title, $date, $time, $speakerId]);

header("Location: ../../public/event_list.php");
exit;
