<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/config.php';

// Works in the web root or in a subfolder (e.g. /portfolio)
$__dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$__dir = preg_replace('#/admin$#', '', $__dir);
define('BASE_URL', $__dir);
define('UPLOAD_PATH', ROOT_PATH . '/uploads/');

error_reporting(E_ALL);
ini_set('display_errors', DEBUG ? '1' : '0');
ini_set('log_errors', '1');

require_once ROOT_PATH . '/includes/db.php';
require_once ROOT_PATH . '/includes/security.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/includes/auth.php';

send_security_headers();
start_secure_session();
