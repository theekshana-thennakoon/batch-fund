<?php
include("./assets/database.php");
session_start();

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get reg_no from cookie (same auth as index.php)
if (!isset($_COOKIE["reg_no"])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$reg_no = $conn->real_escape_string($_COOKIE["reg_no"]);

// Verify the reg_no exists in users table
$sql_user = "SELECT reg_no FROM users WHERE reg_no = '{$reg_no}'";
$result_user = $conn->query($sql_user);
if (!$result_user || $result_user->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

// Get posted balance (total unpaid from 2024 portal)
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['balance']) || !is_numeric($input['balance'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid balance value']);
    exit;
}

$new_balance = (int)$input['balance'];

// Check current balance in prev_system
$sql_check = "SELECT id, balance FROM prev_system WHERE reg_no = '{$reg_no}'";
$result_check = $conn->query($sql_check);

if ($result_check && $result_check->num_rows > 0) {
    $row = $result_check->fetch_assoc();
    $current_balance = (int)$row['balance'];

    if ($current_balance !== $new_balance) {
        // Update existing record
        $sql_update = "UPDATE prev_system SET balance = {$new_balance} WHERE reg_no = '{$reg_no}'";
        if ($conn->query($sql_update)) {
            echo json_encode([
                'success' => true,
                'action' => 'updated',
                'old_balance' => $current_balance,
                'new_balance' => $new_balance
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'DB update failed: ' . $conn->error]);
        }
    } else {
        echo json_encode([
            'success' => true,
            'action' => 'no_change',
            'balance' => $current_balance
        ]);
    }
} else {
    // Insert new record
    $sql_insert = "INSERT INTO prev_system (reg_no, balance) VALUES ('{$reg_no}', {$new_balance})";
    if ($conn->query($sql_insert)) {
        echo json_encode([
            'success' => true,
            'action' => 'inserted',
            'new_balance' => $new_balance
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'DB insert failed: ' . $conn->error]);
    }
}

$conn->close();
?>
