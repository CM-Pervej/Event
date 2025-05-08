<?php
require_once '../../config/db.php';
require_once '../../vendor/autoload.php'; // For QR code generation
use Endroid\QrCode\QrCode;
require_once '../../middleware/token_protect.php'; // Ensure user is logged in

$userId = $GLOBALS['AUTH_USER_ID']; // Get logged-in user's ID
$eventId = $_POST['event_id'] ?? null;
$ticketTypeId = $_POST['ticket_type_id'] ?? null;

// Fetch ticket type details from the database
$stmt = $pdo->prepare("SELECT * FROM ticket_types WHERE id = ?");
$stmt->execute([$ticketTypeId]);
$ticketType = $stmt->fetch();

if (!$ticketType) {
    echo "Invalid ticket type.";
    exit;
}

// Calculate price based on ticket type
$price = $ticketType['base_price'];

// Generate QR code for the ticket
$qrContent = "Ticket for Event ID: $eventId - Type: {$ticketType['name']}";  // Customize this string as needed
$qrCode = new QrCode($qrContent);
$qrCode->setSize(300);
$qrCode->setMargin(10);

// Save QR code to the filesystem (uploads/qr_codes folder)
$qrFileName = "qr_codes/{$userId}_{$eventId}_{$ticketTypeId}.png";  // Unique file name based on user, event, and ticket type
$qrCode->writeFile(__DIR__ . "/../../uploads/$qrFileName");  // Store the QR code image in the 'uploads/qr_codes/' folder

// Insert ticket purchase into the database
$stmt = $pdo->prepare("INSERT INTO tickets (user_id, event_id, ticket_type_id, price, qr_code) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$userId, $eventId, $ticketTypeId, $price, $qrFileName]);

// Redirect user to view their purchased tickets
header("Location: ../../public/user_tickets.php");
exit;
?>
