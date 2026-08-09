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

// Selected threshold criteria
$threshold = isset($_GET['threshold']) ? (float)$_GET['threshold'] : 500;

// Helper function to calculate combined financials for a student
function calculateStudentFinancials($conn, $reg_no) {
    // 1. Get base total from reasons table + 5950 base 2024 contribution
    $tot_reasons = 0.00;
    $res_reasons = $conn->query("SELECT COALESCE(SUM(price), 0) as total FROM reasons");
    if ($res_reasons && $row = $res_reasons->fetch_assoc()) {
        $tot_reasons = (float)$row['total'];
    }
    $tot = $tot_reasons + 5950.00;

    // 2. Get paid amount from payments table for this student
    $tot_paid_reasons = 0.00;
    $stmt_p = $conn->prepare("SELECT COALESCE(SUM(r.price), 0) as paid FROM payments p JOIN reasons r ON p.reason_id = r.id WHERE p.reg_no = ?");
    $stmt_p->bind_param("s", $reg_no);
    $stmt_p->execute();
    $res_p = $stmt_p->get_result();
    if ($res_p && $row_p = $res_p->fetch_assoc()) {
        $tot_paid_reasons = (float)$row_p['paid'];
    }
    $stmt_p->close();

    // 3. Get 2024 paid amount & balance from prev_system table
    $tot_paid_2024 = 5950.00;
    $prev_balance = 0.00;

    $stmt_prev = $conn->prepare("SELECT balance FROM prev_system WHERE reg_no = ?");
    $stmt_prev->bind_param("s", $reg_no);
    $stmt_prev->execute();
    $res_prev = $stmt_prev->get_result();
    if ($res_prev && $row_prev = $res_prev->fetch_assoc()) {
        $prev_balance = (float)$row_prev['balance'];
        if ($prev_balance > 0) {
            $tot_paid_2024 = 5950.00 - $prev_balance;
        }
    }
    $stmt_prev->close();

    $tot_paid = $tot_paid_reasons + $tot_paid_2024;
    $due_balance = $tot - $tot_paid;
    if ($due_balance < 0) {
        $due_balance = 0.00;
    }

    return [
        'tot' => $tot,
        'tot_paid' => $tot_paid,
        'due_balance' => $due_balance,
        'prev_balance' => $prev_balance,
        'tot_paid_reasons' => $tot_paid_reasons,
        'tot_paid_2024' => $tot_paid_2024
    ];
}

// Fetch all registered users matching threshold criteria
$report_students = [];
$total_due_sum = 0.00;

$res_users = $conn->query("SELECT reg_no, Name, card FROM users ORDER BY reg_no ASC");
if ($res_users && $res_users->num_rows > 0) {
    while ($user = $res_users->fetch_assoc()) {
        $fin = calculateStudentFinancials($conn, $user['reg_no']);
        if ($fin['due_balance'] > $threshold) {
            $user['fin'] = $fin;
            $report_students[] = $user;
            $total_due_sum += $fin['due_balance'];
        }
    }
}

