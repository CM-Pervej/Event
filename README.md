# 📅 Event Management System (PHP + MySQL + Tailwind CSS)

A modular, secure, and scalable event management system built with:

- ✅ **PHP (Vanilla)** for backend logic
- ✅ **MySQL** as the relational database
- ✅ **JWT** authentication (access + refresh tokens)
- ✅ **Tailwind CSS + DaisyUI** for UI design
- ✅ **Modular service structure** for easy scalability

---

## 📦 Features

### 🧑‍💼 User Authentication
- Registration with email verification
- JWT-based Login (access + refresh tokens)
- Per-device refresh token management
- Token blacklist on logout

### 🎟️ Event Management
- Event creation, session scheduling
- Speaker/panel integration
- Auto-generated event websites (planned)

### 🎫 Ticketing
- Tiered ticket types
- Secure payments (gateway-ready)
- Refunds and QR-based ticket validation

### 🔐 Security
- Role-based access support
- Token middleware for route protection
- Auto-generated JWT secret via `.env`

---

## 📁 Folder Structure

```
event-management-system/
├── public/                 # Pages accessible via browser (login, dashboard, etc.)
├── includes/               # Header, footer, shared functions
├── config/                 # Database & environment setup
├── middleware/             # Token protection middleware
├── services/               # Logic for auth, users, events, tickets, etc.
├── uploads/                # Uploaded images, files
├── database/               # SQL schema and sample data
├── vendor/                 # Composer dependencies
├── .env                    # App secrets and configs
└── README.md               # This file
```

---

## 🛠 Installation

1. **Clone the repository**
```bash
git clone https://github.com/yourname/event-management-system.git
```

2. **Install dependencies**
```bash
cd event-management-system
composer install
```

3. **Set up the environment**
```bash
cp .env.example .env
```
> The JWT secret will be auto-generated on first login.

4. **Import the database**
- Use phpMyAdmin or CLI to import `database/schema.sql`

5. **Run in local server (XAMPP)**
```
http://localhost/event-management-system/public/
```

---

## 🔐 .env Example
```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=event
JWT_SECRET= # Will auto-generate if empty
```

---

## 📋 SQL Tables
- `users`
- `user_tokens`
- `token_blacklist`
- `events`, `sessions`, `tickets`, `speakers` (planned)

---

## ✅ Auth Flow Summary

1. **Login** → Access + Refresh Token issued
2. **Refresh** → New access token via refresh token
3. **Logout** → Refresh token deleted, access token blacklisted
4. **Protected pages** → Verified via middleware

---

## 📌 Credits & Dependencies
- [firebase/php-jwt](https://github.com/firebase/php-jwt) – Token handling
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) – Email support
- [Tailwind CSS](https://tailwindcss.com/) – Styling
- [DaisyUI](https://daisyui.com/) – UI components

---

## 🚀 What's Next
- Admin panel
- Real-time forum & notifications
- Public-facing event websites
- Payment integration (Stripe, SSLCommerz, etc.)

---

## 💡 License
MIT – feel free to modify and use!

#
