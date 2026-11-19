<?php
/* Security layer: response headers, secure sessions, CSRF protection, safe uploads */

function send_security_headers(): void
{
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

function start_secure_session(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('PORTFOLIOSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* =====================================================
   CSRF protection
   ===================================================== */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify($token): bool
{
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/* =====================================================
   Secure image uploads
   ===================================================== */
/* Returns [filename|null, error|null]. [null, null] means "no file chosen". */
function upload_image(?array $file): array
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            return [null, 'The file is too large.'];
        }
        return [null, 'The upload failed (code ' . (int)$file['error'] . ').'];
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        return [null, 'The file is too large. Maximum size is ' . round(MAX_UPLOAD_BYTES / 1048576, 1) . ' MB.'];
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return [null, 'Invalid upload.'];
    }

    // Check the real content, not the browser-supplied type or filename
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        return [null, 'Only JPG, PNG, GIF and WebP images are allowed.'];
    }
    $info = @getimagesize($file['tmp_name']);
    if (!$info || $info['mime'] !== $mime || $info[0] < 1 || $info[1] < 1) {
        return [null, 'The file is not a valid image.'];
    }
    if ($info[0] > 6000 || $info[1] > 6000) {
        return [null, 'Image dimensions are too large (maximum 6000 x 6000 pixels).'];
    }

    if (!is_dir(UPLOAD_PATH) && !mkdir(UPLOAD_PATH, 0755, true)) {
        return [null, 'The uploads folder could not be created.'];
    }

    $ext  = $allowed[$mime];                                  // extension comes from content, never from the user
    $name = bin2hex(random_bytes(16)) . '.' . $ext;           // unique, unguessable
    $dest = UPLOAD_PATH . $name;
    $ok   = false;

    // Re-encode with GD when available: strips metadata and any hidden payload
    if (extension_loaded('gd') && $ext !== 'gif') {
        $im = null;
        if ($ext === 'jpg') {
            $im = @imagecreatefromjpeg($file['tmp_name']);
        } elseif ($ext === 'png') {
            $im = @imagecreatefrompng($file['tmp_name']);
        } elseif ($ext === 'webp' && function_exists('imagecreatefromwebp')) {
            $im = @imagecreatefromwebp($file['tmp_name']);
        }
        if ($im) {
            if ($ext === 'png') {
                imagealphablending($im, false);
                imagesavealpha($im, true);
                $ok = imagepng($im, $dest, 6);
            } elseif ($ext === 'jpg') {
                $ok = imagejpeg($im, $dest, 88);
            } elseif (function_exists('imagewebp')) {
                $ok = imagewebp($im, $dest, 85);
            }
            imagedestroy($im);
        }
    }
    if (!$ok) {
        $ok = move_uploaded_file($file['tmp_name'], $dest);
    }
    if (!$ok || !file_exists($dest)) {
        return [null, 'The file could not be saved.'];
    }
    @chmod($dest, 0644);
    return [$name, null];
}

function delete_upload(?string $file): void
{
    // Only files we generated ourselves can be removed
    if ($file && preg_match('/^[a-f0-9]{32}\.(jpg|png|gif|webp)$/', $file)) {
        $path = UPLOAD_PATH . $file;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

