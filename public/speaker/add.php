<?php
include $_SERVER['DOCUMENT_ROOT'] . '/event/config/db.php';

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Insert a new speaker
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $name = $_POST['name'];
    $bio = $_POST['bio'];

    // Handle image upload
    if ($_FILES['image']['error'] === 0) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/event/uploads/speakers/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = 'uploads/speakers/' . $filename;
        } else {
            $message = "Failed to upload image.";
        }
    }

    // Insert speaker data into the database
    if (isset($imagePath)) {
        $stmt = $pdo->prepare("INSERT INTO speakers (name, bio, image_path) VALUES (:name, :bio, :image_path)");
        $stmt->execute(['name' => $name, 'bio' => $bio, 'image_path' => $imagePath]);

        $message = "Speaker added successfully!";
        $status = 'success';  // Modal status
    }
}

// Update speaker
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $bio = $_POST['bio'];

    // Check if a new image is uploaded
    if ($_FILES['image']['error'] === 0) {
        // Handle image upload
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/event/uploads/speakers/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate a unique filename for the new image
        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $filename;

        // Move the uploaded image to the target directory
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = 'uploads/speakers/' . $filename;

            // If a new image is uploaded, remove the old image
            $stmt = $pdo->prepare("SELECT image_path FROM speakers WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $speaker = $stmt->fetch();

            if ($speaker) {
                $oldImagePath = $_SERVER['DOCUMENT_ROOT'] . '/event/' . $speaker['image_path'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // Delete the old image
                }
            }
        }
    } else {
        // If no new image uploaded, keep the old image
        $stmt = $pdo->prepare("SELECT image_path FROM speakers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $speaker = $stmt->fetch();
        $imagePath = $speaker['image_path'];  // Retain the old image path
    }

    // Update speaker data in the database
    $stmt = $pdo->prepare("UPDATE speakers SET name = :name, bio = :bio, image_path = :image_path WHERE id = :id");
    $stmt->execute(['name' => $name, 'bio' => $bio, 'image_path' => $imagePath, 'id' => $id]);

    $message = "Speaker updated successfully!";
    $status = 'success';  // Modal status
}


// Delete speaker
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete_speaker'])) {
    $id = $_GET['delete_speaker'];

    // Get the image path before deleting the speaker
    $stmt = $pdo->prepare("SELECT image_path FROM speakers WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $speaker = $stmt->fetch();

    if ($speaker) {
        $imagePath = $speaker['image_path'];

        // Delete the speaker record from the database
        $stmt = $pdo->prepare("DELETE FROM speakers WHERE id = :id");
        $stmt->execute(['id' => $id]);

        // Remove the image file from the server if it exists
        $imagePathWithFullPath = $_SERVER['DOCUMENT_ROOT'] . '/event/' . $imagePath;
        if (file_exists($imagePathWithFullPath)) {
            unlink($imagePathWithFullPath); // Delete the image file
        }

        $message = "Speaker deleted successfully!";
        $status = 'success';  // Modal status
    } else {
        $message = "Speaker not found!";
        $status = 'error';  // Modal status
    }
}

// Get all speakers
$stmt = $pdo->query("SELECT * FROM speakers");
$speakers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Speaker Management</title>
    <!-- Tailwind CSS + DaisyUI -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
