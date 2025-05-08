<?php
  require_once 'middleware/token_protect.php';
  $userId = $GLOBALS['AUTH_USER_ID'];

  // Get the current filename without query strings
  $currentPage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.0.0/dist/full.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
  <style>
    .sidebar-active-link {
      background-color: white;
      color: black;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    .sidebar-container {
      width: 16rem;
      padding: 0 1.5rem 1.5rem 1.5rem;
    }
    .sidebar-heading {
      font-weight: bold;
      color: #4a5568;
    }
    .sidebar-link {
      display: flex;
      align-items: center;
      padding: 0.5rem 1rem;
      color: #1a202c;
      font-weight: 600;
      border-radius: 0.375rem;
      transition: background-color 0.3s ease;
      font-size: 1rem;
    }
    .sidebar-link i,
    .sidebar-link span {
      font-size: inherit;
      margin-right: 0.5rem;
    }
    .sidebar-link:hover {
      background-color: white;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>
<body class="bg-gray-100">
  <!-- Sidebar -->
  <aside class="sidebar-container bg-blue-50 shadow-md hidden md:flex flex-col h-screen">
    <h2 class="text-2xl font-bold text-primary tracking-widest whitespace-nowrap py-4 -ml-2">E v e n t Z o n e</h2>
    <ul class="sidebar-heading menu p-0 flex flex-col gap-1">
      <li>
        <a href="/event/public/index.php" 
           class="sidebar-link <?php echo ($currentPage == 'index.php') ? 'sidebar-active-link' : ''; ?>">
          <i class="fa-solid fa-gauge"></i> <span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="/event/public/events/list.php" 
           class="sidebar-link <?php echo ($currentPage == 'list.php') ? 'sidebar-active-link' : ''; ?>">
          <i class="fa-solid fa-calendar-plus"></i> <span>All Events</span>
        </a>
      </li>
      <li>
        <a href="/event/public/ticket/add.php" 
           class="sidebar-link <?php echo ($currentPage == 'tickets.php') ? 'sidebar-active-link' : ''; ?>">
          <i class="fa-solid fa-ticket"></i> <span>Tickets</span>
        </a>
      </li>
      <li>
        <a href="/event/public/attendees.php" 
           class="sidebar-link <?php echo ($currentPage == 'attendees.php') ? 'sidebar-active-link' : ''; ?>">
          <i class="fa-solid fa-users"></i> <span>Attendees</span>
        </a>
      </li>
      <li>
        <a href="/event/public/analytics.php" 
           class="sidebar-link <?php echo ($currentPage == 'analytics.php') ? 'sidebar-active-link' : ''; ?>">
          <i class="fa-solid fa-chart-bar"></i> <span>Analytics</span>
        </a>
      </li>
      <li>
        <a href="/event/public/settings.php" 
           class="sidebar-link <?php echo ($currentPage == 'settings.php') ? 'sidebar-active-link' : ''; ?>">
          <i class="fa-solid fa-gear"></i> <span>Settings</span>
        </a>
      </li>
      <li>
        <a href="/event/public/logout.php" class="sidebar-link text-error">
          <i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span>
        </a>
      </li>
    </ul>
  </aside>
</body>
</html>
