<?php
include("../assets/database.php");
date_default_timezone_set('Asia/Colombo');
session_start();

header('Content-Type: application/json');

// Must be admin
if (!isset($_SESSION["admin_logged_user"])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Must be AJAX POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'xmlhttprequest') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

// Ensure dropped_students table exists
$conn->query("CREATE TABLE IF NOT EXISTS `dropped_students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `reg_no` VARCHAR(50) NOT NULL UNIQUE,
  `dropped_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$action = trim($_POST['action'] ?? '');
$reg_no = trim($_POST['reg_no'] ?? '');

if (empty($action) || empty($reg_no)) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

// Verify student exists
$stmt = $conn->prepare("SELECT reg_no FROM users WHERE reg_no = ?");
$stmt->bind_param("s", $reg_no);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    $stmt->close();
    echo json_encode(['success' => false, 'error' => 'Student not found']);
    exit;
}
$stmt->close();

if ($action === 'drop') {
    // Insert into dropped_students (ignore if already dropped)
    $ins = $conn->prepare("INSERT IGNORE INTO dropped_students (reg_no) VALUES (?)");
    $ins->bind_param("s", $reg_no);
    if ($ins->execute()) {
        $ins->close();
        echo json_encode(['success' => true, 'message' => "Student {$reg_no} has been dropped."]);
    } else {
        $ins->close();
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    }

} elseif ($action === 'restore') {
    // Remove from dropped_students
    $del = $conn->prepare("DELETE FROM dropped_students WHERE reg_no = ?");
    $del->bind_param("s", $reg_no);
    if ($del->execute()) {
        $del->close();
        echo json_encode(['success' => true, 'message' => "Student {$reg_no} has been restored."]);
    } else {
        $del->close();
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
}

