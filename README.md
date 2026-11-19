# Dynamic Personal Portfolio Website with Admin Dashboard

A personal portfolio website built with PHP and MySQL. All public content (profile, skills, projects, education, experience, achievements, contact details, and site settings) is stored in a database and managed through a secure administrator dashboard.

## Description

This project upgrades a static HTML/CSS/JavaScript portfolio into a dynamic, database-driven system. Visitors see a responsive portfolio with light/dark mode and a working contact form. The site owner signs in to an admin dashboard to edit every part of the portfolio, upload images, and read messages. Changes appear on the public site immediately.

## Features

### Public website
- Home page with personal introduction, profile picture, and featured projects
- About Me, Skills (grouped by category), Projects (with category filters), and Resume pages
- Resume page with Education, Work Experience, and Achievements/Certificates
- Contact page showing contact details and social links
- Responsive layout for desktop, tablet, and mobile, with dark/light mode

### Administrator dashboard
- Dashboard with statistics: total projects, skills, messages (with unread count), experiences, achievements, and education records
- Full CRUD for Skills, Projects, Education, Experience, and Achievements
- Personal Information editor (name, introduction, biography, contact details, social links, profile picture)
- Website Settings (title, tagline, search description, footer text, logo, accent colour)
- Message management: view, mark as read/unread, mark all as read, delete
- Administrator profile and password change

### Security
- Passwords hashed with `password_hash()` and checked with `password_verify()`; never stored as plain text
- PHP sessions with a secure cookie (HttpOnly, SameSite), session ID regenerated after login, and 30-minute idle timeout
- Every admin page requires an authenticated administrator
- Login lockout after 5 failed attempts within 15 minutes, with clear error messages
- CSRF tokens on all forms; destructive actions use POST only
- Prepared statements (PDO) for all database queries; all output is HTML-escaped
- Contact form protection: server-side validation, CSRF token, hidden honeypot field, minimum fill time, and a 3-messages-per-10-minutes limit per IP
- Secure uploads: image types only (JPG, PNG, GIF, WebP), real content checked with `finfo` and `getimagesize()`, 2 MB limit, random filenames, GD re-encoding when available, and script execution blocked in `uploads/`
- Sensitive folders (`config/`, `includes/`, `database/`) blocked from direct web access

## Requirements

- PHP 7.4 or newer (PHP 8.x recommended)
- PHP extensions: `pdo_mysql`, `fileinfo`, and `mbstring` (`gd` is optional but recommended)
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `.htaccess` support (XAMPP, Laragon, or WAMP work well)
  - For nginx, add equivalent rules to deny `config/`, `includes/`, `database/` and to block PHP execution in `uploads/`

## Project structure

```
portfolio/
├── README.md
├── setup.php               one-time installer (delete after use)
├── index.php  about.php  skills.php  projects.php  resume.php  contact.php
├── config/
│   └── config.php          database and security settings
├── database/
│   └── schema.sql          table definitions
├── includes/
│   ├── bootstrap.php       loads everything and starts the session
│   ├── db.php              PDO database connection
│   ├── auth.php            login, logout, lockout, session guard
│   ├── security.php        headers, sessions, CSRF, file uploads
│   ├── functions.php       helpers, settings, form fields, partials
│   ├── header.php          public header
│   └── footer.php          public footer
├── admin/
│   ├── login.php  logout.php  index.php (dashboard)
│   ├── personal.php  skills.php  projects.php  education.php
│   ├── experience.php  achievements.php  messages.php
│   ├── settings.php  account.php
│   ├── includes/           init, layout, generic CRUD engine
│   └── assets/admin.css
├── uploads/                admin-uploaded images (script execution blocked)
├── css/   style.css, extra.css
├── js/    script.js
└── images/                 default images
```

## Database setup

The system uses 10 tables: `admins`, `login_attempts`, `personal_info`, `settings`, `skills`, `projects`, `education`, `experience`, `achievements`, `messages`.

