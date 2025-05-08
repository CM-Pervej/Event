<?php
include $_SERVER['DOCUMENT_ROOT'] . '/event/config/db.php';

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Fetch event_id from the URL
$eventId = $_GET['event_id'] ?? null;

if (!$eventId) {
    echo "Missing event ID.";
    exit;
}

// Fetch event name to display at the top (for visibility only)
$eventStmt = $pdo->prepare("SELECT name FROM events WHERE id = ?");
$eventStmt->execute([$eventId]);
$eventData = $eventStmt->fetch();
$eventName = $eventData ? $eventData['name'] : 'Unknown';

// Fetch speakers for the dropdown
$stmt = $pdo->query("SELECT id, name FROM speakers ORDER BY name ASC");
$speakers = $stmt->fetchAll();

// Insert a new session
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $title = $_POST['title'];
    $date = $_POST['session_date'];
    $time = $_POST['session_time'];
    $speakerId = $_POST['speaker_id'];
    
    // Handle image upload
    $imagePath = null;
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

    // Insert session data into the database with event_id
    $stmt = $pdo->prepare("INSERT INTO sessions (title, session_date, session_time, speaker_id, event_id, image_path) 
                           VALUES (:title, :date, :time, :speaker_id, :event_id, :image_path)");
    $stmt->execute([
        'title' => $title, 
        'date' => $date, 
        'time' => $time, 
        'speaker_id' => $speakerId, 
        'event_id' => $eventId,  // Event ID is automatically passed
        'image_path' => $imagePath
    ]);

    $message = "Session added successfully!";
    $status = 'success';  // Modal status
}

// Update session
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $date = $_POST['session_date'];
    $time = $_POST['session_time'];
    $speakerId = $_POST['speaker_id'];

    // Check if a new image is uploaded
    if ($_FILES['image']['error'] === 0) {
        // Handle image upload
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/event/uploads/sessions/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate a unique filename for the new image
        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $filename;

        // Move the uploaded image to the target directory
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = 'uploads/sessions/' . $filename;

            // If a new image is uploaded, remove the old image
            $stmt = $pdo->prepare("SELECT image_path FROM sessions WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $session = $stmt->fetch();

            if ($session) {
                $oldImagePath = $_SERVER['DOCUMENT_ROOT'] . '/event/' . $session['image_path'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // Delete the old image
                }
            }
        }
    } else {
        // If no new image uploaded, keep the old image
        $stmt = $pdo->prepare("SELECT image_path FROM sessions WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $session = $stmt->fetch();
        $imagePath = $session['image_path'];  // Retain the old image path
    }

    // Update session data in the database, including event_id
    $stmt = $pdo->prepare("UPDATE sessions SET title = :title, session_date = :date, session_time = :time, speaker_id = :speaker_id, image_path = :image_path WHERE id = :id");
    $stmt->execute(['title' => $title, 'date' => $date, 'time' => $time, 'speaker_id' => $speakerId,  'image_path' => $imagePath, 'id' => $id]);

    $message = "Session updated successfully!";
    $status = 'success';  // Modal status
}

// Delete session
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete_session']) && isset($_GET['event_id'])) {
    $id = $_GET['delete_session'];
    $eventId = $_GET['event_id']; // Get the event_id from URL

    // Get the image path before deleting the session
    $stmt = $pdo->prepare("SELECT image_path FROM sessions WHERE id = :id AND event_id = :event_id");
    $stmt->execute(['id' => $id, 'event_id' => $eventId]);
    $session = $stmt->fetch();

    if ($session) {
        $imagePath = $session['image_path'];

        // Delete the session record from the database
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE id = :id AND event_id = :event_id");
        $stmt->execute(['id' => $id, 'event_id' => $eventId]);

        // Remove the image file from the server if it exists
        $imagePathWithFullPath = $_SERVER['DOCUMENT_ROOT'] . '/event/' . $imagePath;
        if (file_exists($imagePathWithFullPath)) {
            unlink($imagePathWithFullPath); // Delete the image file
        }

        $message = "Session deleted successfully!";
        $status = 'success';  // Modal status
    } else {
        $message = "Session not found!";
        $status = 'error';  // Modal status
    }
}

