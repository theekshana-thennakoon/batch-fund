<?php
include("../assets/database.php");
date_default_timezone_set('Asia/Colombo');
session_start();

// Ensure prev_system table exists
$conn->query("CREATE TABLE IF NOT EXISTS `prev_system` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `reg_no` VARCHAR(50) NOT NULL UNIQUE,
  `balance` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$reg_no = '';
$student_name = '';
$student_card = '';
$student_found = false;
$current_2024_balance = 0.00;
$prev_record_exists = false;
$success_msg = '';
$error_msg = '';

// Handle student search or payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['search'])) {
        $reg_no = strtoupper(trim($_POST['reg_no']));
    } elseif (isset($_POST['process_pay_2024'])) {
        $reg_no = strtoupper(trim($_POST['reg_no']));
        $pay_amount = (float)$_POST['pay_amount'];
    }

    if (!empty($reg_no)) {
        // Fetch student info from users
        $stmt = $conn->prepare("SELECT Name, card FROM users WHERE reg_no = ?");
        $stmt->bind_param("s", $reg_no);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $user = $res->fetch_assoc();
            $student_name = $user['Name'];
            $student_card = $user['card'] ?? 'N/A';
            $student_found = true;

            // Fetch 2024 balance details from prev_system
            $stmt_prev = $conn->prepare("SELECT balance FROM prev_system WHERE reg_no = ?");
            $stmt_prev->bind_param("s", $reg_no);
            $stmt_prev->execute();
            $res_prev = $stmt_prev->get_result();

            if ($res_prev && $res_prev->num_rows > 0) {
                $row_prev = $res_prev->fetch_assoc();
                $current_2024_balance = (float)$row_prev['balance'];
                $prev_record_exists = true;
            } else {
                // If not in prev_system table, default balance is 0.00 (Fully Paid)
                $current_2024_balance = 0.00;
                $prev_record_exists = false;
            }
            $stmt_prev->close();

            // Process payment if requested
            if (isset($_POST['process_pay_2024']) && $student_found) {
                if ($pay_amount <= 0) {
                    $error_msg = "Please enter a valid payment amount greater than Rs. 0.";
                } elseif ($current_2024_balance <= 0) {
                    $error_msg = "This student has no unpaid 2024 balance!";
                } else {
                    $new_balance = max(0.00, $current_2024_balance - $pay_amount);

                    if ($prev_record_exists) {
                        $stmt_upd = $conn->prepare("UPDATE prev_system SET balance = ? WHERE reg_no = ?");
                        $stmt_upd->bind_param("ds", $new_balance, $reg_no);
                        $stmt_upd->execute();
                        $stmt_upd->close();
                    } else {
                        $stmt_ins = $conn->prepare("INSERT INTO prev_system (reg_no, balance) VALUES (?, ?)");
                        $stmt_ins->bind_param("sd", $reg_no, $new_balance);
                        $stmt_ins->execute();
                        $stmt_ins->close();
                        $prev_record_exists = true;
                    }

                    $success_msg = "Payment of Rs. " . number_format($pay_amount, 2) . " recorded successfully for 2024! Remaining 2024 Balance: Rs. " . number_format($new_balance, 2);
                    $current_2024_balance = $new_balance;
                }
            }
        } else {
            $error_msg = "No student found with Registration Number: '{$reg_no}'.";
        }
        $stmt->close();
    }
}

// Fetch all registered students for quick select dropdown
$all_students = [];
$res_students = $conn->query("SELECT reg_no, Name FROM users ORDER BY reg_no ASC");
if ($res_students && $res_students->num_rows > 0) {
    while ($r = $res_students->fetch_assoc()) {
        $all_students[] = $r;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2024 Payment Portal - SCMP Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
            overflow-x: hidden;
        }

        #content {
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #e3e6f0;
            padding: 15px 20px;
            font-weight: bold;
        }

        .stat-box {
            border-radius: 8px;
            padding: 15px;
            background-color: #f8f9fc;
            border-left: 4px solid #4e73df;
        }

        .stat-box.success {
            border-left-color: #1cc88a;
        }

        .stat-box.danger {
            border-left-color: #e74a3b;
        }

        .stat-title {
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
            color: #5a5c69;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2e59d9;
        }

        .btn-preset {
            margin-right: 8px;
            margin-bottom: 8px;
        }
    </style>
</head>

