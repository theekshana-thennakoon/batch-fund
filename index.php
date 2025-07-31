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
    $sql_reason = "SELECT * FROM reasons";
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
                    <style>
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
                    </style>
                    <span class="fw-bold h2 mt-2">Batch Fund</span>
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
            <script>
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
            </script>
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
        <script>
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
        </script>

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
        // JavaScript for tab functionality (Bootstrap already handles most of it)
        document.addEventListener('DOMContentLoaded', function() {
            // Any additional JavaScript functionality can be added here

            // Example: Change rank color based on value
            const rankValue = <?php echo $studentInfo['rank']; ?>;
            const rankElement = document.querySelector('.rank-number');

            if (rankValue <= 3) {
                rankElement.style.color = '#ffc107'; // Gold for top 3
            } else if (rankValue <= 10) {
                rankElement.style.color = '#28a745'; // Green for top 10
            }
        });
    </script>
</body>

</html>