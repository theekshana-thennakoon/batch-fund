<?php
include("../assets/database.php");
date_default_timezone_set('Asia/Colombo');
session_start();
if (!isset($_SESSION["admin_logged_user"])) {
    header("Location:login.php");
} else {
    $reg_no = $_SESSION["admin_logged_user"];
}

$alert_message = "";
$alert_type = "";

// Check current display_loan_popup setting
$display_loan_popup_setting = get_system_setting($conn, 'display_loan_popup', '1');
$display_loan_popup = ($display_loan_popup_setting === '1' || $display_loan_popup_setting === 'true' || $display_loan_popup_setting === 1);

// Handle add new loan
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_loan"])) {
    $reason = $conn->real_escape_string($_POST["reason"]);
    $total = (float)$_POST["total"];

    if (!empty($reason) && $total > 0) {
        $sql_insert = "INSERT INTO loans (reason, total, paid, balance) VALUES ('{$reason}', {$total}, 0, {$total})";
        if ($conn->query($sql_insert)) {
            $alert_type = "success";
            $alert_message = "New loan added successfully!";
        } else {
            $alert_type = "danger";
            $alert_message = "Error adding loan: " . $conn->error;
        }
    }
}

// Handle loan update (reason, total, paid)
if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST["update_paid"]) || isset($_POST["update_loan"]))) {
    $loan_id = (int)$_POST["loan_id"];
    $reason = isset($_POST["reason"]) ? $conn->real_escape_string($_POST["reason"]) : null;
    $total = isset($_POST["total"]) ? (float)$_POST["total"] : null;
    $paid = isset($_POST["paid"]) ? (float)$_POST["paid"] : 0.0;

    if ($total === null) {
        $sql_get = "SELECT total FROM loans WHERE id = {$loan_id}";
        $result = $conn->query($sql_get);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $total = (float)$row['total'];
        } else {
            $total = 0.0;
        }
    }

    $balance = $total - $paid;

    if ($reason !== null) {
        $sql_update = "UPDATE loans SET reason = '{$reason}', total = {$total}, paid = {$paid}, balance = {$balance} WHERE id = {$loan_id}";
    } else {
        $sql_update = "UPDATE loans SET paid = {$paid}, balance = {$balance} WHERE id = {$loan_id}";
    }

    if ($conn->query($sql_update)) {
        $alert_type = "success";
        $alert_message = "Loan details updated successfully!";
    } else {
        $alert_type = "danger";
        $alert_message = "Error updating loan: " . $conn->error;
    }
}

// Handle delete loan
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_loan"])) {
    $loan_id = (int)$_POST["loan_id"];
    if ($loan_id > 0) {
        if ($conn->query("DELETE FROM loans WHERE id = {$loan_id}")) {
            $alert_type = "success";
            $alert_message = "Loan deleted successfully!";
        } else {
            $alert_type = "danger";
            $alert_message = "Error deleting loan: " . $conn->error;
        }
    }
}

