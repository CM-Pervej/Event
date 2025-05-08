<?php
require_once '../config/db.php';
require_once '../middleware/token_protect.php';

$userId = $GLOBALS['AUTH_USER_ID']; // Get logged-in user's ID

// Fetch user's purchased tickets
$stmt = $pdo->prepare("SELECT t.id, e.name AS event_name, tt.name AS ticket_type, t.price, t.payment_status 
                       FROM tickets t
                       JOIN events e ON t.event_id = e.id
                       JOIN ticket_types tt ON t.ticket_type_id = tt.id
                       WHERE t.user_id = ?");
$stmt->execute([$userId]);
$tickets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Tickets</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-xl shadow">
        <h1 class="text-2xl font-bold mb-4">Your Tickets</h1>

        <div class="space-y-4">
            <?php foreach ($tickets as $ticket): ?>
                <div class="card p-4 bg-base-100 shadow-lg">
                    <h3 class="text-lg font-semibold"><?= htmlspecialchars($ticket['event_name']) ?> - <?= htmlspecialchars($ticket['ticket_type']) ?></h3>
                    <p>Price: $<?= number_format($ticket['price'], 2) ?></p>
                    <p>Status: <?= ucfirst($ticket['payment_status']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
