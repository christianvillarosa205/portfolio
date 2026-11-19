<?php
require __DIR__ . '/includes/init.php';

$fields = [
    'full_name'     => ['label' => 'Full name', 'type' => 'text', 'required' => true, 'max' => 100],
    'headline'      => ['label' => 'Headline / role', 'type' => 'text', 'max' => 150, 'help' => 'Shown under your name on the home page.'],
    'intro'         => ['label' => 'Home page introduction', 'type' => 'textarea', 'max' => 800, 'rows' => 4],
    'biography'     => ['label' => 'Biography (About page)', 'type' => 'textarea', 'max' => 4000, 'rows' => 6],
    'what_i_do'     => ['label' => '"What I do" card', 'type' => 'textarea', 'max' => 500, 'rows' => 3],
    'what_i_value'  => ['label' => '"What I value" card', 'type' => 'textarea', 'max' => 500, 'rows' => 3],
    'email'         => ['label' => 'Contact email', 'type' => 'email', 'max' => 150],
    'phone'         => ['label' => 'Phone', 'type' => 'text', 'max' => 50],
    'location'      => ['label' => 'Location', 'type' => 'text', 'max' => 150],
    'facebook'      => ['label' => 'Facebook link', 'type' => 'url', 'max' => 255],
    'telegram'      => ['label' => 'Telegram link', 'type' => 'url', 'max' => 255],
    'linkedin'      => ['label' => 'LinkedIn link', 'type' => 'url', 'max' => 500],
    'github'        => ['label' => 'GitHub link', 'type' => 'url', 'max' => 255],
    'profile_image' => ['label' => 'Profile picture', 'type' => 'image'],
];

$st = db()->query('SELECT * FROM personal_info WHERE id = 1');
$existing = $st->fetch() ?: null;
$errors = [];
$values = $existing ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash_set('error', 'Your security token was invalid or expired. Please try again.');
        redirect('admin/personal.php');
    }
    $vals = fields_validate($fields, $_POST, $errors);
    $imgs = ['values' => [], 'new' => [], 'delete' => []];
    if (!$errors) {
        $imgs = fields_images($fields, $existing, $_POST, $_FILES, $errors);
        if ($errors) {
            foreach ($imgs['new'] as $n) {
                delete_upload($n);
            }
        }
    }
    if (!$errors) {
        $data = $vals + $imgs['values'];
        if ($existing) {
            $set = implode(', ', array_map(function ($k) { return "`$k` = ?"; }, array_keys($data)));
            db()->prepare("UPDATE personal_info SET $set WHERE id = 1")->execute(array_values($data));
        } else {
            $data['id'] = 1;
            $cols = implode(', ', array_map(function ($k) { return "`$k`"; }, array_keys($data)));
            db()->prepare("INSERT INTO personal_info ($cols) VALUES (" . implode(', ', array_fill(0, count($data), '?')) . ')')->execute(array_values($data));
        }
        foreach ($imgs['delete'] as $old) {
            delete_upload($old);
        }
        flash_set('success', 'Personal information updated.');
        redirect('admin/personal.php');
    }
    $values = array_merge($values, $_POST);
    $values['profile_image'] = $existing['profile_image'] ?? null;
}

admin_header('Personal Information', 'personal');
?>
<div class="panel">
  <?php if ($errors): ?><div class="notice notice-error">Please fix the highlighted fields.</div><?php endif; ?>
  <form method="post" action="<?= e(url('admin/personal.php')) ?>" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>
    <?php fields_render($fields, $values, $errors); ?>
    <div class="form-actions"><button class="btn" type="submit">Save changes</button></div>
  </form>
</div>
<?php admin_footer(); ?>
