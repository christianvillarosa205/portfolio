<?php
require __DIR__ . '/includes/init.php';

$fields = [
    'site_title'       => ['label' => 'Website title', 'type' => 'text', 'required' => true, 'max' => 100, 'help' => 'Shown in the browser tab and as the brand text when no logo is set.'],
    'site_tagline'     => ['label' => 'Tagline', 'type' => 'text', 'max' => 150],
    'meta_description' => ['label' => 'Search engine description', 'type' => 'text', 'max' => 255],
    'footer_text'      => ['label' => 'Footer text', 'type' => 'text', 'max' => 255, 'help' => 'Leave blank for the default copyright line.'],
    'accent_color'     => ['label' => 'Accent colour', 'type' => 'color'],
    'logo'             => ['label' => 'Logo', 'type' => 'image'],
];

$current = settings();
$errors  = [];
$values  = $current;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash_set('error', 'Your security token was invalid or expired. Please try again.');
        redirect('admin/settings.php');
    }
    $post = $_POST;
    if (!empty($post['accent_default'])) {
        $post['accent_color'] = '';
    }
    $vals = fields_validate($fields, $post, $errors);
    $imgs = ['values' => [], 'new' => [], 'delete' => []];
    if (!$errors) {
        $imgs = fields_images($fields, $current, $post, $_FILES, $errors);
        if ($errors) {
            foreach ($imgs['new'] as $n) {
                delete_upload($n);
            }
        }
    }
    if (!$errors) {
        foreach ($vals + $imgs['values'] as $k => $v) {
            save_setting($k, $v === null ? '' : (string)$v);
        }
        foreach ($imgs['delete'] as $old) {
            delete_upload($old);
        }
        flash_set('success', 'Website settings saved.');
        redirect('admin/settings.php');
    }
    $values = array_merge($values, $post);
    $values['logo'] = $current['logo'] ?? null;
}

$usingDefault = empty($values['accent_color']);
if ($usingDefault) {
    $values['accent_color'] = '#4f46e5';
}

admin_header('Website Settings', 'settings');
?>
<div class="panel">
  <?php if ($errors): ?><div class="notice notice-error">Please fix the highlighted fields.</div><?php endif; ?>
  <form method="post" action="<?= e(url('admin/settings.php')) ?>" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>
    <?php fields_render($fields, $values, $errors); ?>
    <div class="form-row">
      <label class="check"><input type="checkbox" name="accent_default" value="1"<?= $usingDefault ? ' checked' : '' ?>> Use the theme's original colours (ignore the accent colour above)</label>
    </div>
    <div class="form-actions"><button class="btn" type="submit">Save settings</button></div>
  </form>
</div>
<?php admin_footer(); ?>
