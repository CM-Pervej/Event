<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AmarEvent - Landing Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.0.0/dist/full.css" rel="stylesheet" />
</head>
<body class="bg-gray-100">

    <!-- Hero Section -->
    <section class="hero bg-gradient-to-r from-blue-500 to-teal-500 text-white py-24 text-center h-screen">
        <nav class="bg-primary p-4 fixed top-0 w-screen z-10">
            <div class="max-w-screen-xl mx-auto flex justify-between items-center">
                <a href="#" class="text-white text-2xl font-bold">AmarEvent</a>
                <div class="font-semibold text-lg">
                    <a href="#features" class="hover:border-b-2 hover:border-white">Features</a> /
                    <a href="#about" class="hover:border-b-2 hover:border-white">About</a> / 
                    <a href="#contact" class="hover:border-b-2 hover:border-white">Contact</a> /
                    <a href="#" onclick="openLoginModal()" class="hover:border-b-2 hover:border-white">Login</a>
                </div>
            </div>
        </nav>
        <div class="max-w-screen-xl mx-auto">
            <h1 class="text-5xl font-extrabold mb-4">Welcome to AmarEvent</h1>
            <h1 class="text-5xl font-semibold mb-4">Simplify Your Event Experience</h1>
            <p class="text-lg mb-8">Our Event Management System (EMS) offers powerful tools to create, manage, and track events seamlessly. Designed for attendees, organizers, and admins, EMS provides real-time analytics, smart scheduling, ticketing, and secure communication — all in one place.</p>
            <a href="#features" class="btn btn-accent text-white">Discover More</a>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-16 bg-white text-center">
        <div class="max-w-screen-xl mx-auto">
            <h2 class="text-3xl font-bold mb-8">Key Features</h2>
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
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-16 bg-gray-50 text-center">
        <div class="max-w-screen-xl mx-auto">
            <h2 class="text-3xl font-bold mb-8">About AmarEvent</h2>
            <p class="text-lg max-w-2xl mx-auto">AmarEvent is an all-in-one solution for creating, managing, and promoting events. Whether you're hosting a small meetup or a large conference, AmarEvent provides you with the tools you need to streamline event management, including ticketing, scheduling, and more.</p>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-16 bg-base-200">
        <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">How It Works</h2>
        <div class="steps steps-vertical lg:steps-horizontal justify-center">
            <div class="step step-primary">Register</div>
            <div class="step step-primary">Create Event</div>
            <div class="step step-primary">Manage Tickets & Speakers</div>
            <div class="step step-primary">Launch & Analyze</div>
        </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-16 bg-white text-center">
        <div class="max-w-screen-xl mx-auto">
            <h2 class="text-3xl font-bold mb-8">What Our Users Say</h2>
            <div class="space-y-8">
                <div class="testimonial-card card shadow-xl bg-base-100">
                    <div class="card-body">
                        <p class="italic">"AmarEvent made organizing our conference so much easier! The ticketing system was seamless, and the event creation was straightforward." </p>
                        <h5 class="font-semibold">John Doe</h5>
                        <p>Event Organizer</p>
                    </div>
                </div>
                <div class="testimonial-card card shadow-xl bg-base-100">
                    <div class="card-body">
                        <p class="italic">"The ability to manage events, track sales, and integrate payments all in one platform is a game changer for my business." </p>
                        <h5 class="font-semibold">Jane Smith</h5>
                        <p>Business Owner</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
      <?php include 'footer.php'; ?>
    </footer>

    <!-- Login Modal -->
    <input type="checkbox" id="login-modal" class="modal-toggle" />
    <div class="modal" role="dialog">
        <div class="modal-box w-11/12 max-w-2xl p-0 overflow-hidden">
            <div class="relative h-[600px]">
            <iframe src="public/login.php" class="w-full h-full border-0"></iframe>
            <label for="login-modal" class="btn btn-sm btn-circle absolute top-2 right-2 z-10 bg-white">✕</label>
            </div>
        </div>
        <label class="modal-backdrop" for="login-modal"></label>
    </div>
    <script>
        function openLoginModal() {
            document.getElementById('login-modal').checked = true;
        }
    </script>
</body>
</html>
