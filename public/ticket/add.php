<?php
include $_SERVER['DOCUMENT_ROOT'] . '/event/config/db.php';

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Insert a new ticket type
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    // Insert ticket type data into the database
    $stmt = $pdo->prepare("INSERT INTO ticket_types (name, description, price, quantity) VALUES (:name, :description, :price, :quantity)");
    $stmt->execute([
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'quantity' => $quantity
    ]);

    $message = "Ticket type added successfully!";
    $status = 'success';  // Modal status
}

// Update ticket type
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    // Update ticket data in the database
    $stmt = $pdo->prepare("UPDATE ticket_types SET name = :name, description = :description, price = :price, quantity = :quantity WHERE id = :id");
    $stmt->execute([
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'quantity' => $quantity,
        'id' => $id
    ]);

    $message = "Ticket type updated successfully!";
    $status = 'success';  // Modal status
}

// Delete ticket type
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete_ticket'])) {
    $id = $_GET['delete_ticket'];

    // Delete the ticket record from the database
    $stmt = $pdo->prepare("DELETE FROM ticket_types WHERE id = :id");
    $stmt->execute(['id' => $id]);

    $message = "Ticket type deleted successfully!";
    $status = 'success';  // Modal status
}

// Get all ticket types
$stmt = $pdo->query("SELECT * FROM ticket_types");
$tickets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Management</title>
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
                    <h1 class="text-4xl font-bold text-center text-blue-600">Ticket Management</h1>
                    <button id="addTicketBtn" class="btn btn-success text-white px-4 rounded hover:bg-green-700">Add New Ticket</button>
                </div>

                <!-- Tickets Table -->
                <div>
                    <table class="table w-full min-w-full rounded-lg shadow-2xl">
                        <thead>
                            <tr class="text-black text-base">
                                <th class="bg-gray-200 p-3">SL.</th>
                                <th class="bg-gray-200 p-3">Ticket</th>
                                <th class="bg-gray-200 py-3">Description</th>
                                <th class="bg-gray-200 py-3">Price</th>
                                <th class="bg-gray-200 py-3">Quantity</th>
                                <th class="bg-gray-200 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (!empty($tickets)): ?>
                                <?php $sl = 1; ?>
                                <?php foreach ($tickets as $ticket): ?>
                                    <tr class="hover:bg-gray-100">
                                        <td class="text-center"><?php echo $sl++; ?></td>
                                        <td class="font-semibold text-lg whitespace-nowrap"><?php echo $ticket['name']; ?></td>
                                        <td class=""><?php echo $ticket['description']; ?></td>
                                        <td>$<?php echo number_format($ticket['price'], 2); ?></td>
                                        <td class="text-center"><?php echo $ticket['quantity']; ?></td>
                                        <td class="whitespace-nowrap">
                                            <button class="text-green-600 hover:text-green-800 updateBtn" data-id="<?php echo $ticket['id']; ?>" data-name="<?php echo $ticket['name']; ?>" data-description="<?php echo $ticket['description']; ?>" data-price="<?php echo $ticket['price']; ?>" data-quantity="<?php echo $ticket['quantity']; ?>">Update </button> /
                                            <button class="deleteBtn text-red-600 hover:text-red-700" data-id="<?php echo $ticket['id']; ?>">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No tickets found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Add / Update Ticket Form Modal -->
                <div id="modal" class="fixed inset-0 flex items-center justify-center z-50 hidden bg-gray-500 bg-opacity-50">
                    <div class="bg-white p-8 rounded-lg w-1/2 max-h-screen overflow-y-auto">
                        <h2 class="text-lg font-bold mb-4" id="modalTitle">Add New Ticket</h2>
                        <form id="ticketForm" method="POST" action="" autocomplete="off">
                            <input type="hidden" name="id" id="ticketId">
                            <div class="mb-4">
                                <label for="name" class="block">Name:</label>
                                <input type="text" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" name="name" id="name" required>
                            </div>
                            <div class="mb-4">
                                <label for="description" class="block">Description:</label>
                                <textarea class="textarea textarea-bordered w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" name="description" id="description" required></textarea>
                            </div>
                            <div class="mb-4">
                                <label for="price" class="block">Price:</label>
                                <input type="number" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" name="price" id="price" required>
                            </div>
                            <div class="mb-4">
                                <label for="quantity" class="block">Quantity:</label>
                                <input type="number" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" name="quantity" id="quantity" required>
                            </div>
                            <div class="flex justify-end gap-5">
                                <button type="button" id="closeModal" class="text-gray-500 hover:text-gray-800 mr-2">Cancel</button>
                                <button type="submit" name="add" class="!bg-green-600 text-white px-4 py-2 rounded" id="submitBtn">Add New Ticket</button>
                                <button type="submit" name="update" class="!bg-blue-600 text-white px-4 py-2 rounded hidden" id="updateBtn">Save Ticket</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <div id="deleteModal" class="fixed inset-0 flex items-center justify-center z-50 hidden bg-gray-500 bg-opacity-50">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
                        <h2 class="text-lg font-bold mb-4">Are you sure you want to delete this ticket?</h2>
                        <form method="GET">
                            <input type="hidden" name="delete_ticket" id="deleteTicketId">
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

        // Open modal for adding new ticket
        document.getElementById('addTicketBtn').onclick = function() {
            document.getElementById('modal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Add New Ticket';
            document.getElementById('submitBtn').classList.remove('hidden');
            document.getElementById('updateBtn').classList.add('hidden');

            // Reset the form fields
            document.getElementById('ticketForm').reset();
        };

        // Close modal
        document.getElementById('closeModal').onclick = function() {
            document.getElementById('modal').classList.add('hidden');
        };

        // Open modal for updating a ticket
        const updateBtns = document.querySelectorAll('.updateBtn');
        updateBtns.forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const description = this.getAttribute('data-description');
                const price = this.getAttribute('data-price');
                const quantity = this.getAttribute('data-quantity');

                document.getElementById('modal').classList.remove('hidden');
                document.getElementById('modalTitle').textContent = 'Update Ticket';
                document.getElementById('submitBtn').classList.add('hidden');
                document.getElementById('updateBtn').classList.remove('hidden');
                
                document.getElementById('ticketId').value = id;
                document.getElementById('name').value = name;
                document.getElementById('description').value = description;
                document.getElementById('price').value = price;
                document.getElementById('quantity').value = quantity;
            };
        });

        // Open delete confirmation modal
        const deleteBtns = document.querySelectorAll('.deleteBtn');
        deleteBtns.forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                document.getElementById('deleteModal').classList.remove('hidden');
                document.getElementById('deleteTicketId').value = id;
            };
        });

        // Close delete confirmation modal
        document.getElementById('closeDeleteModal').onclick = function() {
            document.getElementById('deleteModal').classList.add('hidden');
        };
    </script>
</body>
</html>
