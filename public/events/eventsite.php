<?php
include $_SERVER['DOCUMENT_ROOT'] . '/event/middleware/token_protect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/event/config/db.php';

$userId = $GLOBALS['AUTH_USER_ID'];

$slug = $_GET['event'] ?? null;
if (!$slug) { 
    echo "Event not found."; 
    exit; 
}

// Fetch the Event
$stmt = $pdo->prepare("SELECT * FROM events WHERE slug = ?");
$stmt->execute([$slug]);
$event = $stmt->fetch();

if (!$event) { 
    echo "Invalid event link."; 
    exit; 
}

$eventId = $event['id'];

// Handle Image Upload for Sessions
$imagePath = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    if ($_FILES['image']['error'] === 0) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/event/uploads/sessions/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = 'uploads/sessions/' . $filename;
        } else {
            $message = "Failed to upload image.";
        }
    }
}

// Fetch Speakers linked to this Event
$speakers = $pdo->prepare("SELECT sp.* FROM speakers sp
                            INNER JOIN sessions s ON s.speaker_id = sp.id
                            WHERE s.event_id = ?");
$speakers->execute([$eventId]);

// Fetch Sessions linked to this Event
$sessions = $pdo->prepare("SELECT s.*, sp.name AS speaker_name, sp.bio AS speaker_bio, sp.image_path AS speaker_image
                            FROM sessions s
                            LEFT JOIN speakers sp ON s.speaker_id = sp.id
                            WHERE s.event_id = ?");
$sessions->execute([$eventId]);

// Fetch the organizer's details
$organizerStmt = $pdo->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
$organizerStmt->execute([$event['user_id']]);
$organizer = $organizerStmt->fetch();
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['name']) ?> | Event Site</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.0.0/dist/full.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
</head>
<body class="bg-base-200">

<div class="flex h-screen">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/event/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Topbar -->
        <header class="bg-blue-50 shadow flex items-center w-full">
            <h1 class="text-xl font-bold whitespace-nowrap"><?= htmlspecialchars($event['name']) ?></h1>
            <span class="w-full">
                <?php include $_SERVER['DOCUMENT_ROOT'] . '/event/topbar.php'; ?>
            </span>
        </header>

        <!-- Content -->
        <main class="flex-1 p-6 overflow-y-auto">
            <figure class="relative w-full rounded-xl overflow-hidden">
                <?php if ($event['image_path']): ?>
                    <img src="../../<?= htmlspecialchars($event['image_path']) ?>" alt="Event Banner" class="size-full object-contain bg-black"/>
                    <div class="absolute inset-0 flex items-center justify-center text-center text-white">
                        <div>
                            <h2 class="text-3xl font-bold drop-shadow-md"><?= htmlspecialchars($event['name']) ?></h2>
                            <p class="text-sm mt-1 drop-shadow-sm"><?= htmlspecialchars($event['description']) ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="w-full h-60 bg-gray-300 flex items-center justify-center text-gray-600 rounded-t-xl"> No Image </div>
                <?php endif; ?>
            </figure>

            <div class="max-w-5xl mx-auto p-6">
                <section class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold mb-2"><?= htmlspecialchars($event['name']) ?></h1>
                        <h2 class="text-xl font-semibold">🗓️ Sessions</h2>
                    </div>
                    <div>
                        <p class="text-lg font-bold">Organizer: <?= htmlspecialchars($organizer['full_name']) ?></p>
                        <p>Email: <a href="mailto:<?= htmlspecialchars($organizer['email']) ?>" class="text-blue-600"><?= htmlspecialchars($organizer['email']) ?></a></p>
                        <p>Phone: <?= htmlspecialchars($organizer['phone'] ?? 'Not provided') ?></p>
                    </div>
                </section>

                <section class="flex">
                    <div class="my-6">
                        <?php foreach ($sessions as $sess): ?>
                            <div class="mb-4 flex group bg-white p-5 rounded shadow-lg relative transition-all duration-300"
                                 style="background-image: url('../../<?= htmlspecialchars($sess['image_path']) ?>'); background-size: cover; background-position: center;">
                                
                                <!-- This div will hide the background image on hover -->
                                <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-75 transition-all duration-300"></div>

                                <div class="flex-1 flex flex-col justify-center items-center text-center gap-3 p-4 relative z-10">
                                    <p class="text-3xl text-blue-600 font-extrabold"><?= htmlspecialchars($sess['title']) ?></p>
                                    <p class="text-lg"><?= date('M j, Y', strtotime($sess['session_date'])) ?> at <?= date('g:i A', strtotime($sess['session_time'])) ?></p>

                                    <?php if ($sess['speaker_image']): ?>
                                        <img src="../../<?= htmlspecialchars($sess['speaker_image']) ?>" alt="<?= htmlspecialchars($sess['speaker_name']) ?>" class="w-24 h-24 rounded-full object-cover">
                                    <?php else: ?>
                                        <p>No image available</p>
                                    <?php endif; ?>

                                    <p class="text-2xl font-bold">Speaker: <?= htmlspecialchars($sess['speaker_name'] ?? 'TBA') ?></p>
                                    <p class="text-base font-semibold"><?= htmlspecialchars($sess['speaker_bio'] ?? 'TBA') ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <?php if (strtotime($event['event_date']) < time()): ?>
                    <div class="mt-6">
                        <a href="feedback_submit.php?event_id=<?= $event['id'] ?>" class="btn btn-outline btn-primary">
                            📝 Leave Feedback
                        </a>
                    </div>
                <?php endif; ?>

                <div class="mt-4">
                    <a href="feedback_view.php?event_id=<?= $event['id'] ?>" class="btn btn-secondary btn-sm">
                        📊 View Feedback
                    </a>
                </div>
            </div>
        </main>
    </div>
</div>

</body>
</html>
