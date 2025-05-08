<?php
// user_profile.php - Fetch and display user profile data

include $_SERVER['DOCUMENT_ROOT'] . '/event/config/db.php'; // Database connection
include $_SERVER['DOCUMENT_ROOT'] . '/event/middleware/token_protect.php';

$userId = $_SESSION['user_id'] ?? null; // Get user_id from session

if (!$userId) {
    echo "You must be logged in to view the profile.";
    exit;
}

// Fetch the user's profile data from the database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$profilePicture = !empty($user['profile_picture']) ? $user['profile_picture'] : 'default-profile.jpg'; // Fallback profile picture

// Construct the image path
$profilePicturePath = "/event/" . $profilePicture;

// Check if the file exists and if not, use a default image
if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $profilePicturePath)) {
    $profilePicturePath = "/uploads/users/default-profile.jpg"; // Fallback image
}

if (!$user) {
    echo "User not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title> <?= htmlspecialchars($user['full_name']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.0.0/dist/full.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
</head>
<body class="">

<div class="flex h-screen">
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/event/sidebar.php'; ?> <!-- Optional sidebar -->

  <div class="flex-1 flex flex-col">
    <header class="shadow flex items-center w-full">
      <?php include $_SERVER['DOCUMENT_ROOT'] . '/event/topbar.php'; ?> <!-- Top bar including the profile button -->
    </header>

      <section class="flex justify-between items-center m-6 p-6 border border-gray-300 shadow-md rounded-lg">
        <section class="flex items-center gap-5">
          <div class="w-max">
            <?php if ($user['profile_picture']): ?>
                <img src="<?= $profilePicturePath ?>" class="w-40 h-40 object-cover border-4 border-blue-100 rounded-lg" alt="Profile Picture"/>
            <?php else: ?>
              <div class="w-40 h-40 bg-gray-300 flex items-center justify-center text-gray-600 rounded-full shadow-md">No Image</div>
            <?php endif; ?>
          </div>
          <div>
            <p class="text-4xl font-bold"><?= htmlspecialchars($user['full_name']) ?></p>
            <p class="text-xl text-gray-500 font-semibold"><?php   echo ($user['role'] == 0) ? 'Admin' : (($user['role'] == 1) ? ' Attendee' : 'Organizer'); ?></p>
            <div class="flex gap-3 mt-2">
              <p class="flex flex-col">
                <span><i class="fa-solid fa-envelope"></i></span>
                <span><i class="fa-solid fa-phone-volume"></i></span>
                <span><i class="fa-solid fa-location-dot"></i></span>
              </p>
              <p class="flex flex-col">
                <span> <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="text-blue-600 hover:underline"> <?= htmlspecialchars($user['email']) ?> </a> </span>
                <span> <a href="tel:<?= htmlspecialchars($user['phone']) ?>" class="text-green-600 hover:underline"> <?= htmlspecialchars($user['phone']) ?> </a> </span>
                <span> <a href="https://www.google.com/maps/search/<?= urlencode($user['address']) ?>" target="_blank" class="text-purple-600 hover:underline"> <?= nl2br(htmlspecialchars($user['address'])) ?> </a> </span>
            </p>
            </div>
          </div>
        </section>
        <div>
          <h2 class="text-2xl font-bold border-b mb-2">Preferences</h2>
          <p>Dietary Requirements: <strong><?= htmlspecialchars($user['dietary_requirements']) ?: 'None' ?></strong> </p>
          <p>Accessibility Needs: <strong><?= htmlspecialchars($user['accessibility_needs']) ?: 'None' ?></strong> </p>
        </div>   
      </section>

      <div>
        <!-- Email Verification Section -->
        <?php if (!$user['email_verified']): ?>
          <div class="mt-6 text-yellow-600 bg-yellow-100 p-4 rounded-xl">
            <p>Your email is not verified. <a href="/event/verify_email.php" class="text-blue-500">Click here to verify your email.</a></p>
          </div>
        <?php endif; ?>
      </div>
  </div>
</div>

</body>
</html>
