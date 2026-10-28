<?php
include("../assets/database.php");
date_default_timezone_set('Asia/Colombo');
session_start();
if (!isset($_SESSION["admin_logged_user"])) {
    header("Location:login.php");
} else {
    $reg_no = $_SESSION["admin_logged_user"];
}

// Get statistics
$tot_paid = 0;
$total_students = 0;
$total_reasons = 0;

// Total paid
$sql_payment = "SELECT SUM(amount) as total FROM (
    SELECT COUNT(*) * price as amount FROM payments p 
    JOIN reasons r ON p.reason_id = r.id 
    GROUP BY p.reason_id
) as totals";
$result = $conn->query($sql_payment);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $tot_paid = $row['total'] ?? 0;
}

// Total students
$sql_students = "SELECT COUNT(DISTINCT reg_no) as count FROM payments";
$result = $conn->query($sql_students);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_students = $row['count'] ?? 0;
}

// Total reasons
$sql_reasons = "SELECT COUNT(*) as count FROM reasons";
$result = $conn->query($sql_reasons);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_reasons = $row['count'] ?? 0;
}

// Total loans
$sql_loans = "SELECT COUNT(*) as count, SUM(total) as total FROM loans";
$result = $conn->query($sql_loans);
$total_loans = 0;
$total_loan_amount = 0;
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_loans = $row['count'] ?? 0;
    $total_loan_amount = $row['total'] ?? 0;
}

// Total loans paid
$sql_loans_paid = "SELECT SUM(paid) as total FROM loans";
$result = $conn->query($sql_loans_paid);
$total_loans_paid = 0;
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_loans_paid = $row['total'] ?? 0;
}

// All loans list for details modal
$sql_admin_loans = "SELECT * FROM loans ORDER BY id ASC";
$result_admin_loans = $conn->query($sql_admin_loans);
$admin_loans_list = [];
$total_admin_loan_balance = 0;
if ($result_admin_loans && $result_admin_loans->num_rows > 0) {
    while ($al = $result_admin_loans->fetch_assoc()) {
        $admin_loans_list[] = $al;
        $total_admin_loan_balance += (float)$al['balance'];
    }
}
$admin_overall_loan_progress = $total_loan_amount > 0 ? round(($total_loans_paid / $total_loan_amount) * 100, 1) : 0;

