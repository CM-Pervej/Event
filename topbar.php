<?php
require_once 'middleware/token_protect.php';
require_once 'config/db.php'; // Ensure the database connection is available

$userId = $GLOBALS['AUTH_USER_ID']; // User ID stored in session

// Fetch user data from the database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Ensure the user data is available
if (!$user) {
    // Redirect to login page if user data is not found (optional)
    header('Location: login.php');
    exit;
}

$userName = htmlspecialchars($user['full_name']);
$profilePicture = !empty($user['profile_picture']) ? $user['profile_picture'] : 'default-profile.jpg'; // Fallback profile picture

// Construct the image path
$profilePicturePath = "/event/" . $profilePicture;

// Check if the file exists and if not, use a default image
if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $profilePicturePath)) {
    $profilePicturePath = "/uploads/users/default-profile.jpg"; // Fallback image
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.0.0/dist/full.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
</head>
<body>
  <!-- Top Bar -->
  <header class="bg-blue-50 w-full h-16 flex items-center">
    <div class="container mx-auto flex items-center justify-between h-full px-4">
      
      <!-- Search Bar with Clear Icon -->
      <div class="relative w-80 h-10">
        <input type="text" name="searchKeyword" id="searchKeyword" placeholder="Search Event"
               class="w-full h-full px-4 text-sm text-gray-700 bg-white border border-blue-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
               autocomplete="off">
        <div id="suggestionBox" class="suggestion-box hidden text-black"></div> 
      </div>

      <!-- User Profile Dropdown -->
      <div class="relative h-10">
        <button id="profileMenuButton" class="flex items-center space-x-2 h-full pl-4 rounded-full bg-white hover:bg-gray-100 border border-gray-200">
          <span class="text-gray-600 font-bold"><?= $userName ?></span>
          <div class="avatar online">
            <div class="w-10 h-10 rounded-full overflow-hidden">
              <img src="<?= $profilePicturePath ?>" class="object-cover w-full h-full"/>
            </div>
          </div>
        </button>

        <!-- Profile Menu -->
        <div id="profileMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-2 text-gray-800 z-20">
          <a href="/event/public/profile.php?user_id=<?= $user['id']?>" class="block px-4 py-2 hover:bg-gray-200">Profile</a>
          <?php if ($user['role'] != 1): ?>
                <a href="/event/public/events/event_list.php" class="block px-4 py-2 hover:bg-gray-200">My Events</a>
          <?php endif; ?>
          <a href="#" class="block px-4 py-2 hover:bg-gray-200">Settings</a>
          <a href="/event/public/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-200">Logout</a>
        </div>
      </div>
    </div>
  </header>

  <script>
// Profile dropdown toggle
document.getElementById('profileMenuButton').onclick = function(event) {
    event.stopPropagation(); // Prevent click event from bubbling up to the document
    const profileMenu = document.getElementById('profileMenu');
    profileMenu.classList.toggle('hidden');
};

// Close the profile menu if clicked outside
document.addEventListener('click', function(event) {
    const profileMenu = document.getElementById('profileMenu');
    const profileMenuButton = document.getElementById('profileMenuButton');

    // Check if the click is outside the profile menu or the button
    if (!profileMenu.contains(event.target) && !profileMenuButton.contains(event.target)) {
        profileMenu.classList.add('hidden');
    }
});

    </script>
</body>
</html>
