# TicketBox (CT428 Project)

## 📋 Overview
TicketBox is a PHP/MySQL web application for managing event ticket sales. The repository contains the full source code, a sample database schema, and a few demo assets.

## 📦 Prerequisites
- **PHP 8.0+** (with PDO extension enabled)
- **MySQL 5.7+** (or MariaDB)
- **Apache** (XAMPP setup works out of the box)
- Composer is optional – the project does not depend on external PHP packages.

## 🗄️ Database setup
1. Create a new MySQL database, e.g.:
   ```sql
   CREATE DATABASE ticketbox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Import the schema (which already contains sample data for all core tables, including `loaive`):
   ```bash
   mysql -u <user> -p ticketbox < ticketbox/database/schema.sql
   ```
   *The import will populate tables such as `nguoidung`, `sukien`, `loaive`, `ve`, …*.
3. Verify that the `loaive` table has sample ticket‑type rows:
   ```sql
   SELECT COUNT(*) FROM loaive;
   ```
   You should see a non‑zero count (the schema ships >30 rows).

## 👤 Test account
A test user is already inserted by the schema:
- **Email:** `a@example.com`
- **Password:** `Password123`
- **Role:** `Customer`
You can log in at `/ticketbox/pages/home.php` after the server is running.

## 🚀 Running the application locally
1. Place the project under your web root (e.g. `htdocs`).
2. Ensure the document root points to `TicketBox-CT428/ticketbox`.
3. Start Apache/XAMPP and navigate to:
   ```
   http://localhost/TicketBox-CT428/ticketbox/
   ```
   The index page will redirect to `pages/home.php`.

## 🧹 Cleanup actions performed
- Removed draft files `test-db.php` and `test.html`.
- deduplicated the `assets/uploads/events/` folder, keeping a single banner and poster image.
- Confirmed that `loaive` table definition and sample INSERT statements exist in `database/schema.sql`.

## 📂 Project structure (relevant parts)
```
TicketBox-CT428/
├─ ticketbox/
│  ├─ api/            # REST API endpoints (all use PDO prepared statements)
│  ├─ app/
│  │   └─ controllers/   # Empty placeholders – not required for the current flow
│  ├─ assets/
│  │   └─ uploads/events/   # Sample images (duplicates removed)
│  ├─ pages/          # Front‑end pages (home, 403, 404, …)
│  ├─ database/
│  │   └─ schema.sql  # Contains full table definitions and sample data
│  └─ index.php       # Redirects to `pages/home.php`
└─ README.md          # **You are reading it!**
```

---
Feel free to modify the schema or add more seed data as needed. If you need further assistance (e.g., adding new API endpoints or customizing the UI), just let me know!
