
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard – AmarEvent</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.0.0/dist/full.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
</head>
<body class="bg-base-200">

  <!-- Layout -->
  <div class="flex h-screen">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/event/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">

      <!-- Topbar -->
      <header class="bg-blue-50 shadow flex items-center w-full">
        <h1 class="text-xl font-bold whitespace-nowrap">Dashboard</h1>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/event/topbar.php'; ?>
      </header>

      <!-- Content -->
      <main class="flex-1 p-6 overflow-y-auto">
        <p class="mb-4">You are logged in as user ID: <strong><?= htmlspecialchars($userId) ?></strong></p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="stats shadow">
            <div class="stat">
              <div class="stat-title">Upcoming Events</div>
              <div class="stat-value text-primary">3</div>
            </div>
          </div>
          <div class="stats shadow">
            <div class="stat">
              <div class="stat-title">Tickets Sold</div>
              <div class="stat-value text-secondary">148</div>
            </div>
          </div>
          <div class="stats shadow">
            <div class="stat">
              <div class="stat-title">Total Revenue</div>
              <div class="stat-value text-accent">$3,200</div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="card bg-white shadow">
            <div class="card-body">
              <h2 class="card-title">Recent Registrations</h2>
              <p>Latest attendees who signed up for your events.</p>
              <div class="overflow-x-auto mt-4">
                <table class="table table-zebra w-full">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Event</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr><td>John Doe</td><td>Tech Conf 2025</td><td>Mar 20</td></tr>
                    <tr><td>Jane Smith</td><td>Marketing Meetup</td><td>Mar 22</td></tr>
                    <tr><td>Ali Khan</td><td>Startup Fest</td><td>Mar 24</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="card bg-white shadow">
            <div class="card-body">
              <h2 class="card-title">Event Insights</h2>
              <p>Summary of engagement from recent events.</p>
              <ul class="mt-4 space-y-2 text-sm">
                <li>🎟️ <strong>Tech Conf 2025:</strong> 82 tickets sold</li>
                <li>📈 <strong>Engagement Rate:</strong> 74%</li>
                <li>🗓️ <strong>Upcoming:</strong> Workshop on April 3</li>
              </ul>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

</body>
</html>