<body>

    <?php include('sidebar.php'); ?>

    <div id="content">
        <?php include('topbar.php'); ?>

        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-calendar-check text-primary me-2"></i> 2024 Contribution Payments</h1>
            </div>

            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $success_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Search Card -->
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center">
                    <i class="fas fa-search me-2 text-primary"></i> Enter Registration Number
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label for="reg_no_input" class="form-label fw-bold">Registration No:</label>
                                <input type="text" class="form-control form-control-lg" id="reg_no_input" name="reg_no" placeholder="BST/2021/000" value="<?php echo htmlspecialchars($reg_no); ?>" required oninput="formatRegistrationNumber(this)" onkeydown="handleBackspace(event)" autocomplete="off" list="studentList">
                                <datalist id="studentList">
                                    <?php foreach ($all_students as $st): ?>
                                        <option value="<?php echo htmlspecialchars($st['reg_no']); ?>"><?php echo htmlspecialchars($st['Name']); ?></option>
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" name="search" class="btn btn-primary btn-lg">
                                    <i class="fas fa-search me-1"></i> Get Details
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Student Details & Payment Card (Displayed after entering reg no) -->
            <?php if ($student_found): ?>
                <div class="row">
                    <!-- Student Info Summary -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header text-primary">
                                <i class="fas fa-user-graduate me-2"></i> Student Details
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <th style="width: 40%;">Registration No:</th>
                                            <td class="fw-bold text-primary"><?php echo htmlspecialchars($reg_no); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Name:</th>
                                            <td class="fw-bold"><?php echo htmlspecialchars($student_name); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Card No:</th>
                                            <td><?php echo htmlspecialchars($student_card); ?></td>
                                        </tr>
                                        <tr>
                                            <th>2024 Base Total:</th>
                                            <td>Rs. 5,950.00</td>
                                        </tr>
                                        <tr>
                                            <th>Amount Paid:</th>
                                            <td class="text-success fw-bold">Rs. <?php echo number_format(max(0, 5950 - $current_2024_balance), 2); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2024 Balance & Status -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header text-primary">
                                <i class="fas fa-file-invoice-dollar me-2"></i> 2024 Balance & Status
                            </div>
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <div class="stat-box <?php echo ($current_2024_balance > 0) ? 'danger' : 'success'; ?> mb-3">
                                    <div class="stat-title">Remaining 2024 Balance</div>
                                    <div class="stat-value <?php echo ($current_2024_balance > 0) ? 'text-danger' : 'text-success'; ?>">
                                        Rs. <?php echo number_format($current_2024_balance, 2); ?>
                                    </div>
                                </div>

                                <div>
                                    <?php if ($current_2024_balance <= 0): ?>
                                        <span class="badge bg-success fs-6 p-3">
                                            <i class="fas fa-check-circle me-1"></i> Fully Paid in 2024
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger fs-6 p-3">
                                            <i class="fas fa-exclamation-triangle me-1"></i> Unpaid 2024 Balance: Rs. <?php echo number_format($current_2024_balance, 2); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Input Section -->
                <?php if ($current_2024_balance > 0): ?>
                    <div class="card">
                        <div class="card-header text-success">
                            <i class="fas fa-money-bill-wave me-2"></i> Process 2024 Payment
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="reg_no" value="<?php echo htmlspecialchars($reg_no); ?>">

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Quick Presets:</label>
                                    <div>
                                        <button type="button" class="btn btn-outline-danger btn-preset" onclick="setAmount(<?php echo $current_2024_balance; ?>)">
                                            Pay Full Balance (Rs. <?php echo number_format($current_2024_balance, 2); ?>)
                                        </button>
                                        <?php if ($current_2024_balance >= 500): ?>
                                            <button type="button" class="btn btn-outline-primary btn-preset" onclick="setAmount(500)">
                                                Pay 1 Sector (Rs. 500.00)
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="pay_amount" class="form-label fw-bold">Payment Amount (Rs.):</label>
                                    <div class="input-group input-group-lg" style="max-width: 400px;">
                                        <span class="input-group-text">Rs.</span>
                                        <input type="number" step="0.01" min="1" max="<?php echo $current_2024_balance; ?>" class="form-control" id="pay_amount" name="pay_amount" value="<?php echo $current_2024_balance; ?>" required>
                                    </div>
                                </div>

                                <button type="submit" name="process_pay_2024" class="btn btn-success btn-lg">
                                    <i class="fas fa-check-circle me-1"></i> Confirm & Record 2024 Payment
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setAmount(amt) {
            const input = document.getElementById('pay_amount');
            if (input) {
                input.value = amt;
            }
        }

        function formatRegistrationNumber(input) {
            let value = input.value.toUpperCase();
            const selectionStart = input.selectionStart;
            let cursorAdjustment = 0;

            // Auto-format department code (ITT, BST, ENT, etc.)
            if ((value.startsWith("ITT") || value.startsWith("BST") || value.startsWith("ENT")) && value.length === 3 && !value.includes("/")) {
                value = value + "/";
                if (selectionStart === 3) {
                    cursorAdjustment = 1;
                }
            }

            // Auto-format year (2021, 2020, etc.)
            if (value.includes("/") && value.indexOf("/") === 3) {
                const afterFirstSlash = value.substring(4);
                if (afterFirstSlash.startsWith("2021") && afterFirstSlash.length === 4 && !afterFirstSlash.includes("/")) {
                    value = value.substring(0, 8) + "/";
                    if (selectionStart === 8) {
                        cursorAdjustment = 1;
                    }
                } else if (afterFirstSlash.startsWith("2020") && afterFirstSlash.length === 4 && !afterFirstSlash.includes("/")) {
                    value = value.substring(0, 8) + "/";
                    if (selectionStart === 8) {
                        cursorAdjustment = 1;
                    }
                }
            }

            // Direct handling of 2021 at the beginning (e.g. 2021/CS/001)
            if (value.startsWith("2021") && value.length === 4 && !value.includes("/")) {
                value = value + "/";
                if (selectionStart === 4) {
                    cursorAdjustment = 1;
                }
            }

            input.value = value;

            if (cursorAdjustment > 0) {
                setTimeout(() => {
                    input.setSelectionRange(selectionStart + cursorAdjustment, selectionStart + cursorAdjustment);
                }, 0);
            }
        }

        function handleBackspace(event) {
            const input = event.target;
            const value = input.value;
            const selectionStart = input.selectionStart;

            if (event.key === "Backspace" && selectionStart > 0) {
                if (value.charAt(selectionStart - 1) === "/") {
                    event.preventDefault();
                }
            }
        }
    </script>
</body>

</html>