</head>
<body class="bg-blue-50 h-screen flex overflow-hidden">
    <!-- Sidebar (fixed) -->
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
                    <h1 class="text-4xl font-bold text-center text-blue-600">Speaker Management</h1>
                    <button id="addSpeakerBtn" class="btn btn-success text-white px-4 rounded hover:bg-green-700">Add New Speaker</button>
                </div>

                <!-- Speakers Table -->
                <div>
                    <table class="table w-full min-w-full rounded-lg shadow-2xl">
                        <thead>
                            <tr class="text-black text-base">
                                <th class="bg-gray-200 p-3">Speaker</th>
                                <th class="bg-gray-200 py-3">Name</th>
                                <th class="bg-gray-200 py-3">Bio</th>
                                <th class="bg-gray-200 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (!empty($speakers)): ?>
                                <?php foreach ($speakers as $speaker): ?>
                                    <tr class="hover:bg-gray-100">
                                        <td class="w-16 h-16">
                                            <img src="/event/<?php echo $speaker['image_path']; ?>" alt="Speaker Image" class="w-16 h-14 object-cover rounded-full">
                                        </td>
                                        <td class="text-blue-600 font-semibold text-lg"><?php echo $speaker['name']; ?></td>
                                        <td>
                                            <?php 
                                                $bio = explode(' ', $speaker['bio']);
                                                $short_bio = implode(' ', array_slice($bio, 0, 40)); 
                                            ?>
                                            <span class="block truncate w-96 text-ellipsis" title="<?php echo $speaker['bio']; ?>">
                                                <?php echo $short_bio; ?><?php echo (count($bio) > 40) ? '...' : ''; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="text-green-600 hover:text-green-800 updateBtn" data-id="<?php echo $speaker['id']; ?>" data-name="<?php echo $speaker['name']; ?>" data-bio="<?php echo $speaker['bio']; ?>" data-image="<?php echo $speaker['image_path']; ?>">Update </button> /
                                            <button class="deleteBtn text-red-600 hover:text-red-700" data-id="<?php echo $speaker['id']; ?>">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No speakers found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Add / Update Speaker Form Modal -->
                <div id="modal" class="fixed inset-0 flex items-center justify-center z-50 hidden bg-gray-500 bg-opacity-50">
                    <div class="bg-white p-8 rounded-lg w-1/2">
                        <h2 class="text-lg font-bold mb-4" id="modalTitle">Add New Speaker</h2>
                        <form id="speakerForm" method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="id" id="speakerId">
                            <div class="mb-4">
                                <label for="name" class="block">Name:</label>
                                <input type="text" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" name="name" id="name" required>
                            </div>
                            <div class="mb-4">
                                <label for="bio" class="block">Bio:</label>
                                <textarea class="textarea textarea-bordered w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" name="bio" id="bio" required></textarea>
                            </div>
                            <div class="mb-4">
                                <label for="image" class="block">Image:</label>
                                <input type="file" class="mt-1 block w-full border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" name="image" id="image">
                            </div>
                            <input type="hidden" name="image_path" id="imagePath">
                            <div class="flex justify-end gap-5">
                                <button type="button" id="closeModal" class="text-gray-500 hover:text-gray-800 mr-2">Cancel</button>
                                <button type="submit" name="add" class="!bg-green-600 text-white px-4 py-2 rounded" id="submitBtn">Add New Speaker</button>
                                <button type="submit" name="update" class="!bg-blue-600 text-white px-4 py-2 rounded hidden" id="updateBtn">Safe Speaker</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <div id="deleteModal" class="fixed inset-0 flex items-center justify-center z-50 hidden bg-gray-500 bg-opacity-50">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
                        <h2 class="text-lg font-bold mb-4">Are you sure you want to delete this speaker?</h2>
                        <form method="GET">
                            <input type="hidden" name="delete_speaker" id="deleteSpeakerId">
                            <div class="flex justify-end mt-4">
                                <button type="button" id="closeDeleteModal" class="text-gray-500 hover:text-gray-800 mr-2">Cancel</button>
                                <button type="submit" class="!bg-red-600 text-white px-4 py-2 rounded">Yes, Delete</button>
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

        // Open modal for adding new speaker
        document.getElementById('addSpeakerBtn').onclick = function() {
            document.getElementById('modal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Add New Speaker';
            document.getElementById('submitBtn').classList.remove('hidden');
            document.getElementById('updateBtn').classList.add('hidden');

            // Reset the form fields
            document.getElementById('speakerForm').reset();
        };

        // Close modal
        document.getElementById('closeModal').onclick = function() {
            document.getElementById('modal').classList.add('hidden');
        };

        // Open modal for updating a speaker
        const updateBtns = document.querySelectorAll('.updateBtn');
        updateBtns.forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const bio = this.getAttribute('data-bio');
                const image = this.getAttribute('data-image');

                document.getElementById('modal').classList.remove('hidden');
                document.getElementById('modalTitle').textContent = 'Update Speaker';
                document.getElementById('submitBtn').classList.add('hidden');
                document.getElementById('updateBtn').classList.remove('hidden');
                
                document.getElementById('speakerId').value = id;
                document.getElementById('name').value = name;
                document.getElementById('bio').value = bio;
                document.getElementById('image').value = image;
            };
        });

        // Open delete confirmation modal
        const deleteBtns = document.querySelectorAll('.deleteBtn');
        deleteBtns.forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                document.getElementById('deleteModal').classList.remove('hidden');
                document.getElementById('deleteSpeakerId').value = id;
            };
        });

        // Close delete confirmation modal
        document.getElementById('closeDeleteModal').onclick = function() {
            document.getElementById('deleteModal').classList.add('hidden');
        };
    </script>
</body>
</html>
