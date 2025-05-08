<?php
require_once '../config/db.php';
require '../vendor/autoload.php'; // Include PHPMailer via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['full_name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address = htmlspecialchars(trim($_POST['address']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = (int)$_POST['role'];
    $dietary = htmlspecialchars(trim($_POST['dietary']));
    $accessibility = htmlspecialchars(trim($_POST['accessibility']));
    $verification_token = bin2hex(random_bytes(16));
    $email_verified = false;
    $profile_picture_path = null;

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "All required fields must be filled out.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/users/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filename = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
        $targetPath = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath)) {
            $profile_picture_path = 'uploads/users/' . $filename;
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $errors[] = "Email already registered.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (
                    full_name, email, phone, address, password, role,
                    dietary_requirements, accessibility_needs, profile_picture,
                    email_verified, verification_token
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->execute([
                    $name, $email, $phone, $address, $hashed_password, $role,
                    $dietary, $accessibility, $profile_picture_path,
                    $email_verified, $verification_token
                ]);

                // Send verification email
                $mail = new PHPMailer(true);
                $verify_link = "http://localhost/event/public/verify.php?token=$verification_token";

                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'paaru.special@gmail.com';
                $mail->Password   = 'dpqi gjwn pnin cvbd';
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('noreply@yourdomain.com', 'Event Management System');
                $mail->addAddress($email, $name);

                $mail->isHTML(true);
                $mail->Subject = 'Verify your email address';
                $mail->Body    = "
                    <h3>Hello $name,</h3>
                    <p>Please verify your email by clicking the link below:</p>
                    <a href='$verify_link'>$verify_link</a>
                    <p>If you didn't register, you can ignore this email.</p>
                ";

                $mail->send();
                $success = "Registration successful! Please check your email to verify your account.";
            }
        } catch (Exception $e) {
            $errors[] = "Mailer Error: " . $mail->ErrorInfo;
        } catch (PDOException $e) {
            $errors[] = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.0.0/dist/full.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-lg p-6 bg-white rounded-xl shadow-lg">
        <h2 class="text-2xl font-bold mb-4 text-center">Create an Account</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error mb-4">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php elseif ($success): ?>
            <div class="alert alert-success mb-4">
                <p><?= $success ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" enctype="multipart/form-data" class="space-y-4" autocomplete="off">
            <input type="text" name="full_name" placeholder="Full Name" class="input input-bordered w-full" required>
            <input type="email" name="email" placeholder="Email" class="input input-bordered w-full" required>
            <input type="text" name="phone" placeholder="Phone" class="input input-bordered w-full">
            <input type="text" name="address" placeholder="Address" class="input input-bordered w-full">
            <select name="role" class="select select-bordered w-full" required>
                <option value="1">Attendee</option>
                <option value="2">Organizer</option>
            </select>
            <textarea name="dietary" placeholder="Dietary Requirements" class="textarea textarea-bordered w-full"></textarea>
            <textarea name="accessibility" placeholder="Accessibility Needs" class="textarea textarea-bordered w-full"></textarea>
            <input type="file" name="profile_picture" class="file-input file-input-bordered w-full">
            <input type="password" name="password" placeholder="Password" class="input input-bordered w-full" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" class="input input-bordered w-full" required>
            <button type="submit" class="btn btn-primary w-full">Register</button>
        </form>

        <p class="mt-4 text-center">Already have an account? <a href="login.php" class="link link-primary">Login here</a>.</p>
    </div>
</body>
</html>
