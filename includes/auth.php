<?php
/* Administrator authentication: login, logout, lockout, session guard */
function current_admin(): ?array
{
    static $admin = false;
    if ($admin === false) {
        if (empty($_SESSION['admin_id'])) {
            $admin = null;
        } else {
            $st = db()->prepare('SELECT id, username, full_name, email, last_login, created_at FROM admins WHERE id = ?');
            $st->execute([(int)$_SESSION['admin_id']]);
            $admin = $st->fetch() ?: null;
        }
    }
    return $admin;
}

function require_admin(): void
{
    if (empty($_SESSION['admin_id']) || !current_admin()) {
        unset($_SESSION['admin_id']);
        redirect('admin/login.php');
    }
    if (time() - (int)($_SESSION['last_activity'] ?? 0) > ADMIN_IDLE_TIMEOUT) {
        unset($_SESSION['admin_id'], $_SESSION['last_activity']);
        session_regenerate_id(true);
        flash_set('error', 'You were signed out after a period of inactivity. Please sign in again.');
        redirect('admin/login.php');
    }
    $_SESSION['last_activity'] = time();
}

function logout_admin(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $p['path'],
            'domain'   => $p['domain'],
            'secure'   => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => 'Lax',
        ]);
    }
    session_destroy();
}

function login_failures(string $ip): int
{
    $w = (int)LOGIN_LOCK_MINUTES;
    $st = db()->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > (NOW() - INTERVAL $w MINUTE)");
    $st->execute([$ip]);
    return (int)$st->fetchColumn();
}

/* Seconds the visitor must still wait, or 0 if not locked. */
function login_lock_seconds(string $ip, string $username): int
{
    $w = (int)LOGIN_LOCK_MINUTES;
    $sql = "SELECT COUNT(*) AS c,
                   TIMESTAMPDIFF(SECOND, NOW(), MIN(attempted_at) + INTERVAL $w MINUTE) AS wait
            FROM login_attempts
            WHERE %s = ? AND attempted_at > (NOW() - INTERVAL $w MINUTE)";

    $st = db()->prepare(sprintf($sql, 'ip'));
    $st->execute([$ip]);
    $r = $st->fetch();
    if ((int)$r['c'] >= LOGIN_MAX_ATTEMPTS) {
        return max(1, (int)$r['wait']);
    }

    // Also protect a single account from being attacked from many addresses
    $st = db()->prepare(sprintf($sql, 'username'));
    $st->execute([$username]);
    $r = $st->fetch();
    if ((int)$r['c'] >= LOGIN_MAX_ATTEMPTS * 2) {
        return max(1, (int)$r['wait']);
    }
    return 0;
}

/* Returns [bool success, string message] */
function attempt_login(string $username, string $password): array
{
    $pdo = db();
    $ip  = client_ip();
    $pdo->exec("DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 1 DAY)");

    $wait = login_lock_seconds($ip, $username);
    if ($wait > 0) {
        return [false, 'Too many failed login attempts. Please try again in ' . max(1, (int)ceil($wait / 60)) . ' minute(s).'];
    }

    $st = $pdo->prepare('SELECT * FROM admins WHERE username = ? LIMIT 1');
    $st->execute([$username]);
    $admin = $st->fetch();

    if ($admin) {
        $ok = password_verify($password, $admin['password_hash']);
    } else {
        password_verify($password, password_hash('dummy-value', PASSWORD_DEFAULT)); // keeps timing similar
        $ok = false;
    }

    if (!$ok) {
        $pdo->prepare('INSERT INTO login_attempts (ip, username, attempted_at) VALUES (?, ?, NOW())')
            ->execute([$ip, substr($username, 0, 50)]);
        $left = max(0, LOGIN_MAX_ATTEMPTS - login_failures($ip));
        if ($left === 0) {
            return [false, 'Too many failed login attempts. This sign-in form is locked for ' . LOGIN_LOCK_MINUTES . ' minutes.'];
        }
        return [false, 'Invalid username or password. ' . $left . ' attempt(s) remaining before a temporary lockout.'];
    }

    if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
        $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($password, PASSWORD_DEFAULT), $admin['id']]);
    }

    $pdo->prepare('DELETE FROM login_attempts WHERE ip = ? OR username = ?')->execute([$ip, $username]);
    $pdo->prepare('UPDATE admins SET last_login = NOW() WHERE id = ?')->execute([$admin['id']]);

    session_regenerate_id(true);   // prevents session fixation
    $_SESSION['admin_id']      = (int)$admin['id'];
    $_SESSION['last_activity'] = time();
    unset($_SESSION['csrf_token']);
    return [true, ''];
}

function password_error(string $pw): ?string
{
    if (strlen($pw) < 10) {
        return 'Password must be at least 10 characters long.';
    }
    if (!preg_match('/[A-Za-z]/', $pw) || !preg_match('/\d/', $pw)) {
        return 'Password must contain at least one letter and one number.';
    }
    return null;
}

