<?php
require_once '../config/db.php';
require_once '../middleware/token_protect.php'; // Ensure user is logged in

$userId = $GLOBALS['AUTH_USER_ID']; // Get logged-in user's ID

// Fetch events from the database
$stmt = $pdo->query("SELECT id, name FROM events");
$events = $stmt->fetchAll();

// If an event_id is provided via the URL, use it
$eventId = $_GET['event_id'] ?? null;

// Handle form submission to insert ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = $_POST['event_id'] ?? null;
    $ticketTypeId = $_POST['ticket_type_id'] ?? null;

    if (!$eventId || !$ticketTypeId) {
        echo "Event and Ticket Type must be selected.";
        exit;
    }

    // Fetch ticket type details from the database
    $stmt = $pdo->prepare("SELECT * FROM ticket_types WHERE id = ?");
    $stmt->execute([$ticketTypeId]);
    $ticketType = $stmt->fetch();

    if (!$ticketType) {
        echo "Invalid ticket type.";
        exit;
    }

    // Get the ticket price
    $price = $ticketType['base_price'];

    // Insert ticket into the database, payment status initially set to 'pending'
    $stmt = $pdo->prepare("INSERT INTO tickets (user_id, event_id, ticket_type_id, price, payment_status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $eventId, $ticketTypeId, $price, 'pending']);

    // Redirect to the simulated payment page
    header("Location: payment_page.php?ticket_id=" . $pdo->lastInsertId());
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Ticket</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-lg mx-auto bg-white p-6 rounded-xl shadow">
        <h1 class="text-2xl font-bold mb-4">Purchase Ticket</h1>
        <form action="ticket_purchase.php" method="POST" class="space-y-4">
            <label for="event_id">Select Event:</label>
            <select name="event_id" id="event_id" class="input input-bordered w-full" required>
                <?php foreach ($events as $event): ?>
                    <option value="<?= htmlspecialchars($event['id']) ?>" <?= ($eventId == $event['id']) ? 'selected' : '' ?>><?= htmlspecialchars($event['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="ticket_type_id">Select Ticket Type:</label>
            <select name="ticket_type_id" id="ticket_type_id" class="input input-bordered w-full" required>
                <option value="1">Regular - $50.00</option>
                <option value="2">VIP - $100.00</option>
                <option value="3">Student - $30.00</option>
            </select>

            <button type="submit" class="btn btn-primary w-full">Buy Ticket</button>
        </form>
    </div>
</body>
</html>
