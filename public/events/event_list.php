<?php
include $_SERVER['DOCUMENT_ROOT'] . '/event/middleware/token_protect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/event/config/db.php';

$userId = $GLOBALS['AUTH_USER_ID'];

// Fetch events
$stmt = $pdo->prepare("SELECT * FROM events WHERE user_id = ? ORDER BY event_date DESC");
$stmt->execute([$userId]);
$events = $stmt->fetchAll();

// Fetch categories and subcategories as maps (id => name)
$categoryMap = [];
foreach ($pdo->query("SELECT id, name FROM categories") as $cat) {
  $categoryMap[$cat['id']] = $cat['name'];
}

$subcategoryMap = [];
foreach ($pdo->query("SELECT id, name FROM subcategories") as $sub) {
  $subcategoryMap[$sub['id']] = $sub['name'];
}

// Time zone 
date_default_timezone_set('Asia/Dhaka');
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <title>My Events – AmarEvent</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.0.0/dist/full.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
</head>
<body class="bg-base-200">

<div class="flex h-screen">
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/event/sidebar.php'; ?>

  <div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-blue-50 shadow flex items-center w-ful">
      <?php include $_SERVER['DOCUMENT_ROOT'] . '/event/topbar.php'; ?>
    </header>

    <main class="flex-1 px-6 overflow-y-auto">
      <div class="mt-8 flex justify-between items-center px-6">
        <h1 class="text-2xl font-bold">My Events</h1>
        <a href="create.php" class="btn btn-primary">➕ Create Another Event</a>
      </div>

      <div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow mt-5">
        <?php if (count($events) === 0): ?>
          <p class="text-center text-gray-500">You haven't created any events yet.</p>
          <div class="mt-6 text-center">
            <a href="create.php" class="btn btn-primary">➕ Create Your First Event</a>
          </div>
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
                      <a href="edit.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-info flex-1">✏️ Edit</a>
                      <a href="clone.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-warning flex-1">🧬 Clone</a>
                      <form action="delete.php" method="POST" class="flex-1" onsubmit="return confirm('Are you sure you want to delete this event?');">
                        <input type="hidden" name="id" value="<?= $event['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-error w-full">🗑️ Delete</button>
                      </form>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                      <a href="../speaker/add.php" class="btn btn-outline btn-sm flex-1">🎤 Add Speaker</a>
                      <a href="../session/add.php?event_id=<?= $event['id'] ?>" class="btn btn-outline btn-sm flex-1">🗓️ Add Session</a>
                      <a href="ticket_purchase.php?event_id=<?= $event['id'] ?>" class="btn btn-outline btn-sm flex-1">🎟️ Buy Tickets</a>
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
