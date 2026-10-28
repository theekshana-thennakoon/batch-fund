<?php
include("./assets/database.php");
date_default_timezone_set('Asia/Colombo');
session_start();
if (!isset($_COOKIE["reg_no"])) {
    header("Location:splash");
} else {
    // $reg_no = $_SESSION["reg_no"];
    $reg_no = $_COOKIE["reg_no"];

    $sql_user = "SELECT * FROM users WHERE reg_no = '{$reg_no}'";
    $result_user = $conn->query($sql_user);
    if ($result_user->num_rows > 0) {
        while ($row_user = $result_user->fetch_assoc()) {
            $uid = $row_user["id"];
            $reg_no = $row_user["reg_no"];
            $name = $row_user["Name"];
            $card = $row_user["card"];
        }
    }
    $tot_paid = 0;
    $sql_payment = "SELECT * FROM payments WHERE reg_no = '{$reg_no}'";
    $result_payment = $conn->query($sql_payment);
    if ($result_payment->num_rows > 0) {
        while ($row_payment = $result_payment->fetch_assoc()) {
            $pid = $row_payment["id"];
            $reason_id = $row_payment["reason_id"];

            $sql_reason_paid = "SELECT * FROM reasons WHERE id = {$reason_id}";
            $result_reason_paid = $conn->query($sql_reason_paid);
            if ($result_reason_paid->num_rows > 0) {
                while ($row_reason_paid = $result_reason_paid->fetch_assoc()) {
                    $upid_paid = $row_reason_paid["id"];
                    $reason_paid = $row_reason_paid["reason"];
                    $reason_price_paid = $row_reason_paid["price"];
                    $tot_paid += $reason_price_paid;
                }
            }
        }
    }

    $tot = 0;
    $table_row = "";

    $sql_reason = "SELECT * FROM reasons ORDER BY id DESC";
    $result_reason = $conn->query($sql_reason);

    if ($result_reason->num_rows > 0) {
        while ($row_reason = $result_reason->fetch_assoc()) {

            $reason_id = $row_reason["id"];
            $reason = $row_reason["reason"];
            $reason_price = $row_reason["price"];
            $type = $row_reason["type"]; // ✅ NEW COLUMN

            $tot += $reason_price;

            $sql_payment = "SELECT * FROM payments WHERE reg_no = '{$reg_no}' AND reason_id = $reason_id";
            $result_payment = $conn->query($sql_payment);

            if ($result_payment->num_rows > 0) {
                $status = "Paid";
                $status_color = "success";
            } else {
                $status = "Unpaid";
                $status_color = "danger";
            }

            // ✅ IMPORTANT: data-type added
            $table_row .= "
        <tr data-type='{$type}'>
            <td>{$reason}</td>
            <td>Rs. {$reason_price}</td>
            <td><span class=\"p-3 badge bg-{$status_color}\">{$status}</span></td>
        </tr>
        ";
        }
    }
}

$tot = $tot + 5950;

// Check prev_system table for student's previous system balance adjustment
$sql_prev = "SELECT * FROM prev_system WHERE reg_no = '{$reg_no}'";
$result_prev = $conn->query($sql_prev);

$is_prev_fully_paid = true;
$prev_due_amount = 0;

if ($result_prev && $result_prev->num_rows > 0) {
    $row_prev = $result_prev->fetch_assoc();
    $prev_balance = (float)$row_prev["balance"];
    if ($prev_balance > 0) {
        $tot_paid = $tot_paid + (5950 - $prev_balance);
        $is_prev_fully_paid = false;
        $prev_due_amount = $prev_balance;
    } else {
        $tot_paid = $tot_paid + 5950;
        $is_prev_fully_paid = true;
    }
} else {
    $tot_paid = $tot_paid + 5950;
    $is_prev_fully_paid = true;
}

$prev_summary_row = "";
$prev_sector_rows = "";

if ($is_prev_fully_paid) {
    $prev_status_badge = "<span class=\"badge bg-success prev-system-badge d-inline-flex align-items-center justify-content-center text-wrap w-100\"><i class=\"fas fa-check-circle fa-xl me-2\"></i> You successfully paid full amount in 2024</span>";
    $prev_summary_row = "
        <tr data-type='prev_system_summary'>
            <td colspan=\"3\" class=\"text-center p-2\">{$prev_status_badge}</td>
        </tr>
    ";
} else {
    $prev_status_badge = "<span class=\"badge bg-danger prev-system-badge blink-badge d-inline-flex align-items-center justify-content-center text-wrap w-100\"><i class=\"fas fa-exclamation-circle fa-xl me-2\"></i> Amount you need to pay for 2024: Rs. {$prev_balance}</span>";
    $prev_summary_row = "
        <tr data-type='prev_system_summary'>
            <td colspan=\"3\" class=\"text-center p-2\">{$prev_status_badge}</td>
        </tr>
    ";

    $num_full_sectors = (int)floor($prev_balance / 500);
    $remainder = fmod($prev_balance, 500);

    $sector_count = 1;

    for ($i = 0; $i < $num_full_sectors; $i++) {
        $prev_sector_rows .= "
        <tr data-type='prev_system_sector' style='display: none;'>
            <td>2024 Contribution (Part {$sector_count})</td>
            <td>Rs. 500</td>
            <td><span class=\"p-3 badge bg-danger\">Unpaid</span></td>
        </tr>
        ";
        $sector_count++;
    }

    if ($remainder > 0) {
        $remainder_formatted = number_format($remainder, ($remainder == (int)$remainder ? 0 : 2));
        $prev_sector_rows .= "
        <tr data-type='prev_system_sector' style='display: none;'>
            <td>2024 Contribution (Remaining Balance)</td>
            <td>Rs. {$remainder_formatted}</td>
            <td><span class=\"p-3 badge bg-danger\">Unpaid</span></td>
        </tr>
        ";
    }
}

$table_row = $prev_summary_row . $prev_sector_rows . $table_row;

$balance = $tot - $tot_paid;

// Fetch Batch Loans Data
$sql_loans_list = "SELECT * FROM loans ORDER BY id ASC";
$result_loans_list = $conn->query($sql_loans_list);
$dashboard_loans = [];
$total_batch_loans = 0;
$total_batch_loans_paid = 0;
$total_batch_loans_balance = 0;

if ($result_loans_list && $result_loans_list->num_rows > 0) {
    while ($loan_row = $result_loans_list->fetch_assoc()) {
        $dashboard_loans[] = $loan_row;
        $total_batch_loans += (float)$loan_row['total'];
        $total_batch_loans_paid += (float)$loan_row['paid'];
        $total_batch_loans_balance += (float)$loan_row['balance'];
    }
}
$overall_loan_progress = $total_batch_loans > 0 ? round(($total_batch_loans_paid / $total_batch_loans) * 100, 1) : 0;

