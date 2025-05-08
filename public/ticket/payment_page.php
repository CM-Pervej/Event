<?php
require_once '../config/db.php';

$ticketId = $_GET['ticket_id'] ?? null;
if (!$ticketId) {
    echo "Ticket ID is missing.";
    exit;
}

// Fetch the ticket details
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->execute([$ticketId]);
$ticket = $stmt->fetch();

if (!$ticket) {
    echo "Ticket not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulate successful payment by updating payment_status to 'paid'
    $stmt = $pdo->prepare("UPDATE tickets SET payment_status = ? WHERE id = ?");
    $stmt->execute(['paid', $ticketId]);

    // Redirect to the user's ticket view page
    header("Location: user_tickets.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simulate Payment</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-lg mx-auto bg-white p-6 rounded-xl shadow">
        <h1 class="text-2xl font-bold mb-4">Payment Page</h1>
        <p>You are about to purchase the ticket for event: <?= htmlspecialchars($ticket['event_id']) ?>. Total price: $<?= htmlspecialchars($ticket['price']) ?></p>

        <form method="POST" class="space-y-4">
            <button type="submit" class="btn btn-primary w-full">Confirm Payment</button>
        </form>

        <p class="mt-4">This is a simulated payment gateway. No real transactions are processed.</p>
    </div>
</body>
</html>