// Get all sessions with speaker name and event name
$stmt = $pdo->prepare("
    SELECT s.*, sp.name AS speaker_name, e.name AS event_name 
    FROM sessions s
    LEFT JOIN speakers sp ON s.speaker_id = sp.id
    LEFT JOIN events e ON s.event_id = e.id
    WHERE s.event_id = :event_id
");
$stmt->execute(['event_id' => $eventId]);
$sessions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Management</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
</head>
<body class="bg-blue-50 h-screen flex overflow-hidden">
    <header class="w-64 bg-blue-50 text-white fixed h-full sidebar-scrollable">
        <?php include '../../sideBar.php'; ?>
    </header>

    <div class="flex flex-col flex-grow ml-64">
        <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-10">
            <?php include '../../topBar.php'; ?>
        </aside>
        <main class="flex-grow p-8 mt-5 bg-white shadow-lg overflow-auto">
            <div class="mx-auto bg-white p-10">
                <!-- Success/Error Modal -->
                <?php if (isset($status)): ?>
                    <div id="alertModal" class="modal modal-open">
                        <div class="modal-box">
                            <h2 class="text-xl font-semibold text-center"><?php echo $status === 'success' ? 'Success' : 'Error'; ?></h2>
                            <p class="text-center mt-2"><?php echo isset($message) ? $message : ''; ?></p>
                            <div class="modal-action">
                                <button class="btn btn-primary w-full" id="closeModalButton">Close</button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="flex justify-between items-center mb-4">
                    <h1 class="text-4xl font-bold text-center text-blue-600"><?php echo $eventName; ?></h1>
                    <button id="addSessionBtn" class="btn btn-success text-white px-4 py-2 rounded hover:bg-green-700 mb-4">Add Session</button>
                </div>

                <!-- Sessions Table -->
                <div>
                    <table class="table w-full text-center min-w-full rounded-lg shadow-2xl">
                        <thead>
                            <tr class="text-black">
                                <th class="bg-gray-200 p-3">Session</th>
                                <th class="bg-gray-200 py-3">Title</th>
                                <th class="bg-gray-200 py-3">Date</th>
                                <th class="bg-gray-200 py-3">Time</th>
                                <th class="bg-gray-200 py-3">Speaker</th>
                                <th class="bg-gray-200 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (!empty($sessions)): ?>
                                <?php foreach ($sessions as $session): ?>
                                    <tr class="hover:bg-gray-100">
                                        <td class="w-16 h-16 px-3 py-1">
                                            <img src="/event/<?php echo $session['image_path']; ?>" alt="Session Image" class="w-full h-full object-cover rounded-full">
                                        </td>
                                        <td class="text-blue-600 font-semibold text-lg"><?php echo $session['title']; ?></td>
                                        <td><?php echo $session['session_date']; ?></td>
                                        <td><?php echo $session['session_time']; ?></td>
                                        <td><?php echo $session['speaker_name']; ?></td> <!-- Display speaker name -->
                                        <td>
                                            <button class="text-green-600 hover:text-green-700 updateBtn" data-id="<?php echo $session['id']; ?>" data-title="<?php echo $session['title']; ?>" data-date="<?php echo $session['session_date']; ?>" data-time="<?php echo $session['session_time']; ?>" data-speaker="<?php echo $session['speaker_id']; ?>" data-event="<?php echo $session['event_id']; ?>" data-image="<?php echo $session['image_path']; ?>">Update</button> / 
                                            <button class="deleteBtn text-red-600 hover:text-red-700" data-id="<?php echo $session['id']; ?>" data-event_id="<?php echo $eventId; ?>">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No sessions found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Add / Update Session Form Modal -->
                <div id="modal" class="fixed inset-0 flex items-center justify-center z-50 hidden bg-gray-500 bg-opacity-50">
                    <div class="bg-white p-8 rounded-lg w-1/2">
                        <h2 class="text-lg font-bold mb-4" id="modalTitle">Add New Session</h2>
                        <form id="sessionForm" method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="id" id="sessionId">
                            <div class="mb-4">
                                <label for="title" class="block">Title:</label>
                                <input type="text" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" name="title" id="title" required>
                            </div>
                            <div class="mb-4">
                                <label for="session_date" class="block">Date:</label>
                                <input type="date" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" name="session_date" id="session_date" required>
                            </div>
                            <div class="mb-4">
                                <label for="session_time" class="block">Time:</label>
                                <input type="time" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" name="session_time" id="session_time" required>
                            </div>
                            <div class="mb-4">
                                <label for="speaker_id" class="block">Speaker:</label>
                                <select name="speaker_id" id="speaker_id" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                                    <option value="">Select Speaker</option>
                                    <!-- Populate speakers dynamically -->
                                    <?php foreach ($speakers as $speaker): ?>
                                        <option value="<?php echo $speaker['id']; ?>"><?php echo $speaker['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="image" class="block">Image:</label>
                                <input type="file" class="mt-1 block w-full border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" name="image" id="image">
                            </div>
                            <input type="hidden" name="image_path" id="imagePath">
                            <div class="flex justify-end gap-5">
                                <button type="button" id="closeModal" class="text-gray-500 hover:text-gray-800 mr-2">Cancel</button>
                                <button type="submit" name="add" class="!bg-green-600 text-white px-4 py-2 rounded" id="submitBtn">Add</button>
                                <button type="submit" name="update" class="!bg-blue-600 text-white px-4 py-2 rounded hidden" id="updateBtn">Update</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <div id="deleteModal" class="fixed inset-0 flex items-center justify-center z-50 hidden bg-gray-500 bg-opacity-50">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
                        <h2 class="text-lg font-bold mb-4">Are you sure you want to delete this session?</h2>
                        <form method="GET">
                            <input type="hidden" name="delete_session" id="deleteSessionId">
                            <input type="hidden" name="event_id" id="deleteEventId">
                            <div class="flex justify-end mt-4">
                                <button type="button" id="closeDeleteModal" class="text-gray-500 hover:text-gray-800 mr-2">Cancel</button>
                                <button type="submit" class="!bg-red-600 text-white px-4 py-2 rounded">Delete</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // JavaScript to close the modal when the "Close" button is clicked
            const closeModalButton = document.getElementById('closeModalButton');
            const alertModal = document.getElementById('alertModal');

            if (closeModalButton && alertModal) {
                closeModalButton.onclick = function() {
                    alertModal.classList.remove('modal-open');
                    alertModal.classList.add('hidden');
                };
            }
        });

        // Open modal for adding new session
        document.getElementById('addSessionBtn').onclick = function() {
            document.getElementById('modal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Add New Session';
            document.getElementById('submitBtn').classList.remove('hidden');
            document.getElementById('updateBtn').classList.add('hidden');

            // Reset the form fields
            document.getElementById('sessionForm').reset();
        };

        // Close modal
        document.getElementById('closeModal').onclick = function() {
            document.getElementById('modal').classList.add('hidden');
        };

        // Open modal for updating a session
        const updateBtns = document.querySelectorAll('.updateBtn');
        updateBtns.forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');
                const date = this.getAttribute('data-date');
                const time = this.getAttribute('data-time');
                const speaker = this.getAttribute('data-speaker');
                const event = this.getAttribute('data-event');
                const image = this.getAttribute('data-image');

                document.getElementById('modal').classList.remove('hidden');
                document.getElementById('modalTitle').textContent = 'Update Session';
                document.getElementById('submitBtn').classList.add('hidden');
                document.getElementById('updateBtn').classList.remove('hidden');
                
                document.getElementById('sessionId').value = id;
                document.getElementById('title').value = title;
                document.getElementById('session_date').value = date;
                document.getElementById('session_time').value = time;
                document.getElementById('speaker_id').value = speaker;
                document.getElementById('event_id').value = event;
                document.getElementById('image').value = image;
            };
        });

        // Open delete confirmation modal
        const deleteBtns = document.querySelectorAll('.deleteBtn');
        deleteBtns.forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                const event_id = this.getAttribute('data-event_id');
                document.getElementById('deleteModal').classList.remove('hidden');
                document.getElementById('deleteSessionId').value = id;
                document.getElementById('deleteEventId').value = event_id;
            };
        });

        // Close delete confirmation modal
        document.getElementById('closeDeleteModal').onclick = function() {
            document.getElementById('deleteModal').classList.add('hidden');
        };
    </script>
</body>
</html>
