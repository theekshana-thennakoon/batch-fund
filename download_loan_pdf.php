<?php
include(__DIR__ . "/assets/database.php");
date_default_timezone_set('Asia/Colombo');
session_start();

// Verify user or admin authentication
if (!isset($_COOKIE["reg_no"]) && !isset($_SESSION["admin_logged"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

// Prepare font paths if available
$calibri_regular = str_replace('\\', '/', realpath(__DIR__ . '/assets/fonts/Calibri-Regular.ttf'));
$calibri_bold = str_replace('\\', '/', realpath(__DIR__ . '/assets/fonts/Calibri-Bold.ttf'));
$noto_regular = str_replace('\\', '/', realpath(__DIR__ . '/assets/fonts/NotoSansSinhala-Regular.ttf'));
$noto_bold = str_replace('\\', '/', realpath(__DIR__ . '/assets/fonts/NotoSansSinhala-Bold.ttf'));
$logo_path = str_replace('\\', '/', realpath(__DIR__ . '/assets/logo.png'));

// Fetch Loans Data
$sql_loans = "SELECT * FROM loans ORDER BY id ASC";
$result_loans = $conn->query($sql_loans);
$loans = [];
$total_borrowed = 0;
$total_paid = 0;
$total_balance = 0;

if ($result_loans && $result_loans->num_rows > 0) {
    while ($row = $result_loans->fetch_assoc()) {
        $loans[] = $row;
        $total_borrowed += (float)$row['total'];
        $total_paid += (float)$row['paid'];
        $total_balance += (float)$row['balance'];
    }
}
$repayment_rate = $total_borrowed > 0 ? round(($total_paid / $total_borrowed) * 100, 1) : 0;

// Setup Dompdf Options
$options = new \Dompdf\Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('isFontSubsettingEnabled', true);
$options->set('chroot', [realpath(__DIR__)]);

$dompdf = new \Dompdf\Dompdf($options);

// Build HTML content
$html = '<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Batch 20/21 Loans & Repayments Report</title>
    <style>
        @font-face {
            font-family: "Calibri";
            font-style: normal;
            font-weight: normal;
            src: url("' . $calibri_regular . '") format("truetype");
        }
        @font-face {
            font-family: "Calibri";
            font-style: normal;
            font-weight: bold;
            src: url("' . $calibri_bold . '") format("truetype");
        }
        @font-face {
            font-family: "NotoSansSinhala";
            font-style: normal;
            font-weight: normal;
            src: url("' . $noto_regular . '") format("truetype");
        }
        @font-face {
            font-family: "NotoSansSinhala";
            font-style: normal;
            font-weight: bold;
            src: url("' . $noto_bold . '") format("truetype");
        }

        * {
            box-sizing: border-box;
            font-family: "Calibri", "NotoSansSinhala", Arial, sans-serif;
        }
        body {
            font-family: "Calibri", "NotoSansSinhala", Arial, sans-serif;
            font-size: 11.5px;
            color: #1e293b;
            line-height: 1.45;
            margin: 20px 25px;
            padding: 0;
        }

        /* Header Layout */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #059669;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header-logo {
            width: 65px;
            height: auto;
        }
        .title-main {
            font-size: 19px;
            font-weight: bold;
            color: #064e3b;
            margin: 0;
            letter-spacing: 0.3px;
        }
        .title-sub {
            font-size: 12px;
            color: #059669;
            font-weight: bold;
            margin: 3px 0 0 0;
        }
        .title-desc {
            font-size: 10px;
            color: #64748b;
            margin: 2px 0 0 0;
        }
        .meta-text {
            font-size: 9.5px;
            color: #64748b;
            text-align: right;
            line-height: 1.4;
        }

        /* KPI Summary Grid */
        .kpi-table {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: separate;
            border-spacing: 8px 0;
        }
        .kpi-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            text-align: center;
        }
        .kpi-box-green {
            background-color: #ecfdf5;
            border-color: #a7f3d0;
        }
        .kpi-box-amber {
            background-color: #fffbeb;
            border-color: #fde68a;
        }
        .kpi-label {
            font-size: 8.5px;
            text-transform: uppercase;
            font-weight: bold;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .kpi-value {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
        }
        .text-green { color: #047857; }
        .text-amber { color: #b45309; }

        /* Report Table */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .report-table th {
            background-color: #047857;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            border: 1px solid #047857;
        }
        .report-table td {
            padding: 7px 10px;
            border: 1px solid #e2e8f0;
            font-size: 11px;
            vertical-align: middle;
        }
        .report-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }

        /* Status Badges */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-settled {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        .badge-partial {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }
        .badge-unpaid {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* Grand Total Row */
        .total-row {
            background-color: #f1f5f9 !important;
            font-weight: bold;
            border-top: 2px solid #059669;
        }
        .total-row td {
            border-top: 2px solid #059669;
            font-size: 11.5px;
        }

        /* Notice & Verification Box */
        .notice-box {
            background-color: #f0fdf4;
            border: 1px dashed #059669;
            border-radius: 6px;
            padding: 10px 14px;
            margin-top: 15px;
            margin-bottom: 25px;
        }
        .notice-title {
            font-size: 10px;
            font-weight: bold;
            color: #047857;
            margin: 0 0 3px 0;
        }
        .notice-text {
            font-size: 9.5px;
            color: #475569;
            margin: 0;
            line-height: 1.4;
        }

        /* Footer Signatures */
        .footer-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .footer-table td {
            border: none;
            padding: 0;
            font-size: 9.5px;
            color: #64748b;
        }
        .signature-line {
            display: inline-block;
            width: 180px;
            border-top: 1px solid #94a3b8;
            margin-top: 35px;
            text-align: center;
            padding-top: 4px;
            color: #475569;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>';

if (file_exists($logo_path)) {
    $html .= '<td style="width: 75px; vertical-align: middle;">
                <img src="' . $logo_path . '" class="header-logo" alt="SCMP Logo">
              </td>';
}

$html .= '  <td style="vertical-align: middle;">
                <h1 class="title-main">Student Contribution Management Portal</h1>
                <div class="title-sub">Batch 20/21 - Loans & Repayments Official Statement</div>
                <div class="title-desc">Comprehensive record of batch-level borrowings and settlement transparency</div>
            </td>
            <td style="vertical-align: middle;" class="meta-text">
                <strong>Report Ref:</strong> BF-LN-' . date('Ymd') . '<br>
                <strong>Generated:</strong> ' . date('Y-m-d H:i:s') . '<br>
                <strong>Status:</strong> Active & Audited
            </td>
        </tr>
    </table>

    <!-- 4 KPI Metrics Table -->
    <table class="kpi-table">
        <tr>
            <td style="width: 25%;">
                <div class="kpi-box">
                    <div class="kpi-label">Total Borrowed</div>
                    <div class="kpi-value">Rs. ' . number_format($total_borrowed, 2) . '</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="kpi-box kpi-box-green">
                    <div class="kpi-label">Settled / Paid</div>
                    <div class="kpi-value text-green">Rs. ' . number_format($total_paid, 2) . '</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="kpi-box kpi-box-amber">
                    <div class="kpi-label">Balance Outstanding</div>
                    <div class="kpi-value text-amber">Rs. ' . number_format($total_balance, 2) . '</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="kpi-box">
                    <div class="kpi-label">Repayment Rate</div>
                    <div class="kpi-value text-green">' . $repayment_rate . '%</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Loans Breakdown Table -->
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 35px;" class="text-center">#</th>
                <th style="text-align: left;">Reason / Lender Party</th>
                <th style="width: 105px;" class="text-right">Total (Rs.)</th>
                <th style="width: 105px;" class="text-right">Settled (Rs.)</th>
                <th style="width: 105px;" class="text-right">Balance (Rs.)</th>
                <th style="width: 65px;" class="text-center">Rate</th>
                <th style="width: 85px;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>';

if (!empty($loans)) {
    foreach ($loans as $idx => $loan) {
        $tot = (float)$loan['total'];
        $paid = (float)$loan['paid'];
        $bal = (float)$loan['balance'];
        $prog = $tot > 0 ? round(($paid / $tot) * 100, 1) : 0;

        if ($bal <= 0) {
            $badge = '<span class="badge badge-settled">Fully Settled</span>';
        } elseif ($paid > 0) {
            $badge = '<span class="badge badge-partial">Partially Paid</span>';
        } else {
            $badge = '<span class="badge badge-unpaid">Unpaid</span>';
        }

        $html .= '<tr>
            <td class="text-center">' . ($idx + 1) . '</td>
            <td><strong>' . htmlspecialchars($loan['reason']) . '</strong></td>
            <td class="text-right">' . number_format($tot, 2) . '</td>
            <td class="text-right text-green fw-bold">' . number_format($paid, 2) . '</td>
            <td class="text-right text-amber fw-bold">' . number_format($bal, 2) . '</td>
            <td class="text-center">' . $prog . '%</td>
            <td class="text-center">' . $badge . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="7" class="text-center" style="padding: 15px; color: #64748b;">No loan records registered.</td></tr>';
}

$html .= '</tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="text-right"><strong>GRAND TOTAL:</strong></td>
                <td class="text-right"><strong>Rs. ' . number_format($total_borrowed, 2) . '</strong></td>
                <td class="text-right text-green"><strong>Rs. ' . number_format($total_paid, 2) . '</strong></td>
                <td class="text-right text-amber"><strong>Rs. ' . number_format($total_balance, 2) . '</strong></td>
                <td colspan="2" class="text-center text-green"><strong>' . $repayment_rate . '% Settled</strong></td>
            </tr>
        </tfoot>
    </table>

    <!-- Notice Box -->
    <div class="notice-box">
        <div class="notice-title">Official Financial Transparency Statement</div>
        <div class="notice-text">
            This document certifies all batch-level loan disbursements, borrowings for batch events/functions, verified repayments, and current outstanding balances for Batch 20/21. All records have been audited and published for official transparency.
        </div>
    </div>

    <!-- Signatures and Verification Footer -->
    <table class="footer-table">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                Student Contribution Management Portal &copy; ' . date('Y') . '<br>
                Official Batch Fund Records | Verified Electronic Copy
            </td>
            <td style="width: 50%; text-align: right; vertical-align: top;">
                <div class="signature-line">
                    Batch Representative / Treasurer
                </div>
            </td>
        </tr>
    </table>

</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = "Batch_20_21_Loans_Report_" . date('Ymd') . ".pdf";
$dompdf->stream($filename, ["Attachment" => true]);
exit;
