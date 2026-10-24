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

// Find student ITT/2021/106 or latest student as sample
$sampleStudent = [
    'reg_no' => 'ITT/2021/106',
    'Name' => 'Test Student (ITT/2021/106)',
    'card' => 'CARD-106',
];

$studentResult = $conn->query("SELECT reg_no, Name, card FROM users WHERE reg_no = 'ITT/2021/106' LIMIT 1");
if ($studentResult && $studentResult->num_rows > 0) {
    $sampleStudent = $studentResult->fetch_assoc();
} else {
    $anyResult = $conn->query("SELECT reg_no, Name, card FROM users ORDER BY id DESC LIMIT 1");
    if ($anyResult && $anyResult->num_rows > 0) {
        $sampleStudent = $anyResult->fetch_assoc();
    }
}

$mailer = new MailService();
$demoHtml = $mailer->renderTemplate('loan_report_notice', [
    'name' => $sampleStudent['Name'] ?? 'Student Name',
    'regNo' => $sampleStudent['reg_no'] ?? 'ITT/2021/106',
    'card' => $sampleStudent['card'] ?? '',
    'email' => strtolower(str_replace('/', '', $sampleStudent['reg_no'] ?? 'itt2021106')) . '@tec.rjt.ac.lk',
    'date' => date('l, F j, Y'),
    'companyName' => $config['settings']['company_name'] ?? 'Batch Fund (20/21)',
    'companyPhone' => '+9478 965 1168',
    'contactUrl' => $config['settings']['contact_url'] ?? '#',
    'webUrl' => $config['settings']['baseURL'] ?? 'http://localhost/batchfund',
    'companyEmail' => 'theekshanathennakoon79@gmail.com',
    'companyLogo' => '../assets/logo.png',
    'isWebPreview' => true,
]);

echo $demoHtml;
