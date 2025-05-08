<?php
// user_events.php - Events Page for a Selected Organizer

require_once $_SERVER['DOCUMENT_ROOT'] . '/event/config/db.php'; // Database connection

// Get the selected organizer's user_id
$organizerId = $_GET['user_id'] ?? null;

if (!$organizerId) {
  echo "Invalid user.";
  exit;
}

// Determine the current view (default is 'draft', if 'status' is set, it will change to 'published')
$status = (isset($_GET['status']) && $_GET['status'] == 'published') ? 'published' : 'draft';

// Fetch events for the selected organizer based on the current status
$stmt = $pdo->prepare("SELECT e.*, u.full_name 
                       FROM events e 
                       JOIN users u ON e.user_id = u.id 
                       WHERE e.user_id = ? AND e.status = ? 
                       ORDER BY event_date DESC");
$stmt->execute([$organizerId, $status]);
$events = $stmt->fetchAll();

// Fetch the organizer's name for the page title
$stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt->execute([$organizerId]);
$organizer = $stmt->fetch();

if (!$organizer) {
  echo "Organizer not found.";
  exit;
}

// Fetch categories and subcategories as maps (id => name)
$categoryMap = [];
foreach ($pdo->query("SELECT id, name FROM categories") as $cat) {
  $categoryMap[$cat['id']] = $cat['name'];
}

$subcategoryMap = [];
foreach ($pdo->query("SELECT id, name FROM subcategories") as $sub) {
  $subcategoryMap[$sub['id']] = $sub['name'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($organizer['full_name']) ?>'s Events</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.0.0/dist/full.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
</head>
<body class="bg-base-200">

<div class="flex h-screen">
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/event/sidebar.php'; ?> <!-- Optional sidebar -->

  <div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-blue-50 shadow flex items-center w-full">
      <?php include $_SERVER['DOCUMENT_ROOT'] . '/event/topbar.php'; ?> <!-- Optional top bar -->
    </header>

    <main class="flex-1 px-6 overflow-y-auto">

      <!-- Toggle Button Section -->
      <div class="mt-8 flex justify-between items-center px-6">
        <h1 class="text-2xl font-bold">Events organized by <?= htmlspecialchars($organizer['full_name']) ?></h1>
        <p class="text-2xl font-bold">
          <!-- Toggle Button: Switches between draft and published -->
          <?php
            $toggleStatus = ($status == 'published') ? 'draft' : 'published';
            $toggleLabel = ($status == 'draft') ? 'Show Published' : 'Show Draft';
          ?>
          <!-- Include the user_id in the URL along with the status -->
          <a href="?user_id=<?= urlencode($organizerId) ?>&status=<?= $toggleStatus; ?>" class="hover:border-b-2 hover:border-black">
            <?php echo $toggleLabel; ?>
          </a>
        </p>
      </div>

      <!-- Event List Section -->
      <div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow mt-5">
        <?php if (count($events) === 0): ?>
          <p class="text-center text-gray-500">This organizer has no events at the moment.</p>
        <?php else: ?>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($events as $event): ?>
              <div class="card bg-base-100 shadow-md h-max" data-event-id="<?= $event['id'] ?>">
                <figure class="h-96">
                  <?php if ($event['image_path']): ?>
                    <img src="../../<?= htmlspecialchars($event['image_path']) ?>" alt="Event Banner" class="w-full h-full object-cover">
                  <?php else: ?>
                    <div class="w-full h-48 bg-gray-300 flex items-center justify-center text-gray-600">No Image</div>
                  <?php endif; ?>
                </figure>
                <div class="card-body">
                  <h2 class="card-title flex justify-between items-center">
                    <?= htmlspecialchars($event['name']) ?>
                    <button class="btn btn-xs btn-outline ml-2 toggle-btn">See More</button>
                  </h2>

                  <div class="event-details hidden mt-2">
                    <p><?= htmlspecialchars($event['description']) ?></p>

                    <div class="mt-2">
                      <span class="badge <?= $event['status'] === 'published' ? 'badge-success' : 'badge-warning' ?>">
                        <?= ucfirst($event['status']) ?>
                      </span>
                    </div>

                    <div class="text-sm text-gray-500 mt-2">
                      <p>
                        <?= htmlspecialchars($categoryMap[$event['category_id']] ?? 'N/A') ?>
                        >
                        <?= htmlspecialchars($subcategoryMap[$event['subcategory_id']] ?? 'N/A') ?>
                      </p>
                      <p><?= date('F j, Y g:i A', strtotime($event['event_date'])) ?></p>
                      <p>📍 <?= htmlspecialchars($event['location']) ?></p>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                      <a href="eventsite.php?event=<?= urlencode($event['slug']) ?>" class="btn btn-sm btn-outline flex-1">🔗 View</a>
                      <a href="../ticket/register.php?event_id=<?= $event['id'] ?>" class="btn btn-outline btn-sm flex-1">🎟️ Register</a>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <?php if (strtotime($event['event_date']) < time()): ?>
                          <!-- this will be displayed after the event has been organized completely based on date_default_timezone_set('Asia/Dhaka');  -->
                          <a href="../feedback/feedback_submit.php?event_id=<?= $event['id'] ?>" class="btn btn-outline btn-sm flex-1">📝 Leave Feedback</a>
                        <?php endif; ?>
                        <a href="../feedback/feedback_view.php?event_id=<?= $event['id'] ?>" class="btn btn-secondary btn-sm flex-1">📊 View Feedback</a>
                      </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const expandedEvents = JSON.parse(localStorage.getItem('expandedEvents') || '[]');

    // Show previously expanded events
    expandedEvents.forEach(id => {
      const card = document.querySelector(`.card[data-event-id="${id}"]`);
      if (card) {
        const details = card.querySelector('.event-details');
        const button = card.querySelector('.toggle-btn');
        details.classList.remove('hidden');
        if (button) button.textContent = 'See Less';
      }
    });

    // Attach toggle logic
    document.querySelectorAll('.toggle-btn').forEach(button => {
      button.addEventListener('click', () => {
        const card = button.closest('.card');
        const details = card.querySelector('.event-details');
        const eventId = card.dataset.eventId;
        const expandedEvents = JSON.parse(localStorage.getItem('expandedEvents') || '[]');

        const index = expandedEvents.indexOf(eventId);
        if (details.classList.contains('hidden')) {
          details.classList.remove('hidden');
          button.textContent = 'See Less';
          if (index === -1) expandedEvents.push(eventId);
        } else {
          details.classList.add('hidden');
          button.textContent = 'See More';
          if (index !== -1) expandedEvents.splice(index, 1);
        }

        localStorage.setItem('expandedEvents', JSON.stringify(expandedEvents));
      });
    });
  });
</script>

</body>
</html>
