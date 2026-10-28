<?php
include("../assets/database.php");
date_default_timezone_set('Asia/Colombo');
session_start();

header('Content-Type: application/json; charset=utf-8');

// Ensure admin is logged in
if (!isset($_SESSION["admin_logged_user"])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized: Admin session required'
    ]);
    exit;
}

// Supported settings whitelist
$allowed_settings = [
    'display_loan_popup' => [
        'default' => '1',
        'type' => 'boolean',
        'name' => 'Dashboard Loan Popup Modal'
    ]
];

$method = $_SERVER['REQUEST_METHOD'];

// Handle GET: retrieve setting value
if ($method === 'GET') {
    $key = isset($_GET['key']) ? trim($_GET['key']) : 'display_loan_popup';
    if (!array_key_exists($key, $allowed_settings)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid setting key'
        ]);
        exit;
    }

    $current_value = get_system_setting($conn, $key, $allowed_settings[$key]['default']);
    $is_enabled = ($current_value === '1' || $current_value === 'true' || $current_value === 1);

    echo json_encode([
        'success' => true,
        'key' => $key,
        'value' => $current_value,
        'is_enabled' => $is_enabled,
        'name' => $allowed_settings[$key]['name']
    ]);
    exit;
}

// Handle POST: update or toggle setting
if ($method === 'POST') {
    $raw_input = file_get_contents('php://input');
    $json_data = json_decode($raw_input, true);

    $key = '';
    $value = null;

    if (is_array($json_data)) {
        $key = isset($json_data['key']) ? trim($json_data['key']) : '';
        $value = isset($json_data['value']) ? $json_data['value'] : null;
    } else {
        $key = isset($_POST['key']) ? trim($_POST['key']) : '';
        $value = isset($_POST['value']) ? $_POST['value'] : null;
    }

    if (empty($key)) {
        $key = 'display_loan_popup';
    }

    if (!array_key_exists($key, $allowed_settings)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid setting key: ' . htmlspecialchars($key)
        ]);
        exit;
    }

    // Determine new value
    if ($value === null || $value === '' || $value === 'toggle') {
        $current = get_system_setting($conn, $key, $allowed_settings[$key]['default']);
        $new_value = ($current === '1' || $current === 'true' || $current === 1) ? '0' : '1';
    } else {
        $new_value = ($value === '1' || $value === 1 || $value === true || $value === 'true' || $value === 'on') ? '1' : '0';
    }

    $updated = set_system_setting($conn, $key, $new_value);

    if ($updated) {
        $is_enabled = ($new_value === '1');
        echo json_encode([
            'success' => true,
            'key' => $key,
            'value' => $new_value,
            'is_enabled' => $is_enabled,
            'message' => $is_enabled ? 'Loan popup has been enabled on the dashboard.' : 'Loan popup has been disabled on the dashboard.',
            'name' => $allowed_settings[$key]['name'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update setting in database: ' . $conn->error
        ]);
    }
    exit;
}

http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => 'Method not allowed'
]);
exit;
