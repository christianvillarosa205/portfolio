# Dynamic Portfolio (PHP + MySQL)

## Install
1. Copy this folder to your server (XAMPP: `htdocs/portfolio`).
2. Copy your existing `css/style.css`, `js/script.js` and your `images/` files into the matching folders.
3. In phpMyAdmin create an empty database named `portfolio_db` (utf8mb4).
4. Edit `config/config.php` if your MySQL user/password differ.
5. Open `http://localhost/portfolio/setup.php`, create the admin account.
6. **Delete `setup.php`.**
7. Sign in at `/admin/login.php`.

Requirements: PHP 7.4+ (8.x recommended), MySQL/MariaDB, PDO MySQL, fileinfo. GD is optional (re-encodes uploads).

## Structure
- `config/`      database + security settings
- `database/`    schema.sql
- `includes/`    bootstrap, helpers, public header/footer
- `admin/`       login, dashboard, CRUD pages, messages
- `uploads/`     admin-uploaded images (script execution blocked by .htaccess)
- `css/ js/ images/`  public assets