// Loan popup setting for student dashboard
$display_loan_popup_setting = get_system_setting($conn, 'display_loan_popup', '1');
$display_loan_popup = ($display_loan_popup_setting === '1' || $display_loan_popup_setting === 'true' || $display_loan_popup_setting === 1);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <style>
        :root {
            --dark-bg: #0f1419;
            --dark-card: #1a1f2e;
            --dark-border: #2d3748;
            --text-primary: #e9ecef;
            --text-secondary: #a8b2bf;
            --primary-color: #6366f1;
            --primary-light: #818cf8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-primary);
            transition: all 0.3s ease;
        }

        /* Sidebar */
        #sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(135deg, #1a1f2e 0%, #0f1419 100%);
            border-right: 2px solid var(--dark-border);
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s ease;
        }

        #sidebar .sidebar-header {
            padding: 25px;
            text-align: center;
            border-bottom: 2px solid var(--dark-border);
        }

        #sidebar .sidebar-header h3 {
            color: var(--primary-light);
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        #sidebar ul.components {
            padding: 20px 0;
        }

        #sidebar ul li {
            list-style: none;
        }

        #sidebar ul li a {
            padding: 15px 25px;
            display: block;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
            font-size: 0.95rem;
        }

        #sidebar ul li a:hover,
        #sidebar ul li a.active {
            color: var(--primary-light);
            background: rgba(99, 102, 241, 0.1);
            border-left-color: var(--primary-light);
            padding-left: 30px;
        }

        #sidebar ul li a i {
            margin-right: 12px;
            width: 20px;
        }

        /* Content Area */
        #content {
            margin-left: 260px;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* Topbar */
        .topbar {
            background: var(--dark-card);
            border-bottom: 2px solid var(--dark-border);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-light);
            margin: 0;
        }

        .topbar-right {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .theme-toggle {
            background: var(--dark-border);
            border: none;
            color: var(--primary-light);
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1.2rem;
        }

        .theme-toggle:hover {
            background: var(--primary-color);
            color: white;
            transform: scale(1.1);
        }

        .logout-btn {
            background: var(--danger);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .logout-btn:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        /* Main Container */
        .container-fluid {
            padding: 30px;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-light), var(--info));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        /* Cards Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: linear-gradient(135deg, var(--dark-card), rgba(99, 102, 241, 0.05));
            border: 2px solid var(--dark-border);
            border-radius: 12px;
            padding: 18px;
            transition: all 0.3s;
            overflow: hidden;
            position: relative;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            border-color: var(--primary-light);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.2);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-light), var(--info));
        }

        .stat-card-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 10px;
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary-light);
        }

        .stat-card.warning .stat-card-icon {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .stat-card.success .stat-card-icon {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .stat-card.danger .stat-card-icon {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .stat-card.info .stat-card-icon {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 6px;
        }

        .stat-change {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            border: none;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }

        /* Responsive */
        @media (max-width: 768px) {
            #sidebar {
                width: 60px;
            }

            #sidebar .sidebar-header h3 {
                font-size: 0;
            }

            #sidebar ul li a {
                padding: 15px;
                text-align: center;
            }

            #sidebar ul li a span {
                display: none;
            }

            #content {
                margin-left: 60px;
            }

            .page-header h2 {
                font-size: 1.8rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .container-fluid {
                padding: 15px;
            }
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--dark-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--dark-border);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }

        /* ========== BATCH PHOTO ANNOUNCEMENT CARD ========== */
        .batch-photo-card {
            position: relative;
            background: linear-gradient(135deg, rgba(30, 27, 75, 0.78) 0%, rgba(49, 46, 129, 0.6) 50%, rgba(15, 23, 42, 0.85) 100%);
            border: 1px solid rgba(165, 180, 252, 0.3);
            border-radius: 16px;
            padding: 24px 26px;
            margin-bottom: 25px;
            box-shadow: 0 12px 36px -10px rgba(99, 102, 241, 0.35);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        [data-theme="light"] .batch-photo-card {
            background: linear-gradient(135deg, rgba(238, 242, 255, 0.95) 0%, rgba(224, 231, 255, 0.85) 50%, rgba(245, 243, 255, 0.95) 100%);
            border: 1px solid rgba(99, 102, 241, 0.3);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.12);
        }

        .batch-photo-card::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(168, 85, 247, 0.3) 0%, rgba(56, 189, 248, 0.15) 60%, transparent 80%);
            border-radius: 50%;
            pointer-events: none;
            filter: blur(20px);
        }

        .batch-photo-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(99, 102, 241, 0.25);
            border: 1px solid rgba(165, 180, 252, 0.4);
            color: #c7d2fe;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 50px;
        }

        [data-theme="light"] .batch-photo-badge {
            background: rgba(99, 102, 241, 0.15);
            color: #4338ca;
            border-color: rgba(99, 102, 241, 0.35);
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
            padding: 4px 12px;
            border-radius: 50px;
        }

        [data-theme="light"] .batch-date-pill {
            background: rgba(217, 119, 6, 0.12);
            color: #b45309;
            border-color: rgba(217, 119, 6, 0.35);
        }

        .batch-camera-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: #ffffff;
            font-size: 1.3rem;
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
            flex-shrink: 0;
        }

        .batch-photo-title {
            font-size: 1.45rem;
            font-weight: 800;
            margin: 10px 0 6px 0;
            color: #ffffff;
        }

        .batch-photo-title .title-highlight {
            background: linear-gradient(135deg, #a5b4fc 0%, #ffffff 40%, #f472b6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        [data-theme="light"] .batch-photo-title {
            color: #1e1b4b;
        }

        [data-theme="light"] .batch-photo-title .title-highlight {
            background: linear-gradient(135deg, #4338ca 0%, #6366f1 50%, #db2777 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .batch-photo-quote {
            font-size: 0.95rem;
            line-height: 1.5;
            color: #cbd5e1;
            margin-bottom: 4px;
            font-style: italic;
        }

        [data-theme="light"] .batch-photo-quote {
            color: #334155;
        }

        .batch-photo-sinhala {
            font-size: 0.9rem;
            color: #a5b4fc;
            margin-bottom: 16px;
            font-weight: 500;
        }

        [data-theme="light"] .batch-photo-sinhala {
            color: #4f46e5;
        }

        .countdown-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            max-width: 440px;
            margin-bottom: 14px;
        }

        .countdown-box {
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid rgba(165, 180, 252, 0.25);
            border-radius: 12px;
            padding: 10px 6px;
            text-align: center;
        }

        [data-theme="light"] .countdown-box {
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(99, 102, 241, 0.25);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.08);
        }

        .countdown-number {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1.1;
            background: linear-gradient(180deg, #38bdf8 0%, #818cf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        [data-theme="light"] .countdown-number {
            background: linear-gradient(180deg, #2563eb 0%, #4f46e5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .countdown-label {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            font-weight: 700;
            margin-top: 3px;
        }

        [data-theme="light"] .countdown-label {
            color: #64748b;
        }

        .countdown-label-si {
            display: block;
            font-size: 0.6rem;
            color: #64748b;
            font-weight: 500;
        }

        [data-theme="light"] .countdown-label-si {
            color: #8b5cf6;
        }

        /* ========== ACADEMIC & HOSTEL NOTICE CARD ========== */
        .academic-notice-card {
            position: relative;
            background: linear-gradient(135deg, rgba(24, 24, 40, 0.85) 0%, rgba(30, 27, 75, 0.72) 50%, rgba(15, 23, 42, 0.9) 100%) !important;
            backdrop-filter: blur(20px) saturate(190%) !important;
            -webkit-backdrop-filter: blur(20px) saturate(190%) !important;
            border: 1px solid rgba(245, 158, 11, 0.35) !important;
            border-radius: 20px !important;
            padding: 24px 26px !important;
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

        @keyframes orbFloatAmber {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(-25px, 20px) scale(1.12); }
        }

        [data-theme="light"] .academic-notice-card {
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
        }

        [data-theme="light"] .academic-badge {
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

        [data-theme="light"] .academic-date-pill {
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
        }

        [data-theme="light"] .hostel-vacate-pill {
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
            flex-shrink: 0;
        }

        .academic-title {
            font-size: 1.45rem;
            font-weight: 800;
            margin: 6px 0 16px 0;
            color: #ffffff;
        }

        .academic-title .title-highlight-amber {
            background: linear-gradient(135deg, #fde68a 0%, #fbbf24 50%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        [data-theme="light"] .academic-title {
            color: #1e1b4b;
        }

        [data-theme="light"] .academic-title .title-highlight-amber {
            background: linear-gradient(135deg, #b45309 0%, #d97706 50%, #ea580c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

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
            padding: 16px 18px;
            position: relative;
            backdrop-filter: blur(10px);
            transition: all 0.25s ease;
        }

        [data-theme="light"] .notice-lang-box {
            background: rgba(255, 255, 255, 0.88);
            border-color: rgba(217, 119, 6, 0.22);
            box-shadow: 0 4px 14px rgba(217, 119, 6, 0.08);
        }

        .notice-lang-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
        }

        [data-theme="light"] .notice-lang-header {
            border-bottom-color: rgba(0, 0, 0, 0.08);
        }

        .notice-lang-box .lang-badge {
            font-size: 0.72rem;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 6px;
            text-transform: uppercase;
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

        [data-theme="light"] .notice-lang-box.si-box .lang-badge {
            background: rgba(217, 119, 6, 0.15);
            color: #b45309;
        }

        [data-theme="light"] .notice-lang-box.en-box .lang-badge {
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
        }

        .notice-lang-header .lang-title {
            font-size: 0.84rem;
            font-weight: 700;
            color: #cbd5e1;
        }

        [data-theme="light"] .notice-lang-header .lang-title {
            color: #475569;
        }

        .notice-point-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 10px;
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

        [data-theme="light"] .notice-point-item {
            color: #1e293b;
        }

        [data-theme="light"] .notice-point-item.hostel-point {
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

        [data-theme="light"] .notice-point-item strong {
            color: #0f172a;
        }

        [data-theme="light"] .notice-point-item.academic-point strong {
            color: #b45309;
        }

        .academic-countdown-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            max-width: 440px;
            margin-bottom: 14px;
        }

        .academic-countdown-box {
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid rgba(245, 158, 11, 0.28);
            border-radius: 12px;
            padding: 10px 6px;
            text-align: center;
        }

        [data-theme="light"] .academic-countdown-box {
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(217, 119, 6, 0.25);
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.08);
        }

        .academic-countdown-number {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1.1;
            background: linear-gradient(180deg, #fbbf24 0%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        [data-theme="light"] .academic-countdown-number {
            background: linear-gradient(180deg, #d97706 0%, #ea580c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
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

        [data-theme="light"] .loan-report-notice-card {
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

        [data-theme="light"] .loan-badge {
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

        [data-theme="light"] .loan-status-pill {
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

        [data-theme="light"] .loan-card-title {
            color: #064e3b;
        }

        [data-theme="light"] .loan-card-title .title-highlight-emerald {
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

        [data-theme="light"] .loan-message-box {
            background: rgba(255, 255, 255, 0.85);
            border-color: rgba(16, 185, 129, 0.25);
        }

        .loan-statement-si {
            font-size: 1.02rem;
            line-height: 1.65;
            color: #e2e8f0;
            font-weight: 500;
        }

        [data-theme="light"] .loan-statement-si {
            color: #1e293b;
        }

        .loan-statement-si strong {
            color: #6ee7b7;
            font-weight: 700;
        }

        [data-theme="light"] .loan-statement-si strong {
            color: #047857;
        }

        .loan-statement-en {
            font-size: 0.9rem;
            line-height: 1.55;
            color: #94a3b8;
        }

        [data-theme="light"] .loan-statement-en {
            color: #64748b;
        }

        .loan-statement-en strong {
            color: #cbd5e1;
            font-weight: 600;
        }

        [data-theme="light"] .loan-statement-en strong {
            color: #334155;
        }

        .text-emerald {
            color: #34d399 !important;
        }

        [data-theme="light"] .text-emerald {
            color: #059669 !important;
        }

        /* ========== ANNOUNCEMENTS SWIPER STYLING ========== */
        .announcements-swiper-wrapper {
            position: relative;
            margin-bottom: 24px;
        }

        .adminAnnouncementsSwiper {
            overflow: hidden;
            border-radius: 20px;
        }

        .adminAnnouncementsSwiper .swiper-wrapper {
            display: flex;
            align-items: stretch;
        }

        .adminAnnouncementsSwiper .swiper-slide {
            height: auto;
            display: flex;
        }

        .adminAnnouncementsSwiper .swiper-slide .batch-photo-card,
        .adminAnnouncementsSwiper .swiper-slide .academic-notice-card,
        .adminAnnouncementsSwiper .swiper-slide .loan-report-notice-card {
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

        .admin-announcement-pagination {
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

        [data-theme="light"] .custom-swiper-pill {
            background: rgba(0, 0, 0, 0.05);
            border-color: rgba(0, 0, 0, 0.1);
            color: #64748b;
        }

        [data-theme="light"] .custom-swiper-pill.swiper-pagination-bullet-active {
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

        [data-theme="light"] .swiper-nav-btn {
            background: rgba(0, 0, 0, 0.05);
            border-color: rgba(0, 0, 0, 0.12);
            color: #475569;
        }

        [data-theme="light"] .swiper-nav-btn:hover {
            background: rgba(99, 102, 241, 0.15);
            color: #4338ca;
        }

        @media (max-width: 576px) {
            .batch-photo-card,
            .academic-notice-card {
                padding: 18px 14px;
                margin-bottom: 20px;
            }
            .countdown-grid,
            .academic-countdown-grid {
                gap: 8px;
            }
            .countdown-box,
            .academic-countdown-box {
                padding: 8px 4px;
            }
            .countdown-number,
            .academic-countdown-number {
                font-size: 1.35rem;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <?php include("sidebar.php"); ?>

    <!-- Main Content -->
    <div id="content">
        <!-- Topbar -->
        <div class="topbar">
            <h1><i class="fas fa-chart-line me-2"></i>Dashboard</h1>
            <div class="topbar-right">
                <button class="theme-toggle" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="processing?logout=1" class="logout-btn">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>

        <!-- Page Content -->
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <h2>Welcome to Admin Panel</h2>
            </div>

            <!-- Announcements Swiper Slider (Batch Photo & Academic Notice in same line) -->
            <div class="announcements-swiper-wrapper">
                <div class="swiper adminAnnouncementsSwiper">
                    <div class="swiper-wrapper">
                        <!-- Slide 1: Batch Photo Card -->
                        <div class="swiper-slide">
                            <div class="batch-photo-card">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="batch-photo-badge">
                                            <i class="fas fa-camera-retro"></i> Batch Photo • Batch 20/21
                                        </span>
                                        <span class="batch-date-pill">
                                            <i class="far fa-calendar-alt"></i> 2026.11.07 (Saturday)
                                        </span>
                                    </div>
                                    <span style="font-size: 0.8rem; color: #94a3b8;"><i class="fas fa-clock me-1"></i> 08:30 AM Onwards</span>
                                </div>

                                <div class="d-flex align-items-start gap-3 mt-3">
                                    <div class="batch-camera-icon d-none d-sm-inline-flex">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h3 class="batch-photo-title mb-3">
                                            <span class="title-highlight">Batch Photo 2026:</span> Freezing Memories, Celebrating One Unbroken Bond! 📸
                                        </h3>

                                        <!-- Countdown Grid -->
                                        <div class="countdown-grid" id="adminBpCountdown">
                                            <div class="countdown-box">
                                                <div class="countdown-number" id="adminBpDays">--</div>
                                                <div class="countdown-label">Days<span class="countdown-label-si">දින</span></div>
                                            </div>
                                            <div class="countdown-box">
                                                <div class="countdown-number" id="adminBpHours">--</div>
                                                <div class="countdown-label">Hours<span class="countdown-label-si">පැය</span></div>
                                            </div>
                                            <div class="countdown-box">
                                                <div class="countdown-number" id="adminBpMinutes">--</div>
                                                <div class="countdown-label">Minutes<span class="countdown-label-si">මිනිත්තු</span></div>
                                            </div>
                                            <div class="countdown-box">
                                                <div class="countdown-number" id="adminBpSeconds">--</div>
                                                <div class="countdown-label">Seconds<span class="countdown-label-si">තත්පර</span></div>
                                            </div>
                                        </div>

                                        <!-- Action Links -->
                                        <div class="d-flex flex-wrap gap-2 mt-3">
                                            <a href="./batch_photo_email" class="btn btn-primary btn-sm fw-bold" style="text-decoration:none;">
                                                <i class="fas fa-paper-plane me-1"></i> Send Notice to All Students
                                            </a>
                                            <a href="./batch_photo_email" class="btn btn-outline-success btn-sm fw-bold" style="text-decoration:none;">
                                                <i class="fas fa-vial me-1"></i> Test Send (ITT/2021/106)
                                            </a>
                                            <a href="./batch_photo_demo" target="_blank" class="btn btn-outline-info btn-sm fw-bold" style="text-decoration:none;">
                                                <i class="fas fa-eye me-1"></i> Preview Email Template
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 2: Academic Notice Card -->
                        <div class="swiper-slide">
                            <div class="academic-notice-card" id="adminAcademicNoticeCard">
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
                                            <div class="academic-countdown-grid" id="adminAcademicCountdown">
                                                <div class="academic-countdown-box">
                                                    <div class="academic-countdown-number" id="adminAcDays">--</div>
                                                    <div class="countdown-label">Days<span class="countdown-label-si">දින</span></div>
                                                </div>
                                                <div class="academic-countdown-box">
                                                    <div class="academic-countdown-number" id="adminAcHours">--</div>
                                                    <div class="countdown-label">Hours<span class="countdown-label-si">පැය</span></div>
                                                </div>
                                                <div class="academic-countdown-box">
                                                    <div class="academic-countdown-number" id="adminAcMinutes">--</div>
                                                    <div class="countdown-label">Minutes<span class="countdown-label-si">මිනිත්තු</span></div>
                                                </div>
                                                <div class="academic-countdown-box">
                                                    <div class="academic-countdown-number" id="adminAcSeconds">--</div>
                                                    <div class="countdown-label">Seconds<span class="countdown-label-si">තත්පර</span></div>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-wrap gap-2 mt-3">
                                                <span class="badge bg-secondary py-2 px-3" style="font-size: 0.8rem;">
                                                    <i class="fas fa-boxes-packing me-1 text-warning"></i> Full Clearance Required
                                                </span>
                                                <span class="badge bg-secondary py-2 px-3" style="font-size: 0.8rem;">
                                                    <i class="fas fa-key me-1 text-info"></i> Room Key Handover
                                                </span>
                                                <span class="badge bg-secondary py-2 px-3" style="font-size: 0.8rem;">
                                                    <i class="far fa-clock me-1 text-danger"></i> Deadline: 2026.11.30 23:59
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 3: Loans & Repayments Report Notice Card -->
                        <div class="swiper-slide">
                            <div class="loan-report-notice-card" id="adminLoanReportNoticeCard">
                                <div class="loan-report-content">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="loan-badge">
                                                <i class="fas fa-hand-holding-dollar"></i> Loans & Repayments • Batch 20/21
                                            </span>
                                            <span id="adminPopupStatusBadge" class="loan-status-pill" style="<?php echo $display_loan_popup ? 'background: rgba(16, 185, 129, 0.2); border-color: rgba(52, 211, 153, 0.5); color: #34d399;' : 'background: rgba(239, 68, 68, 0.2); border-color: rgba(248, 113, 113, 0.5); color: #f87171;'; ?> animation: none;">
                                                <i class="fas <?php echo $display_loan_popup ? 'fa-check-circle' : 'fa-ban'; ?>"></i> Popup: <span id="adminPopupStatusText"><?php echo $display_loan_popup ? 'Enabled (Auto-Show)' : 'Disabled (Hidden)'; ?></span>
                                            </span>
                                        </div>
                                        <span class="badge bg-secondary py-2 px-3" style="font-size: 0.8rem;">
                                            <i class="fas fa-chart-pie me-1 text-info"></i> Financial Transparency
                                        </span>
                                    </div>

                                    <div class="d-flex align-items-start gap-3 mt-3">
                                        <div class="loan-icon d-none d-sm-inline-flex">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h3 class="loan-card-title mb-3">
                                                <span class="title-highlight-emerald">Batch 20/21:</span> ණය සහ නැවත ගෙවීම් වාර්තාව (Loans & Repayments Report) 📋
                                            </h3>

                                            <div class="loan-message-box">
                                                <div class="loan-statement-si mb-2">
                                                    <i class="fas fa-bullhorn text-emerald me-2"></i>
                                                    <strong>20/21 කණ්ඩායම විසින්</strong> විවිධ හේතුන් නිසා ලබාගන්නා ලද ණය මුදල් සහ එහි නැවත ගෙවිම් විස්තර පිලිබද වාර්තාව <strong>දැන් මෙහි සක්‍රීයව නැරඹිය හැක</strong>.
                                                </div>
                                                <div class="loan-statement-en">
                                                    <i class="fas fa-info-circle text-info me-2"></i>
                                                    A detailed comprehensive report regarding the loans obtained by <strong>Batch 20/21</strong> and their settlement status is now published and active.
                                                </div>
                                            </div>

                                            <!-- Dashboard Loan Popup Toggle Control Box -->
                                            <div class="mt-3 p-3 rounded-3" style="background: rgba(15, 23, 42, 0.65); border: 1px solid rgba(16, 185, 129, 0.35); backdrop-filter: blur(8px);">
                                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: rgba(16, 185, 129, 0.18); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; font-size: 1.2rem;">
                                                            <i class="fas fa-window-restore"></i>
                                                        </div>
                                                        <div>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <strong class="text-light" style="font-size: 0.92rem;">Student Dashboard Loan Popup Modal</strong>
                                                                <span id="adminPopupPill" class="badge <?php echo $display_loan_popup ? 'bg-success' : 'bg-secondary'; ?>">
                                                                    <?php echo $display_loan_popup ? 'Active' : 'Disabled'; ?>
                                                                </span>
                                                            </div>
                                                            <small class="text-secondary d-block" id="adminPopupHelpText">
                                                                <?php echo $display_loan_popup 
                                                                    ? 'The Loan Notice popup will automatically pop up when students load their dashboard.' 
                                                                    : 'Popup is disabled. It will NOT automatically open on the student dashboard.'; ?>
                                                            </small>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex align-items-center gap-3 ms-auto">
                                                        <div class="form-check form-switch m-0" style="font-size: 1.25rem;">
                                                            <input class="form-check-input" type="checkbox" role="switch" id="adminLoanPopupSwitch" <?php echo $display_loan_popup ? 'checked' : ''; ?> style="cursor: pointer; width: 3em; height: 1.6em;">
                                                        </div>
                                                        <button type="button" class="btn btn-outline-info btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#adminLoanPopupPreviewModal" title="Preview Student Popup">
                                                            <i class="fas fa-eye me-1"></i> Preview Popup
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-wrap gap-2 mt-3">
                                                <span class="badge bg-secondary py-2 px-3" style="font-size: 0.8rem;">
                                                    <i class="fas fa-coins me-1 text-warning"></i> Total: Rs. <?php echo number_format($total_loan_amount, 2); ?>
                                                </span>
                                                <span class="badge bg-secondary py-2 px-3" style="font-size: 0.8rem;">
                                                    <i class="fas fa-receipt me-1 text-success"></i> Paid: Rs. <?php echo number_format($total_loans_paid, 2); ?>
                                                </span>
                                                <span class="badge bg-secondary py-2 px-3" style="font-size: 0.8rem;">
                                                    <i class="fas fa-clock me-1 text-info"></i> Balance: Rs. <?php echo number_format($total_admin_loan_balance, 2); ?>
                                                </span>
                                            </div>

                                            <!-- Action Links -->
                                            <div class="d-flex flex-wrap gap-2 mt-3">
                                                <button type="button" class="btn btn-warning btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#adminLoanDetailsModal">
                                                    <i class="fas fa-table-list me-1"></i> View Loan Details
                                                </button>
                                                <a href="./loans" class="btn btn-primary btn-sm fw-bold" style="text-decoration:none;">
                                                    <i class="fas fa-money-bill-wave me-1"></i> Manage Loans
                                                </a>
                                                <a href="./loan_report_email" class="btn btn-success btn-sm fw-bold" style="text-decoration:none;">
                                                    <i class="fas fa-paper-plane me-1"></i> Send Notice to Students
                                                </a>
                                                <a href="./loan_report_demo" target="_blank" class="btn btn-outline-info btn-sm fw-bold" style="text-decoration:none;">
                                                    <i class="fas fa-eye me-1"></i> Preview Template
                                                </a>
                                                <a href="./settings" class="btn btn-outline-light btn-sm fw-bold" style="text-decoration:none;">
                                                    <i class="fas fa-cog me-1"></i> Settings
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
                    <div class="swiper-pagination admin-announcement-pagination"></div>
                    <div class="swiper-nav-buttons">
                        <button type="button" class="swiper-nav-btn admin-swiper-btn-prev" title="Previous Announcement">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" class="swiper-nav-btn admin-swiper-btn-next" title="Next Announcement">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <!-- Total Payments Card -->
                <div class="stat-card success">
                    <div class="stat-card-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-label">Total Payments</div>
                    <div class="stat-value">Rs. <?php echo number_format($tot_paid, 2); ?></div>
                    <div class="stat-change">All collected amounts</div>
                </div>

                <!-- Students Card -->
                <div class="stat-card warning">
                    <div class="stat-card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-label">Total Students</div>
                    <div class="stat-value"><?php echo $total_students; ?></div>
                    <div class="stat-change">Registered students</div>
                </div>

                <!-- Reasons Card -->
                <div class="stat-card">
                    <div class="stat-card-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="stat-label">Total Reasons</div>
                    <div class="stat-value"><?php echo $total_reasons; ?></div>
                    <div class="stat-change">Payment reasons</div>
                </div>

                <!-- Loans Card -->
                <div class="stat-card danger">
                    <div class="stat-card-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-label">Total Loans</div>
                    <div class="stat-value"><?php echo $total_loans; ?></div>
                    <div class="stat-change">Rs. <?php echo number_format($total_loan_amount, 2); ?> total</div>
                </div>

                <!-- Loans Paid Card -->
                <div class="stat-card info">
                    <div class="stat-card-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="stat-label">Loans Paid</div>
                    <div class="stat-value">Rs. <?php echo number_format($total_loans_paid, 2); ?></div>
                    <div class="stat-change">Paid amount</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div style="margin-top: 40px;">
                <h3 style="font-size: 1.3rem; margin-bottom: 20px; color: var(--primary-light);">
                    <i class="fas fa-rocket me-2"></i>Quick Actions
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <a href="./add_reason" class="btn btn-primary" style="text-decoration: none;">
                        <i class="fas fa-plus me-2"></i>Add Reason
                    </a>
                    <a href="./mark_paid" class="btn btn-primary" style="text-decoration: none;">
                        <i class="fas fa-check me-2"></i>Mark as Paid
                    </a>
                    <a href="./loans" class="btn btn-primary" style="text-decoration: none;">
                        <i class="fas fa-money-bill-wave me-2"></i>Manage Loans
                    </a>
                    <a href="./reason_list" class="btn btn-primary" style="text-decoration: none;">
                        <i class="fas fa-download me-2"></i>Export Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Loan Details Modal -->
    <div class="modal fade" id="adminLoanDetailsModal" tabindex="-1" aria-labelledby="adminLoanDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="background: var(--dark-card); border: 1px solid var(--dark-border); color: var(--text-primary);">
                <div class="modal-header" style="border-bottom: 1px solid var(--dark-border);">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(16, 185, 129, 0.2); color: #34d399; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                            <i class="fas fa-hand-holding-dollar"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="adminLoanDetailsModalLabel">
                                Batch 20/21: Loans & Repayments Report
                            </h5>
                            <span class="small" style="color: #34d399;">Financial Overview & Breakdown</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Top Summary Grid -->
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.04); border: 1px solid var(--dark-border);">
                                <div class="text-secondary small text-uppercase fw-bold mb-1">Total Loans</div>
                                <div class="h5 fw-bold mb-0 text-white">Rs. <?php echo number_format($total_loan_amount, 2); ?></div>
                                <div class="small text-secondary"><?php echo count($admin_loans_list); ?> record(s)</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 rounded" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3);">
                                <div class="text-secondary small text-uppercase fw-bold mb-1">Total Settled</div>
                                <div class="h5 fw-bold mb-0" style="color: #34d399;">Rs. <?php echo number_format($total_loans_paid, 2); ?></div>
                                <div class="small text-secondary">Repaid amount</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 rounded" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3);">
                                <div class="text-secondary small text-uppercase fw-bold mb-1">Balance Due</div>
                                <div class="h5 fw-bold mb-0" style="color: #fbbf24;">Rs. <?php echo number_format($total_admin_loan_balance, 2); ?></div>
                                <div class="small text-secondary">Outstanding balance</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 rounded" style="background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.3);">
                                <div class="text-secondary small text-uppercase fw-bold mb-1">Progress</div>
                                <div class="h5 fw-bold mb-0 text-white"><?php echo $admin_overall_loan_progress; ?>%</div>
                                <div class="progress mt-2" style="height: 6px; background: rgba(255, 255, 255, 0.1);">
                                    <div class="progress-bar bg-success" style="width: <?php echo min(100, $admin_overall_loan_progress); ?>%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loans Table -->
                    <div class="table-responsive mb-3" style="border-radius: 10px; -webkit-overflow-scrolling: touch;">
                        <div class="d-md-none small mb-2 p-2 rounded d-flex align-items-center justify-content-between" style="background: rgba(99, 102, 241, 0.1); border: 1px dashed rgba(99, 102, 241, 0.35); color: #818cf8;">
                            <span><i class="fas fa-arrows-left-right me-1"></i> Swipe horizontally to view all columns</span>
                            <span class="badge bg-dark text-secondary">7 Columns</span>
                        </div>
                        <table class="table table-striped table-hover text-nowrap align-middle">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th style="min-width: 180px; white-space: normal;">Reason / Lender Party</th>
                                    <th class="text-end">Total (Rs.)</th>
                                    <th class="text-end">Paid (Rs.)</th>
                                    <th class="text-end">Balance (Rs.)</th>
                                    <th style="width: 140px;">Progress</th>
                                    <th class="text-center" style="width: 120px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($admin_loans_list)): ?>
                                    <?php foreach ($admin_loans_list as $idx => $loan): 
                                        $l_tot = (float)$loan['total'];
                                        $l_paid = (float)$loan['paid'];
                                        $l_bal = (float)$loan['balance'];
                                        $l_prog = $l_tot > 0 ? round(($l_paid / $l_tot) * 100, 1) : 0;
                                        
                                        if ($l_bal <= 0) {
                                            $badge_bg = 'bg-success';
                                            $status_txt = 'Fully Settled';
                                        } elseif ($l_paid > 0) {
                                            $badge_bg = 'bg-warning text-dark';
                                            $status_txt = 'Partially Paid';
                                        } else {
                                            $badge_bg = 'bg-danger';
                                            $status_txt = 'Unpaid';
                                        }
                                    ?>
                                        <tr>
                                            <td class="text-center fw-bold text-secondary"><?php echo $loan['id']; ?></td>
                                            <td>
                                                <strong class="text-white"><?php echo htmlspecialchars($loan['reason']); ?></strong>
                                            </td>
                                            <td class="text-end fw-bold">Rs. <?php echo number_format($l_tot, 2); ?></td>
                                            <td class="text-end fw-bold text-success">Rs. <?php echo number_format($l_paid, 2); ?></td>
                                            <td class="text-end fw-bold text-warning">Rs. <?php echo number_format($l_bal, 2); ?></td>
                                            <td>
                                                <div class="small fw-bold mb-1"><?php echo $l_prog; ?>%</div>
                                                <div class="progress" style="height: 5px; background: rgba(255, 255, 255, 0.1);">
                                                    <div class="progress-bar bg-success" style="width: <?php echo min(100, $l_prog); ?>%;"></div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?php echo $badge_bg; ?> px-2 py-1">
                                                    <?php echo $status_txt; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-secondary">
                                            No loans registered.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr style="border-top: 2px solid var(--dark-border); font-weight: 800;">
                                    <td colspan="2" class="text-end">Total:</td>
                                    <td class="text-end">Rs. <?php echo number_format($total_loan_amount, 2); ?></td>
                                    <td class="text-end text-success">Rs. <?php echo number_format($total_loans_paid, 2); ?></td>
                                    <td class="text-end text-warning">Rs. <?php echo number_format($total_admin_loan_balance, 2); ?></td>
                                    <td colspan="2" class="text-center text-success"><?php echo $admin_overall_loan_progress; ?>% Settled</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--dark-border);">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="../download_loan_pdf.php" class="btn btn-success">
                        <i class="fas fa-file-pdf me-1"></i> Download as PDF
                    </a>
                    <a href="./loans" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Edit & Update in Loans Manager
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme toggle
        const themeToggle = document.querySelector('.theme-toggle');
        const html = document.documentElement;

        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'dark';
        html.setAttribute('data-theme', savedTheme);
        updateThemeIcon();

        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon();
        });

        function updateThemeIcon() {
            const theme = html.getAttribute('data-theme');
            const icon = themeToggle.querySelector('i');
            icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }

        // Admin Batch Photo Countdown
        const adminPhotoDate = new Date('2026-11-07T08:30:00+05:30').getTime();
        function updateAdminCountdown() {
            const now = new Date().getTime();
            const diff = adminPhotoDate - now;

            const daysEl = document.getElementById('adminBpDays');
            const hoursEl = document.getElementById('adminBpHours');
            const minEl = document.getElementById('adminBpMinutes');
            const secEl = document.getElementById('adminBpSeconds');

            if (!daysEl || !hoursEl || !minEl || !secEl) return;

            if (diff <= 0) {
                daysEl.textContent = '00';
                hoursEl.textContent = '00';
                minEl.textContent = '00';
                secEl.textContent = '00';
                return;
            }

            daysEl.textContent = String(Math.floor(diff / (1000 * 60 * 60 * 24))).padStart(2, '0');
            hoursEl.textContent = String(Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
            minEl.textContent = String(Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
            secEl.textContent = String(Math.floor((diff % (1000 * 60)) / 1000)).padStart(2, '0');
        }
        updateAdminCountdown();
        setInterval(updateAdminCountdown, 1000);

        // Admin Academic Completion Countdown
        const adminAcademicDate = new Date('2026-11-30T23:59:59+05:30').getTime();
        function updateAdminAcademicCountdown() {
            const now = new Date().getTime();
            const diff = adminAcademicDate - now;

            const daysEl = document.getElementById('adminAcDays');
            const hoursEl = document.getElementById('adminAcHours');
            const minEl = document.getElementById('adminAcMinutes');
            const secEl = document.getElementById('adminAcSeconds');

            if (!daysEl || !hoursEl || !minEl || !secEl) return;

            if (diff <= 0) {
                daysEl.textContent = '00';
                hoursEl.textContent = '00';
                minEl.textContent = '00';
                secEl.textContent = '00';
                return;
            }

            daysEl.textContent = String(Math.floor(diff / (1000 * 60 * 60 * 24))).padStart(2, '0');
            hoursEl.textContent = String(Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
            minEl.textContent = String(Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
            secEl.textContent = String(Math.floor((diff % (1000 * 60)) / 1000)).padStart(2, '0');
        }
        updateAdminAcademicCountdown();
        setInterval(updateAdminAcademicCountdown, 1000);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        // Admin Announcements Swiper (Batch Photo & Academic Notice in same line)
        if (typeof Swiper !== 'undefined') {
            const adminAnnouncementSwiper = new Swiper('.adminAnnouncementsSwiper', {
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
                    el: '.admin-announcement-pagination',
                    clickable: true,
                    renderBullet: function (index, className) {
                        const labels = [
                            '<i class="fas fa-camera-retro me-1"></i> Batch Photo 2026',
                            '<i class="fas fa-graduation-cap me-1"></i> Academic & Hostel Notice',
                            '<i class="fas fa-hand-holding-dollar me-1"></i> Loans & Repayments'
                        ];
                        return '<button type="button" class="' + className + ' custom-swiper-pill">' + labels[index] + '</button>';
                    },
                },
                navigation: {
                    nextEl: '.admin-swiper-btn-next',
                    prevEl: '.admin-swiper-btn-prev',
                },
            });
        }
    </script>
    <!-- Loan Notice Preview Modal for Admin -->
    <div class="modal fade" id="adminLoanPopupPreviewModal" tabindex="-1" aria-labelledby="adminLoanPopupPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background: #111827; border: 1px solid rgba(16, 185, 129, 0.45); color: #e9ecef; border-radius: 16px; box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 35px rgba(16, 185, 129, 0.25);">
                <div class="modal-header pb-2" style="border-bottom: 1px solid rgba(16, 185, 129, 0.2);">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-success py-2 px-3">
                            <i class="fas fa-hand-holding-dollar me-1"></i> LOANS & REPAYMENTS • BATCH 20/21
                        </span>
                        <span class="badge bg-info py-2 px-3">
                            <i class="fas fa-eye me-1"></i> Exact Student Popup Preview
                        </span>
                    </div>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="p-3 rounded-3 d-none d-sm-inline-flex" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; font-size: 1.5rem;">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="fw-bold mb-2 text-light">
                                <span style="color: #34d399;">Batch 20/21:</span> Loans & Repayments Report 📋
                            </h4>

                            <div class="p-3 rounded-3 mb-3" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(16, 185, 129, 0.25);">
                                <div class="row g-2 text-center">
                                    <div class="col-6 col-md-3">
                                        <div class="p-2 rounded" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255, 255, 255, 0.08);">
                                            <span class="d-block small text-secondary">Total Borrowed</span>
                                            <strong class="text-light">Rs. <?php echo number_format($total_loan_amount, 2); ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="p-2 rounded" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255, 255, 255, 0.08);">
                                            <span class="d-block small text-secondary">Settled</span>
                                            <strong class="text-success">Rs. <?php echo number_format($total_loans_paid, 2); ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="p-2 rounded" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255, 255, 255, 0.08);">
                                            <span class="d-block small text-secondary">Balance Due</span>
                                            <strong class="text-warning">Rs. <?php echo number_format($total_admin_loan_balance, 2); ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="p-2 rounded" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255, 255, 255, 0.08);">
                                            <span class="d-block small text-secondary">Repaid Rate</span>
                                            <strong class="text-info"><?php echo $admin_overall_loan_progress; ?>%</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p class="text-secondary small mb-3">
                                When enabled, this popup will automatically display to every student who visits their dashboard.
                            </p>

                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-success btn-sm fw-bold" data-bs-dismiss="modal">
                                    <i class="fas fa-check me-1"></i> Close Preview
                                </button>
                                <a href="../download_loan_pdf.php" target="_blank" class="btn btn-outline-light btn-sm">
                                    <i class="fas fa-file-pdf me-1 text-danger"></i> Download PDF
                                </a>
                                <a href="../index.php" target="_blank" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-external-link-alt me-1"></i> Visit Student Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Toast Notification -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11000;">
        <div id="adminActionToast" class="toast align-items-center text-white bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i id="adminToastIcon" class="fas fa-check-circle text-success fs-5"></i>
                    <span id="adminToastMsg">Status updated.</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
        // Loan Popup Switch Live Toggle
        const adminLoanPopupSwitch = document.getElementById('adminLoanPopupSwitch');
        const adminPopupStatusBadge = document.getElementById('adminPopupStatusBadge');
        const adminPopupStatusText = document.getElementById('adminPopupStatusText');
        const adminPopupPill = document.getElementById('adminPopupPill');
        const adminPopupHelpText = document.getElementById('adminPopupHelpText');

        function showAdminToast(msg, isSuccess = true) {
            const toastEl = document.getElementById('adminActionToast');
            const msgEl = document.getElementById('adminToastMsg');
            const iconEl = document.getElementById('adminToastIcon');

            if (!toastEl) return;
            msgEl.textContent = msg;

            if (isSuccess) {
                toastEl.className = 'toast align-items-center text-white bg-success border-0';
                iconEl.className = 'fas fa-check-circle text-white fs-5';
            } else {
                toastEl.className = 'toast align-items-center text-white bg-danger border-0';
                iconEl.className = 'fas fa-exclamation-triangle text-white fs-5';
            }

            const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
            toast.show();
        }

        if (adminLoanPopupSwitch) {
            adminLoanPopupSwitch.addEventListener('change', async function() {
                const isChecked = this.checked;
                this.disabled = true;

                try {
                    const response = await fetch('api_toggle_setting.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            key: 'display_loan_popup',
                            value: isChecked ? '1' : '0'
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        const enabled = data.is_enabled;
                        if (enabled) {
                            adminPopupStatusBadge.style.background = 'rgba(16, 185, 129, 0.2)';
                            adminPopupStatusBadge.style.borderColor = 'rgba(52, 211, 153, 0.5)';
                            adminPopupStatusBadge.style.color = '#34d399';
                            adminPopupStatusBadge.innerHTML = '<i class="fas fa-check-circle"></i> Popup: <span id="adminPopupStatusText">Enabled (Auto-Show)</span>';
                            
                            adminPopupPill.className = 'badge bg-success';
                            adminPopupPill.textContent = 'Active';

                            adminPopupHelpText.textContent = 'The Loan Notice popup will automatically pop up when students load their dashboard.';
                        } else {
                            adminPopupStatusBadge.style.background = 'rgba(239, 68, 68, 0.2)';
                            adminPopupStatusBadge.style.borderColor = 'rgba(248, 113, 113, 0.5)';
                            adminPopupStatusBadge.style.color = '#f87171';
                            adminPopupStatusBadge.innerHTML = '<i class="fas fa-ban"></i> Popup: <span id="adminPopupStatusText">Disabled (Hidden)</span>';
                            
                            adminPopupPill.className = 'badge bg-secondary';
                            adminPopupPill.textContent = 'Disabled';

                            adminPopupHelpText.textContent = 'Popup is disabled. It will NOT automatically open on the student dashboard.';
                        }
                        showAdminToast(data.message, true);
                    } else {
                        showAdminToast(data.message || 'Failed to update setting', false);
                        adminLoanPopupSwitch.checked = !isChecked;
                    }
                } catch (e) {
                    console.error(e);
                    showAdminToast('Network error while toggling loan popup setting.', false);
                    adminLoanPopupSwitch.checked = !isChecked;
                } finally {
                    adminLoanPopupSwitch.disabled = false;
                }
            });
        }
    </script>
</body>

</html>