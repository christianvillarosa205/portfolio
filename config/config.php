<?php
define('DB_HOST', 'sqlXXX.infinityfree.com');  
define('DB_NAME', 'if0_43483183_portfolio');
define('DB_USER', 'if0_43483183');
define('DB_PASS', 'your_password');         



define('DB_CHARSET', 'utf8mb4');

// ---- Uploads ----
define('MAX_UPLOAD_BYTES', 2 * 1024 * 1024);   // 2 MB

// ---- Login protection ----
define('LOGIN_MAX_ATTEMPTS', 5);     
define('LOGIN_LOCK_MINUTES', 15);    

// ---- Admin session idle timeout (seconds) ----
define('ADMIN_IDLE_TIMEOUT', 1800);

// Set to true only while debugging on your own computer
define('DEBUG', false);
