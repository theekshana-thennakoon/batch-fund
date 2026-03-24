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
?>
