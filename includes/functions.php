<?php
/* =====================================================
   General helpers
   ===================================================== */
function e($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function client_ip(): string
{
    // Only REMOTE_ADDR is trustworthy; forwarded headers can be forged.
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

/* Image URL. Accepts an uploaded filename or a legacy path such as "images/profile.jpg". */
function img_url(?string $file, string $fallback = ''): string
{
    if (!$file) {
        return $fallback !== '' ? url($fallback) : '';
    }
    if (strpos($file, '/') !== false) {
        return url($file);
    }
    return url('uploads/' . rawurlencode($file));
}

/* =====================================================
   Settings & personal info (read from the database)
   ===================================================== */
function settings(bool $refresh = false): array
{
    static $cache = null;
    if ($cache === null || $refresh) {
        $cache = [];
        try {
            foreach (db()->query('SELECT setting_key, setting_value FROM settings') as $r) {
                $cache[$r['setting_key']] = $r['setting_value'];
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $s = settings();
    return (isset($s[$key]) && $s[$key] !== '') ? (string)$s[$key] : $default;
}

function save_setting(string $key, ?string $value): void
{
    db()->prepare(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    )->execute([$key, $value]);
    settings(true);
}

function personal(): array
{
    static $row = null;
    if ($row === null) {
        try {
            $row = db()->query('SELECT * FROM personal_info WHERE id = 1')->fetch() ?: [];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $row = [];
        }
    }
    return $row;
}

function pinfo(string $key, string $default = ''): string
{
    $p = personal();
    return (isset($p[$key]) && $p[$key] !== '') ? (string)$p[$key] : $default;
}

function unread_count(): int
{
    static $n = null;
    if ($n === null) {
        $n = (int)db()->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
    }
    return $n;
}

/* =====================================================
   Flash messages
   ===================================================== */
function flash_set(string $type, string $msg): void
{
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function flash_render(string $cls = 'alert'): string
{
    $out = '';
    foreach ($_SESSION['flash'] ?? [] as $f) {
        $type = $f['type'] === 'success' ? 'success' : 'error';
        $out .= '<div class="' . $cls . ' ' . $cls . '-' . $type . '" role="alert">' . e($f['msg']) . '</div>';
    }
    unset($_SESSION['flash']);
    return $out;
}

/* =====================================================
   Field engine (validation + rendering) shared by admin forms
   ===================================================== */
function fields_validate(array $fields, array $post, array &$errors): array
{
    $vals = [];
    foreach ($fields as $k => $f) {
        $type  = $f['type'] ?? 'text';
        if ($type === 'image') {
            continue;
        }
        $label = $f['label'] ?? $k;
        $raw   = trim((string)($post[$k] ?? ''));

        if ($raw === '') {
            if (!empty($f['required'])) {
                $errors[$k] = $label . ' is required.';
            }
            $vals[$k] = in_array($type, ['date', 'url'], true) ? null : ($type === 'number' ? 0 : '');
            continue;
        }

        $max = $f['max'] ?? ($type === 'textarea' ? 5000 : 255);
        if (mb_strlen($raw) > $max) {
            $errors[$k] = $label . ' must be ' . $max . ' characters or fewer.';
            continue;
        }

        switch ($type) {
            case 'number':
                if (!preg_match('/^-?\d{1,9}$/', $raw)) {
                    $errors[$k] = $label . ' must be a whole number.';
                } else {
                    $raw = (int)$raw;
                }
                break;
            case 'email':
                if (!filter_var($raw, FILTER_VALIDATE_EMAIL)) {
                    $errors[$k] = $label . ' must be a valid email address.';
                }
                break;
            case 'url':
                $scheme = strtolower((string)parse_url($raw, PHP_URL_SCHEME));
                if (!filter_var($raw, FILTER_VALIDATE_URL) || !in_array($scheme, ['http', 'https'], true)) {
                    $errors[$k] = $label . ' must be a valid http:// or https:// link.';
                }
                break;
            case 'date':
                $d = DateTime::createFromFormat('Y-m-d', $raw);
                if (!$d || $d->format('Y-m-d') !== $raw) {
                    $errors[$k] = $label . ' must be a valid date.';
                }
                break;
            case 'select':
                if (!isset($f['options'][$raw])) {
                    $errors[$k] = 'Please choose a valid ' . strtolower($label) . '.';
                }
                break;
            case 'color':
                if (!preg_match('/^#[0-9a-fA-F]{6}$/', $raw)) {
                    $errors[$k] = $label . ' must be a valid colour.';
                }
                break;
        }
        $vals[$k] = $raw;
    }
    return $vals;
}

/* Handles image fields. Returns ['values'=>[], 'new'=>[files created], 'delete'=>[files to delete after save]] */
function fields_images(array $fields, ?array $existing, array $post, array $files, array &$errors): array
{
    $out = ['values' => [], 'new' => [], 'delete' => []];
    foreach ($fields as $k => $f) {
        if (($f['type'] ?? '') !== 'image') {
            continue;
        }
        $cur = $existing[$k] ?? null;
        $out['values'][$k] = $cur ?: null;

        if (!empty($post['remove_' . $k]) && $cur) {
            $out['values'][$k] = null;
            $out['delete'][]   = $cur;
        }
        if (isset($files[$k])) {
            [$name, $err] = upload_image($files[$k]);
            if ($err) {
                $errors[$k] = ($f['label'] ?? $k) . ': ' . $err;
                continue;
            }
            if ($name) {
                if ($cur && !in_array($cur, $out['delete'], true)) {
                    $out['delete'][] = $cur;
                }
                $out['values'][$k] = $name;
                $out['new'][]      = $name;
            }
        }
    }
    return $out;
}

function fields_render(array $fields, array $values, array $errors): void
{
    foreach ($fields as $k => $f) {
        $type  = $f['type'] ?? 'text';
        $label = $f['label'] ?? $k;
        $val   = $values[$k] ?? ($f['default'] ?? '');
        $req   = !empty($f['required']) ? ' required' : '';
        $max   = isset($f['max']) ? ' maxlength="' . (int)$f['max'] . '"' : '';
        echo '<div class="form-row' . (isset($errors[$k]) ? ' has-error' : '') . '">';
        echo '<label for="f_' . e($k) . '">' . e($label) . (!empty($f['required']) ? ' <span class="req">*</span>' : '') . '</label>';

        if ($type === 'textarea') {
            echo '<textarea id="f_' . e($k) . '" name="' . e($k) . '" rows="' . (int)($f['rows'] ?? 5) . '"' . $max . $req . '>' . e($val) . '</textarea>';
        } elseif ($type === 'select') {
            echo '<select id="f_' . e($k) . '" name="' . e($k) . '"' . $req . '>';
            foreach ($f['options'] as $ov => $ol) {
                echo '<option value="' . e($ov) . '"' . ((string)$ov === (string)$val ? ' selected' : '') . '>' . e($ol) . '</option>';
            }
            echo '</select>';
        } elseif ($type === 'image') {
            if ($val) {
                echo '<div class="img-current"><img src="' . e(img_url($val)) . '" alt="Current image">';
                echo '<label class="check"><input type="checkbox" name="remove_' . e($k) . '" value="1"> Remove current image</label></div>';
            }
            echo '<input type="file" id="f_' . e($k) . '" name="' . e($k) . '" accept="image/jpeg,image/png,image/gif,image/webp">';
            echo '<small>JPG, PNG, GIF or WebP, up to ' . round(MAX_UPLOAD_BYTES / 1048576, 1) . ' MB.</small>';
        } else {
            $htmlType = in_array($type, ['email', 'url', 'date', 'number', 'color'], true) ? $type : 'text';
            echo '<input type="' . $htmlType . '" id="f_' . e($k) . '" name="' . e($k) . '" value="' . e($val) . '"' . $max . $req . '>';
        }
        if (!empty($f['help'])) {
            echo '<small>' . e($f['help']) . '</small>';
        }
        if (isset($errors[$k])) {
            echo '<div class="field-error">' . e($errors[$k]) . '</div>';
        }
        echo '</div>';
    }
}

/* =====================================================
   Public-site partials
   ===================================================== */
function render_project_card(array $p): void
{
    $img = img_url($p['image'] ?? '');
    ?>
    <article class="card project" data-category="<?= e($p['category']) ?>">
      <?php if ($img): ?>
        <div class="thumb"><img src="<?= e($img) ?>" alt="<?= e($p['title']) ?> screenshot" loading="lazy"></div>
      <?php endif; ?>
      <div class="body">
        <h3><?= e($p['title']) ?></h3>
        <p><?= e($p['description']) ?></p>
        <?php if (!empty($p['project_url'])): ?>
          <a class="more" href="<?= e($p['project_url']) ?>" target="_blank" rel="noopener noreferrer">View project</a>
        <?php endif; ?>
      </div>
    </article>
    <?php
}