1. Start Apache and MySQL (XAMPP Control Panel or Laragon).
2. Open phpMyAdmin (`http://localhost/phpmyadmin`).
3. Create an empty database named `portfolio_db` with collation `utf8mb4_unicode_ci`, or run:
   ```sql
   CREATE DATABASE portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
4. (Recommended) Create a dedicated database user instead of using `root`:
   ```sql
   CREATE USER 'portfolio_user'@'localhost' IDENTIFIED BY 'ChangeThisStrongPassword1';
   GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX ON portfolio_db.* TO 'portfolio_user'@'localhost';
   FLUSH PRIVILEGES;
   ```
5. Open `config/config.php` and set your credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'portfolio_db');
   define('DB_USER', 'root');            // or portfolio_user
   define('DB_PASS', '');                // your password
   ```
6. The tables are created automatically by `setup.php` (see below). You may instead import `database/schema.sql` manually in phpMyAdmin.

## Installation

1. Copy the `portfolio` folder to your web server directory (for XAMPP: `C:\xampp\htdocs\portfolio`).
2. Copy your existing `css/style.css`, `js/script.js`, and image files (`profile.jpg`, project screenshots) into the matching folders.
3. Complete the database setup above.
4. Make sure the `uploads/` folder is writable by the web server (on Linux: `chmod 755 uploads`, and set the correct owner).
5. Open `http://localhost/portfolio/` in your browser. If you see a "Database connection failed" message, re-check `config/config.php`.

## Administrator setup procedure

1. Open `http://localhost/portfolio/setup.php`.
2. The installer creates all tables and imports the portfolio content from the original static pages (name, skills, projects, education, social links).
3. Fill in the administrator form: username, full name, email, and password (minimum 10 characters, with at least one letter and one number).
4. Click **Create administrator**. The password is stored as a hash.
5. **Delete `setup.php` from the server immediately.** It locks itself once an administrator exists, but it should not remain on a live site.
6. Sign in at `http://localhost/portfolio/admin/login.php`.
7. Go to **Personal Info** to edit your details and upload a profile picture, then add content under Skills, Projects, Education, Experience, and Achievements.
8. Open **My Account** at any time to change your name, email, or password.

## Using the dashboard

| Section | What you can do |
|---|---|
| Dashboard | View statistics, recent messages, and admin profile |
| Personal Info | Edit name, introduction, biography, contact details, social links, profile picture |
| Skills | Add, edit, delete, and categorize skills |
| Projects | Add, edit, delete projects and upload screenshots |
| Education / Experience / Achievements | Add, edit, delete records (achievements support certificate images) |
| Messages | Read, mark read/unread, delete contact form submissions |
| Website Settings | Change site title, tagline, logo, footer text, accent colour |
| My Account | Update profile and change password |

## Troubleshooting

| Problem | Solution |
|---|---|
| "Database connection failed" | Start MySQL and check `DB_NAME`, `DB_USER`, `DB_PASS` in `config/config.php` |
| `could not find driver` | Enable `extension=pdo_mysql` in `php.ini` and restart Apache |
| Image upload fails | Check that `uploads/` is writable and the file is a JPG/PNG/GIF/WebP under 2 MB |
| Locked out of login | Wait 15 minutes, or run `DELETE FROM login_attempts;` in phpMyAdmin |
| Forgot admin password | Generate a hash with `php -r "echo password_hash('NewPassword123', PASSWORD_DEFAULT);"` and update the `password_hash` column in the `admins` table |
| Styles missing | Make sure `css/style.css` and `js/script.js` were copied into the project |

## Security checklist before going live

- Delete `setup.php`
- Use a dedicated database user with a strong password
- Set `DEBUG` to `false` in `config/config.php`
- Serve the site over HTTPS
- Keep PHP and MySQL updated

## Author

Christian Villarosa