// Dompdf PDF Generation when requested
if (isset($_GET['download_pdf'])) {
    require_once __DIR__ . '/../vendor/autoload.php';

    $font_regular = str_replace('\\', '/', realpath(__DIR__ . '/../assets/fonts/AbhayaLibre-Regular.ttf'));
    $font_bold = str_replace('\\', '/', realpath(__DIR__ . '/../assets/fonts/AbhayaLibre-Bold.ttf'));

    $options = new \Dompdf\Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('isFontSubsettingEnabled', true);
    $options->set('defaultFont', 'AbhayaLibre');

    $dompdf = new \Dompdf\Dompdf($options);

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Student Outstanding PDF Report</title>
        <style>
            @font-face {
                font-family: "AbhayaLibre";
                font-style: normal;
                font-weight: normal;
                src: url("file:///' . $font_regular . '") format("truetype");
            }
            @font-face {
                font-family: "AbhayaLibre";
                font-style: normal;
                font-weight: bold;
                src: url("file:///' . $font_bold . '") format("truetype");
            }

            body { font-family: "AbhayaLibre", sans-serif; font-size: 12px; color: #333; line-height: 1.4; margin: 0; padding: 0; }
            .header-table { width: 100%; border-bottom: 2px solid #4e73df; padding-bottom: 10px; margin-bottom: 15px; }
            .header-title { font-size: 18px; font-weight: bold; color: #4e73df; margin: 0; }
            .header-subtitle { font-size: 11px; color: #666; margin: 3px 0 0 0; }
            .badge-filter { background-color: #e74a3b; color: white; padding: 4px 8px; font-size: 10px; font-weight: bold; border-radius: 4px; }
            .summary-table { width: 100%; margin-bottom: 15px; border-spacing: 5px; }
            .summary-box { background-color: #f8f9fc; border: 1px solid #e3e6f0; padding: 8px 12px; border-radius: 5px; }
            .summary-title { font-size: 9px; text-transform: uppercase; color: #666; font-weight: bold; }
            .summary-val-blue { font-size: 14px; font-weight: bold; color: #4e73df; }
            .summary-val-red { font-size: 14px; font-weight: bold; color: #e74a3b; }
            .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            .data-table th { background-color: #4e73df; color: white; font-size: 11px; padding: 7px 5px; text-align: left; border: 1px solid #4e73df; }
            .data-table td { padding: 6px 5px; font-size: 10px; border: 1px solid #dddddd; }
            .data-table tr:nth-child(even) { background-color: #f8f9fc; }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .text-green { color: #1cc88a; font-weight: bold; }
            .text-red { color: #e74a3b; font-weight: bold; }
            .footer-table { width: 100%; font-size: 9px; color: #777; border-top: 1px solid #ddd; padding-top: 10px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <table class="header-table">
            <tr>
                <td>
                    <div class="header-title">Student Contribution Management Portal</div>
                    <div class="header-subtitle">Official Student Outstanding Payment Report</div>
                </td>
                <td class="text-right">
                    <span class="badge-filter">Filter: Due > Rs. ' . number_format($threshold, 0) . '</span>
                    <div style="font-size: 9px; color: #777; margin-top: 4px;">Date: ' . date('Y-m-d H:i:s') . '</div>
                </td>
            </tr>
        </table>

        <table class="summary-table">
            <tr>
                <td style="width: 50%;">
                    <div class="summary-box">
                        <div class="summary-title">Matching Students Count</div>
                        <div class="summary-val-blue">' . count($report_students) . ' Students</div>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="summary-box">
                        <div class="summary-title">Combined Outstanding Balance</div>
                        <div class="summary-val-red">Rs. ' . number_format($total_due_sum, 2) . '</div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 20%;">Reg No</th>
                    <th style="width: 28%;">Student Name</th>
                    <th style="width: 17%;">Card No</th>
                    <th style="width: 15%;">Total Paid</th>
                    <th style="width: 15%;">Total Due (Rs.)</th>
                </tr>
            </thead>
            <tbody>';

    if (empty($report_students)) {
        $html .= '<tr><td colspan="6" class="text-center" style="padding: 15px;">No students found matching criteria (Due > Rs. ' . number_format($threshold, 0) . ').</td></tr>';
    } else {
        $i = 1;
        foreach ($report_students as $st) {
            $html .= '
                <tr>
                    <td class="text-center">' . $i++ . '</td>
                    <td><strong>' . htmlspecialchars($st['reg_no']) . '</strong></td>
                    <td>' . htmlspecialchars($st['Name']) . '</td>
                    <td>' . htmlspecialchars($st['card'] ?? 'N/A') . '</td>
                    <td class="text-green">Rs. ' . number_format($st['fin']['tot_paid'], 2) . '</td>
                    <td class="text-red">Rs. ' . number_format($st['fin']['due_balance'], 2) . '</td>
                </tr>';
        }
    }

    $html .= '
            </tbody>
            <tfoot>
                <tr style="background-color: #eaecf4; font-weight: bold;">
                    <td colspan="5" class="text-right">Grand Total Outstanding:</td>
                    <td class="text-red">Rs. ' . number_format($total_due_sum, 2) . '</td>
                </tr>
            </tfoot>
        </table>

        <table class="footer-table">
            <tr>
                <td>
                    Calculated across reasons, users, payments, and prev_system tables.<br>
                    Student Contribution Management Portal &copy; ' . date('Y') . '
                </td>
                <td class="text-right" style="vertical-align: bottom;">
                    Authorized Signature: _______________________
                </td>
            </tr>
        </table>
    </body>
    </html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $filename = "Student_Outstanding_Report_Over_" . (int)$threshold . ".pdf";
    $dompdf->stream($filename, ["Attachment" => true]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Outstanding Reports - SCMP Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts for Sinhala & English -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Abhaya+Libre:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
            --primary-color: #4e73df;
        }

        body {
            font-family: 'Abhaya Libre', 'Nunito', sans-serif;
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

        .pdf-report-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            border: 1px solid #e3e6f0;
            color: #333;
            font-family: 'Abhaya Libre', serif;
        }

        .report-header {
            border-bottom: 2px solid #4e73df;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .report-header h2 {
            color: #4e73df;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-table th {
            background-color: #4e73df !important;
            color: white !important;
            font-weight: 600;
        }

        @media print {
            #sidebar, .topbar, .no-print {
                display: none !important;
            }
            #content {
                width: 100% !important;
                margin-left: 0 !important;
            }
            .pdf-report-container {
                border: none !important;
                padding: 0 !important;
            }
        }
    </style>
</head>

<body>

    <?php include('sidebar.php'); ?>

    <div id="content">
        <?php include('topbar.php'); ?>

        <div class="container-fluid p-4">
            <!-- Title & Action Buttons -->
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-file-pdf text-danger me-2"></i> Student Outstanding PDF Reports</h1>
                <div>
                    <a href="?threshold=<?php echo (int)$threshold; ?>&download_pdf=1" class="btn btn-danger btn-lg me-2">
                        <i class="fas fa-file-pdf me-1"></i> Download PDF Report
                    </a>
                    <button onclick="window.print()" class="btn btn-secondary btn-lg">
                        <i class="fas fa-print me-1"></i> Print Page
                    </button>
                </div>
            </div>

            <!-- Criteria Filter Card -->
            <div class="card mb-4 no-print">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-filter text-primary me-2"></i> Select Criteria Threshold
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label for="thresholdSelect" class="form-label fw-bold">Display Users Who Need To Pay:</label>
                            <select class="form-select form-select-lg" id="thresholdSelect" name="threshold">
                                <option value="500" <?php echo $threshold == 500 ? 'selected' : ''; ?>>Users who need to pay > Rs. 500</option>
                                <option value="1000" <?php echo $threshold == 1000 ? 'selected' : ''; ?>>Users who need to pay > Rs. 1,000</option>
                                <option value="1500" <?php echo $threshold == 1500 ? 'selected' : ''; ?>>Users who need to pay > Rs. 1,500</option>
                                <option value="2000" <?php echo $threshold == 2000 ? 'selected' : ''; ?>>Users who need to pay > Rs. 2,000</option>
                                <option value="2500" <?php echo $threshold == 2500 ? 'selected' : ''; ?>>Users who need to pay > Rs. 2,500</option>
                                <option value="3000" <?php echo $threshold == 3000 ? 'selected' : ''; ?>>Users who need to pay > Rs. 3,000</option>
                                <option value="0" <?php echo $threshold == 0 ? 'selected' : ''; ?>>All Users with Unpaid Balance (> Rs. 0)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-sync-alt me-1"></i> Apply Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Report Live Preview -->
            <div id="pdf-report-container" class="pdf-report-container">
                <div class="report-header d-flex justify-content-between align-items-center">
                    <div>
                        <h2>Student Contribution Management Portal</h2>
                        <p class="mb-0 text-muted">Official Student Outstanding Payment Report</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-danger fs-6 mb-1">Filter: Due > Rs. <?php echo number_format($threshold, 0); ?></span>
                        <div class="small text-muted">Generated: <?php echo date('Y-m-d H:i:s'); ?></div>
                    </div>
                </div>

                <!-- Report Overview Summary Cards -->
                <div class="row mb-4">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded border">
                            <div class="text-muted small uppercase fw-bold">Matching Students Count</div>
                            <div class="h4 mb-0 text-primary fw-bold"><?php echo count($report_students); ?> Students</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded border">
                            <div class="text-muted small uppercase fw-bold">Combined Total Outstanding Balance</div>
                            <div class="h4 mb-0 text-danger fw-bold">Rs. <?php echo number_format($total_due_sum, 2); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Student Data Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle report-table">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 18%;">Reg No</th>
                                <th style="width: 25%;">Student Name</th>
                                <th style="width: 15%;">Card No</th>
                                <th style="width: 12%;">Total Expected</th>
                                <th style="width: 12%;">Total Paid</th>
                                <th style="width: 13%;">Total Due (Rs.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($report_students)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        No students found matching criteria (Due > Rs. <?php echo number_format($threshold, 0); ?>).
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $idx = 1; foreach ($report_students as $st): ?>
                                    <tr>
                                        <td><?php echo $idx++; ?></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($st['reg_no']); ?></td>
                                        <td><?php echo htmlspecialchars($st['Name']); ?></td>
                                        <td><?php echo htmlspecialchars($st['card'] ?? 'N/A'); ?></td>
                                        <td>Rs. <?php echo number_format($st['fin']['tot'], 2); ?></td>
                                        <td class="text-success">Rs. <?php echo number_format($st['fin']['tot_paid'], 2); ?></td>
                                        <td class="text-danger fw-bold">Rs. <?php echo number_format($st['fin']['due_balance'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($report_students)): ?>
                            <tfoot>
                                <tr class="fw-bold bg-light">
                                    <td colspan="6" class="text-end">Grand Total Outstanding:</td>
                                    <td class="text-danger">Rs. <?php echo number_format($total_due_sum, 2); ?></td>
                                </tr>
                            </tfoot>
                        <?php endif; ?>
                    </table>
                </div>

                <!-- Report Footer Signatures -->
                <div class="mt-5 pt-4 border-top d-flex justify-content-between text-muted small">
                    <div>
                        <p class="mb-0">Calculated across: reasons, users, payments, and prev_system tables.</p>
                        <p class="mb-0">Student Contribution Management Portal © <?php echo date('Y'); ?></p>
                    </div>
                    <div class="text-end">
                        <p class="mb-0">Authorized Signature: _______________________</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
