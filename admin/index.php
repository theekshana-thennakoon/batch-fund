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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    </script>
</body>

</html>