<?php
require_once '../../middleware/token_protect.php';
require_once '../../config/db.php';

$userId = $GLOBALS['AUTH_USER_ID'];
$eventId = $_GET['event_id'] ?? null;

if (!$eventId) {
    echo "Missing event ID.";
    exit;
}

// Check event exists and is over
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND event_date < NOW()");
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    echo "Event not found or hasn't occurred yet.";
    exit;
}

// Check if feedback already submitted
$stmt = $pdo->prepare("SELECT * FROM feedback WHERE user_id = ? AND event_id = ?");
$stmt->execute([$userId, $eventId]);
if ($stmt->rowCount() > 0) {
    echo "You already submitted feedback for this event.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Post-Event Feedback</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-lg mx-auto bg-white p-6 rounded-xl shadow">
    <h1 class="text-xl font-bold mb-4">Leave Feedback for <?= htmlspecialchars($event['name']) ?></h1>
    <form id="feedbackForm" action="../../services/feedback/add.php" method="POST" class="space-y-4">
      <input type="hidden" name="event_id" value="<?= $eventId ?>">

      <label class="block">
        <span class="label-text">Rating (1 to 5):</span>
        <input type="number" name="rating" min="1" max="5" required class="input input-bordered w-full" />
      </label>

      <label class="block">
        <span class="label-text">Comment (optional):</span>
        <textarea name="comment" rows="4" class="textarea textarea-bordered w-full"></textarea>
      </label>

      <button type="submit" class="btn btn-primary w-full">Submit Feedback</button>
    </form>

    <div id="successMessage" class="mt-4 text-green-600 hidden">
      <p>Your feedback has been submitted successfully!</p>
    </div>
  </div>

  <script>
    $(document).ready(function() {
        $('#feedbackForm').submit(function(event) {
            event.preventDefault(); // Prevent form from submitting normally

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    // If submission is successful, show success message
                    $('#successMessage').removeClass('hidden');
                    $('#feedbackForm').hide(); // Optionally hide the form after submission
                },
                error: function() {
                    alert("There was an error submitting your feedback.");
                }
            });
        });
    });
  </script>
</body>
</html>
