<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EventEase | Event Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@3.1.0/dist/full.css" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-base-100 text-base-content">

  <!-- Navbar -->
  <div class="navbar bg-primary text-primary-content px-6">
    <div class="flex-1">
      <a class="text-2xl font-bold tracking-wide" href="#">EventEase</a>
    </div>
    <div class="flex-none space-x-4">
      <a class="btn btn-ghost" href="/event/public/login.php">Login</a>
      <a class="btn btn-accent text-white" href="/event/public/register.php">Register</a>
    </div>
  </div>

  <!-- Hero Section -->
  <section class="hero min-h-screen bg-base-200">
    <div class="hero-content flex-col lg:flex-row-reverse">
      <img src="https://source.unsplash.com/600x400/?event,conference" class="max-w-lg rounded-lg shadow-2xl" />
      <div>
        <h1 class="text-5xl font-bold">Simplify Your Event Experience</h1>
        <p class="py-6 text-lg">EventEase is your all-in-one platform to manage, organize, and analyze events. From ticketing to speaker management, we’ve got you covered.</p>
        <div class="space-x-4">
          <a href="/event/public/register.php" class="btn btn-primary">Get Started</a>
          <a href="/event/public/login.php" class="btn btn-outline">Login</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section class="py-16 px-6 bg-white text-neutral">
    <div class="text-center mb-12">
      <h2 class="text-4xl font-bold">Key Features</h2>
      <p class="text-lg mt-2">A comprehensive suite to manage every aspect of your event</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 max-w-6xl mx-auto">
      <div class="card bg-base-100 shadow-md p-6">
        <h3 class="text-xl font-semibold mb-2">Event Creation</h3>
        <p>Create and customize events with flexible scheduling, themes, and location settings.</p>
      </div>
      <div class="card bg-base-100 shadow-md p-6">
        <h3 class="text-xl font-semibold mb-2">Ticketing System</h3>
        <p>Offer multiple ticket types, manage availability, and handle online payments securely.</p>
      </div>
      <div class="card bg-base-100 shadow-md p-6">
        <h3 class="text-xl font-semibold mb-2">Speaker Management</h3>
        <p>Add and schedule guest speakers with detailed bios, slots, and session types.</p>
      </div>
      <div class="card bg-base-100 shadow-md p-6">
        <h3 class="text-xl font-semibold mb-2">User Registration</h3>
        <p>Enable users to register, update profiles, and get personalized schedules.</p>
      </div>
      <div class="card bg-base-100 shadow-md p-6">
        <h3 class="text-xl font-semibold mb-2">Notifications & Reminders</h3>
        <p>Send real-time updates via email, SMS, or push notifications for critical event changes.</p>
      </div>
      <div class="card bg-base-100 shadow-md p-6">
        <h3 class="text-xl font-semibold mb-2">Analytics Dashboard</h3>
        <p>Track registrations, engagement, and revenue with visual reports and insights.</p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer p-10 bg-neutral text-neutral-content mt-20">
    <div>
      <p class="font-bold text-lg">EventEase</p>
      <p>Empowering your events. One platform, endless possibilities.</p>
    </div>
    <div>
      <span class="footer-title">Quick Links</span>
      <a href="login.html" class="link link-hover">Login</a>
      <a href="register.html" class="link link-hover">Register</a>
      <a href="#" class="link link-hover">About</a>
    </div>
    <div>
      <span class="footer-title">Contact</span>
      <p>Email: support@eventease.com</p>
      <p>Phone: +123-456-7890</p>
    </div>
  </footer>

</body>
</html>
