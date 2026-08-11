<?php
include("./assets/database.php");
date_default_timezone_set('Asia/Colombo');
session_start();
if (!isset($_SESSION["reg_no"])) {
    header("Location:splash");
} else {
    $reg_no = $_SESSION["reg_no"];

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
        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
        }

        .scmp-header {
            background-color: #427BFF;
            color: white;
            padding: 10px 0;
        }

        .scmp-logo {
            height: 80px;
            margin-right: 10px;
        }

        .scmp-footer {
            background-color: #3c3c3c;
            color: white;
            padding: 20px 0;
            margin-top: 30px;
        }

        .nav-link {
            color: white;
            margin-right: 15px;
        }

        .logout-btn {
            background-color: #dc3545;
            color: white;
            border: none;
        }

        .shield-icon {
            font-size: 24px;
            margin-right: 10px;
        }

        .info-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: white;
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
            border: 1px solid #eee;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
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
            color: #6c757d;
            margin: 0;
        }

        .nav-tabs .nav-link {
            color: #495057;
        }

        .nav-tabs .nav-link.active {
            font-weight: bold;
            color: #007bff;
        }

        .paid-badge {
            background-color: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 0.8rem;
        }

        .footer-logo {
            height: 50px;
        }

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
        }
    </style>
</head>

