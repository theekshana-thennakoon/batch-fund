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
            <!-- Batch Photo Announcement & Countdown Card -->
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
                            <h3 class="batch-photo-title">
                                <span class="title-highlight">Batch Photo 2026:</span> Freezing Memories, Celebrating One Unbroken Bond! 📸
                            </h3>
                            <p class="batch-photo-quote mb-1">
                                "Years will pass, chapters will turn, but in this single frame, our smiles will stay timeless. Let's make every second leading up to our official Batch Photo count!"
                            </p>
                            <p class="batch-photo-sinhala mb-3">
                                "නොමැකෙන මතකයන් එකම රාමුවකට... අපේ සොඳුරු බැච් ෆොටෝ (Batch Photo) මතක සටහනට තව දින කිහිපයයි!"
                            </p>

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
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="contributionTabsContent">
                <div class="tab-pane fade show active" id="batch-fund" role="tabpanel">
                    <div class="info-card">
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
                        });
                    }, 350);
                } else if (mainContent) {
                    mainContent.style.display = 'block';
                    mainContent.style.opacity = '1';
                    startCounterAnimations();
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

            // Tab filtering logic
            const tabs = document.querySelectorAll('#contributionTabs .nav-link');
            const rows = document.querySelectorAll('tbody tr');

            function filterRows(type) {
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

        });
    </script>
</body>

</html>