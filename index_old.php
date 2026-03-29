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
    $sql_reason = "SELECT * FROM reasons order by id desc";
    $result_reason = $conn->query($sql_reason);
    if ($result_reason->num_rows > 0) {
        while ($row_reason = $result_reason->fetch_assoc()) {
            $reason_id = $row_reason["id"];
            $reason = $row_reason["reason"];
            $reason_price = $row_reason["price"];
            $tot += $reason_price;


            $sql_payment = "SELECT * FROM payments WHERE reg_no = '{$reg_no}' and reason_id = $reason_id";
            $result_payment = $conn->query($sql_payment);
            if ($result_payment->num_rows > 0) {
                $status = "paid";
                $status_color = "success";
            } else {
                $status = "Unpaid";
                $status_color = "danger";
            }

            $table_row .= "
            <tr>
                <td>{$reason}</td>
                <td>Rs. {$reason_price}</td>
                <td><span class=\"p-3 badge bg-{$status_color}\">{$status}</span></td>
            </tr>
            ";
        }
    }
}

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
    </style>
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
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
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
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const total = <?php echo isset($tot) ? $tot : 25000; ?>;
                                const duration = 1200;
                                const frameRate = 30;
                                const steps = Math.ceil(duration / (1000 / frameRate));
                                let current = 0;
                                const increment = total / steps;
                                const el = document.getElementById('totalAmount');
                                if (el) {
                                    function animate() {
                                        current += increment;
                                        if (current < total) {
                                            el.textContent = 'Rs. ' + Math.floor(current);
                                            requestAnimationFrame(animate);
                                        } else {
                                            el.textContent = 'Rs. ' + total;
                                        }
                                    }
                                    animate();
                                }
                            });
                        </script>
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
                        <h4 class="contribution-amount" id="totalPaidAmount">Rs. <?php echo isset($tot_paid) ? $tot_paid : 15000; ?></h4>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const total = <?php echo isset($tot_paid) ? $tot_paid : 15000; ?>;
                                const duration = 1200;
                                const frameRate = 30;
                                const steps = Math.ceil(duration / (1000 / frameRate));
                                let current = 0;
                                const increment = total / steps;
                                const el = document.getElementById('totalPaidAmount');
                                if (el) {
                                    function animate() {
                                        current += increment;
                                        if (current < total) {
                                            el.textContent = 'Rs. ' + Math.floor(current);
                                            requestAnimationFrame(animate);
                                        } else {
                                            el.textContent = 'Rs. ' + total;
                                        }
                                    }
                                    animate();
                                }
                            });
                        </script>
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
                        <h4 class="contribution-amount" id="totalBalanceAmount">Rs. <?php echo isset($balance) ? $balance : 10000; ?></h4>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const total = <?php echo isset($balance) ? $balance : 10000; ?>;
                                const duration = 1200;
                                const frameRate = 30;
                                const steps = Math.ceil(duration / (1000 / frameRate));
                                let current = 0;
                                const increment = total / steps;
                                const el = document.getElementById('totalBalanceAmount');
                                if (el) {
                                    function animate() {
                                        current += increment;
                                        if (current < total) {
                                            el.textContent = 'Rs. ' + Math.floor(current);
                                            requestAnimationFrame(animate);
                                        } else {
                                            el.textContent = 'Rs. ' + total;
                                        }
                                    }
                                    animate();
                                }
                            });
                        </script>
                        <p class="contribution-label">Total Due / ගෙවීමට ඇති මුළු එකතුව</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contribution Tabs -->
        <ul class="nav nav-tabs" id="contributionTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="batch-fund-tab" data-bs-toggle="tab" data-bs-target="#batch-fund" type="button" role="tab">Batch Fund</button>
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

    <!-- Footer -->
    <footer class="scmp-footer">
        <div class="container">
            <div class="d-flex justify-content-center align-items-center mb-3">
                <img src="./assets/logo.png" alt="SCMP Logo" class="footer-logo">
            </div>
            <div class="text-center">
                <p>Student Contribution Management Portal</p>
                <p class="small">Copyright © Student Contribution Management Portal | V 2.0 |
                    Developed by Thisula Development
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
</body>

</html>