// Check if displaying loan popup on student dashboard is enabled in admin settings
$display_loan_popup_setting = get_system_setting($conn, 'display_loan_popup', '1');
$display_loan_popup = ($display_loan_popup_setting === '1' || $display_loan_popup_setting === 'true' || $display_loan_popup_setting === 1);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Contribution Management Portal</title>
    <link rel="icon" type="image/x-icon" href="./assets/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
    <style>
        /* ========== DARK MODE (Default) ========== */
        :root {
            --bg-body: #0a0a0f;
            --text-primary: #e9ecef;
            --text-secondary: #adb5bd;
            --card-bg: #1a1a2e;
            --card-border: #2d2d44;
            --table-header-bg: #1f2a3e;
            --table-header-text: #ffffff;
            --table-bg: #1a1a2e;
            --table-striped-bg: #22223b;
            --table-border-color: #2d2d44;
            --footer-bg: #050508;
            --footer-text: #adb5bd;
            --alert-bg: #1a2a3a;
            --alert-text: #9ec8ff;
            --alert-border: #2c4a6e;
            --contribution-box-bg: #1a1a2e;
            --contribution-box-border: #2d2d44;
            --nav-link-color: #adb5bd;
            --nav-link-active: #5a9eff;
            --info-card-bg: #1a1a2e;
            --badge-success-bg: #0d6e2e;
            --badge-danger-bg: #9b2c2c;
            --badge-text: #ffffff;
            --header-bg: #1a2a4f;
        }

        /* ========== LIGHT MODE ========== */
        body.light-mode {
            --bg-body: #fff;
            --text-primary: #212529;
            --text-secondary: #495057;
            --card-bg: white;
            --card-border: #ddd;
            --table-header-bg: #427BFF;
            --table-header-text: white;
            --table-bg: white;
            --table-striped-bg: #f8f9fa;
            --table-border-color: #dee2e6;
            --footer-bg: #3c3c3c;
            --footer-text: white;
            --alert-bg: #cfe4ff;
            --alert-text: #084298;
            --alert-border: #b6d4fe;
            --contribution-box-bg: white;
            --contribution-box-border: #eee;
            --nav-link-color: #495057;
            --nav-link-active: #007bff;
            --info-card-bg: white;
            --badge-success-bg: #28a745;
            --badge-danger-bg: #dc3545;
            --badge-text: white;
            --header-bg: #427BFF;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Header styles */
        .scmp-header {
            background-color: var(--header-bg);
            color: white;
            padding: 10px 0;
            transition: background-color 0.3s ease;
        }

        .scmp-logo {
            height: 80px;
            margin-right: 10px;
        }

        .scmp-footer {
            background-color: var(--footer-bg);
            color: var(--footer-text);
            padding: 20px 0;
            margin-top: 30px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .nav-link {
            color: var(--nav-link-color);
            margin-right: 15px;
            transition: color 0.2s ease;
        }

        .nav-link:hover {
            color: var(--nav-link-active);
        }

        .logout-btn {
            background-color: #dc3545;
            color: white;
            border: none;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

        .shield-icon {
            font-size: 24px;
            margin-right: 10px;
        }

        .info-card {
            border: 1px solid var(--card-border);
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: var(--info-card-bg);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .rank-box {
            text-align: center;
            padding: 10px;
        }

        .rank-number {
            font-size: 3rem;
            font-weight: bold;
            color: #ffc107;
        }

        .rank-text {
            font-size: 0.8rem;
            color: #ffc107;
        }

        .contribution-box {
            border: 1px solid var(--contribution-box-border);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: var(--contribution-box-bg);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .contribution-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .blue-icon {
            background-color: #007bff;
        }

        .green-icon {
            background-color: #28a745;
        }

        .red-icon {
            background-color: #dc3545;
        }

        .contribution-amount {
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0;
        }

        .contribution-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin: 0;
        }

        .nav-tabs .nav-link {
            color: var(--nav-link-color);
        }

        .nav-tabs .nav-link.active {
            font-weight: bold;
            color: var(--nav-link-active);
            background-color: transparent;
            border-color: var(--card-border) var(--card-border) transparent;
        }

        .footer-logo {
            height: 50px;
        }

        /* ========== TABLE STYLES - WORKS FOR BOTH LIGHT AND DARK MODE ========== */
        .table {
            color: var(--text-primary);
            background-color: var(--table-bg);
            border-color: var(--table-border-color);
        }

        .table> :not(caption)>*>* {
            background-color: var(--table-bg);
            color: var(--text-primary);
            border-bottom-color: var(--table-border-color);
        }

        /* Table header styles */
        .table thead th {
            background-color: var(--table-header-bg);
            color: var(--table-header-text);
            border-bottom: 2px solid var(--table-border-color);
            font-weight: 600;
        }

        /* Ensure header text is visible */
        .table-dark th {
            background-color: var(--table-header-bg) !important;
            color: var(--table-header-text) !important;
        }

        /* Table body rows */
        .table tbody tr {
            background-color: var(--table-bg);
            transition: background-color 0.2s ease;
        }

        /* Striped rows */
        .table-striped>tbody>tr:nth-of-type(odd)>* {
            background-color: var(--table-striped-bg);
            color: var(--text-primary);
        }

        .table-striped>tbody>tr:nth-of-type(even)>* {
            background-color: var(--table-bg);
            color: var(--text-primary);
        }

        /* Hover effect for table rows */
        .table-hover tbody tr:hover>* {
            background-color: rgba(90, 158, 255, 0.15);
            color: var(--text-primary);
        }

        body.light-mode .table-hover tbody tr:hover>* {
            background-color: rgba(66, 123, 255, 0.1);
        }

        /* Table borders */
        .table {
            border: 1px solid var(--table-border-color);
        }

        .table td,
        .table th {
            border-color: var(--table-border-color);
        }

        /* Alert styles */
        .alert-info {
            background-color: var(--alert-bg);
            color: var(--alert-text);
            border-color: var(--alert-border);
        }

        /* Badge styles with theme support */
        .badge.bg-success {
            background-color: var(--badge-success-bg) !important;
            color: var(--badge-text) !important;
            padding: 8px 12px;
            border-radius: 6px;
        }

        .badge.bg-danger {
            background-color: var(--badge-danger-bg) !important;
            color: var(--badge-text) !important;
            padding: 8px 12px;
            border-radius: 6px;
        }

        /* Theme toggle button */
        .theme-toggle-btn {
            background-color: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            border-radius: 50px;
            padding: 8px 16px;
            font-size: 14px;
            margin-right: 12px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .theme-toggle-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transform: scale(1.02);
        }

        body.light-mode .theme-toggle-btn {
            background-color: rgba(0, 0, 0, 0.1);
            color: #1a2a4f;
        }

        body.light-mode .theme-toggle-btn:hover {
            background-color: rgba(0, 0, 0, 0.2);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .contribution-box {
                flex-direction: column;
                text-align: center;
            }

            .contribution-icon {
                margin-bottom: 10px;
            }

            .info-card .row {
                flex-direction: row;
            }

            .rank-box {
                margin-top: 15px;
            }

            .theme-toggle-btn {
                padding: 6px 12px;
                font-size: 12px;
            }

            .prev-system-badge {
                font-size: 0.8rem !important;
                padding: 6px 10px !important;
            }

            .prev-system-badge i {
                font-size: 0.95rem !important;
            }
        }

        .prev-system-badge {
            font-size: 1.1rem;
            padding: 10px 16px;
        }

        /* Blink animation for 2024 tab and row badge when balance > 0 */
        @keyframes tab-blink {

            0%,
            100% {
                background-color: #dc3545;
                color: #ffffff;
                box-shadow: 0 0 12px rgba(220, 53, 69, 0.9);
            }

            50% {
                background-color: #721c24;
                color: #ffc9c9;
                box-shadow: 0 0 2px rgba(220, 53, 69, 0.3);
            }
        }

        .blink-tab {
            animation: tab-blink 1.2s ease-in-out infinite !important;
            font-weight: bold !important;
            border-color: #dc3545 !important;
        }

        @keyframes badge-blink {

            0%,
            100% {
                opacity: 1;
                box-shadow: 0 0 10px rgba(220, 53, 69, 0.7);
            }

            50% {
                opacity: 0.75;
                box-shadow: 0 0 2px rgba(220, 53, 69, 0.2);
            }
        }

        .blink-badge {
            animation: badge-blink 1.2s ease-in-out infinite !important;
        }

        /* Voice Button for Fully Paid */
        .due-amount-wrapper {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
        }

        @media (max-width: 768px) {
            .due-amount-wrapper {
                justify-content: center;
            }
        }

        .speak-voice-btn {
            background-color: rgba(40, 167, 69, 0.15);
            border: 1px solid rgba(40, 167, 69, 0.4);
            color: #28a745;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 0;
            font-size: 13px;
            transition: all 0.25s ease;
            outline: none;
        }

        .speak-voice-btn:hover {
            background-color: #28a745;
            color: #ffffff;
            transform: scale(1.12);
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.4);
        }

        .speak-voice-btn.speaking {
            background-color: #28a745;
            color: #ffffff;
            animation: speakPulse 0.9s infinite alternate;
        }

        body.light-mode .speak-voice-btn {
            background-color: rgba(40, 167, 69, 0.12);
            border-color: rgba(40, 167, 69, 0.5);
            color: #1e7e34;
        }

        body.light-mode .speak-voice-btn:hover {
            background-color: #28a745;
            color: #ffffff;
        }

        .speak-voice-btn.red-speak-btn {
            background-color: rgba(220, 53, 69, 0.15);
            border-color: rgba(220, 53, 69, 0.4);
            color: #dc3545;
        }

        .speak-voice-btn.red-speak-btn:hover {
            background-color: #dc3545;
            color: #ffffff;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.4);
        }

        .speak-voice-btn.red-speak-btn.speaking {
            background-color: #dc3545;
            color: #ffffff;
            animation: speakPulseRed 0.9s infinite alternate;
        }

        body.light-mode .speak-voice-btn.red-speak-btn {
            background-color: rgba(220, 53, 69, 0.12);
            border-color: rgba(220, 53, 69, 0.5);
            color: #c82333;
        }

        body.light-mode .speak-voice-btn.red-speak-btn:hover {
            background-color: #dc3545;
            color: #ffffff;
        }

        @keyframes speakPulseRed {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.6);
            }

            100% {
                transform: scale(1.18);
                box-shadow: 0 0 12px 4px rgba(220, 53, 69, 0.35);
            }
        }

        @keyframes speakPulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.6);
            }

            100% {
                transform: scale(1.18);
                box-shadow: 0 0 12px 4px rgba(40, 167, 69, 0.35);
            }
        }

        /* Ensure table wrapper has proper background */
        .table-responsive {
            background-color: transparent;
        }

        /* Additional fix for table header */
        .table thead tr th {
            background-color: var(--table-header-bg);
            color: var(--table-header-text);
        }

        /* Card title styling */
        .info-card h4 {
            color: var(--text-primary);
            margin-bottom: 20px;
        }

        /* Alert link styling */
        .alert-info p {
            color: var(--alert-text);
        }

        /* Welcome text styling */
        .fw-bold {
            color: var(--text-primary);
        }

        /* ========== FLOWBITE SKELETON LOADER STYLES ========== */
        @keyframes flowbite-pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.35;
            }
        }

        .flowbite-skeleton {
            animation: flowbite-pulse 1.8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        :root {
            --skeleton-bg: #282e42;
            --skeleton-bg-subtle: #222736;
            --skeleton-border: #2d2d44;
        }

        body.light-mode {
            --skeleton-bg: #e5e7eb;
            --skeleton-bg-subtle: #f3f4f6;
            --skeleton-border: #e2e8f0;
        }

        .skeleton-card {
            background-color: var(--info-card-bg);
            border: 1px solid var(--card-border);
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .skeleton-line {
            height: 0.875rem;
            background-color: var(--skeleton-bg);
            border-radius: 0.25rem;
            margin-bottom: 0.75rem;
        }

        .skeleton-line.sm {
            height: 0.65rem;
            margin-bottom: 0.5rem;
        }

        .skeleton-line.lg {
            height: 1.25rem;
        }

        .skeleton-line.xl {
            height: 1.75rem;
        }

        .skeleton-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--skeleton-bg);
            flex-shrink: 0;
        }

        .skeleton-badge-box {
            width: 70px;
            height: 28px;
            border-radius: 6px;
            background-color: var(--skeleton-bg);
        }

        .skeleton-tab-btn {
            width: 75px;
            height: 38px;
            border-radius: 6px 6px 0 0;
            background-color: var(--skeleton-bg);
            margin-right: 8px;
            display: inline-block;
        }

        /* Width Utilities for Skeletons */
        .w-20 {
            width: 20% !important;
        }

        .w-30 {
            width: 30% !important;
        }

        .w-35 {
            width: 35% !important;
        }

        .w-40 {
            width: 40% !important;
        }

        .w-60 {
            width: 60% !important;
        }

        .w-65 {
            width: 65% !important;
        }

        .w-70 {
            width: 70% !important;
        }

        .w-80 {
            width: 80% !important;
        }

        .w-85 {
            width: 85% !important;
        }

        #main-content {
            opacity: 0;
            transition: opacity 0.4s ease-in-out;
        }

        /* ========== BATCH PHOTO ANNOUNCEMENT CARD ========== */
        .batch-photo-card {
            position: relative;
            background: linear-gradient(135deg, rgba(30, 27, 75, 0.78) 0%, rgba(49, 46, 129, 0.6) 50%, rgba(15, 23, 42, 0.85) 100%) !important;
            backdrop-filter: blur(20px) saturate(190%) !important;
            -webkit-backdrop-filter: blur(20px) saturate(190%) !important;
            border: 1px solid rgba(165, 180, 252, 0.3) !important;
            border-radius: 20px !important;
            padding: 26px 28px !important;
            margin-bottom: 24px !important;
            box-shadow: 0 16px 40px -10px rgba(99, 102, 241, 0.32), inset 0 1px 1px rgba(255, 255, 255, 0.15) !important;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .batch-photo-card::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(168, 85, 247, 0.35) 0%, rgba(56, 189, 248, 0.18) 60%, transparent 80%);
            border-radius: 50%;
            pointer-events: none;
            filter: blur(20px);
            z-index: 0;
            animation: orbFloat 6s ease-in-out infinite alternate;
        }

        .batch-photo-card::after {
            content: '';
            position: absolute;
            bottom: -40px;
            left: 20%;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.25) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            filter: blur(25px);
            z-index: 0;
        }

        @keyframes orbFloat {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(-20px, 15px) scale(1.1); }
        }

        body.light-mode .batch-photo-card {
            background: linear-gradient(135deg, rgba(238, 242, 255, 0.9) 0%, rgba(224, 231, 255, 0.8) 50%, rgba(245, 243, 255, 0.9) 100%) !important;
            border: 1px solid rgba(99, 102, 241, 0.3) !important;
            box-shadow: 0 14px 34px -8px rgba(99, 102, 241, 0.18), inset 0 1px 2px rgba(255, 255, 255, 0.95) !important;
        }

        .batch-photo-content {
            position: relative;
            z-index: 1;
        }

        .batch-photo-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(99, 102, 241, 0.25);
            border: 1px solid rgba(165, 180, 252, 0.45);
            color: #c7d2fe;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 50px;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.2);
        }

        body.light-mode .batch-photo-badge {
            background: rgba(99, 102, 241, 0.15);
            border-color: rgba(99, 102, 241, 0.4);
            color: #4338ca;
        }

        .batch-date-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(245, 158, 11, 0.18);
            border: 1px solid rgba(245, 158, 11, 0.45);
            color: #fbbf24;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 5px 13px;
            border-radius: 50px;
        }

        body.light-mode .batch-date-pill {
            background: rgba(217, 119, 6, 0.12);
            border-color: rgba(217, 119, 6, 0.4);
            color: #b45309;
        }

        .batch-camera-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: #ffffff;
            font-size: 1.4rem;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.45);
            animation: cameraPulse 3s infinite ease-in-out;
            flex-shrink: 0;
        }

        @keyframes cameraPulse {
            0%, 100% {
                transform: scale(1) rotate(0deg);
                box-shadow: 0 8px 20px rgba(99, 102, 241, 0.45);
            }
            50% {
                transform: scale(1.08) rotate(-4deg);
                box-shadow: 0 12px 28px rgba(168, 85, 247, 0.6);
            }
        }

        .batch-photo-title {
            font-size: clamp(1.35rem, 2.8vw, 1.85rem);
            font-weight: 800;
            line-height: 1.25;
            margin: 12px 0 8px 0;
            color: #ffffff;
        }

        .batch-photo-title .title-highlight {
            background: linear-gradient(135deg, #a5b4fc 0%, #e0e7ff 40%, #f472b6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        body.light-mode .batch-photo-title {
            color: #1e1b4b;
        }

        body.light-mode .batch-photo-title .title-highlight {
            background: linear-gradient(135deg, #4338ca 0%, #6366f1 50%, #db2777 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .batch-photo-quote {
            font-size: 0.98rem;
            line-height: 1.55;
            color: #cbd5e1;
            margin-bottom: 6px;
            font-style: italic;
        }

        body.light-mode .batch-photo-quote {
            color: #334155;
        }

        .batch-photo-sinhala {
            font-size: 0.92rem;
            line-height: 1.5;
            color: #a5b4fc;
            margin-bottom: 18px;
            font-weight: 500;
        }

        body.light-mode .batch-photo-sinhala {
            color: #4f46e5;
        }

        /* Countdown Grid */
        .countdown-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            max-width: 480px;
            margin-bottom: 16px;
        }

        .countdown-box {
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid rgba(165, 180, 252, 0.25);
            border-radius: 14px;
            padding: 12px 8px;
            text-align: center;
            backdrop-filter: blur(10px);
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
        }

        .countdown-box:hover {
            transform: translateY(-3px);
            border-color: rgba(168, 85, 247, 0.55);
            box-shadow: 0 8px 22px rgba(99, 102, 241, 0.35);
        }

        body.light-mode .countdown-box {
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(99, 102, 241, 0.25);
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.1);
        }

        .countdown-number {
            font-size: clamp(1.4rem, 2.6vw, 1.9rem);
            font-weight: 800;
            line-height: 1.1;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(180deg, #38bdf8 0%, #818cf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px rgba(56, 189, 248, 0.25);
        }

        body.light-mode .countdown-number {
            background: linear-gradient(180deg, #2563eb 0%, #4f46e5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: none;
        }

        .countdown-label {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #94a3b8;
            font-weight: 700;
            margin-top: 4px;
        }

        body.light-mode .countdown-label {
            color: #64748b;
        }

        .countdown-label-si {
            display: block;
            font-size: 0.62rem;
            color: #64748b;
            font-weight: 500;
            margin-top: 1px;
            letter-spacing: normal;
        }

        body.light-mode .countdown-label-si {
            color: #8b5cf6;
        }

        /* Action buttons */
        .batch-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin-top: 4px;
        }

        .btn-calendar {
            background: rgba(99, 102, 241, 0.18);
            color: #c7d2fe !important;
            border: 1px solid rgba(165, 180, 252, 0.35);
            padding: 8px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.25s ease;
        }

        .btn-calendar:hover {
            background: rgba(99, 102, 241, 0.3);
            border-color: rgba(165, 180, 252, 0.6);
            color: #ffffff !important;
            transform: translateY(-2px);
        }

        body.light-mode .btn-calendar {
            background: rgba(99, 102, 241, 0.1);
            color: #4338ca !important;
            border-color: rgba(99, 102, 241, 0.3);
        }

        body.light-mode .btn-calendar:hover {
            background: rgba(99, 102, 241, 0.2);
            color: #312e81 !important;
        }

        .batch-chip-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            color: #94a3b8;
            padding: 6px 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        body.light-mode .batch-chip-item {
            color: #64748b;
            background: rgba(0, 0, 0, 0.04);
            border-color: rgba(0, 0, 0, 0.08);
        }

        /* ========== ACADEMIC & HOSTEL NOTICE CARD ========== */
        .academic-notice-card {
            position: relative;
            background: linear-gradient(135deg, rgba(24, 24, 40, 0.85) 0%, rgba(30, 27, 75, 0.72) 50%, rgba(15, 23, 42, 0.9) 100%) !important;
            backdrop-filter: blur(20px) saturate(190%) !important;
            -webkit-backdrop-filter: blur(20px) saturate(190%) !important;
            border: 1px solid rgba(245, 158, 11, 0.35) !important;
            border-radius: 20px !important;
            padding: 26px 28px !important;
            margin-bottom: 24px !important;
            box-shadow: 0 16px 40px -10px rgba(245, 158, 11, 0.18), inset 0 1px 1px rgba(255, 255, 255, 0.12) !important;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .academic-notice-card::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 240px;
            height: 240px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.28) 0%, rgba(239, 68, 68, 0.16) 50%, transparent 75%);
            border-radius: 50%;
            pointer-events: none;
            filter: blur(25px);
            z-index: 0;
            animation: orbFloatAmber 7s ease-in-out infinite alternate;
        }

        .academic-notice-card::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 15%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.2) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            filter: blur(25px);
            z-index: 0;
        }

        @keyframes orbFloatAmber {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(-25px, 20px) scale(1.12); }
        }

        body.light-mode .academic-notice-card {
            background: linear-gradient(135deg, rgba(254, 249, 195, 0.75) 0%, rgba(255, 251, 235, 0.95) 50%, rgba(241, 245, 249, 0.9) 100%) !important;
            border: 1px solid rgba(217, 119, 6, 0.35) !important;
            box-shadow: 0 14px 34px -8px rgba(217, 119, 6, 0.16), inset 0 1px 2px rgba(255, 255, 255, 0.95) !important;
        }

        .academic-notice-content {
            position: relative;
            z-index: 1;
        }

        .academic-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(245, 158, 11, 0.22);
            border: 1px solid rgba(251, 191, 36, 0.55);
            color: #fde68a;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 1.1px;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 50px;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);
        }

        body.light-mode .academic-badge {
            background: rgba(217, 119, 6, 0.14);
            border-color: rgba(217, 119, 6, 0.45);
            color: #92400e;
        }

        .academic-date-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(59, 130, 246, 0.18);
            border: 1px solid rgba(96, 165, 250, 0.45);
            color: #93c5fd;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 5px 13px;
            border-radius: 50px;
        }

        body.light-mode .academic-date-pill {
            background: rgba(37, 99, 235, 0.1);
            border-color: rgba(37, 99, 235, 0.35);
            color: #1d4ed8;
        }

        .hostel-vacate-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(248, 113, 113, 0.5);
            color: #fca5a5;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            padding: 5px 13px;
            border-radius: 50px;
            animation: hostelPulse 2.5s infinite ease-in-out;
        }

        @keyframes hostelPulse {
            0%, 100% {
                border-color: rgba(248, 113, 113, 0.5);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.25);
            }
            50% {
                border-color: rgba(248, 113, 113, 0.9);
                box-shadow: 0 0 14px rgba(239, 68, 68, 0.4);
            }
        }

        body.light-mode .hostel-vacate-pill {
            background: rgba(220, 38, 38, 0.12);
            border-color: rgba(220, 38, 38, 0.4);
            color: #b91c1c;
        }

        .academic-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 60%, #ea580c 100%);
            color: #ffffff;
            font-size: 1.4rem;
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.45);
            animation: academicPulse 3s infinite ease-in-out;
            flex-shrink: 0;
        }

        @keyframes academicPulse {
            0%, 100% {
                transform: scale(1) rotate(0deg);
                box-shadow: 0 8px 20px rgba(245, 158, 11, 0.45);
            }
            50% {
                transform: scale(1.07) rotate(-3deg);
                box-shadow: 0 12px 28px rgba(234, 88, 12, 0.6);
            }
        }

        .academic-title {
            font-size: clamp(1.25rem, 2.6vw, 1.75rem);
            font-weight: 800;
            line-height: 1.25;
            margin: 6px 0 16px 0;
            color: #ffffff;
        }

        .academic-title .title-highlight-amber {
            background: linear-gradient(135deg, #fde68a 0%, #fbbf24 50%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        body.light-mode .academic-title {
            color: #1e1b4b;
        }

        body.light-mode .academic-title .title-highlight-amber {
            background: linear-gradient(135deg, #b45309 0%, #d97706 50%, #ea580c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Bilingual Box Grid */
        .notice-bilingual-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(310px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .notice-lang-box {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 18px 20px;
            position: relative;
            backdrop-filter: blur(10px);
            transition: all 0.25s ease;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }

        .notice-lang-box:hover {
            border-color: rgba(245, 158, 11, 0.45);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }

        body.light-mode .notice-lang-box {
            background: rgba(255, 255, 255, 0.88);
            border-color: rgba(217, 119, 6, 0.22);
            box-shadow: 0 4px 14px rgba(217, 119, 6, 0.08);
        }

        .notice-lang-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
        }

        body.light-mode .notice-lang-header {
            border-bottom-color: rgba(0, 0, 0, 0.08);
        }

        .notice-lang-box .lang-badge {
            font-size: 0.72rem;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .notice-lang-box.si-box .lang-badge {
            background: rgba(245, 158, 11, 0.25);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.45);
        }

        .notice-lang-box.en-box .lang-badge {
            background: rgba(59, 130, 246, 0.25);
            color: #93c5fd;
            border: 1px solid rgba(59, 130, 246, 0.45);
        }

        body.light-mode .notice-lang-box.si-box .lang-badge {
            background: rgba(217, 119, 6, 0.15);
            color: #b45309;
        }

        body.light-mode .notice-lang-box.en-box .lang-badge {
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
        }

        .notice-lang-header .lang-title {
            font-size: 0.84rem;
            font-weight: 700;
            color: #cbd5e1;
            letter-spacing: 0.4px;
        }

        body.light-mode .notice-lang-header .lang-title {
            color: #475569;
        }

        .notice-point-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
            font-size: 0.94rem;
            line-height: 1.6;
            color: #e2e8f0;
        }

        .notice-point-item:last-child {
            margin-bottom: 0;
        }

        .notice-point-item .point-icon {
            font-size: 1.05rem;
            margin-top: 3px;
            flex-shrink: 0;
        }

        .notice-point-item.academic-point .point-icon {
            color: #fbbf24;
        }

        .notice-point-item.hostel-point {
            background: rgba(239, 68, 68, 0.12);
            border-left: 3px solid #ef4444;
            padding: 10px 14px;
            border-radius: 0 10px 10px 0;
            color: #fecaca;
        }

        .notice-point-item.hostel-point .point-icon {
            color: #f87171;
            margin-top: 2px;
        }

        body.light-mode .notice-point-item {
            color: #1e293b;
        }

        body.light-mode .notice-point-item.hostel-point {
            background: rgba(239, 68, 68, 0.08);
            border-left-color: #dc2626;
            color: #991b1b;
        }

        .notice-point-item strong {
            color: #ffffff;
            font-weight: 700;
        }

        .notice-point-item.academic-point strong {
            color: #fde68a;
        }

        body.light-mode .notice-point-item strong {
            color: #0f172a;
        }

        body.light-mode .notice-point-item.academic-point strong {
            color: #b45309;
        }

        /* Countdown for Academic Notice */
        .academic-countdown-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            max-width: 480px;
            margin-bottom: 16px;
        }

        .academic-countdown-box {
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid rgba(245, 158, 11, 0.28);
            border-radius: 14px;
            padding: 12px 8px;
            text-align: center;
            backdrop-filter: blur(10px);
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
        }

        .academic-countdown-box:hover {
            transform: translateY(-3px);
            border-color: rgba(245, 158, 11, 0.6);
            box-shadow: 0 8px 22px rgba(245, 158, 11, 0.35);
        }

        body.light-mode .academic-countdown-box {
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(217, 119, 6, 0.25);
            box-shadow: 0 4px 14px rgba(217, 119, 6, 0.1);
        }

        .academic-countdown-number {
            font-size: clamp(1.4rem, 2.6vw, 1.9rem);
            font-weight: 800;
            line-height: 1.1;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(180deg, #fbbf24 0%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px rgba(251, 191, 36, 0.25);
        }

        body.light-mode .academic-countdown-number {
            background: linear-gradient(180deg, #d97706 0%, #ea580c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: none;
        }

        .academic-countdown-label {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #94a3b8;
            font-weight: 700;
            margin-top: 4px;
        }

        body.light-mode .academic-countdown-label {
            color: #64748b;
        }

        .academic-countdown-label-si {
            display: block;
            font-size: 0.62rem;
            color: #64748b;
            font-weight: 500;
            margin-top: 1px;
            letter-spacing: normal;
        }

        body.light-mode .academic-countdown-label-si {
            color: #d97706;
        }

        .btn-calendar-amber {
            background: rgba(245, 158, 11, 0.18);
            color: #fde68a !important;
            border: 1px solid rgba(251, 191, 36, 0.4);
            padding: 8px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.25s ease;
        }

        .btn-calendar-amber:hover {
            background: rgba(245, 158, 11, 0.32);
            border-color: rgba(251, 191, 36, 0.7);
            color: #ffffff !important;
            transform: translateY(-2px);
        }

        body.light-mode .btn-calendar-amber {
            background: rgba(217, 119, 6, 0.12);
            color: #92400e !important;
            border-color: rgba(217, 119, 6, 0.35);
        }

        body.light-mode .btn-calendar-amber:hover {
            background: rgba(217, 119, 6, 0.22);
            color: #78350f !important;
        }

        /* ========== LOANS & REPAYMENTS NOTICE CARD ========== */
        .loan-report-notice-card {
            position: relative;
            background: linear-gradient(135deg, rgba(6, 30, 25, 0.88) 0%, rgba(13, 40, 35, 0.75) 50%, rgba(15, 23, 42, 0.92) 100%) !important;
            backdrop-filter: blur(20px) saturate(190%) !important;
            -webkit-backdrop-filter: blur(20px) saturate(190%) !important;
            border: 1px solid rgba(16, 185, 129, 0.38) !important;
            border-radius: 20px !important;
            padding: 26px 28px !important;
            box-shadow: 0 16px 40px -10px rgba(16, 185, 129, 0.22), inset 0 1px 1px rgba(255, 255, 255, 0.12) !important;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .loan-report-notice-card::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 240px;
            height: 240px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.28) 0%, rgba(6, 182, 212, 0.15) 55%, transparent 75%);
            border-radius: 50%;
            pointer-events: none;
            filter: blur(25px);
            z-index: 0;
            animation: orbFloatEmerald 7s ease-in-out infinite alternate;
        }

        .loan-report-notice-card::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 20%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(5, 150, 105, 0.2) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            filter: blur(25px);
            z-index: 0;
        }

        @keyframes orbFloatEmerald {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(-25px, 20px) scale(1.12); }
        }

        body.light-mode .loan-report-notice-card {
            background: linear-gradient(135deg, rgba(209, 250, 229, 0.75) 0%, rgba(236, 253, 245, 0.95) 50%, rgba(240, 253, 250, 0.9) 100%) !important;
            border: 1px solid rgba(16, 185, 129, 0.35) !important;
            box-shadow: 0 14px 34px -8px rgba(16, 185, 129, 0.16), inset 0 1px 2px rgba(255, 255, 255, 0.95) !important;
        }

        .loan-report-content {
            position: relative;
            z-index: 1;
        }

        .loan-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(16, 185, 129, 0.22);
            border: 1px solid rgba(52, 211, 153, 0.55);
            color: #a7f3d0;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 1.1px;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 50px;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
        }

        body.light-mode .loan-badge {
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(5, 150, 105, 0.45);
            color: #065f46;
        }

        .loan-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(6, 182, 212, 0.18);
            border: 1px solid rgba(34, 211, 238, 0.45);
            color: #67e8f9;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 5px 13px;
            border-radius: 50px;
            animation: pulseEmerald 2.5s infinite ease-in-out;
        }

        @keyframes pulseEmerald {
            0%, 100% {
                border-color: rgba(34, 211, 238, 0.45);
                box-shadow: 0 0 0 0 rgba(6, 182, 212, 0.2);
            }
            50% {
                border-color: rgba(34, 211, 238, 0.85);
                box-shadow: 0 0 12px rgba(6, 182, 212, 0.35);
            }
        }

        body.light-mode .loan-status-pill {
            background: rgba(8, 145, 178, 0.1);
            border-color: rgba(8, 145, 178, 0.35);
            color: #0e7490;
        }

        .loan-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #10b981 0%, #059669 60%, #047857 100%);
            color: #ffffff;
            font-size: 1.4rem;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.45);
            animation: loanPulse 3s infinite ease-in-out;
            flex-shrink: 0;
        }

        @keyframes loanPulse {
            0%, 100% {
                transform: scale(1) rotate(0deg);
                box-shadow: 0 8px 20px rgba(16, 185, 129, 0.45);
            }
            50% {
                transform: scale(1.07) rotate(3deg);
                box-shadow: 0 12px 28px rgba(16, 185, 129, 0.6);
            }
        }

        .loan-card-title {
            font-size: clamp(1.25rem, 2.6vw, 1.75rem);
            font-weight: 800;
            line-height: 1.25;
            margin: 6px 0 14px 0;
            color: #ffffff;
        }

        .loan-card-title .title-highlight-emerald {
            background: linear-gradient(135deg, #a7f3d0 0%, #34d399 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        body.light-mode .loan-card-title {
            color: #064e3b;
        }

        body.light-mode .loan-card-title .title-highlight-emerald {
            background: linear-gradient(135deg, #047857 0%, #059669 50%, #0891b2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .loan-message-box {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(16, 185, 129, 0.25);
            border-radius: 14px;
            padding: 16px 20px;
            backdrop-filter: blur(8px);
        }

        body.light-mode .loan-message-box {
            background: rgba(255, 255, 255, 0.85);
            border-color: rgba(16, 185, 129, 0.25);
        }

        .loan-statement-en {
            font-size: 0.98rem;
            line-height: 1.65;
            color: #e2e8f0;
        }

        body.light-mode .loan-statement-en {
            color: #1e293b;
        }

        .loan-statement-en strong {
            color: #6ee7b7;
            font-weight: 700;
        }

        body.light-mode .loan-statement-en strong {
            color: #047857;
        }

        .text-emerald {
            color: #34d399 !important;
        }

        body.light-mode .text-emerald {
            color: #059669 !important;
        }

        /* Loan interactive styles & badges */
        .loan-status-active {
            background: rgba(16, 185, 129, 0.22) !important;
            border: 1px solid rgba(52, 211, 153, 0.6) !important;
            color: #34d399 !important;
            animation: none !important;
        }

        body.light-mode .loan-status-active {
            background: rgba(16, 185, 129, 0.15) !important;
            border-color: rgba(5, 150, 105, 0.45) !important;
            color: #065f46 !important;
        }

        .btn-emerald-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: #ffffff !important;
            font-size: 0.92rem;
            font-weight: 700;
            padding: 10px 22px;
            border-radius: 12px;
            border: 1px solid rgba(52, 211, 153, 0.4);
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.35);
            text-decoration: none;
            transition: all 0.25s ease;
            cursor: pointer;
        }

        .btn-emerald-action:hover {
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(16, 185, 129, 0.5);
            color: #ffffff !important;
        }

        .btn-outline-emerald-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(16, 185, 129, 0.12);
            color: #34d399 !important;
            font-size: 0.92rem;
            font-weight: 700;
            padding: 10px 20px;
            border-radius: 12px;
            border: 1px solid rgba(52, 211, 153, 0.4);
            text-decoration: none;
            transition: all 0.25s ease;
            cursor: pointer;
        }

        .btn-outline-emerald-action:hover {
            background: rgba(16, 185, 129, 0.25);
            color: #a7f3d0 !important;
            transform: translateY(-2px);
        }

        body.light-mode .btn-outline-emerald-action {
            background: rgba(5, 150, 105, 0.08);
            color: #047857 !important;
            border-color: rgba(5, 150, 105, 0.35);
        }

        body.light-mode .btn-outline-emerald-action:hover {
            background: rgba(5, 150, 105, 0.18);
            color: #064e3b !important;
        }

        .loan-stat-badge-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 10px;
            margin: 14px 0 10px 0;
        }

        .loan-stat-mini-pill {
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid rgba(16, 185, 129, 0.25);
            border-radius: 12px;
            padding: 8px 12px;
            display: flex;
            flex-direction: column;
        }

        body.light-mode .loan-stat-mini-pill {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(16, 185, 129, 0.3);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .loan-mini-lbl {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
            font-weight: 700;
        }

        body.light-mode .loan-mini-lbl {
            color: #64748b;
        }

        .loan-mini-val {
            font-size: 1rem;
            font-weight: 800;
            color: #ffffff;
            margin-top: 2px;
        }

        body.light-mode .loan-mini-val {
            color: #0f172a;
        }

        /* Loans Card in Tab Area */
        .loans-details-card {
            background: var(--card-bg);
            border: 1px solid rgba(16, 185, 129, 0.35);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            transition: all 0.3s ease;
        }

        body.light-mode .loans-details-card {
            background: #ffffff;
            border: 1px solid rgba(16, 185, 129, 0.25);
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.08);
        }

        .loans-card-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            padding-bottom: 18px;
            border-bottom: 1px solid rgba(16, 185, 129, 0.2);
            margin-bottom: 20px;
        }

        .loans-card-title {
            font-size: 1.35rem;
            font-weight: 800;
            margin: 0;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        body.light-mode .loans-card-title {
            color: #064e3b;
        }

        .loan-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .loan-kpi-box {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 16px 18px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        body.light-mode .loan-kpi-box {
            background: #f8fafc;
            border-color: #e2e8f0;
        }

        .loan-kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .loan-kpi-icon.blue { background: rgba(59, 130, 246, 0.18); color: #60a5fa; }
        .loan-kpi-icon.green { background: rgba(16, 185, 129, 0.18); color: #34d399; }
        .loan-kpi-icon.amber { background: rgba(245, 158, 11, 0.18); color: #fbbf24; }
        .loan-kpi-icon.purple { background: rgba(139, 92, 246, 0.18); color: #a78bfa; }

        .loan-kpi-label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-secondary);
            margin-bottom: 3px;
        }

        .loan-kpi-val {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1.2;
        }

        /* Progress Bar */
        .loan-progress-wrap {
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            overflow: hidden;
            margin-top: 6px;
        }

        body.light-mode .loan-progress-wrap {
            background: #e2e8f0;
        }

        .loan-progress-bar {
            height: 100%;
            border-radius: 20px;
            background: linear-gradient(90deg, #059669 0%, #10b981 100%);
            transition: width 0.6s ease;
        }

        .loan-table th {
            background-color: rgba(16, 185, 129, 0.12) !important;
            color: #34d399 !important;
            border-bottom: 2px solid rgba(16, 185, 129, 0.25) !important;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 14px 16px;
            white-space: nowrap;
        }

        body.light-mode .loan-table th {
            background-color: #ecfdf5 !important;
            color: #065f46 !important;
            border-bottom: 2px solid #a7f3d0 !important;
        }

        .loan-table td {
            padding: 14px 16px;
            vertical-align: middle;
            font-size: 0.92rem;
            border-color: rgba(255, 255, 255, 0.05);
        }

        body.light-mode .loan-table td {
            border-color: #f1f5f9;
        }

        .badge-settled {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border: 1px solid rgba(52, 211, 153, 0.4);
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        body.light-mode .badge-settled {
            background: #d1fae5;
            color: #065f46;
            border-color: #6ee7b7;
        }

        .badge-partial {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.4);
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        body.light-mode .badge-partial {
            background: #fef3c7;
            color: #92400e;
            border-color: #fcd34d;
        }

        .badge-unpaid {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border: 1px solid rgba(248, 113, 113, 0.4);
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        body.light-mode .badge-unpaid {
            background: #fee2e2;
            color: #991b1b;
            border-color: #fca5a5;
        }

        /* Modal Styles */
        .modal-glass .modal-content {
            background: #111827;
            border: 1px solid rgba(16, 185, 129, 0.35);
            border-radius: 20px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7);
            color: #e5e7eb;
        }

        body.light-mode .modal-glass .modal-content {
            background: #ffffff;
            border-color: rgba(16, 185, 129, 0.25);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.15);
            color: #1f2937;
        }

        .modal-glass .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 20px 24px;
        }

        body.light-mode .modal-glass .modal-header {
            border-bottom-color: #f1f5f9;
        }

        .modal-glass .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 16px 24px;
        }

        body.light-mode .modal-glass .modal-footer {
            border-top-color: #f1f5f9;
        }

        /* ========== LOANS RESPONSIVE MOBILE STYLES ========== */
        .loan-mobile-card {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(16, 185, 129, 0.28);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.25s ease;
        }

        body.light-mode .loan-mobile-card {
            background: #ffffff;
            border-color: rgba(16, 185, 129, 0.22);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .loan-mobile-card-total {
            background: rgba(16, 185, 129, 0.12) !important;
            border: 1px solid rgba(16, 185, 129, 0.35) !important;
        }

        body.light-mode .loan-mobile-card-total {
            background: #ecfdf5 !important;
            border-color: #a7f3d0 !important;
        }

        .loan-table-wrapper {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 12px;
            margin-bottom: 1rem;
            position: relative;
        }

        .loan-table-wrapper::-webkit-scrollbar {
            height: 7px;
        }

        .loan-table-wrapper::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.4);
            border-radius: 10px;
        }

        body.light-mode .loan-table-wrapper::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .loan-table-wrapper::-webkit-scrollbar-thumb {
            background: rgba(16, 185, 129, 0.45);
            border-radius: 10px;
        }

        .loan-table-wrapper::-webkit-scrollbar-thumb:hover {
            background: rgba(16, 185, 129, 0.7);
        }

        .loan-table-wrapper .loan-table {
            min-width: 680px;
            margin-bottom: 0;
            width: 100%;
        }

        .loan-table-wrapper .loan-table th,
        .loan-table-wrapper .loan-table td {
            white-space: nowrap;
        }

        .loan-table-wrapper .loan-table th.col-reason,
        .loan-table-wrapper .loan-table td.col-reason {
            white-space: normal;
            min-width: 200px;
            max-width: 320px;
        }

        @media (max-width: 768px) {
            .loans-details-card {
                padding: 16px 12px !important;
                border-radius: 14px !important;
            }

            .loans-card-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 12px !important;
                padding-bottom: 14px !important;
                margin-bottom: 16px !important;
            }

            .loans-card-title {
                font-size: 1.15rem !important;
            }

            .loan-kpi-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 10px !important;
                margin-bottom: 16px !important;
            }

            .loan-kpi-box {
                padding: 12px 10px !important;
                border-radius: 12px !important;
                gap: 10px !important;
            }

            .loan-kpi-icon {
                width: 38px !important;
                height: 38px !important;
                font-size: 1.1rem !important;
                border-radius: 10px !important;
            }

            .loan-kpi-label {
                font-size: 0.68rem !important;
            }

            .loan-kpi-val {
                font-size: 1.05rem !important;
            }

            .modal-glass .modal-content {
                border-radius: 16px !important;
            }

            .modal-glass .modal-body {
                padding: 16px 12px !important;
            }
        }

        @media (max-width: 480px) {
            .loan-kpi-grid {
                grid-template-columns: 1fr 1fr !important;
                gap: 8px !important;
            }

            .loan-kpi-val {
                font-size: 0.95rem !important;
            }
        }

        /* ========== ANNOUNCEMENTS SWIPER STYLING ========== */
        .announcements-swiper-wrapper {
            position: relative;
            margin-bottom: 24px;
        }

        .announcementsSwiper {
            overflow: hidden;
            border-radius: 20px;
        }

        .announcementsSwiper .swiper-wrapper {
            display: flex;
            align-items: stretch;
        }

        .announcementsSwiper .swiper-slide {
            height: auto;
            display: flex;
        }

        .announcementsSwiper .swiper-slide .batch-photo-card,
        .announcementsSwiper .swiper-slide .academic-notice-card,
        .announcementsSwiper .swiper-slide .loan-report-notice-card {
            width: 100%;
            height: 100%;
            margin-bottom: 0 !important;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .swiper-controls-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 12px;
            padding: 0 4px;
            gap: 12px;
            flex-wrap: wrap;
        }

        .announcement-pagination {
            position: static !important;
            display: inline-flex;
            gap: 8px;
            width: auto !important;
            flex-wrap: wrap;
        }

        .custom-swiper-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 50px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #94a3b8;
            transition: all 0.25s ease;
            opacity: 1 !important;
            width: auto !important;
            height: auto !important;
            margin: 0 !important;
            line-height: normal;
        }

        .custom-swiper-pill:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }

        .custom-swiper-pill.swiper-pagination-bullet-active {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.35) 0%, rgba(168, 85, 247, 0.35) 100%);
            border-color: rgba(165, 180, 252, 0.65);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
        }

        body.light-mode .custom-swiper-pill {
            background: rgba(0, 0, 0, 0.05);
            border-color: rgba(0, 0, 0, 0.1);
            color: #64748b;
        }

        body.light-mode .custom-swiper-pill.swiper-pagination-bullet-active {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(168, 85, 247, 0.2) 100%);
            border-color: rgba(99, 102, 241, 0.5);
            color: #4338ca;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
        }

        .swiper-nav-buttons {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .swiper-nav-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.06);
            color: #cbd5e1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s ease;
            backdrop-filter: blur(8px);
        }

        .swiper-nav-btn:hover {
            background: rgba(99, 102, 241, 0.3);
            border-color: rgba(165, 180, 252, 0.5);
            color: #ffffff;
            transform: scale(1.08);
        }

        body.light-mode .swiper-nav-btn {
            background: rgba(0, 0, 0, 0.05);
            border-color: rgba(0, 0, 0, 0.12);
            color: #475569;
        }

        body.light-mode .swiper-nav-btn:hover {
            background: rgba(99, 102, 241, 0.15);
            color: #4338ca;
        }

        @media (max-width: 768px) {
            .container {
                padding-left: 20px !important;
                padding-right: 20px !important;
            }

            .scmp-header .container,
            .scmp-footer .container {
                padding-left: 18px !important;
                padding-right: 18px !important;
            }

            .batch-photo-card {
                margin-top: 8px !important;
                margin-bottom: 24px !important;
                padding: 22px 18px !important;
            }

            .info-card,
            .skeleton-card {
                margin-bottom: 22px !important;
            }

            .alert-info {
                margin-bottom: 22px !important;
            }

            .nav-tabs {
                margin-bottom: 20px !important;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding-left: 16px !important;
                padding-right: 16px !important;
            }

            .batch-photo-card {
                margin-top: 6px !important;
                margin-bottom: 22px !important;
                padding: 20px 16px !important;
            }

            .info-card,
            .skeleton-card {
                padding: 18px 16px !important;
                margin-bottom: 20px !important;
            }

            .countdown-grid {
                gap: 8px !important;
                margin-top: 8px !important;
                margin-bottom: 18px !important;
            }

            .countdown-box {
                padding: 10px 4px !important;
            }

            .countdown-number {
                font-size: 1.35rem !important;
            }

            .batch-actions {
                flex-direction: column !important;
                align-items: stretch !important;
                margin-top: 10px !important;
                gap: 10px !important;
            }

            .btn-calendar {
                justify-content: center !important;
            }
        }
    </style>
    <!-- Glassy Screen Styling -->
    <link rel="stylesheet" href="./assets/glassy.css">
