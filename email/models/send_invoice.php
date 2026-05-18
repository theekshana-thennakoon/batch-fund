<?php

use App\Services\MailService;

require_once __DIR__ . '/../services/MailService.php';
require_once __DIR__ . '/../../app/config/Database.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // HTML2PDF

// ✅ Load BOTH configs
$dbconfig = require __DIR__ . '/../../app/config/config.php';
$config   = require __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invoiceStatus'])) {

    try {

        // ✅ Validate ID
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            echo json_encode(['status' => 'incorrect', 'msg' => 'Invalid invoice ID.']);
            exit();
        }

        $id = (int) $_POST['id'];
        $toemail = $_POST['toemail'] ?? '';


        $emails = array_map('trim', explode(',', $toemail));

        $toemail1 = filter_var($emails[0] ?? '', FILTER_VALIDATE_EMAIL);
        $toemail2 = filter_var($emails[1] ?? '', FILTER_VALIDATE_EMAIL);




        // ✅ DB Connection
        $db = new Database($dbconfig['db'], $dbconfig['settings']);
        $pdo = $db->getConnection();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // ✅ Fetch Invoice Data
        $stmt = $pdo->prepare("
            SELECT C.email , C.name AS clientName, C.address AS clientAddress, I.date AS invoice_date, 
                   I.price AS invoice_amount, I.paid AS invoice_paid,
                   S.case_name AS case_name, B.name_en AS barristerName
            FROM client C
            INNER JOIN invoices I ON C.id = I.client_id
            JOIN cases S ON I.Case = S.id
            JOIN barrister B ON S.assigned_barrister = B.id
            WHERE I.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            echo json_encode(['status' => 'incorrect', 'msg' => 'Invoice not found.']);
            exit();
        }

        // ✅ Email logic
        $client_email = !empty($toemail1) ? $toemail1 : $data['email'];
        $client_name  = $data['clientName'];

        if (!$client_email) {
            echo json_encode(['status' => 'incorrect', 'msg' => 'Client email not found.']);
            exit();
        }



        // ✅ Prepare template data
        $params = [
            'invoice_id' => $id,
            'email' => $client_email,
            'name' => $client_name,
            'clientaddress' => $data['clientAddress'],
            'date' => date('l, F j, Y'),
            'invoiceDate' => $data['invoice_date'],
            'invoiceAmount' => $data['invoice_amount'],
            'invoicePaid' => $data['invoice_paid'],
            'caseName' => $data['case_name'],
            'barristerName' => $data['barristerName'],
            'companyName' => $config['settings']['company_name'],
            'companyPhone' => $config['settings']['company_phone'],
            'contactUrl' => $config['settings']['contact_url'],
            'webUrl' => $config['settings']['baseURL'],
            'bannerUrl' => $config['settings']['banner_url'],
            'companyEmail' => $config['settings']['admin_email'],
            'companyLogo' => $config['settings']['baseURL'] . '/assets/images/logo.png',
        ];

        // ✅ Generate HTML from template
        $mailer = new MailService();
        $html = $mailer->renderTemplate('send_invoice', $params);

        // ❗ Remove unsupported tags for PDF
        $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);

        // ✅ Generate PDF
        $pdfPath = __DIR__ . '/invoice/invoice_' . $id . '.pdf';

        $html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'en');
        $html2pdf->writeHTML($html);
        $html2pdf->output($pdfPath, 'F');

        // ✅ Send Email with attachment
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        $smtp = $config['smtp'];

        $mail->isSMTP();
        $mail->Host       = $smtp['host'];
        $mail->SMTPAuth   = $smtp['auth'];
        $mail->Username   = $smtp['username'];
        $mail->Password   = $smtp['password'];
        $mail->SMTPSecure = $smtp['encryption'];
        $mail->Port       = $smtp['port'];

        $mail->setFrom($smtp['from_email'], $smtp['from_name']);
        // $mail->addAddress($client_email, $client_name);

        // // if($toemail2 != ''){ {
        // //     $mail->addAddress($toemail2, $client_name);
        // // }

        foreach ($emails as $emailx) {
            if (filter_var($emailx, FILTER_VALIDATE_EMAIL)) {
                $mail->addAddress($emailx, $client_name);
            }
        }

        $mail->isHTML(true);
        $mail->Subject = "Invoice - {$client_name}";
        $mail->Body    = $html;

        // ✅ Attach PDF
        $mail->addAttachment($pdfPath);

        $mail->send();

        // ✅ Delete PDF after sending
        if (file_exists($pdfPath)) {
            unlink($pdfPath);
        }

        echo json_encode([
            'status' => 'correct',
            'msg' => 'Invoice sent successfully with PDF attachment!'
        ]);
    } catch (Exception $e) {

        echo json_encode([
            'status' => 'incorrect',
            'msg' => 'Error: ' . $e->getMessage()
        ]);
    }

    exit();
}

echo json_encode([
    'status' => 'incorrect',
    'msg' => 'Invalid request.'
]);
exit();
