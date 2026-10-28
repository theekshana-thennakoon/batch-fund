<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "batch_fund";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection 
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to support Sinhala
$conn->set_charset("utf8mb4");

// Helper function to get a system setting
if (!function_exists('get_system_setting')) {
    function get_system_setting($conn, $key, $default = '1') {
        if (!$conn) return $default;
        $conn->query("CREATE TABLE IF NOT EXISTS `system_settings` (
            `setting_key` VARCHAR(100) NOT NULL PRIMARY KEY,
            `setting_value` TEXT NOT NULL,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $safe_key = $conn->real_escape_string($key);
        $res = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = '{$safe_key}' LIMIT 1");
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            return $row['setting_value'];
        }
        return $default;
    }
}

// Helper function to set a system setting
if (!function_exists('set_system_setting')) {
    function set_system_setting($conn, $key, $value) {
        if (!$conn) return false;
        $conn->query("CREATE TABLE IF NOT EXISTS `system_settings` (
            `setting_key` VARCHAR(100) NOT NULL PRIMARY KEY,
            `setting_value` TEXT NOT NULL,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $safe_key = $conn->real_escape_string($key);
        $safe_val = $conn->real_escape_string((string)$value);
        return $conn->query("INSERT INTO system_settings (setting_key, setting_value) VALUES ('{$safe_key}', '{$safe_val}') ON DUPLICATE KEY UPDATE setting_value = '{$safe_val}'");
    }
}
?>
