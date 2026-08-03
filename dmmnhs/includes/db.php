<?php
// Load environment-specific configuration if available
$config_path = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
if (file_exists($config_path)) {
    require_once $config_path;
}

// Fallback to defaults if constants are not defined
$host = defined('DB_HOST') ? DB_HOST : 'sql110.infinityfree.com';
$db   = defined('DB_NAME') ? DB_NAME : 'if0_41352954_school_system';
$user = defined('DB_USER') ? DB_USER : 'if0_41352954';
$pass = defined('DB_PASS') ? DB_PASS : 'LXNHpAc6hlbD4m';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>