// Get all loan applications
$sql_loans = "SELECT * FROM loans ORDER BY id DESC";
$result_loans = $conn->query($sql_loans);
$loans = [];
if ($result_loans->num_rows > 0) {
    while ($row = $result_loans->fetch_assoc()) {
        $loans[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Applications</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
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

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-primary);
        }

        #sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(135deg, #1a1f2e 0%, #0f1419 100%);
            border-right: 2px solid var(--dark-border);
            z-index: 1000;
            color: white;
            transition: all 0.3s;
        }

        #sidebar .sidebar-header {
            padding: 25px;
            text-align: center;
            border-bottom: 2px solid var(--dark-border);
        }

        #sidebar .sidebar-header h3 {
            color: var(--primary-light);
            margin: 0;
            font-weight: 700;
        }

        #sidebar ul.components {
            padding: 20px 0;
        }

        #sidebar ul li a {
            padding: 15px 25px;
            display: block;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        #sidebar ul li a:hover,
        #sidebar ul li.active a {
            color: var(--primary-light);
            background: rgba(99, 102, 241, 0.1);
            border-left-color: var(--primary-light);
            padding-left: 30px;
        }

        #sidebar ul li a i {
            margin-right: 12px;
            width: 20px;
        }

        #main-content {
            margin-left: 260px;
            padding: 30px;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

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

        .card {
            background: var(--dark-card);
            border: 2px solid var(--dark-border);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            margin-bottom: 2rem;
            transition: all 0.3s;
        }

        .card:hover {
            border-color: var(--primary-light);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.15);
        }

        .card-header {
            background: rgba(99, 102, 241, 0.05);
            border-bottom: 2px solid var(--dark-border);
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h4 {
            margin: 0;
            color: var(--primary-light);
            font-weight: 700;
            font-size: 1.3rem;
        }

        .card-body {
            padding: 25px;
        }

        .table {
            color: var(--text-primary);
            background-color: transparent;
            border-color: var(--dark-border);
            margin-bottom: 0;
        }

        .table th {
            background-color: rgba(99, 102, 241, 0.1);
            color: var(--primary-light);
            border-bottom: 2px solid var(--dark-border);
            font-weight: 700;
            padding: 12px;
            text-align: center;
        }

        .table td {
            padding: 12px;
            text-align: center;
            border-color: var(--dark-border);
            vertical-align: middle;
        }

        .table tbody tr {
            transition: all 0.3s;
            border-color: var(--dark-border);
        }

        .table tbody tr:hover {
            background-color: rgba(99, 102, 241, 0.05);
        }

        .btn {
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(99, 102, 241, 0.3);
            color: white;
        }

        .btn-light {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary-light);
            border: 2px solid var(--primary-light);
        }

        .btn-light:hover {
            background: var(--primary-light);
            color: white;
        }

        .btn-secondary {
            background: var(--dark-border);
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background: var(--text-secondary);
            color: var(--dark-bg);
        }

        .modal-content {
            background: var(--dark-card);
            border: 2px solid var(--dark-border);
            border-radius: 12px;
            color: var(--text-primary);
        }

        .modal-body {
            color: var(--text-primary);
        }

        .modal-header {
            border-bottom: 2px solid var(--dark-border);
            background: rgba(99, 102, 241, 0.05);
        }

        .modal-footer {
            border-top: 2px solid var(--dark-border);
            background: rgba(99, 102, 241, 0.02);
        }

        .btn-outline-danger {
            border: 1px solid var(--danger);
            color: var(--danger);
            background: transparent;
        }

        .btn-outline-danger:hover {
            background: var(--danger);
            color: white;
        }

        .modal-title {
            color: var(--primary-light);
            font-weight: 700;
        }

        .btn-close {
            filter: invert(1);
        }

        .form-control {
            background: var(--dark-border);
            border: 2px solid var(--dark-border);
            color: var(--text-primary);
            border-radius: 8px;
        }

        .form-control:focus {
            background: var(--dark-border);
            border-color: var(--primary-light);
            color: var(--text-primary);
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
        }

        .form-label {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--dark-border);
        }

        .reason-text {
            max-width: 300px;
            word-wrap: break-word;
            text-align: left;
        }

        .table-responsive {
            border-radius: 12px;
            background-color: transparent;
        }
    </style>
</head>

