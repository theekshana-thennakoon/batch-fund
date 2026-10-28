<?php
include("../assets/database.php");
date_default_timezone_set('Asia/Colombo');
session_start();

if (!isset($_SESSION["admin_logged_user"])) {
    header("Location:login.php");
    exit;
} else {
    $reg_no = $_SESSION["admin_logged_user"];
}

// Fetch current settings
$display_loan_popup_setting = get_system_setting($conn, 'display_loan_popup', '1');
$display_loan_popup = ($display_loan_popup_setting === '1' || $display_loan_popup_setting === 'true' || $display_loan_popup_setting === 1);

// Handle fallback standard POST submission
$status_message = "";
$status_type = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_settings"])) {
    $new_status = isset($_POST["display_loan_popup"]) ? '1' : '0';
    if (set_system_setting($conn, 'display_loan_popup', $new_status)) {
        $display_loan_popup = ($new_status === '1');
        $display_loan_popup_setting = $new_status;
        $status_type = "success";
        $status_message = "Settings updated successfully! Dashboard loan popup is now " . ($display_loan_popup ? "Enabled" : "Disabled") . ".";
    } else {
        $status_type = "danger";
        $status_message = "Failed to update settings: " . $conn->error;
    }
}

// Fetch loan statistics for preview modal
$sql_loans = "SELECT COUNT(*) as count, SUM(total) as total, SUM(paid) as paid, SUM(balance) as balance FROM loans";
$result_loans = $conn->query($sql_loans);
$total_batch_loans = 0;
$total_batch_loans_paid = 0;
$total_batch_loans_balance = 0;
if ($result_loans && $result_loans->num_rows > 0) {
    $row_loans = $result_loans->fetch_assoc();
    $total_batch_loans = (float)($row_loans['total'] ?? 0);
    $total_batch_loans_paid = (float)($row_loans['paid'] ?? 0);
    $total_batch_loans_balance = (float)($row_loans['balance'] ?? 0);
}
$overall_loan_progress = $total_batch_loans > 0 ? round(($total_batch_loans_paid / $total_batch_loans) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Admin Panel</title>
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
            --sidebar-width: 250px;
        }

        [data-theme="light"] {
            --dark-bg: #f8f9fa;
            --dark-card: #ffffff;
            --dark-border: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
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
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        #content {
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
        }

        .topbar {
            background-color: var(--dark-card);
            border-bottom: 1px solid var(--dark-border);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar h1 {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary-light);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .theme-toggle,
        .logout-btn {
            background: rgba(129, 140, 248, 0.1);
            border: 1px solid rgba(129, 140, 248, 0.2);
            color: var(--primary-light);
            padding: 8px 14px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-weight: 500;
        }

        .theme-toggle:hover,
        .logout-btn:hover {
            background: rgba(129, 140, 248, 0.2);
            color: var(--primary-light);
            transform: translateY(-2px);
        }

        .page-header {
            margin-bottom: 25px;
        }

        .page-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .settings-card {
            background: var(--dark-card);
            border: 1px solid var(--dark-border);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .settings-card:hover {
            border-color: rgba(99, 102, 241, 0.4);
            box-shadow: 0 14px 35px rgba(99, 102, 241, 0.08);
        }

        .setting-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--dark-border);
            margin-bottom: 20px;
        }

        .setting-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .setting-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(5, 150, 105, 0.1) 100%);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .form-switch .form-check-input {
            width: 3.2em;
            height: 1.7em;
            cursor: pointer;
            background-color: rgba(100, 116, 139, 0.4);
            border-color: rgba(100, 116, 139, 0.5);
            transition: all 0.3s ease;
        }

        .form-switch .form-check-input:checked {
            background-color: #10b981;
            border-color: #10b981;
            box-shadow: 0 0 12px rgba(16, 185, 129, 0.45);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .status-pill.status-active {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.4);
        }

        .status-pill.status-disabled {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.4);
        }

        .info-bubble {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid var(--dark-border);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }

        [data-theme="light"] .info-bubble {
            background: rgba(241, 245, 249, 0.8);
        }

        .preview-toast {
            position: fixed;
            bottom: 25px;
            right: 25px;
            z-index: 9999;
            min-width: 300px;
        }

        /* Modal styling matching user dashboard */
        .modal-glass .modal-content {
            background: #111827;
            border: 1px solid rgba(16, 185, 129, 0.45);
            color: #e9ecef;
            border-radius: 16px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 35px rgba(16, 185, 129, 0.25);
        }

        .loan-stat-badge-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 10px;
        }

        .loan-stat-mini-pill {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(16, 185, 129, 0.25);
            border-radius: 10px;
            padding: 8px 10px;
            display: flex;
            flex-direction: column;
        }

        .loan-mini-lbl {
            font-size: 0.72rem;
            color: #94a3b8;
            font-weight: 600;
        }

        .loan-mini-val {
            font-size: 0.95rem;
            font-weight: 700;
            color: #ffffff;
            margin-top: 2px;
        }

        @media (max-width: 768px) {
            #content {
                width: 100%;
                margin-left: 0;
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
            <h1><i class="fas fa-cog me-2"></i>System Settings</h1>
            <div class="topbar-right">
                <button class="theme-toggle" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="processing?logout=1" class="logout-btn">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>

        <div class="container-fluid p-4">
            <!-- Breadcrumb / Page Header -->
            <div class="page-header">
                <h2>Portal Configuration</h2>
                <p class="text-secondary">Control system behaviors, notifications, and popup announcements across student dashboards.</p>
            </div>

            <?php if (!empty($status_message)): ?>
                <div class="alert alert-<?php echo $status_type; ?> alert-dismissible fade show" role="alert">
                    <i class="fas <?php echo $status_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
                    <?php echo $status_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Setting 1: Dashboard Loan Popup Display -->
            <div class="settings-card">
                <div class="setting-header">
                    <div class="setting-header-left">
                        <div class="setting-icon-box">
                            <i class="fas fa-window-restore"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">Student Dashboard Loan Popup</h4>
                            <p class="text-secondary mb-0 small">Control whether the Loans & Repayments report modal pops up automatically when students open their dashboard.</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <span id="popupStatusBadge" class="status-pill <?php echo $display_loan_popup ? 'status-active' : 'status-disabled'; ?>">
                            <i class="fas <?php echo $display_loan_popup ? 'fa-check-circle' : 'fa-ban'; ?>"></i>
                            <span id="popupStatusBadgeText"><?php echo $display_loan_popup ? 'Enabled (Auto-Show)' : 'Disabled (Hidden)'; ?></span>
                        </span>

                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="displayLoanPopupSwitch" <?php echo $display_loan_popup ? 'checked' : ''; ?>>
                        </div>
                    </div>
                </div>

                <div class="row g-4 align-items-center">
                    <div class="col-lg-8">
                        <div class="info-bubble">
                            <h6 class="fw-bold mb-2 text-primary-light">
                                <i class="fas fa-circle-info me-2"></i>How this setting works:
                            </h6>
                            <ul class="mb-0 ps-3 small text-secondary" style="line-height: 1.8;">
                                <li><strong>When Enabled (ON):</strong> Every student who logs in or refreshes their dashboard will see the comprehensive Loan & Repayment Notice popup automatically.</li>
                                <li><strong>When Disabled (OFF):</strong> The on-load popup is suppressed. Students can still access the loan report details manually via the dashboard slider card or table tabs if needed.</li>
                                <li><strong>Instant Sync:</strong> Changes saved here or via the toggle take effect immediately for all current and future dashboard sessions.</li>
                            </ul>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-primary btn-sm fw-bold px-3 py-2" id="btnToggleLoanPopup">
                                <i class="fas fa-power-off me-2"></i>
                                <span id="btnToggleText"><?php echo $display_loan_popup ? 'Disable Loan Popup' : 'Enable Loan Popup'; ?></span>
                            </button>

                            <button type="button" class="btn btn-outline-info btn-sm fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#previewLoanPopupModal">
                                <i class="fas fa-eye me-2"></i>Preview Popup Window
                            </button>

                            <a href="../index.php" target="_blank" class="btn btn-outline-light btn-sm fw-bold px-3 py-2">
                                <i class="fas fa-external-link-alt me-2"></i>Open Student Dashboard
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.08); border: 1px dashed rgba(16, 185, 129, 0.35);">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fas fa-chart-pie text-success"></i>
                                <strong class="small text-success">Live Summary Stats</strong>
                            </div>
                            <div class="small text-secondary mb-1">Total Loan Sum: <strong class="text-light">Rs. <?php echo number_format($total_batch_loans, 2); ?></strong></div>
                            <div class="small text-secondary mb-1">Settled: <strong class="text-success">Rs. <?php echo number_format($total_batch_loans_paid, 2); ?></strong></div>
                            <div class="small text-secondary mb-1">Balance Due: <strong class="text-warning">Rs. <?php echo number_format($total_batch_loans_balance, 2); ?></strong></div>
                            <div class="small text-secondary">Repayment Rate: <strong class="text-info"><?php echo $overall_loan_progress; ?>%</strong></div>
                        </div>
                    </div>
                </div>

                <!-- Fallback Traditional Form -->
                <form method="POST" id="fallbackSettingsForm" class="d-none">
                    <input type="hidden" name="save_settings" value="1">
                    <input type="checkbox" name="display_loan_popup" id="fallbackLoanPopupInput" <?php echo $display_loan_popup ? 'checked' : ''; ?>>
                </form>
            </div>

            <!-- Quick Links Card -->
            <div class="settings-card">
                <h5 class="fw-bold mb-3"><i class="fas fa-link me-2 text-primary-light"></i>Related Loan Tools</h5>
                <div class="d-flex flex-wrap gap-2">
                    <a href="./loans" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-money-bill-wave me-1"></i> Loan Applications & Records
                    </a>
                    <a href="./loan_report_email" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-paper-plane me-1"></i> Send Loan Report Notices via Email
                    </a>
                    <a href="./loan_report_demo" target="_blank" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-file-code me-1"></i> Email Template Preview
                    </a>
                    <a href="./" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-home me-1"></i> Return to Admin Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Loan Notice Preview Modal (Exact replica of student dashboard popup) -->
    <div class="modal fade modal-glass" id="previewLoanPopupModal" tabindex="-1" aria-labelledby="previewLoanPopupModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header pb-2" style="border-bottom: 1px solid rgba(16, 185, 129, 0.2);">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-success py-2 px-3">
                            <i class="fas fa-hand-holding-dollar me-1"></i> LOANS & REPAYMENTS • BATCH 20/21
                        </span>
                        <span class="badge bg-info py-2 px-3">
                            <i class="fas fa-check-circle me-1"></i> Student Dashboard View Preview
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

                            <div class="p-3 rounded-3 mb-3" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(16, 185, 129, 0.25);">
                                <div class="loan-stat-badge-grid">
                                    <div class="loan-stat-mini-pill">
                                        <span class="loan-mini-lbl"><i class="fas fa-coins me-1 text-warning"></i>Total Borrowed</span>
                                        <span class="loan-mini-val">Rs. <?php echo number_format($total_batch_loans, 2); ?></span>
                                    </div>
                                    <div class="loan-stat-mini-pill">
                                        <span class="loan-mini-lbl"><i class="fas fa-check me-1 text-success"></i>Settled / Paid</span>
                                        <span class="loan-mini-val text-success">Rs. <?php echo number_format($total_batch_loans_paid, 2); ?></span>
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

                            <p class="text-secondary small mb-3">
                                This is the exact modal that students see automatically when this setting is enabled.
                            </p>

                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-success btn-sm fw-bold" data-bs-dismiss="modal">
                                    <i class="fas fa-check me-1"></i> Close Preview
                                </button>
                                <a href="../download_loan_pdf.php" target="_blank" class="btn btn-outline-light btn-sm">
                                    <i class="fas fa-file-pdf me-1 text-danger"></i> Download Report PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div class="toast-container preview-toast">
        <div id="settingsToast" class="toast align-items-center text-white bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i id="toastIcon" class="fas fa-check-circle text-success fs-5"></i>
                    <span id="toastMessage">Settings updated.</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme toggle logic
        const themeToggleBtn = document.querySelector('.theme-toggle');
        const themeIcon = themeToggleBtn.querySelector('i');
        const currentTheme = localStorage.getItem('theme') || 'dark';

        if (currentTheme === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
            themeIcon.classList.replace('fa-moon', 'fa-sun');
        }

        themeToggleBtn.addEventListener('click', () => {
            const isDark = !document.documentElement.hasAttribute('data-theme') || document.documentElement.getAttribute('data-theme') === 'dark';
            if (isDark) {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
                themeIcon.classList.replace('fa-moon', 'fa-sun');
            } else {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('theme', 'dark');
                themeIcon.classList.replace('fa-sun', 'fa-moon');
            }
        });

        // Toast Helper
        function showNotification(message, isSuccess = true) {
            const toastEl = document.getElementById('settingsToast');
            const toastMsg = document.getElementById('toastMessage');
            const toastIcon = document.getElementById('toastIcon');

            toastMsg.textContent = message;
            if (isSuccess) {
                toastEl.className = 'toast align-items-center text-white bg-success border-0';
                toastIcon.className = 'fas fa-check-circle text-white fs-5';
            } else {
                toastEl.className = 'toast align-items-center text-white bg-danger border-0';
                toastIcon.className = 'fas fa-exclamation-triangle text-white fs-5';
            }

            const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
            toast.show();
        }

        // Toggle Switch & Button Logic
        const loanPopupSwitch = document.getElementById('displayLoanPopupSwitch');
        const btnToggleLoanPopup = document.getElementById('btnToggleLoanPopup');
        const btnToggleText = document.getElementById('btnToggleText');
        const popupStatusBadge = document.getElementById('popupStatusBadge');
        const popupStatusBadgeText = document.getElementById('popupStatusBadgeText');
        const fallbackLoanPopupInput = document.getElementById('fallbackLoanPopupInput');

        function updateUIState(isEnabled) {
            loanPopupSwitch.checked = isEnabled;
            fallbackLoanPopupInput.checked = isEnabled;

            if (isEnabled) {
                popupStatusBadge.className = 'status-pill status-active';
                popupStatusBadge.innerHTML = '<i class="fas fa-check-circle"></i> <span>Enabled (Auto-Show)</span>';
                btnToggleText.textContent = 'Disable Loan Popup';
                btnToggleLoanPopup.className = 'btn btn-danger btn-sm fw-bold px-3 py-2';
            } else {
                popupStatusBadge.className = 'status-pill status-disabled';
                popupStatusBadge.innerHTML = '<i class="fas fa-ban"></i> <span>Disabled (Hidden)</span>';
                btnToggleText.textContent = 'Enable Loan Popup';
                btnToggleLoanPopup.className = 'btn btn-success btn-sm fw-bold px-3 py-2';
            }
        }

        async function sendToggleRequest(targetValue = null) {
            loanPopupSwitch.disabled = true;
            btnToggleLoanPopup.disabled = true;

            try {
                const payload = { key: 'display_loan_popup' };
                if (targetValue !== null) {
                    payload.value = targetValue ? '1' : '0';
                } else {
                    payload.value = 'toggle';
                }

                const response = await fetch('api_toggle_setting.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                if (data.success) {
                    updateUIState(data.is_enabled);
                    showNotification(data.message, true);
                } else {
                    showNotification(data.message || 'Error updating setting', false);
                    loanPopupSwitch.checked = !loanPopupSwitch.checked;
                }
            } catch (err) {
                console.error(err);
                showNotification('Network connection error while saving setting.', false);
                loanPopupSwitch.checked = !loanPopupSwitch.checked;
            } finally {
                loanPopupSwitch.disabled = false;
                btnToggleLoanPopup.disabled = false;
            }
        }

        loanPopupSwitch.addEventListener('change', function() {
            sendToggleRequest(this.checked);
        });

        btnToggleLoanPopup.addEventListener('click', function() {
            sendToggleRequest(null);
        });
    </script>
</body>

</html>
