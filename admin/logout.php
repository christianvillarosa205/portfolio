<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify($_POST['csrf_token'] ?? null)) {
    logout_admin();
    session_start();
    session_regenerate_id(true);
    flash_set('success', 'You have been signed out.');
    redirect('admin/login.php');
}
redirect('admin/index.php');