</head>

<body class="dark-mode">

    <!-- Prevent Right Click and Long Press -->
    <script>
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
        document.addEventListener('mousedown', function(e) {
            if (e.button === 2) e.preventDefault();
        });
        document.addEventListener('touchstart', function(e) {
            if (e.touches.length === 1) {
                let timer = setTimeout(function() {
                    e.preventDefault();
                }, 500);
                e.target.addEventListener('touchend', function handler() {
                    clearTimeout(timer);
                    e.target.removeEventListener('touchend', handler);
                });
            }
        }, {
            passive: false
        });
    </script>
    <!-- Prevent text selection -->
    <style>
        body,
        html,
        * {
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            user-select: none !important;
        }
    </style>

    <!-- Header -->
    <header class="scmp-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img src="assets/logo.png" alt="SCMP Logo" class="scmp-logo">
                    <span class="fw-bold h2 mt-2 text-white">Batch Fund (20/21)</span>
                </div>
                <div class="d-flex align-items-center">
                    <!-- Dark/Light Mode Toggle Button -->
                    <button id="themeToggle" class="theme-toggle-btn me-2">
                        <i class="fas fa-sun me-1"></i>
                    </button>
                    <a class="btn logout-btn" href="x.php?logout=1">
                        <i class="fas fa-sign-out-alt me-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="d-flex align-items-center mb-3">
            <i class="fas fa-shield-alt shield-icon"></i>
            <h2 class="mb-0">Student Contribution Profile</h2>
        </div>

        <hr>

        <!-- FAQ Notice -->
        <div class="alert alert-info">
            <p class="mb-0">Details are listed here from January 1, 2025..</p>
            <p class="mb-0">2025 වර්ෂයේ ජනවාරි 1 වනදා සිට විස්තර මෙහි සදහන් වේ. </p>
        </div>

        <!-- Flowbite Skeleton Loader -->
        <div id="skeleton-loader" class="flowbite-skeleton">
            <!-- Batch Photo Card Skeleton -->
            <div class="skeleton-card" style="border: 1px solid rgba(99, 102, 241, 0.35); border-left: 5px solid #6366f1;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="skeleton-line sm w-25 mb-0"></div>
                    <div class="skeleton-line sm w-20 mb-0"></div>
                </div>
                <div class="skeleton-line xl w-60 mb-2"></div>
                <div class="skeleton-line sm w-85 mb-3"></div>
                <div class="row g-2" style="max-width: 480px;">
                    <div class="col-3"><div class="skeleton-line xl" style="height: 52px; border-radius: 12px;"></div></div>
                    <div class="col-3"><div class="skeleton-line xl" style="height: 52px; border-radius: 12px;"></div></div>
                    <div class="col-3"><div class="skeleton-line xl" style="height: 52px; border-radius: 12px;"></div></div>
                    <div class="col-3"><div class="skeleton-line xl" style="height: 52px; border-radius: 12px;"></div></div>
                </div>
            </div>

            <!-- Student Info Card Skeleton -->
            <div class="skeleton-card">
                <div class="row">
                    <div class="col-md-8">
                        <div class="row mb-3 align-items-center">
                            <div class="col-4">
                                <div class="skeleton-line lg w-75"></div>
                            </div>
                            <div class="col-8">
                                <div class="skeleton-line lg w-50"></div>
                            </div>
                        </div>
                        <div class="row mb-3 align-items-center">
                            <div class="col-4">
                                <div class="skeleton-line lg w-60"></div>
                            </div>
                            <div class="col-8">
                                <div class="skeleton-line lg w-75"></div>
                            </div>
                        </div>
                        <div class="row mb-3 align-items-center">
                            <div class="col-4">
                                <div class="skeleton-line lg w-50"></div>
                            </div>
                            <div class="col-8">
                                <div class="skeleton-line lg w-60"></div>
                            </div>
                        </div>
                        <div class="row mb-2 align-items-center">
                            <div class="col-4">
                                <div class="skeleton-line lg w-40"></div>
                            </div>
                            <div class="col-8">
                                <div class="skeleton-line lg w-30"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contribution Summary Skeleton -->
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="contribution-box">
                        <div class="skeleton-circle"></div>
                        <div class="flex-grow-1 ms-3">
                            <div class="skeleton-line xl w-60 mb-2"></div>
                            <div class="skeleton-line sm w-80 mb-0"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="contribution-box">
                        <div class="skeleton-circle"></div>
                        <div class="flex-grow-1 ms-3">
                            <div class="skeleton-line xl w-60 mb-2"></div>
                            <div class="skeleton-line sm w-80 mb-0"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="contribution-box">
                        <div class="skeleton-circle"></div>
                        <div class="flex-grow-1 ms-3">
                            <div class="skeleton-line xl w-60 mb-2"></div>
                            <div class="skeleton-line sm w-80 mb-0"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Skeleton -->
            <div class="d-flex mb-3">
                <div class="skeleton-tab-btn" style="width: 60px;"></div>
                <div class="skeleton-tab-btn" style="width: 80px;"></div>
                <div class="skeleton-tab-btn" style="width: 75px;"></div>
                <div class="skeleton-tab-btn" style="width: 85px;"></div>
                <div class="skeleton-tab-btn" style="width: 120px;"></div>
            </div>

            <!-- Table Skeleton -->
            <div class="skeleton-card">
                <div class="skeleton-line lg w-25 mb-4"></div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>
                                    <div class="skeleton-line sm w-50 mb-0"></div>
                                </th>
                                <th>
                                    <div class="skeleton-line sm w-40 mb-0"></div>
                                </th>
                                <th>
                                    <div class="skeleton-line sm w-30 mb-0"></div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="skeleton-line sm w-75 mb-0"></div>
                                </td>
                                <td>
                                    <div class="skeleton-line sm w-50 mb-0"></div>
                                </td>
                                <td>
                                    <div class="skeleton-badge-box"></div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="skeleton-line sm w-60 mb-0"></div>
                                </td>
                                <td>
                                    <div class="skeleton-line sm w-40 mb-0"></div>
                                </td>
                                <td>
                                    <div class="skeleton-badge-box"></div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="skeleton-line sm w-80 mb-0"></div>
                                </td>
                                <td>
                                    <div class="skeleton-line sm w-55 mb-0"></div>
                                </td>
                                <td>
                                    <div class="skeleton-badge-box"></div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="skeleton-line sm w-65 mb-0"></div>
                                </td>
                                <td>
                                    <div class="skeleton-line sm w-45 mb-0"></div>
                                </td>
                                <td>
                                    <div class="skeleton-badge-box"></div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="skeleton-line sm w-70 mb-0"></div>
                                </td>
                                <td>
                                    <div class="skeleton-line sm w-50 mb-0"></div>
                                </td>
                                <td>
                                    <div class="skeleton-badge-box"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <noscript>
            <style>
                #skeleton-loader {
                    display: none !important;
                }

                #main-content {
                    display: block !important;
                    opacity: 1 !important;
                }
            </style>
        </noscript>

        <!-- Main Content Container -->
        <div id="main-content" style="display: none;">
            <!-- Student Info Card -->
            <div class="info-card" id="infoCardBox">
                <div class="row">
                    <div class="col-md-8">
                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Registration No</div>
                            <div class="col-8">: <?php echo isset($reg_no) ? $reg_no : '2021/CS/001'; ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Name</div>
                            <div class="col-8">: <?php echo isset($name) ? $name : 'John Doe'; ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Card</div>
                            <div class="col-8">: <?php echo isset($card) ? $card : '****-****-1234'; ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Batch</div>
                            <div class="col-8">: 2021</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contribution Summary -->
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="contribution-box">
                        <div class="contribution-icon blue-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div>
                            <h4 class="contribution-amount" id="totalAmount">Rs. 0</h4>
                            <p class="contribution-label">Total / මුළු එකතුව</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="contribution-box">
                        <div class="contribution-icon green-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h4 class="contribution-amount" id="totalPaidAmount">Rs. 0</h4>
                            <p class="contribution-label">Total Paid / ගෙවා ඇති මුළු එකතුව</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="contribution-box">
                        <div class="contribution-icon red-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div>
                            <div class="due-amount-wrapper">
                                <?php if (isset($balance) && (float)$balance <= 0): ?>
                                    <button type="button" id="speakDueBtn" class="speak-voice-btn" title="Click to hear: 'ඔයා ඔක්කොම ගෙවලා ඉවරයි. ආදරෙයි'" aria-label="Listen: ඔයා ඔක්කොම ගෙවලා ඉවරයි. ආදරෙයි">
                                        <i class="fas fa-volume-up"></i>
                                    </button>
                                    <audio id="dueStatusAudio" src="./assets/audio/fully_paid_sinhala.mp3" preload="auto" playsinline></audio>
                                <?php elseif (isset($balance) && (float)$balance > 0): ?>
                                    <button type="button" id="speakDueBtn" class="speak-voice-btn red-speak-btn" title="Click to hear: 'තව <?php echo (int)$balance; ?>යි බං තියෙන්නේ. ඕක දීල ඉවර කරපන් බං.'" aria-label="Listen: Due amount">
                                        <i class="fas fa-volume-up"></i>
                                    </button>
                                    <audio id="dueStatusAudio" src="./tts_speech.php?balance=<?php echo (int)$balance; ?>" preload="auto" playsinline></audio>
                                <?php endif; ?>
                                <h4 class="contribution-amount" id="totalBalanceAmount">Rs. 0</h4>
                            </div>
                            <p class="contribution-label">Total Due / ගෙවීමට ඇති මුළු එකතුව</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Announcements Swiper Slider (Batch Photo & Academic Notice in same line) -->
            <div class="announcements-swiper-wrapper">
                <div class="swiper announcementsSwiper">
                    <div class="swiper-wrapper">
                        <!-- Slide 1: Batch Photo Card -->
                        <div class="swiper-slide">
                            <div class="batch-photo-card" id="batchPhotoCard">
                                <div class="batch-photo-content">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="batch-photo-badge">
                                                <i class="fas fa-camera-retro"></i> Batch Photo • Batch 20/21
                                            </span>
                                            <span class="batch-date-pill">
                                                <i class="far fa-calendar-alt"></i> 2026.11.07 (Saturday)
                                            </span>
                                        </div>
                                        <div class="batch-chip-item">
                                            <i class="fas fa-clock"></i> 08:30 AM Onwards
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-start gap-3 mt-3">
                                        <div class="batch-camera-icon d-none d-sm-inline-flex">
                                            <i class="fas fa-camera"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h3 class="batch-photo-title mb-3">
                                                <span class="title-highlight">Batch Photo 2026:</span> Freezing Memories, Celebrating One Unbroken Bond! 📸
                                            </h3>

                                            <!-- Countdown Timer -->
                                            <div class="countdown-grid" id="batchPhotoCountdown">
                                                <div class="countdown-box">
                                                    <div class="countdown-number" id="bpDays">--</div>
                                                    <div class="countdown-label">Days<span class="countdown-label-si">දින</span></div>
                                                </div>
                                                <div class="countdown-box">
                                                    <div class="countdown-number" id="bpHours">--</div>
                                                    <div class="countdown-label">Hours<span class="countdown-label-si">පැය</span></div>
                                                </div>
                                                <div class="countdown-box">
                                                    <div class="countdown-number" id="bpMinutes">--</div>
                                                    <div class="countdown-label">Minutes<span class="countdown-label-si">මිනිත්තු</span></div>
                                                </div>
                                                <div class="countdown-box">
                                                    <div class="countdown-number" id="bpSeconds">--</div>
                                                    <div class="countdown-label">Seconds<span class="countdown-label-si">තත්පර</span></div>
                                                </div>
                                            </div>

                                            <!-- Actions -->
                                            <div class="batch-actions">
                                                <a href="https://calendar.google.com/calendar/render?action=TEMPLATE&text=Batch+Photo+2026+-+Batch+20/21&dates=20261107T030000Z/20261107T113000Z&details=Official+Batch+Photo+Session+for+Batch+20/21.+Freezing+our+memories+together!&location=University+Premises" 
                                                   target="_blank" rel="noopener noreferrer" class="btn-calendar" title="Add event to Google Calendar">
                                                    <i class="far fa-calendar-plus"></i> Add to Calendar
                                                </a>
                                                <div class="batch-chip-item">
                                                    <i class="fas fa-user-tie"></i> Dress Code: Official / Formal
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 2: Academic Notice Card -->
                        <div class="swiper-slide">
                            <div class="academic-notice-card" id="academicNoticeCard">
                                <div class="academic-notice-content">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="academic-badge">
                                                <i class="fas fa-graduation-cap"></i> Academic Completion • Batch 20/21
                                            </span>
                                            <span class="academic-date-pill">
                                                <i class="far fa-calendar-alt"></i> 2026.11.30 (Monday)
                                            </span>
                                        </div>
                                        <div class="hostel-vacate-pill">
                                            <i class="fas fa-door-open"></i> Hostel Vacation Notice
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-start gap-3 mt-3">
                                        <div class="academic-icon d-none d-sm-inline-flex">
                                            <i class="fas fa-graduation-cap"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h3 class="academic-title mb-3">
                                                <span class="title-highlight-amber">Batch 20/21:</span> Academic Conclusion & Hostel Clearance Notice 🎓
                                            </h3>

                                            <!-- Countdown Timer to 2026.11.30 -->
                                            <div class="academic-countdown-grid" id="academicCountdown">
                                                <div class="academic-countdown-box">
                                                    <div class="academic-countdown-number" id="acDays">--</div>
                                                    <div class="academic-countdown-label">Days<span class="academic-countdown-label-si">දින</span></div>
                                                </div>
                                                <div class="academic-countdown-box">
                                                    <div class="academic-countdown-number" id="acHours">--</div>
                                                    <div class="academic-countdown-label">Hours<span class="academic-countdown-label-si">පැය</span></div>
                                                </div>
                                                <div class="academic-countdown-box">
                                                    <div class="academic-countdown-number" id="acMinutes">--</div>
                                                    <div class="academic-countdown-label">Minutes<span class="academic-countdown-label-si">මිනිත්තු</span></div>
                                                </div>
                                                <div class="academic-countdown-box">
                                                    <div class="academic-countdown-number" id="acSeconds">--</div>
                                                    <div class="academic-countdown-label">Seconds<span class="academic-countdown-label-si">තත්පර</span></div>
                                                </div>
                                            </div>

                                            <!-- Actions & Guidelines -->
                                            <div class="batch-actions">
                                                <a href="https://calendar.google.com/calendar/render?action=TEMPLATE&text=Batch+20/21+Academic+Conclusion+%26+Hostel+Clearance&dates=20261130T030000Z/20261130T180000Z&details=Final+date+of+academic+period+for+Batch+20/21.+All+students+must+vacate+hostels+with+all+belongings.&location=University+Premises" 
                                                   target="_blank" rel="noopener noreferrer" class="btn-calendar-amber" title="Add event to Google Calendar">
                                                    <i class="far fa-calendar-plus"></i> Add to Calendar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Swiper Pill Pagination & Controls Bar -->
                <div class="swiper-controls-bar">
                    <div class="swiper-pagination announcement-pagination"></div>
                    <div class="swiper-nav-buttons">
                        <button type="button" class="swiper-nav-btn swiper-btn-prev" title="Previous Announcement">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" class="swiper-nav-btn swiper-btn-next" title="Next Announcement">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Contribution Tabs -->
            <ul class="nav nav-tabs mb-3" id="contributionTabs">
                <li class="nav-item">
                    <button class="nav-link active" data-type="all">All</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-type="monthly">Monthly</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-type="funeral">Funeral</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-type="function">Function</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-type="prev_system">2024</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-type="loans" id="loansTabButton">
                        <i class="fas fa-hand-holding-dollar me-1"></i> Loans (ණය)
                        <span class="badge rounded-pill ms-1" style="background:#059669;color:#fff;font-size:0.75rem;"><?php echo count($dashboard_loans); ?></span>
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="contributionTabsContent">
                <div class="tab-pane fade show active" id="batch-fund" role="tabpanel">
                    <div class="info-card" id="contributionTableCard">
                        <h4>Contribution Details</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Contribution</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Sample data for demonstration - table rows will be visible in both modes
                                    if (isset($table_row) && !empty($table_row)) {
                                        echo $table_row;
                                    } else {
                                        // Sample data to demonstrate table visibility
                                        $sampleData = [
                                            ['Semester Fee', 5000, 'paid'],
                                            ['Library Fund', 2000, 'paid'],
                                            ['Sports Fund', 3000, 'Unpaid'],
                                            ['Cultural Fund', 2500, 'paid'],
                                            ['Development Fund', 4000, 'Unpaid'],
                                            ['Welfare Fund', 3500, 'paid'],
                                            ['Annual Contribution', 5000, 'Unpaid']
                                        ];

                                        foreach ($sampleData as $item) {
                                            $status = $item[2];
                                            $status_color = ($status == 'paid') ? 'success' : 'danger';
                                            $display_status = ucfirst($status);
                                            echo "
                                            <tr>
                                                <td>{$item[0]}</td>
                                                <td>Rs. {$item[1]}</td>
                                                <td><span class=\"badge bg-{$status_color}\">{$display_status}</span></td>
                                            </tr>
                                            ";
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Batch Loans & Repayments Details Card (Visible on Loans Tab) -->
                    <div class="loans-details-card" id="loansDetailsCard" style="display: none;">
                        <div class="loans-card-header">
                            <div>
                                <h3 class="loans-card-title">
                                    <i class="fas fa-hand-holding-dollar text-emerald"></i> Batch 20/21: Loans & Repayments Report
                                </h3>
                                <p class="text-secondary small mb-0 mt-1">Official batch fund loan disbursements, repayments, and settlement status</p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <a href="download_loan_pdf.php" class="btn btn-sm btn-outline-emerald-action" title="Download Official PDF Report">
                                    <i class="fas fa-file-pdf me-1"></i> Download as PDF
                                </a>
                                <button type="button" class="btn btn-sm btn-emerald-action" data-bs-toggle="modal" data-bs-target="#loanDetailsModal">
                                    <i class="fas fa-expand me-1"></i> Expand Modal
                                </button>
                            </div>
                        </div>

                        <!-- Loan KPI Summary Grid -->
                        <div class="loan-kpi-grid">
                            <div class="loan-kpi-box">
                                <div class="loan-kpi-icon blue">
                                    <i class="fas fa-coins"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="loan-kpi-label">Total Borrowed</div>
                                    <div class="loan-kpi-val">Rs. <?php echo number_format($total_batch_loans, 2); ?></div>
                                    <div class="small text-secondary"><?php echo count($dashboard_loans); ?> loan record(s)</div>
                                </div>
                            </div>
                            <div class="loan-kpi-box">
                                <div class="loan-kpi-icon green">
                                    <i class="fas fa-check-double"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="loan-kpi-label">Settled / Repaid</div>
                                    <div class="loan-kpi-val text-emerald">Rs. <?php echo number_format($total_batch_loans_paid, 2); ?></div>
                                    <div class="small text-secondary">Verified settlements</div>
                                </div>
                            </div>
                            <div class="loan-kpi-box">
                                <div class="loan-kpi-icon amber">
                                    <i class="fas fa-clock-rotate-left"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="loan-kpi-label">Balance Due</div>
                                    <div class="loan-kpi-val text-warning">Rs. <?php echo number_format($total_batch_loans_balance, 2); ?></div>
                                    <div class="small text-secondary">Outstanding to be cleared</div>
                                </div>
                            </div>
                            <div class="loan-kpi-box">
                                <div class="loan-kpi-icon purple">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="loan-kpi-label">Repaid Rate</div>
                                    <div class="loan-kpi-val"><?php echo $overall_loan_progress; ?>%</div>
                                    <div class="loan-progress-wrap">
                                        <div class="loan-progress-bar" style="width: <?php echo min(100, $overall_loan_progress); ?>%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- View Switcher for Mobile -->
                        <div class="d-flex justify-content-between align-items-center mb-3 d-md-none">
                            <span class="small text-secondary fw-bold">
                                <i class="fas fa-list-check text-emerald me-1"></i> Loans (<?php echo count($dashboard_loans); ?>)
                            </span>
                            <div class="btn-group btn-group-sm" role="group" id="loansTabToggleGroup">
                                <button type="button" class="btn btn-sm btn-outline-emerald-action active btn-loans-cards-view" id="btnLoansCardViewTab" onclick="setLoansView('cards', 'loansDetailsCard')">
                                    <i class="fas fa-grip-vertical me-1"></i> Cards
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-emerald-action btn-loans-table-view" id="btnLoansTableViewTab" onclick="setLoansView('table', 'loansDetailsCard')">
                                    <i class="fas fa-table me-1"></i> Table
                                </button>
                            </div>
                        </div>

                        <!-- 1. Mobile Cards View (Optimized for Phones) -->
                        <div class="loan-mobile-cards-container d-block d-md-none">
                            <?php if (!empty($dashboard_loans)): ?>
                                <?php foreach ($dashboard_loans as $idx => $loan): 
                                    $l_tot = (float)$loan['total'];
                                    $l_paid = (float)$loan['paid'];
                                    $l_bal = (float)$loan['balance'];
                                    $l_prog = $l_tot > 0 ? round(($l_paid / $l_tot) * 100, 1) : 0;
                                    
                                    if ($l_bal <= 0) {
                                        $badge_class = 'badge-settled';
                                        $badge_icon = 'fa-check-circle';
                                        $status_txt = 'Fully Settled';
                                        $bar_gradient = 'linear-gradient(90deg, #059669 0%, #10b981 100%)';
                                    } elseif ($l_paid > 0) {
                                        $badge_class = 'badge-partial';
                                        $badge_icon = 'fa-spinner fa-spin-pulse';
                                        $status_txt = 'Partially Paid';
                                        $bar_gradient = 'linear-gradient(90deg, #d97706 0%, #f59e0b 100%)';
                                    } else {
                                        $badge_class = 'badge-unpaid';
                                        $badge_icon = 'fa-circle-exclamation';
                                        $status_txt = 'Unpaid';
                                        $bar_gradient = 'linear-gradient(90deg, #dc2626 0%, #ef4444 100%)';
                                    }
                                ?>
                                    <div class="loan-mobile-card">
                                        <!-- Header: #, Reason & Status Badge -->
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2" style="border-bottom: 1px solid rgba(255,255,255,0.08);">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge rounded-pill" style="background: rgba(16,185,129,0.2); color: #34d399; font-size: 0.78rem;">#<?php echo $idx + 1; ?></span>
                                                <strong class="text-primary text-break" style="font-size: 0.98rem;"><?php echo htmlspecialchars($loan['reason']); ?></strong>
                                            </div>
                                            <span class="<?php echo $badge_class; ?>" style="font-size: 0.75rem; padding: 4px 10px; white-space: nowrap;">
                                                <i class="fas <?php echo $badge_icon; ?>"></i> <?php echo $status_txt; ?>
                                            </span>
                                        </div>

                                        <!-- 3 Figures Grid -->
                                        <div class="row g-2 text-center my-2">
                                            <div class="col-4">
                                                <div class="p-2 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                                                    <div class="text-secondary small" style="font-size: 0.68rem; text-transform: uppercase;">Total</div>
                                                    <div class="fw-bold" style="font-size: 0.88rem;">Rs. <?php echo number_format($l_tot, 0); ?></div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="p-2 rounded" style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.25);">
                                                    <div class="text-secondary small" style="font-size: 0.68rem; text-transform: uppercase;">Settled</div>
                                                    <div class="fw-bold text-emerald" style="font-size: 0.88rem;">Rs. <?php echo number_format($l_paid, 0); ?></div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="p-2 rounded" style="background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.25);">
                                                    <div class="text-secondary small" style="font-size: 0.68rem; text-transform: uppercase;">Balance</div>
                                                    <div class="fw-bold text-warning" style="font-size: 0.88rem;">Rs. <?php echo number_format($l_bal, 0); ?></div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Progress Bar -->
                                        <div class="mt-2 pt-1">
                                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                                <span class="text-secondary" style="font-size: 0.75rem;">Repayment Progress</span>
                                                <span class="fw-bold text-emerald" style="font-size: 0.75rem;"><?php echo $l_prog; ?>%</span>
                                            </div>
                                            <div class="loan-progress-wrap" style="height: 6px;">
                                                <div class="loan-progress-bar" style="width: <?php echo min(100, $l_prog); ?>%; background: <?php echo $bar_gradient; ?>;"></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <!-- Mobile Grand Total Summary Card -->
                                <div class="loan-mobile-card loan-mobile-card-total">
                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-secondary border-opacity-25">
                                        <strong class="text-uppercase" style="font-size: 0.85rem;"><i class="fas fa-calculator text-emerald me-1"></i> Grand Total Summary</strong>
                                        <span class="badge bg-success" style="font-size: 0.75rem;"><?php echo $overall_loan_progress; ?>% Settled</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center small py-1">
                                        <span class="text-secondary">Total Borrowed:</span>
                                        <strong>Rs. <?php echo number_format($total_batch_loans, 2); ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center small py-1">
                                        <span class="text-secondary">Total Settled:</span>
                                        <strong class="text-emerald">Rs. <?php echo number_format($total_batch_loans_paid, 2); ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center small py-1">
                                        <span class="text-secondary">Balance Remaining:</span>
                                        <strong class="text-warning">Rs. <?php echo number_format($total_batch_loans_balance, 2); ?></strong>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 text-secondary">
                                    <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                    No batch loans currently registered.
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- 2. Full Table View (Scrollable on mobile, shown on desktop) -->
                        <div class="loan-table-wrapper d-none d-md-block">
                            <div class="d-md-none small mb-2 p-2 rounded d-flex align-items-center justify-content-between" style="background: rgba(16, 185, 129, 0.12); border: 1px dashed rgba(16, 185, 129, 0.35); color: #34d399;">
                                <span><i class="fas fa-arrows-left-right me-1"></i> Swipe horizontally to view full table</span>
                                <span class="badge bg-dark text-secondary">7 Columns</span>
                            </div>
                            <table class="table table-striped table-hover loan-table">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;" class="text-center">#</th>
                                        <th class="col-reason">Reason / Lender Party</th>
                                        <th class="text-end">Total Amount</th>
                                        <th class="text-end">Amount Settled</th>
                                        <th class="text-end">Balance Remaining</th>
                                        <th style="width: 140px;">Progress</th>
                                        <th class="text-center" style="width: 120px;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($dashboard_loans)): ?>
                                        <?php foreach ($dashboard_loans as $idx => $loan): 
                                            $l_tot = (float)$loan['total'];
                                            $l_paid = (float)$loan['paid'];
                                            $l_bal = (float)$loan['balance'];
                                            $l_prog = $l_tot > 0 ? round(($l_paid / $l_tot) * 100, 1) : 0;
                                            
                                            if ($l_bal <= 0) {
                                                $badge_class = 'badge-settled';
                                                $badge_icon = 'fa-check-circle';
                                                $status_txt = 'Fully Settled';
                                                $bar_gradient = 'linear-gradient(90deg, #059669 0%, #10b981 100%)';
                                            } elseif ($l_paid > 0) {
                                                $badge_class = 'badge-partial';
                                                $badge_icon = 'fa-spinner fa-spin-pulse';
                                                $status_txt = 'Partially Paid';
                                                $bar_gradient = 'linear-gradient(90deg, #d97706 0%, #f59e0b 100%)';
                                            } else {
                                                $badge_class = 'badge-unpaid';
                                                $badge_icon = 'fa-circle-exclamation';
                                                $status_txt = 'Unpaid';
                                                $bar_gradient = 'linear-gradient(90deg, #dc2626 0%, #ef4444 100%)';
                                            }
                                        ?>
                                            <tr>
                                                <td class="text-center fw-bold text-secondary"><?php echo $idx + 1; ?></td>
                                                <td class="col-reason">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="fas fa-file-contract text-emerald"></i>
                                                        <strong class="text-primary"><?php echo htmlspecialchars($loan['reason']); ?></strong>
                                                    </div>
                                                </td>
                                                <td class="text-end fw-bold">Rs. <?php echo number_format($l_tot, 2); ?></td>
                                                <td class="text-end fw-bold text-emerald">Rs. <?php echo number_format($l_paid, 2); ?></td>
                                                <td class="text-end fw-bold text-warning">Rs. <?php echo number_format($l_bal, 2); ?></td>
                                                <td>
                                                    <div class="d-flex justify-content-between small fw-bold mb-1">
                                                        <span><?php echo $l_prog; ?>%</span>
                                                    </div>
                                                    <div class="loan-progress-wrap" style="height: 6px;">
                                                        <div class="loan-progress-bar" style="width: <?php echo min(100, $l_prog); ?>%; background: <?php echo $bar_gradient; ?>;"></div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="<?php echo $badge_class; ?>">
                                                        <i class="fas <?php echo $badge_icon; ?>"></i> <?php echo $status_txt; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-secondary">
                                                <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                                No batch loans currently registered.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr style="border-top: 2px solid rgba(16, 185, 129, 0.4); font-weight: 800;">
                                        <td colspan="2" class="text-uppercase text-end">Grand Total:</td>
                                        <td class="text-end">Rs. <?php echo number_format($total_batch_loans, 2); ?></td>
                                        <td class="text-end text-emerald">Rs. <?php echo number_format($total_batch_loans_paid, 2); ?></td>
                                        <td class="text-end text-warning">Rs. <?php echo number_format($total_batch_loans_balance, 2); ?></td>
                                        <td colspan="2" class="text-center text-emerald"><?php echo $overall_loan_progress; ?>% Settled</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>


                    </div>
                </div>

                <div class="tab-pane fade" id="batch-function" role="tabpanel">
                    <div class="info-card">
                        <h4>Batch Function Details</h4>
                        <p>No batch function details available at the moment.</p>
                    </div>
                </div>

                <div class="tab-pane fade" id="night-fund" role="tabpanel">
                    <div class="info-card">
                        <h4>Night Fund Details</h4>
                        <p>No night fund details available at the moment.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loan Notice On-Load Popup Modal -->
    <?php if ($display_loan_popup): ?>
    <div class="modal fade modal-glass" id="loanNoticePopupModal" tabindex="-1" aria-labelledby="loanNoticePopupModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border: 1px solid rgba(16, 185, 129, 0.45); box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 35px rgba(16, 185, 129, 0.25);">
                <div class="modal-header pb-2" style="border-bottom: 1px solid rgba(16, 185, 129, 0.2);">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="loan-badge">
                            <i class="fas fa-hand-holding-dollar"></i> LOANS & REPAYMENTS • BATCH 20/21
                        </span>
                        <span class="loan-status-pill loan-status-active">
                            <i class="fas fa-check-circle"></i> Published • Active Report
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-auto">
                        <div class="batch-chip-item d-none d-sm-inline-flex me-1">
                            <i class="fas fa-chart-pie"></i> Financial Transparency
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-3 p-md-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="loan-icon d-none d-sm-inline-flex">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="loan-card-title mb-2">
                                <span class="title-highlight-emerald">Batch 20/21:</span> Loans & Repayments Report 📋
                            </h3>

                            <div class="loan-message-box">
                                <!-- Quick Mini-Stats Grid -->
                                <div class="loan-stat-badge-grid mt-0">
                                    <div class="loan-stat-mini-pill">
                                        <span class="loan-mini-lbl"><i class="fas fa-coins me-1 text-warning"></i>Total Borrowed</span>
                                        <span class="loan-mini-val">Rs. <?php echo number_format($total_batch_loans, 2); ?></span>
                                    </div>
                                    <div class="loan-stat-mini-pill">
                                        <span class="loan-mini-lbl"><i class="fas fa-check me-1 text-success"></i>Settled / Paid</span>
                                        <span class="loan-mini-val text-emerald">Rs. <?php echo number_format($total_batch_loans_paid, 2); ?></span>
                                    </div>
                                    <div class="loan-stat-mini-pill">
                                        <span class="loan-mini-lbl"><i class="fas fa-clock me-1 text-info"></i>Balance Due</span>
                                        <span class="loan-mini-val text-warning">Rs. <?php echo number_format($total_batch_loans_balance, 2); ?></span>
                                    </div>
                                    <div class="loan-stat-mini-pill">
                                        <span class="loan-mini-lbl"><i class="fas fa-chart-line me-1 text-primary"></i>Repaid Rate</span>
                                        <span class="loan-mini-val"><?php echo $overall_loan_progress; ?>%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                                <button type="button" class="btn btn-emerald-action" id="popupOpenDetailsModalBtn">
                                    <i class="fas fa-table-list me-2"></i>View Loan Details & Summary
                                </button>
                                <button type="button" class="btn btn-outline-emerald-action" id="popupViewLoansTabBtn">
                                    <i class="fas fa-table me-2"></i>View in Dashboard Table
                                </button>
                                <a href="download_loan_pdf.php" target="_blank" class="btn btn-outline-emerald-action" title="Download Official PDF Report">
                                    <i class="fas fa-file-pdf me-2 text-danger"></i>Download as PDF
                                </a>
                                <div class="batch-chip-item ms-auto d-none d-md-inline-flex">
                                    <i class="fas fa-shield-halved text-info"></i> Official Audit Verified
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Loan Details Modal -->
    <div class="modal fade modal-glass" id="loanDetailsModal" tabindex="-1" aria-labelledby="loanDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="loan-icon" style="width: 42px; height: 42px; font-size: 1.2rem;">
                            <i class="fas fa-hand-holding-dollar"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="loanDetailsModalLabel">
                                Batch 20/21: Loans & Repayments Report
                            </h5>
                            <span class="small text-emerald fw-semibold">ණය සහ නැවත ගෙවීම් වාර්තාව &bull; Financial Transparency</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Top Summary KPI Cards -->
                    <div class="loan-kpi-grid">
                        <div class="loan-kpi-box">
                            <div class="loan-kpi-icon blue">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="loan-kpi-label">Total Borrowed</div>
                                <div class="loan-kpi-val">Rs. <?php echo number_format($total_batch_loans, 2); ?></div>
                            </div>
                        </div>
                        <div class="loan-kpi-box">
                            <div class="loan-kpi-icon green">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="loan-kpi-label">Settled / Paid</div>
                                <div class="loan-kpi-val text-emerald">Rs. <?php echo number_format($total_batch_loans_paid, 2); ?></div>
                            </div>
                        </div>
                        <div class="loan-kpi-box">
                            <div class="loan-kpi-icon amber">
                                <i class="fas fa-clock-rotate-left"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="loan-kpi-label">Balance Due</div>
                                <div class="loan-kpi-val text-warning">Rs. <?php echo number_format($total_batch_loans_balance, 2); ?></div>
                            </div>
                        </div>
                        <div class="loan-kpi-box">
                            <div class="loan-kpi-icon purple">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="loan-kpi-label">Repaid Rate</div>
                                <div class="loan-kpi-val"><?php echo $overall_loan_progress; ?>%</div>
                                <div class="loan-progress-wrap">
                                    <div class="loan-progress-bar" style="width: <?php echo min(100, $overall_loan_progress); ?>%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- View Switcher for Mobile inside Modal -->
                    <div class="d-flex justify-content-between align-items-center mb-3 d-md-none">
                        <span class="small text-secondary fw-bold">
                            <i class="fas fa-list-check text-emerald me-1"></i> Loan Records (<?php echo count($dashboard_loans); ?>)
                        </span>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-sm btn-outline-emerald-action active btn-loans-cards-view" onclick="setLoansView('cards', 'loanDetailsModal')">
                                <i class="fas fa-grip-vertical me-1"></i> Cards
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-emerald-action btn-loans-table-view" onclick="setLoansView('table', 'loanDetailsModal')">
                                <i class="fas fa-table me-1"></i> Table
                            </button>
                        </div>
                    </div>

                    <!-- 1. Mobile Cards View inside Modal -->
                    <div class="loan-mobile-cards-container d-block d-md-none mb-3">
                        <?php if (!empty($dashboard_loans)): ?>
                            <?php foreach ($dashboard_loans as $idx => $loan): 
                                $l_tot = (float)$loan['total'];
                                $l_paid = (float)$loan['paid'];
                                $l_bal = (float)$loan['balance'];
                                $l_prog = $l_tot > 0 ? round(($l_paid / $l_tot) * 100, 1) : 0;
                                
                                if ($l_bal <= 0) {
                                    $badge_class = 'badge-settled';
                                    $badge_icon = 'fa-check-circle';
                                    $status_txt = 'Fully Settled';
                                    $bar_gradient = 'linear-gradient(90deg, #059669 0%, #10b981 100%)';
                                } elseif ($l_paid > 0) {
                                    $badge_class = 'badge-partial';
                                    $badge_icon = 'fa-spinner fa-spin-pulse';
                                    $status_txt = 'Partially Paid';
                                    $bar_gradient = 'linear-gradient(90deg, #d97706 0%, #f59e0b 100%)';
                                } else {
                                    $badge_class = 'badge-unpaid';
                                    $badge_icon = 'fa-circle-exclamation';
                                    $status_txt = 'Unpaid';
                                    $bar_gradient = 'linear-gradient(90deg, #dc2626 0%, #ef4444 100%)';
                                }
                            ?>
                                <div class="loan-mobile-card">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2" style="border-bottom: 1px solid rgba(255,255,255,0.08);">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge rounded-pill" style="background: rgba(16,185,129,0.2); color: #34d399; font-size: 0.78rem;">#<?php echo $idx + 1; ?></span>
                                            <strong class="text-primary text-break" style="font-size: 0.98rem;"><?php echo htmlspecialchars($loan['reason']); ?></strong>
                                        </div>
                                        <span class="<?php echo $badge_class; ?>" style="font-size: 0.75rem; padding: 4px 10px; white-space: nowrap;">
                                            <i class="fas <?php echo $badge_icon; ?>"></i> <?php echo $status_txt; ?>
                                        </span>
                                    </div>
                                    <div class="row g-2 text-center my-2">
                                        <div class="col-4">
                                            <div class="p-2 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                                                <div class="text-secondary small" style="font-size: 0.68rem; text-transform: uppercase;">Total</div>
                                                <div class="fw-bold" style="font-size: 0.88rem;">Rs. <?php echo number_format($l_tot, 0); ?></div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2 rounded" style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.25);">
                                                <div class="text-secondary small" style="font-size: 0.68rem; text-transform: uppercase;">Settled</div>
                                                <div class="fw-bold text-emerald" style="font-size: 0.88rem;">Rs. <?php echo number_format($l_paid, 0); ?></div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2 rounded" style="background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.25);">
                                                <div class="text-secondary small" style="font-size: 0.68rem; text-transform: uppercase;">Balance</div>
                                                <div class="fw-bold text-warning" style="font-size: 0.88rem;">Rs. <?php echo number_format($l_bal, 0); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2 pt-1">
                                        <div class="d-flex justify-content-between align-items-center small mb-1">
                                            <span class="text-secondary" style="font-size: 0.75rem;">Repayment Progress</span>
                                            <span class="fw-bold text-emerald" style="font-size: 0.75rem;"><?php echo $l_prog; ?>%</span>
                                        </div>
                                        <div class="loan-progress-wrap" style="height: 6px;">
                                            <div class="loan-progress-bar" style="width: <?php echo min(100, $l_prog); ?>%; background: <?php echo $bar_gradient; ?>;"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <!-- Grand Total Summary Card in Modal -->
                            <div class="loan-mobile-card loan-mobile-card-total">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-secondary border-opacity-25">
                                    <strong class="text-uppercase" style="font-size: 0.85rem;"><i class="fas fa-calculator text-emerald me-1"></i> Grand Total Summary</strong>
                                    <span class="badge bg-success" style="font-size: 0.75rem;"><?php echo $overall_loan_progress; ?>% Settled</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center small py-1">
                                    <span class="text-secondary">Total Borrowed:</span>
                                    <strong>Rs. <?php echo number_format($total_batch_loans, 2); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center small py-1">
                                    <span class="text-secondary">Total Settled:</span>
                                    <strong class="text-emerald">Rs. <?php echo number_format($total_batch_loans_paid, 2); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center small py-1">
                                    <span class="text-secondary">Balance Remaining:</span>
                                    <strong class="text-warning">Rs. <?php echo number_format($total_batch_loans_balance, 2); ?></strong>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4 text-secondary">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                No batch loans recorded.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- 2. Full Table View inside Modal -->
                    <div class="loan-table-wrapper d-none d-md-block mb-3">
                        <div class="d-md-none small mb-2 p-2 rounded d-flex align-items-center justify-content-between" style="background: rgba(16, 185, 129, 0.12); border: 1px dashed rgba(16, 185, 129, 0.35); color: #34d399;">
                            <span><i class="fas fa-arrows-left-right me-1"></i> Swipe horizontally to view full table</span>
                            <span class="badge bg-dark text-secondary">7 Columns</span>
                        </div>
                        <table class="table table-striped table-hover loan-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th class="col-reason">Reason / Lender Party</th>
                                    <th class="text-end">Total (Rs.)</th>
                                    <th class="text-end">Paid (Rs.)</th>
                                    <th class="text-end">Balance (Rs.)</th>
                                    <th style="width: 130px;">Progress</th>
                                    <th class="text-center" style="width: 120px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($dashboard_loans)): ?>
                                    <?php foreach ($dashboard_loans as $idx => $loan): 
                                        $l_tot = (float)$loan['total'];
                                        $l_paid = (float)$loan['paid'];
                                        $l_bal = (float)$loan['balance'];
                                        $l_prog = $l_tot > 0 ? round(($l_paid / $l_tot) * 100, 1) : 0;
                                        
                                        if ($l_bal <= 0) {
                                            $badge_class = 'badge-settled';
                                            $badge_icon = 'fa-check-circle';
                                            $status_txt = 'Fully Settled';
                                            $bar_gradient = 'linear-gradient(90deg, #059669 0%, #10b981 100%)';
                                        } elseif ($l_paid > 0) {
                                            $badge_class = 'badge-partial';
                                            $badge_icon = 'fa-spinner fa-spin-pulse';
                                            $status_txt = 'Partially Paid';
                                            $bar_gradient = 'linear-gradient(90deg, #d97706 0%, #f59e0b 100%)';
                                        } else {
                                            $badge_class = 'badge-unpaid';
                                            $badge_icon = 'fa-circle-exclamation';
                                            $status_txt = 'Unpaid';
                                            $bar_gradient = 'linear-gradient(90deg, #dc2626 0%, #ef4444 100%)';
                                        }
                                    ?>
                                        <tr>
                                            <td class="text-center fw-bold text-secondary"><?php echo $idx + 1; ?></td>
                                            <td class="col-reason">
                                                <strong class="text-primary"><?php echo htmlspecialchars($loan['reason']); ?></strong>
                                            </td>
                                            <td class="text-end fw-bold">Rs. <?php echo number_format($l_tot, 2); ?></td>
                                            <td class="text-end fw-bold text-emerald">Rs. <?php echo number_format($l_paid, 2); ?></td>
                                            <td class="text-end fw-bold text-warning">Rs. <?php echo number_format($l_bal, 2); ?></td>
                                            <td>
                                                <div class="small fw-bold mb-1"><?php echo $l_prog; ?>%</div>
                                                <div class="loan-progress-wrap" style="height: 5px;">
                                                    <div class="loan-progress-bar" style="width: <?php echo min(100, $l_prog); ?>%; background: <?php echo $bar_gradient; ?>;"></div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="<?php echo $badge_class; ?>" style="font-size: 0.78rem; padding: 4px 10px;">
                                                    <i class="fas <?php echo $badge_icon; ?>"></i> <?php echo $status_txt; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-secondary">
                                            No batch loans recorded.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr style="border-top: 2px solid rgba(16, 185, 129, 0.4); font-weight: 800;">
                                    <td colspan="2" class="text-end">Grand Total:</td>
                                    <td class="text-end">Rs. <?php echo number_format($total_batch_loans, 2); ?></td>
                                    <td class="text-end text-emerald">Rs. <?php echo number_format($total_batch_loans_paid, 2); ?></td>
                                    <td class="text-end text-warning">Rs. <?php echo number_format($total_batch_loans_balance, 2); ?></td>
                                    <td colspan="2" class="text-center text-emerald"><?php echo $overall_loan_progress; ?>% Settled</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="p-3 rounded" style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25);">
                        <p class="mb-1 small" style="color: #34d399; font-weight: 700;">
                            <i class="fas fa-bullhorn me-1"></i> Batch 20/21 Financial Transparency & Audit Compliance
                        </p>
                        <p class="mb-0 small text-secondary">
                            All loans obtained for batch programs, special events, and functions, along with verified settlements and clearance records, are officially published here for all batch members.
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="download_loan_pdf.php" class="btn btn-emerald-action">
                        <i class="fas fa-file-pdf me-1"></i> Download as PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="scmp-footer">
        <div class="container">
            <div class="d-flex justify-content-center align-items-center mb-3">
                <img src="./assets/logo.png" alt="SCMP Logo" class="footer-logo">
            </div>
            <div class="text-center">
                <p>Student Contribution Management Portal</p>
                <p class="small">Copyright © Student Contribution Management Portal | V 2.0 |
                    Developed by Theekshana Thennakoon
                </p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Dark/Light Mode Toggle JavaScript - Dark Mode Default -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get the toggle button
            const themeToggle = document.getElementById('themeToggle');
            const body = document.body;

            // Check for saved theme preference in localStorage
            const savedTheme = localStorage.getItem('scmp_theme_dashboard');

            // Function to apply theme
            function applyTheme(theme) {
                if (theme === 'light') {
                    body.classList.remove('dark-mode');
                    body.classList.add('light-mode');
                    if (themeToggle) {
                        themeToggle.innerHTML = '<i class="fas fa-moon me-1"></i>';
                    }
                } else {
                    body.classList.remove('light-mode');
                    body.classList.add('dark-mode');
                    if (themeToggle) {
                        themeToggle.innerHTML = '<i class="fas fa-sun me-1"></i>';
                    }
                }
            }

            // Apply saved theme or default to dark mode
            if (savedTheme === 'light') {
                applyTheme('light');
            } else {
                // Default to dark mode
                applyTheme('dark');
                // Save dark mode as default preference if no saved preference exists
                if (!savedTheme) {
                    localStorage.setItem('scmp_theme_dashboard', 'dark');
                }
            }

            // Toggle theme on button click
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    if (body.classList.contains('dark-mode')) {
                        // Switch to light mode
                        applyTheme('light');
                        localStorage.setItem('scmp_theme_dashboard', 'light');
                    } else {
                        // Switch to dark mode
                        applyTheme('dark');
                        localStorage.setItem('scmp_theme_dashboard', 'dark');
                    }
                });
            }

            // Add smooth transition effect when toggling
            const style = document.createElement('style');
            style.textContent = `
                .table, .table tbody, .table tr, .table td, .table th {
                    transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
                }
                /* Ensure table headers always have proper contrast */
                .table thead th {
                    background-color: var(--table-header-bg) !important;
                    color: var(--table-header-text) !important;
                }
            `;
            document.head.appendChild(style);

            console.log('Dashboard theme initialized: Dark Mode is default');
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Function to animate numbers count up
            function animateCounter(id, targetVal) {
                const el = document.getElementById(id);
                if (!el) return;
                const duration = 1000;
                const frameRate = 30;
                const steps = Math.ceil(duration / (1000 / frameRate));
                let current = 0;
                const increment = targetVal / steps;

                function animate() {
                    current += increment;
                    if (current < targetVal) {
                        el.textContent = 'Rs. ' + Math.floor(current);
                        requestAnimationFrame(animate);
                    } else {
                        el.textContent = 'Rs. ' + targetVal;
                    }
                }
                animate();
            }

            function startCounterAnimations() {
                animateCounter('totalAmount', <?php echo isset($tot) ? (int)$tot : 25000; ?>);
                animateCounter('totalPaidAmount', <?php echo isset($tot_paid) ? (int)$tot_paid : 15000; ?>);
                animateCounter('totalBalanceAmount', <?php echo isset($balance) ? (int)$balance : 10000; ?>);
            }

            // Text to Voice & Status Speech in Sinhala
            const balanceAmount = <?php echo isset($balance) ? (int)$balance : 0; ?>;
            const isTotalDueZero = balanceAmount <= 0;
            const sinhalaSpeechText = isTotalDueZero ?
                "ඔයා ඔක්කොම ගෙවලා ඉවරයි. ආදරෙයි" :
                "තව " + balanceAmount + "යි බං තියෙන්නේ. ඕක දීල ඉවර කරපන් බං.";
            const sinhalaAudioSrc = isTotalDueZero ?
                "./assets/audio/fully_paid_sinhala.mp3" :
                "./tts_speech.php?balance=" + balanceAmount;

            let hasSpokenDueSpeech = false;

            function launchConfettiCelebration() {
                if (typeof confetti !== 'function') return;

                // Burst 1: Cannon from bottom center
                confetti({
                    particleCount: 80,
                    spread: 80,
                    origin: {
                        y: 0.75
                    },
                    zIndex: 9999
                });

                // Burst 2: Dual cannons from left & right sides
                setTimeout(() => {
                    confetti({
                        particleCount: 50,
                        angle: 60,
                        spread: 65,
                        origin: {
                            x: 0,
                            y: 0.7
                        },
                        zIndex: 9999
                    });
                    confetti({
                        particleCount: 50,
                        angle: 120,
                        spread: 65,
                        origin: {
                            x: 1,
                            y: 0.7
                        },
                        zIndex: 9999
                    });
                }, 300);

                // Burst 3: Gentle raining confetti from top
                setTimeout(() => {
                    confetti({
                        particleCount: 45,
                        spread: 120,
                        origin: {
                            y: 0.2
                        },
                        gravity: 0.8,
                        ticks: 300,
                        zIndex: 9999
                    });
                }, 700);
            }

            function playDueStatusSpeech() {
                if (hasSpokenDueSpeech) return;

                const speakBtn = document.getElementById('speakDueBtn');
                const audioEl = document.getElementById('dueStatusAudio') || new Audio(sinhalaAudioSrc);

                function onPlaying() {
                    hasSpokenDueSpeech = true;
                    if (speakBtn) speakBtn.classList.add('speaking');
                    if (isTotalDueZero) {
                        launchConfettiCelebration();
                    }
                }

                function onEnded() {
                    if (speakBtn) speakBtn.classList.remove('speaking');
                }

                audioEl.onplay = onPlaying;
                audioEl.onended = onEnded;
                audioEl.onpause = onEnded;
                audioEl.onerror = function() {
                    if (speakBtn) speakBtn.classList.remove('speaking');
                    fallbackSpeechSynthesis();
                };

                try {
                    audioEl.currentTime = 0;
                    const playPromise = audioEl.play();
                    if (playPromise !== undefined) {
                        playPromise.then(function() {
                            hasSpokenDueSpeech = true;
                        }).catch(function(err) {
                            console.log('Autoplay audio waiting for user gesture:', err);
                            setupUserInteractionFallback(audioEl);
                            fallbackSpeechSynthesis();
                        });
                    }
                } catch (err) {
                    console.error('Audio play error:', err);
                    setupUserInteractionFallback(audioEl);
                    fallbackSpeechSynthesis();
                }
            }

            function setupUserInteractionFallback(audioEl) {
                if (hasSpokenDueSpeech) return;
                const gestureEvents = ['click', 'touchstart', 'pointerdown', 'keydown', 'scroll'];
                const onFirstGesture = function() {
                    if (!hasSpokenDueSpeech) {
                        hasSpokenDueSpeech = true;
                        try {
                            audioEl.currentTime = 0;
                            audioEl.play().catch(function() {
                                fallbackSpeechSynthesis();
                            });
                        } catch (e) {
                            fallbackSpeechSynthesis();
                        }
                    }
                    gestureEvents.forEach(evt => window.removeEventListener(evt, onFirstGesture, true));
                };
                gestureEvents.forEach(evt => window.addEventListener(evt, onFirstGesture, {
                    once: true,
                    capture: true
                }));
            }

            // Fallback to Web Speech API
            function fallbackSpeechSynthesis() {
                if (!('speechSynthesis' in window)) return;
                try {
                    window.speechSynthesis.cancel();
                    const utterance = new SpeechSynthesisUtterance(sinhalaSpeechText);
                    utterance.lang = 'si-LK';
                    utterance.rate = 0.95;
                    if (!isTotalDueZero) {
                        utterance.pitch = 0.75;
                        const voices = window.speechSynthesis.getVoices();
                        const maleVoice = voices.find(v => v.lang.startsWith('si') && (v.name.toLowerCase().includes('male') || v.name.toLowerCase().includes('sameera')));
                        if (maleVoice) {
                            utterance.voice = maleVoice;
                        }
                    }

                    const speakBtn = document.getElementById('speakDueBtn');
                    utterance.onstart = function() {
                        hasSpokenDueSpeech = true;
                        if (speakBtn) speakBtn.classList.add('speaking');
                        if (isTotalDueZero) {
                            launchConfettiCelebration();
                        }
                    };
                    utterance.onend = function() {
                        if (speakBtn) speakBtn.classList.remove('speaking');
                    };
                    utterance.onerror = function() {
                        if (speakBtn) speakBtn.classList.remove('speaking');
                    };

                    window.speechSynthesis.speak(utterance);
                } catch (e) {
                    console.error('Speech synthesis fallback error:', e);
                }
            }

            // Play voice and background celebration on load
            playDueStatusSpeech();

            const speakBtn = document.getElementById('speakDueBtn');
            if (speakBtn) {
                speakBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    hasSpokenDueSpeech = false;
                    const audioEl = document.getElementById('dueStatusAudio') || new Audio(sinhalaAudioSrc);
                    audioEl.currentTime = 0;
                    audioEl.play().catch(function() {
                        fallbackSpeechSynthesis();
                    });
                    if (isTotalDueZero) {
                        launchConfettiCelebration();
                    }
                });
            }

            // Skeleton Loader transition
            const skeletonLoader = document.getElementById('skeleton-loader');
            const mainContent = document.getElementById('main-content');

            const isLoanPopupEnabled = <?php echo $display_loan_popup ? 'true' : 'false'; ?>;

            function showLoanNoticePopupOnLoad() {
                if (!isLoanPopupEnabled) return;
                const loanPopupEl = document.getElementById('loanNoticePopupModal');
                if (loanPopupEl && typeof bootstrap !== 'undefined') {
                    const popupModal = new bootstrap.Modal(loanPopupEl);
                    popupModal.show();
                }
            }

            setTimeout(function() {
                if (skeletonLoader && mainContent) {
                    skeletonLoader.style.opacity = '0';
                    skeletonLoader.style.transition = 'opacity 0.35s ease-out';

                    setTimeout(function() {
                        skeletonLoader.style.display = 'none';
                        mainContent.style.display = 'block';
                        requestAnimationFrame(function() {
                            mainContent.style.opacity = '1';
                            startCounterAnimations();
                            if (window.announcementsSwiperInstance) {
                                window.announcementsSwiperInstance.update();
                            }
                            setTimeout(showLoanNoticePopupOnLoad, 450);
                        });
                    }, 350);
                } else if (mainContent) {
                    mainContent.style.display = 'block';
                    mainContent.style.opacity = '1';
                    startCounterAnimations();
                    if (window.announcementsSwiperInstance) {
                        window.announcementsSwiperInstance.update();
                    }
                    setTimeout(showLoanNoticePopupOnLoad, 450);
                }
            }, 600);

            // Batch Photo Countdown Logic (Target: 2026.11.07 08:30:00 Asia/Colombo)
            const photoTargetDate = new Date('2026-11-07T08:30:00+05:30').getTime();

            function updateBatchPhotoCountdown() {
                const now = new Date().getTime();
                const distance = photoTargetDate - now;

                const daysEl = document.getElementById('bpDays');
                const hoursEl = document.getElementById('bpHours');
                const minEl = document.getElementById('bpMinutes');
                const secEl = document.getElementById('bpSeconds');

                if (!daysEl || !hoursEl || !minEl || !secEl) return;

                if (distance <= 0) {
                    daysEl.textContent = '00';
                    hoursEl.textContent = '00';
                    minEl.textContent = '00';
                    secEl.textContent = '00';

                    const countdownContainer = document.getElementById('batchPhotoCountdown');
                    if (countdownContainer && !countdownContainer.dataset.celebrated) {
                        countdownContainer.dataset.celebrated = 'true';
                        countdownContainer.innerHTML = `
                            <div class="countdown-box w-100" style="grid-column: 1 / -1; padding: 14px;">
                                <div class="countdown-number" style="font-size: 1.4rem;">
                                    🎉 TODAY IS THE BIG DAY! SMILE & FREEZE THE MOMENT! 📸
                                </div>
                            </div>
                        `;
                    }
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                daysEl.textContent = String(days).padStart(2, '0');
                hoursEl.textContent = String(hours).padStart(2, '0');
                minEl.textContent = String(minutes).padStart(2, '0');
                secEl.textContent = String(seconds).padStart(2, '0');
            }

            updateBatchPhotoCountdown();
            setInterval(updateBatchPhotoCountdown, 1000);

            // Academic Period End Countdown (Target: 2026.11.30 23:59:59 Asia/Colombo)
            const academicTargetDate = new Date('2026-11-30T23:59:59+05:30').getTime();

            function updateAcademicCountdown() {
                const now = new Date().getTime();
                const distance = academicTargetDate - now;

                const daysEl = document.getElementById('acDays');
                const hoursEl = document.getElementById('acHours');
                const minEl = document.getElementById('acMinutes');
                const secEl = document.getElementById('acSeconds');

                if (!daysEl || !hoursEl || !minEl || !secEl) return;

                if (distance <= 0) {
                    daysEl.textContent = '00';
                    hoursEl.textContent = '00';
                    minEl.textContent = '00';
                    secEl.textContent = '00';

                    const countdownContainer = document.getElementById('academicCountdown');
                    if (countdownContainer && !countdownContainer.dataset.celebrated) {
                        countdownContainer.dataset.celebrated = 'true';
                        countdownContainer.innerHTML = `
                            <div class="academic-countdown-box w-100" style="grid-column: 1 / -1; padding: 14px;">
                                <div class="academic-countdown-number" style="font-size: 1.3rem;">
                                    🎓 ACADEMIC JOURNEY CONCLUDED • WISHING BATCH 20/21 SUCCESS & VICTORY! 🌟
                                </div>
                            </div>
                        `;
                    }
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                daysEl.textContent = String(days).padStart(2, '0');
                hoursEl.textContent = String(hours).padStart(2, '0');
                minEl.textContent = String(minutes).padStart(2, '0');
                secEl.textContent = String(seconds).padStart(2, '0');
            }

            updateAcademicCountdown();
            setInterval(updateAcademicCountdown, 1000);

            // Announcements Swiper (Batch Photo & Academic Notice in same line)
            if (typeof Swiper !== 'undefined') {
                window.announcementsSwiperInstance = new Swiper('.announcementsSwiper', {
                    slidesPerView: 1,
                    spaceBetween: 24,
                    loop: true,
                    speed: 600,
                    grabCursor: true,
                    observer: true,
                    observeParents: true,
                    autoplay: {
                        delay: 3000,
                        disableOnInteraction: false,
                        pauseOnMouseEnter: true,
                    },
                    pagination: {
                        el: '.announcement-pagination',
                        clickable: true,
                        renderBullet: function (index, className) {
                            const labels = [
                                '<i class="fas fa-camera-retro me-1"></i> Batch Photo 2026',
                                '<i class="fas fa-graduation-cap me-1"></i> Academic & Hostel Notice'
                            ];
                            return '<button type="button" class="' + className + ' custom-swiper-pill">' + (labels[index] || '') + '</button>';
                        },
                    },
                    navigation: {
                        nextEl: '.swiper-btn-next',
                        prevEl: '.swiper-btn-prev',
                    },
                });
            }

            // Tab filtering logic
            const tabs = document.querySelectorAll('#contributionTabs .nav-link');
            const rows = document.querySelectorAll('tbody tr');
            const contribCard = document.getElementById('contributionTableCard');
            const loansCard = document.getElementById('loansDetailsCard');

            function filterRows(type) {
                if (type === 'loans') {
                    if (contribCard) contribCard.style.display = 'none';
                    if (loansCard) loansCard.style.display = 'block';
                    return;
                } else {
                    if (contribCard) contribCard.style.display = 'block';
                    if (loansCard) loansCard.style.display = 'none';
                }

                const isFullyPaid = <?php echo isset($is_prev_fully_paid) && $is_prev_fully_paid ? 'true' : 'false'; ?>;
                rows.forEach(row => {
                    const rowType = row.getAttribute('data-type');

                    if (type === 'all') {
                        if (rowType === 'prev_system_sector') {
                            row.style.display = 'none';
                        } else {
                            row.style.display = '';
                        }
                    } else if (type === 'prev_system') {
                        if (rowType === 'prev_system_sector' || (rowType === 'prev_system_summary' && isFullyPaid)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    } else {
                        if (rowType === type) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            }

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {

                    // Active tab UI
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    const type = this.getAttribute('data-type');
                    filterRows(type);
                });
            });

            // Initial filter on page load for active tab
            const activeTab = document.querySelector('#contributionTabs .nav-link.active');
            if (activeTab) {
                filterRows(activeTab.getAttribute('data-type'));
            }

            // Quick view in dashboard table button click handler
            const viewLoansTabBtn = document.getElementById('viewLoansTabBtn');
            if (viewLoansTabBtn) {
                viewLoansTabBtn.addEventListener('click', function() {
                    const loansTabBtn = document.getElementById('loansTabButton');
                    if (loansTabBtn) {
                        loansTabBtn.click();
                        loansTabBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            }

            // Loan Notice Popup Action Buttons
            const popupOpenDetailsBtn = document.getElementById('popupOpenDetailsModalBtn');
            if (popupOpenDetailsBtn) {
                popupOpenDetailsBtn.addEventListener('click', function() {
                    const popupEl = document.getElementById('loanNoticePopupModal');
                    if (popupEl && typeof bootstrap !== 'undefined') {
                        const popupModal = bootstrap.Modal.getInstance(popupEl);
                        if (popupModal) {
                            popupModal.hide();
                        }
                    }
                    const detailsEl = document.getElementById('loanDetailsModal');
                    if (detailsEl && typeof bootstrap !== 'undefined') {
                        const detailsModal = new bootstrap.Modal(detailsEl);
                        detailsModal.show();
                    }
                });
            }

            const popupViewLoansTabBtn = document.getElementById('popupViewLoansTabBtn');
            if (popupViewLoansTabBtn) {
                popupViewLoansTabBtn.addEventListener('click', function() {
                    const popupEl = document.getElementById('loanNoticePopupModal');
                    if (popupEl && typeof bootstrap !== 'undefined') {
                        const popupModal = bootstrap.Modal.getInstance(popupEl);
                        if (popupModal) {
                            popupModal.hide();
                        }
                    }
                    const loansTabBtn = document.getElementById('loansTabButton');
                    if (loansTabBtn) {
                        loansTabBtn.click();
                        setTimeout(function() {
                            const loansCard = document.getElementById('loansDetailsCard');
                            if (loansCard) {
                                loansCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                        }, 200);
                    }
                });
            }

            // Toggle between Cards and Table view for loans on mobile devices
            window.setLoansView = function(viewType, scopeId) {
                const scope = document.getElementById(scopeId);
                if (!scope) return;

                const cardsContainer = scope.querySelector('.loan-mobile-cards-container');
                const tableWrapper = scope.querySelector('.loan-table-wrapper');
                const btnCards = scope.querySelector('.btn-loans-cards-view');
                const btnTable = scope.querySelector('.btn-loans-table-view');

                if (viewType === 'cards') {
                    if (cardsContainer) {
                        cardsContainer.classList.remove('d-none');
                        cardsContainer.classList.add('d-block');
                    }
                    if (tableWrapper) {
                        tableWrapper.classList.remove('d-block');
                        tableWrapper.classList.add('d-none');
                    }
                    if (btnCards) btnCards.classList.add('active');
                    if (btnTable) btnTable.classList.remove('active');
                } else if (viewType === 'table') {
                    if (cardsContainer) {
                        cardsContainer.classList.remove('d-block');
                        cardsContainer.classList.add('d-none');
                    }
                    if (tableWrapper) {
                        tableWrapper.classList.remove('d-none');
                        tableWrapper.classList.add('d-block');
                    }
                    if (btnCards) btnCards.classList.remove('active');
                    if (btnTable) btnTable.classList.add('active');
                }
            };

        });
    </script>
</body>

</html>