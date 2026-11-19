<?php
/* TEMPORARY connection tester. DELETE THIS FILE when finished. */
require __DIR__ . '/config/config.php';
header('Content-Type: text/plain; charset=utf-8');

echo "Host:     " . DB_HOST . "\n";
echo "Database: " . DB_NAME . "\n";
echo "User:     " . DB_USER . "\n";
echo "Password: " . (DB_PASS === '' ? '(empty)' : str_repeat('*', strlen(DB_PASS)) . ' (' . strlen(DB_PASS) . ' characters)') . "\n\n";

if (stripos(DB_HOST, 'XXX') !== false) {
    exit("PROBLEM: DB_HOST is still the placeholder. Paste the real MySQL Hostname from your InfinityFree panel.\n");
}
if (DB_PASS === 'your_password') {
    exit("PROBLEM: DB_PASS is still 'your_password'. Put your real MySQL password in config/config.php.\n");
}
if (DB_HOST === 'localhost' || DB_HOST === '127.0.0.1') {
    echo "WARNING: InfinityFree does not use localhost. Use the hostname from the MySQL Databases page.\n\n";
}

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS,
                   [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 8]);
    echo "SUCCESS: connected to the database.\n";
    $n = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables found: " . count($n) . ($n ? ' (' . implode(', ', $n) . ')' : '') . "\n";
    echo "\nNext: open setup.php, then DELETE dbtest.php.\n";
} catch (PDOException $e) {
    echo "FAILED: " . $e->getMessage() . "\n\n";
    $m = $e->getMessage();
    if (strpos($m, '1045') !== false)      echo "Meaning: wrong username or password.\n";
    elseif (strpos($m, '1049') !== false)  echo "Meaning: the database name does not exist. Check the exact name in MySQL Databases.\n";
    elseif (strpos($m, '2002') !== false || stripos($m, 'getaddrinfo') !== false || strpos($m, 'php_network') !== false)
                                           echo "Meaning: the hostname is wrong or unreachable.\n";
    elseif (strpos($m, '1044') !== false)  echo "Meaning: this user has no access to that database.\n";
}
