<?php
require_once '../config/db.php';

$id = $_POST['id'] ?? null;
if (!$id) exit("Missing session ID");

$stmt = $pdo->prepare("SELECT event_id FROM sessions WHERE id = ?");
$stmt->execute([$id]);
$session = $stmt->fetch();
$eventId = $session['event_id'] ?? null;

$stmt = $pdo->prepare("DELETE FROM sessions WHERE id = ?");
$stmt->execute([$id]);

header("Location: session_add.php?event_id=$eventId");
exit;
