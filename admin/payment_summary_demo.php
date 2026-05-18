<?php
include("../assets/database.php");
date_default_timezone_set('Asia/Colombo');
session_start();

if (!isset($_SESSION["admin_logged_user"])) {
    header("Location:login.php");
    exit;
}

require_once __DIR__ . '/../email/services/MailService.php';

use App\Services\MailService;

$config = require __DIR__ . '/../email/config/config.php';

$sampleStudent = [
    'reg_no' => 'BST/2021/005',
    'Name' => 'Sample Student',
    'card' => 'CARD-001',
];

$studentResult = $conn->query("SELECT reg_no, Name, card FROM users ORDER BY id DESC LIMIT 1");
if ($studentResult && $studentResult->num_rows > 0) {
    $sampleStudent = $studentResult->fetch_assoc();
}

$totalAmount = 0;
$totalResult = $conn->query('SELECT COALESCE(SUM(price), 0) AS total_amount FROM reasons');
if ($totalResult && $totalRow = $totalResult->fetch_assoc()) {
    $totalAmount = (float) $totalRow['total_amount'];
}

$paidAmount = 0;
$regNo = $sampleStudent['reg_no'] ?? '';
$paidSummarySql = '
    SELECT COALESCE(SUM(r.price), 0) AS total_paid
    FROM (
        SELECT DISTINCT reg_no, reason_id
        FROM payments
        WHERE reg_no = ?
    ) p
    INNER JOIN reasons r ON r.id = p.reason_id
';
$paidStmt = $conn->prepare($paidSummarySql);
if ($paidStmt) {
    $paidStmt->bind_param('s', $regNo);
    $paidStmt->execute();
    $paidResult = $paidStmt->get_result();
    if ($paidResult && $paidRow = $paidResult->fetch_assoc()) {
        $paidAmount = (float) $paidRow['total_paid'];
    }
    $paidStmt->close();
}

$balance = max(0, $totalAmount - $paidAmount);

$mailer = new MailService();
$demoHtml = $mailer->renderTemplate('payment_summary', [
    'name' => $sampleStudent['Name'] ?? 'Sample Student',
    'regNo' => $regNo ?: 'BST/2021/005',
    'card' => $sampleStudent['card'] ?? 'CARD-001',
    'email' => 'sample.student@example.com',
    'date' => date('l, F j, Y'),
    'totalAmount' => $totalAmount,
    'totalPaid' => $paidAmount,
    'balance' => $balance,
    'companyName' => $config['settings']['company_name'],
    'companyPhone' => $config['settings']['company_phone'],
    'contactUrl' => $config['settings']['contact_url'],
    'webUrl' => $config['settings']['baseURL'],
    'bannerUrl' => $config['settings']['banner_url'],
    'companyEmail' => $config['settings']['admin_email'],
    'companyLogo' => '../assets/logo.png',
]);

$demoHtml = htmlspecialchars($demoHtml, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Summary Template Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fc;
        }

        .demo-shell {
            min-height: 100vh;
            padding: 24px;
        }

        .demo-frame {
            width: 100%;
            height: calc(100vh - 180px);
            border: 1px solid rgba(58, 59, 69, 0.12);
            border-radius: 12px;
            background: #fff;
        }
    </style>
</head>

<body>
    <div class="demo-shell container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h3 class="mb-1"><i class="fas fa-eye me-2 text-primary"></i>Payment Summary Template Demo</h3>
                <div class="text-muted">Preview rendered from the same email template used for bulk sending.</div>
            </div>
            <a href="send_email.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Email Sender
            </a>
        </div>

        <iframe class="demo-frame" srcdoc="<?php echo $demoHtml; ?>" title="Payment Summary Template Preview"></iframe>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>