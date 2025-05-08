<?php
// Include middleware for token protection
require_once $_SERVER['DOCUMENT_ROOT'] . '/event/middleware/token_protect.php';

// Include database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/event/config/db.php';

// Get the event_id from the URL parameter
if (!isset($_GET['event_id'])) {
    die('Event ID is required');
}

$event_id = $_GET['event_id'];

// Prepare the query to fetch the event details
$stmt = $pdo->prepare('SELECT * FROM events WHERE id = :event_id');
$stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
$stmt->execute();

// Fetch the event details
$event = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if event exists
if (!$event) {
    die('Event not found');
}

// Fetch the available ticket types (ticket_types are universal, not event-specific)
$stmt = $pdo->prepare('SELECT * FROM ticket_types');
$stmt->execute();

$ticket_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $ticket_type_id = $_POST['ticket_type_id'];

    // Get the user ID from the middleware (authenticated user)
    $user_id = $GLOBALS['AUTH_USER_ID']; // Using the user ID from the global middleware

    // Check if user is already registered for the event
    $stmt = $pdo->prepare('SELECT * FROM event_participation WHERE user_id = :user_id AND event_id = :event_id');
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo 'You are already registered for this event.';
    } else {
        // Insert registration into event_participation table
        $stmt = $pdo->prepare('
            INSERT INTO event_participation (user_id, event_id, ticket_types_id)
            VALUES (:user_id, :event_id, :ticket_type_id)
        ');

        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
        $stmt->bindParam(':ticket_type_id', $ticket_type_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo 'Registration successful!';
        } else {
            echo 'Error during registration.';
        }
    }
} else {
    // Display the event registration form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for Event: <?= htmlspecialchars($event['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-light border-right" id="sidebar-wrapper">
            <div class="sidebar-heading">Event Management</div>
            <div class="list-group list-group-flush">
                <a href="/event/dashboard.php" class="list-group-item list-group-item-action bg-light">Dashboard</a>
                <a href="/event/events.php" class="list-group-item list-group-item-action bg-light">Events</a>
                <a href="/event/users.php" class="list-group-item list-group-item-action bg-light">Users</a>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Topbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button class="btn btn-primary" id="menu-toggle">☰</button>
                    <span class="navbar-text">
                        Welcome, <?= htmlspecialchars($GLOBALS['AUTH_USER_ID']) ?>
                    </span>
                    <button class="btn btn-danger ml-auto">Logout</button>
                </div>
            </nav>

            <div class="container mt-5">
                <h1>Register for Event: <?= htmlspecialchars($event['name']) ?></h1>
                <p><?= htmlspecialchars($event['description']) ?></p>

                <form action="" method="POST">
                    <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['id']) ?>">

                    <div class="mb-3">
                        <label for="ticket_type" class="form-label">Select Ticket Type</label>
                        <select id="ticket_type" name="ticket_type_id" class="form-select" required>
                            <?php foreach ($ticket_types as $ticket): ?>
                                <option value="<?= $ticket['id'] ?>"><?= htmlspecialchars($ticket['name']) ?> - $<?= number_format($ticket['price'], 2) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toggle Sidebar Script -->
    <script>
        document.getElementById("menu-toggle").addEventListener("click", function() {
            document.getElementById("wrapper").classList.toggle("toggled");
        });
    </script>
</body>
</html>
<?php
}
?>