<body>

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
    <!-- Header --></script>
    <header class="scmp-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img src="assets/logo.png" alt="SCMP Logo" class="scmp-logo">
                    <!--<style>
                        .scmp-logo,
                        .footer-logo {
                            animation: rotateLogo 85s linear infinite;
                        }

                        @keyframes rotateLogo {
                            from {
                                transform: rotate(0deg);
                            }

                            to {
                                transform: rotate(360deg);
                            }
                        }
                    </style>-->
                    <span class="fw-bold h2 mt-2">Batch Fund (20/21)</span>
                </div>
                <div class="d-flex align-items-center">
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
            <h2 class="mb-0" id="studentContributionProfile">Student Contribution Profile</h2>
            <span id="studentContributionProfile" class="studentContributionProfile"></span>
            <!--<script>
                document.addEventListener('DOMContentLoaded', function() {
                    const text = "Student Contribution Profile";
                    const el = document.getElementById('studentContributionProfile');

                    function type() {
                        el.textContent = "";
                        let idx = 0;

                        function typing() {
                            if (idx < text.length) {
                                el.textContent += text[idx];
                                idx++;
                                setTimeout(typing, 60);
                            }
                        }
                        typing();
                    }
                    type();
                    setInterval(type, 3000);
                });
            </script>-->
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
                        <div class="col-8">: <?php echo $reg_no; ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Name</div>
                        <div class="col-8">: <?php echo $name; ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Card</div>
                        <div class="col-8">: <?php echo $card; ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Batch</div>
                        <div class="col-8">: <?php echo "2021"; ?></div>
                    </div>
                </div>
                <!--<div class="col-md-4">
                <div class="rank-box">
                <div class="rank-number"><?php echo "1"; ?></div>
                <div class="rank-text">Student Contribution Rank</div>
                </div>
            </div>-->
            </div>
        </div>
        <!--<script>
            document.addEventListener('DOMContentLoaded', function() {
                const colors = [
                    'linear-gradient(90deg, #dc3545 0%, #ff7675 100%)', // red gradient
                    'linear-gradient(90deg, #28a745 0%, #81c784 100%)', // green gradient
                    'linear-gradient(90deg, #427BFF 0%, #6a89cc 100%)' // blue gradient
                ];
                let idx = 0;
                setInterval(function() {
                    document.getElementById('infoCardBox').style.background = colors[idx];
                    idx = (idx + 1) % colors.length;
                }, 1000);
            });
        </script>-->

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
                                const total = <?php echo $tot; ?>;
                                const duration = 1200; // ms
                                const frameRate = 30; // fps
                                const steps = Math.ceil(duration / (1000 / frameRate));
                                let current = 0;
                                const increment = total / steps;
                                const el = document.getElementById('totalAmount');

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
                        <h4 class="contribution-amount" id="totalPaidAmount">Rs. <?php echo "{$tot_paid}"; ?></h4>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const total = <?php echo $tot_paid; ?>;
                                const duration = 1200; // ms
                                const frameRate = 30; // fps
                                const steps = Math.ceil(duration / (1000 / frameRate));
                                let current = 0;
                                const increment = total / steps;
                                const el = document.getElementById('totalPaidAmount');

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
                        <h4 class="contribution-amount" id="totalBalanceAmount">Rs. <?php echo "{$balance}"; ?></h4>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const total = <?php echo $balance; ?>;
                                const duration = 1200; // ms
                                const frameRate = 30; // fps
                                const steps = Math.ceil(duration / (1000 / frameRate));
                                let current = 0;
                                const increment = total / steps;
                                const el = document.getElementById('totalBalanceAmount');

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
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="web-app-2024-tab" data-bs-toggle="tab" data-bs-target="#web-app-2024-pane" type="button" role="tab"><i class="fas fa-globe me-1"></i> 2024 Portal (Web App)</button>
            </li>
            <!-- <li class="nav-item" role="presentation">
                <button class="nav-link" id="batch-function-tab" data-bs-toggle="tab" data-bs-target="#batch-function" type="button" role="tab">Batch Function</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="night-fund-tab" data-bs-toggle="tab" data-bs-target="#night-fund" type="button" role="tab">Night Fund</button>
            </li> -->
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="contributionTabsContent">
            <div class="tab-pane fade show active" id="batch-fund" role="tabpanel">
                <div class="info-card">
                    <h4>Contribution Details</h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr class="table-dark" style="background:#427BFF;">
                                    <th style="background:#427BFF;">Contribution</th>
                                    <th style="background:#427BFF;">Amount</th>
                                    <th style="background:#427BFF;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                echo $table_row;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 2024 Web App Details Pane -->
            <div class="tab-pane fade" id="web-app-2024-pane" role="tabpanel">
                <div class="mt-3">
                    <!-- Top Action Banner -->
                    <div class="info-card d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div>
                            <h4 class="mb-1"><i class="fas fa-globe text-primary me-2"></i>2024 Batch Fund Web App Details</h4>
                            <p class="text-secondary small mb-0">Live details from <strong>batch-fund-rusl.web.app</strong> for Registration No: <strong><?php echo htmlspecialchars($reg_no); ?></strong> (Password: <code>R2024</code>)</p>
                        </div>
                        <a href="https://batch-fund-rusl.web.app/" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3">
                            <i class="fas fa-external-link-alt me-1"></i> Open batch-fund-rusl.web.app
                        </a>
                    </div>

                    <!-- Loading Spinner -->
                    <div id="webApp2024Loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-secondary">Fetching 2024 portal details for <strong><?php echo htmlspecialchars($reg_no); ?></strong>...</p>
                    </div>

                    <div id="webApp2024Error" class="alert alert-danger d-none mb-3"></div>

                    <!-- Content Area once loaded -->
                    <div id="webApp2024Content" class="d-none">
                        <!-- Student Info & Rank Card Row -->
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="info-card h-100 mb-0">
                                    <h5 class="mb-3 text-primary"><i class="fas fa-user-graduate me-2"></i>2024 Student Profile</h5>
                                    <div class="row mb-2">
                                        <div class="col-5 fw-bold">Registration No</div>
                                        <div class="col-7">: <span id="wa2024_regNo"><?php echo htmlspecialchars($reg_no); ?></span></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-5 fw-bold">Name</div>
                                        <div class="col-7">: <span id="wa2024_name">-</span></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-5 fw-bold">Card</div>
                                        <div class="col-7">: <span id="wa2024_card">-</span></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-5 fw-bold">Batch</div>
                                        <div class="col-7">: 20/21</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card h-100 text-center d-flex flex-column align-items-center justify-content-center mb-0" id="wa2024_rankCard" style="border: 2px solid #ffc107;">
                                    <h2 class="display-3 fw-bold mb-0" id="wa2024_rankNum" style="color: #ffc107;">0</h2>
                                    <h5 class="fw-bold text-uppercase mt-1 mb-2" id="wa2024_rankTitle" style="color: #ffc107;">Student Contribution Rank (2024)</h5>
                                    <p class="small text-secondary mb-0">Boost your rank by paying your contribution!</p>
                                </div>
                            </div>
                        </div>

                        <!-- 2024 Summary Totals -->
                        <div class="row mb-3">
                            <div class="col-md-4 mb-2">
                                <div class="contribution-box mb-0">
                                    <div class="contribution-icon blue-icon">
                                        <i class="fas fa-calculator"></i>
                                    </div>
                                    <div>
                                        <h4 class="contribution-amount" id="wa2024_totalPayable">Rs. 0</h4>
                                        <p class="contribution-label">Total Payable (2024)</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="contribution-box mb-0">
                                    <div class="contribution-icon green-icon">
                                        <i class="fas fa-check-double"></i>
                                    </div>
                                    <div>
                                        <h4 class="contribution-amount" id="wa2024_totalPaid">Rs. 0</h4>
                                        <p class="contribution-label">Total Paid (2024)</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="contribution-box mb-0">
                                    <div class="contribution-icon red-icon">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div>
                                        <h4 class="contribution-amount" id="wa2024_totalDue">Rs. 0</h4>
                                        <p class="contribution-label">Total Due (2024)</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sub Tabs for 2024 Web App (Fund, Function, Night Fund, Live Frame) -->
                        <div class="info-card">
                            <ul class="nav nav-pills mb-3" id="wa2024SubTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active me-2 mb-1" id="wa2024-fund-tab" type="button" data-target="#wa2024-fund">Batch Fund</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link me-2 mb-1" id="wa2024-function-tab" type="button" data-target="#wa2024-function">Batch Function</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link me-2 mb-1" id="wa2024-night-tab" type="button" data-target="#wa2024-night">Night Fund</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link mb-1" id="wa2024-iframe-tab" type="button" data-target="#wa2024-iframe-view"><i class="fas fa-window-maximize me-1"></i> Live Web App View</button>
                                </li>
                            </ul>

                            <div class="tab-content" id="wa2024SubTabsContent">
                                <!-- Fund Table -->
                                <div class="tab-pane fade show active" id="wa2024-fund" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Contribution Name</th>
                                                    <th>Amount</th>
                                                    <th class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="wa2024_fundTableBody">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Function Table -->
                                <div class="tab-pane fade" id="wa2024-function" role="tabpanel" style="display: none;">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Function Item</th>
                                                    <th>Amount</th>
                                                    <th class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="wa2024_functionTableBody">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Night Fund Table -->
                                <div class="tab-pane fade" id="wa2024-night" role="tabpanel" style="display: none;">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Night Fund Item</th>
                                                    <th>Amount</th>
                                                    <th class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="wa2024_nightTableBody">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Embedded Iframe View -->
                                <div class="tab-pane fade" id="wa2024-iframe-view" role="tabpanel" style="display: none;">
                                    <div class="ratio ratio-16x9 mt-2" style="min-height: 500px; border: 1px solid #ddd; border-radius: 6px; overflow: hidden;">
                                        <iframe src="https://batch-fund-rusl.web.app/" title="Batch Fund RUSL Web App" allowfullscreen></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let wa2024Loaded = false;

            async function loadWebApp2024Data() {
                if (wa2024Loaded) return;

                const regNo = '<?php echo isset($reg_no) ? $reg_no : ''; ?>';
                const loadingEl = document.getElementById('webApp2024Loading');
                const contentEl = document.getElementById('webApp2024Content');
                const errorEl = document.getElementById('webApp2024Error');

                if (!loadingEl || !contentEl) return;
                loadingEl.classList.remove('d-none');
                contentEl.classList.add('d-none');
                if (errorEl) errorEl.classList.add('d-none');

                try {
                    const loginUrl = `https://script.google.com/macros/s/AKfycbywpw10oeD48HtnRcblmRbaeiPDGqIG7m1NX4nYAjcPuzFT22QQSTQnaaSv1-9iGSrl_A/exec?action=login&username=${encodeURIComponent(regNo)}&password=R2024&ip=127.0.0.1`;
                    const loginRes = await fetch(loginUrl).then(r => r.json());

                    if (!loginRes || !loginRes.success || !loginRes.user) {
                        throw new Error(loginRes && loginRes.message ? loginRes.message : 'Registration number not found in 2024 Web App database.');
                    }

                    const userObj = loginRes.user;
                    const gender = userObj.gender || 'Male';
                    const sheetName = gender;

                    const regNoEl = document.getElementById('wa2024_regNo');
                    const nameEl = document.getElementById('wa2024_name');
                    const cardEl = document.getElementById('wa2024_card');

                    if (regNoEl) regNoEl.textContent = userObj.registrationNo || regNo;
                    if (nameEl) nameEl.textContent = ((userObj.initials || '') + ' ' + (userObj.lastName || '') + ' ' + (userObj.name ? '(' + userObj.name + ')' : '')).trim();
                    if (cardEl) cardEl.textContent = userObj.card || '-';

                    const rankUrl = `https://script.google.com/macros/s/AKfycbwsmNHqQakwbtj1Ji1TQD-t9YgM-4avZFfCCqNl7d0MjoHigfRMAU89NxskS4DZfnd0iA/exec?action=rankByRegNo&regNo=${encodeURIComponent(regNo)}`;

                    const functionApiUrl = (gender === 'Male') ?
                        'https://script.google.com/macros/s/AKfycbyCcLLb91bssd_jASLs0LodyLGdJtSJ2a6WCtAzAHZxPiKP61Q95S2EbtR7MpND_Dg8/exec' :
                        'https://script.google.com/macros/s/AKfycbwAJ3TRhju54secSe4A_co8-jhv7Pz5RYgI3FrfEaAzLbu-XRtknu-LrV1kjbqP5iP5/exec';

                    const fundApiUrl = 'https://script.google.com/macros/s/AKfycbyRAzak9wKD5foyM_1IFRR3LABgaD7ZuIfSAC40sQhH870g8tCB_9xbWyOAomLPm-2-/exec';
                    const nightFundApiUrl = 'https://script.google.com/macros/s/AKfycbwGSABRHjHsLCdMpHSzwYKuu7wBly1wikFIROwaiUq2hFLVEhR7U2JBHb_JznNonl-jDQ/exec';

                    const [rankData, functionData, fundData, nightFundData] = await Promise.all([
                        fetch(rankUrl).then(r => r.json()).catch(() => ({ rank: 0 })),
                        fetch(`${functionApiUrl}?registrationNo=${encodeURIComponent(regNo)}&sheetName=${sheetName}`).then(r => r.json()).catch(() => ({})),
                        fetch(`${fundApiUrl}?registrationNo=${encodeURIComponent(regNo)}&sheetName=${sheetName}`).then(r => r.json()).catch(() => ({})),
                        fetch(`${nightFundApiUrl}?registrationNo=${encodeURIComponent(regNo)}&sheetName=${sheetName}`).then(r => r.json()).catch(() => ({}))
                    ]);

                    const rank = rankData.rank || 0;
                    const rankNumEl = document.getElementById('wa2024_rankNum');
                    const rankTitleEl = document.getElementById('wa2024_rankTitle');
                    const rankCardEl = document.getElementById('wa2024_rankCard');
                    if (rankNumEl) rankNumEl.textContent = rank;

                    let rankColor = '#ffc107';
                    if (rank === 1) rankColor = '#ffd700';
                    else if (rank >= 2 && rank <= 10) rankColor = '#b6bdc1';
                    else if (rank >= 11 && rank <= 50) rankColor = '#cd7f32';
                    else if (rank >= 51 && rank <= 100) rankColor = '#2860dd';
                    else if (rank >= 101 && rank <= 200) rankColor = '#8e28dd';
                    else if (rank >= 201 && rank <= 300) rankColor = '#f0abcb';

                    if (rankNumEl) rankNumEl.style.color = rankColor;
                    if (rankTitleEl) rankTitleEl.style.color = rankColor;
                    if (rankCardEl) rankCardEl.style.borderColor = rankColor;

                    let totalPayable = 0;
                    let totalPaid = 0;

                    function processDataObj(dataObj, tableBodyId) {
                        const tbody = document.getElementById(tableBodyId);
                        if (!tbody) return;
                        tbody.innerHTML = '';
                        let idx = 1;

                        for (const key in dataObj) {
                            if (dataObj.hasOwnProperty(key) && !['Registration No', 'Name', 'Card', 'error', 'No', 'Title', 'Last Name', 'Initials', 'Address', 'Council Road', 'Town', 'Distric', 'නම'].includes(key)) {
                                const amountMatch = key.match(/Rs\.(\d+)/);
                                const amount = amountMatch ? parseInt(amountMatch[1]) : 0;
                                if (amount !== 0) {
                                    const isPaid = !!dataObj[key];
                                    totalPayable += amount;
                                    if (isPaid) totalPaid += amount;

                                    const itemName = key.replace(/Rs\.\d+/g, '').trim();
                                    const statusBadge = isPaid ?
                                        '<span class="badge bg-success"><i class="fas fa-check me-1"></i>PAID</span>' :
                                        '<span class="badge bg-danger"><i class="fas fa-times me-1"></i>NOT PAID</span>';

                                    const tr = document.createElement('tr');
                                    tr.innerHTML = `
                                        <td>${idx++}</td>
                                        <td>${itemName}</td>
                                        <td>Rs. ${amount}</td>
                                        <td class="text-center">${statusBadge}</td>
                                    `;
                                    tbody.appendChild(tr);
                                }
                            }
                        }

                        if (tbody.children.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-secondary">No record found</td></tr>';
                        }
                    }

                    processDataObj(fundData, 'wa2024_fundTableBody');
                    processDataObj(functionData, 'wa2024_functionTableBody');
                    processDataObj(nightFundData, 'wa2024_nightTableBody');

                    const totalDue = totalPayable - totalPaid;
                    const totPayEl = document.getElementById('wa2024_totalPayable');
                    const totPaidEl = document.getElementById('wa2024_totalPaid');
                    const totDueEl = document.getElementById('wa2024_totalDue');

                    if (totPayEl) totPayEl.textContent = 'Rs. ' + totalPayable;
                    if (totPaidEl) totPaidEl.textContent = 'Rs. ' + totalPaid;
                    if (totDueEl) totDueEl.textContent = 'Rs. ' + totalDue;

                    loadingEl.classList.add('d-none');
                    contentEl.classList.remove('d-none');
                    wa2024Loaded = true;

                } catch (err) {
                    loadingEl.classList.add('d-none');
                    if (errorEl) {
                        errorEl.textContent = 'Error loading 2024 Web App details: ' + err.message;
                        errorEl.classList.remove('d-none');
                    }
                }
            }

            const webAppTabBtn = document.getElementById('web-app-2024-tab');
            if (webAppTabBtn) {
                webAppTabBtn.addEventListener('shown.bs.tab', function() {
                    loadWebApp2024Data();
                });
            }

            const subTabButtons = document.querySelectorAll('#wa2024SubTabs .nav-link');
            subTabButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    subTabButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const targetId = this.getAttribute('data-target');
                    const allPanes = document.querySelectorAll('#wa2024SubTabsContent .tab-pane');
                    allPanes.forEach(pane => {
                        pane.style.display = 'none';
                        pane.classList.remove('show', 'active');
                    });

                    const targetPane = document.querySelector(targetId);
                    if (targetPane) {
                        targetPane.style.display = 'block';
                        targetPane.classList.add('show', 'active');
                    }
                });
            });
        });
    </script>
</body>

</html>