<body>
    <?php include("sidebar.php"); ?>
    <?php include("topbar.php"); ?>

    <div id="main-content">
        <div class="container-fluid">
            <?php if (!empty($alert_message)): ?>
                <div class="alert alert-<?php echo $alert_type; ?> alert-dismissible fade show" role="alert">
                    <i class="fas <?php echo $alert_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
                    <?php echo $alert_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Dashboard Loan Popup Notice Control -->
            <div class="card mb-4" style="background: rgba(26, 31, 46, 0.95); border: 1px solid rgba(16, 185, 129, 0.35); box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #10b981; font-size: 1.25rem;">
                                <i class="fas fa-window-restore"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <strong class="text-light" style="font-size: 0.95rem;">Student Dashboard Loan Popup Display:</strong>
                                    <span id="loansPopupStatusBadge" class="badge <?php echo $display_loan_popup ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $display_loan_popup ? 'Enabled (Auto-Show)' : 'Disabled (Hidden)'; ?>
                                    </span>
                                </div>
                                <small class="text-secondary" id="loansPopupHelpText">
                                    <?php echo $display_loan_popup 
                                        ? 'Students see the loan notice popup modal automatically when opening their dashboard.' 
                                        : 'The loan popup modal will NOT automatically open on the student dashboard.'; ?>
                                </small>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 ms-auto">
                            <div class="form-check form-switch m-0" style="font-size: 1.25rem;">
                                <input class="form-check-input" type="checkbox" role="switch" id="loansPopupSwitch" <?php echo $display_loan_popup ? 'checked' : ''; ?> style="cursor: pointer; width: 2.9em; height: 1.55em;">
                            </div>
                            <a href="./settings" class="btn btn-outline-light btn-sm" title="Advanced System Settings">
                                <i class="fas fa-cog me-1"></i> Settings
                            </a>
                            <a href="../index.php" target="_blank" class="btn btn-outline-info btn-sm" title="View Student Dashboard">
                                <i class="fas fa-external-link-alt me-1"></i> Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-money-bill-wave"></i> Loan Applications</h4>
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addLoanModal">
                        <i class="fas fa-plus me-1"></i> Add New Loan
                    </button>
                </div>
                <div class="card-body">
                    <?php if (count($loans) > 0) { ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Loan ID</th>
                                        <th>Reason</th>
                                        <th>Total (Rs.)</th>
                                        <th>Paid (Rs.)</th>
                                        <th>Balance (Rs.)</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($loans as $index => $loan) { ?>
                                        <tr>
                                            <td><?php echo $loan['id']; ?></td>
                                            <td>
                                                <div class="reason-text">
                                                    <?php echo htmlspecialchars(substr($loan['reason'], 0, 50)); ?>
                                                    <?php if (strlen($loan['reason']) > 50) echo '...'; ?>
                                                </div>
                                            </td>
                                            <td><?php echo number_format($loan['total'], 2); ?></td>
                                            <td><?php echo number_format($loan['paid'], 2); ?></td>
                                            <td><?php echo number_format($loan['balance'], 2); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $loan['id']; ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?php echo $loan['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Update Loan Status</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="">
                                                        <div class="modal-body">
                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <span class="badge text-light border border-secondary px-3 py-2" style="background: rgba(255, 255, 255, 0.05); font-size: 0.9rem;">
                                                                    <i class="fas fa-hashtag me-1 text-primary-light"></i> Loan ID: <?php echo $loan['id']; ?>
                                                                </span>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="reason<?php echo $loan['id']; ?>" class="form-label">
                                                                    <i class="fas fa-file-alt me-1 text-primary-light"></i> Reason for Loan <span class="text-danger">*</span>
                                                                </label>
                                                                <textarea class="form-control" id="reason<?php echo $loan['id']; ?>" name="reason" rows="3" placeholder="Enter the reason for the loan" required><?php echo htmlspecialchars($loan['reason']); ?></textarea>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="total<?php echo $loan['id']; ?>" class="form-label">
                                                                        <i class="fas fa-coins me-1 text-primary-light"></i> Total Amount (Rs.) <span class="text-danger">*</span>
                                                                    </label>
                                                                    <input type="number" class="form-control" id="total<?php echo $loan['id']; ?>" name="total" step="0.01" min="0" value="<?php echo htmlspecialchars($loan['total']); ?>" placeholder="Total amount" required>
                                                                </div>

                                                                <div class="col-md-6 mb-3">
                                                                    <label for="paid<?php echo $loan['id']; ?>" class="form-label">
                                                                        <i class="fas fa-hand-holding-dollar me-1 text-primary-light"></i> Paid Amount (Rs.) <span class="text-danger">*</span>
                                                                    </label>
                                                                    <input type="number" class="form-control" id="paid<?php echo $loan['id']; ?>" name="paid" step="0.01" min="0" value="<?php echo htmlspecialchars($loan['paid']); ?>" placeholder="Paid amount" required>
                                                                </div>
                                                            </div>

                                                            <div class="p-3 rounded mb-2" style="background: rgba(99, 102, 241, 0.1); border: 1px solid var(--dark-border);">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span class="text-secondary fw-semibold">
                                                                        <i class="fas fa-calculator me-1"></i> Balance Remaining:
                                                                    </span>
                                                                    <span class="fs-5 fw-bold text-warning">
                                                                        Rs. <span id="balance<?php echo $loan['id']; ?>"><?php echo number_format($loan['balance'], 2); ?></span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer d-flex justify-content-between">
                                                            <button type="submit" name="delete_loan" value="1" class="btn btn-outline-danger btn-sm" formnovalidate onclick="return confirm('Are you sure you want to delete Loan #<?php echo $loan['id']; ?>? This action cannot be undone.');">
                                                                <i class="fas fa-trash-alt me-1"></i> Delete
                                                            </button>
                                                            <div class="d-flex gap-2">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                                                                <input type="hidden" name="update_loan" value="1">
                                                                <input type="hidden" name="update_paid" value="1">
                                                                <button type="submit" class="btn btn-primary">
                                                                    <i class="fas fa-save me-1"></i> Update Details
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                    <script>
                                                        (function() {
                                                            var totalEl = document.getElementById('total<?php echo $loan['id']; ?>');
                                                            var paidEl = document.getElementById('paid<?php echo $loan['id']; ?>');
                                                            var balanceEl = document.getElementById('balance<?php echo $loan['id']; ?>');
                                                            function recalc() {
                                                                if (!totalEl || !paidEl || !balanceEl) return;
                                                                var total = parseFloat(totalEl.value) || 0;
                                                                var paid = parseFloat(paidEl.value) || 0;
                                                                var balance = total - paid;
                                                                balanceEl.textContent = balance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                                            }
                                                            if (totalEl && paidEl) {
                                                                totalEl.addEventListener('input', recalc);
                                                                paidEl.addEventListener('input', recalc);
                                                            }
                                                        })();
                                                    </script>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else { ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No loan applications yet</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add New Loan Modal -->
    <div class="modal fade" id="addLoanModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Loan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Loan</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Enter the reason for the loan" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="total" class="form-label">Loan Amount (Rs.)</label>
                            <input type="number" class="form-control" id="total" name="total" step="0.01" placeholder="Enter the total loan amount" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <input type="hidden" name="add_loan" value="1">
                        <button type="submit" class="btn btn-primary">Add Loan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Loans Page Toast Container -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11000;">
        <div id="loansActionToast" class="toast align-items-center text-white bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i id="loansToastIcon" class="fas fa-check-circle text-success fs-5"></i>
                    <span id="loansToastMsg">Setting updated.</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
        const loansPopupSwitch = document.getElementById('loansPopupSwitch');
        const loansPopupStatusBadge = document.getElementById('loansPopupStatusBadge');
        const loansPopupHelpText = document.getElementById('loansPopupHelpText');

        function showLoansToast(msg, isSuccess = true) {
            const toastEl = document.getElementById('loansActionToast');
            const msgEl = document.getElementById('loansToastMsg');
            const iconEl = document.getElementById('loansToastIcon');

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

        if (loansPopupSwitch) {
            loansPopupSwitch.addEventListener('change', async function() {
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
                            loansPopupStatusBadge.className = 'badge bg-success';
                            loansPopupStatusBadge.textContent = 'Enabled (Auto-Show)';
                            loansPopupHelpText.textContent = 'Students see the loan notice popup modal automatically when opening their dashboard.';
                        } else {
                            loansPopupStatusBadge.className = 'badge bg-danger';
                            loansPopupStatusBadge.textContent = 'Disabled (Hidden)';
                            loansPopupHelpText.textContent = 'The loan popup modal will NOT automatically open on the student dashboard.';
                        }
                        showLoansToast(data.message, true);
                    } else {
                        showLoansToast(data.message || 'Failed to update setting', false);
                        loansPopupSwitch.checked = !isChecked;
                    }
                } catch (e) {
                    console.error(e);
                    showLoansToast('Network error while updating loan popup setting.', false);
                    loansPopupSwitch.checked = !isChecked;
                } finally {
                    loansPopupSwitch.disabled = false;
                }
            });
        }
    </script>
</body>

</html>