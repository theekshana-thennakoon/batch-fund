<?php

use App\Services\MailService;
use PDO;
use DateTime;

require_once __DIR__ . '/../services/MailService.php';
require_once __DIR__ . '/../../app/config/Database.php';
$dbconfig = require __DIR__ . '/../../app/config/config.php';

$config = require __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invoiceStatus'])) {

    try {

        // Validate ID
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            echo json_encode([
                'status' => 'incorrect',
                'msg' => 'Invalid invoice ID.'
            ]);
            exit();
        }

        $id = (int) $_POST['id'];

        $toemail = $_POST['toemail'];

        // Create DB connection
        $db = new Database($dbconfig['db'], $dbconfig['settings']);
        $pdo = $db->getConnection();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch client email
        $stmt = $pdo->prepare("
            SELECT C.email , C.name AS clientName, I.date AS invoice_date, I.price AS invoice_amount, I.paid AS invoice_paid,
            S.case_name AS case_name, B.name_en AS barristerName
            FROM client C
            INNER JOIN invoices I ON C.id = I.client_id
            JOIN cases S ON I.Case = S.id
            JOIN barrister B ON S.assigned_barrister = B.id
            WHERE I.id = :id
        ");

        $stmt->execute(['id' => $id]);

        $client_data = $stmt->fetch(PDO::FETCH_ASSOC);
        // $client_email = $toemail ?? $client_data['email'];
        $client_email = !empty($toemail) ? $toemail : $client_data['email'];
        $client_name = $client_data['clientName'];
        $invoice_date = $client_data['invoice_date'];
        $invoice_amount = $client_data['invoice_amount'];
        $invoice_paid = $client_data['invoice_paid'];
        $case_name = $client_data['case_name'];
        $barrister_name = $client_data['barristerName'];

        if (!$client_email) {
            echo json_encode([
                'status' => 'incorrect',
                'msg' => 'Client email not found for this invoice.'
            ]);
            exit();
        }

        // Current date
        $currentDateTime = new DateTime('now');
        $currentDate = $currentDateTime->format('l, F j, Y');

        // Send Email
        $mailer = new MailService();

        $body = $mailer->renderTemplate('send_invoice', [
            'email'        => $client_email,
            'name'         => $client_name,
            'date'         => $currentDate,
            'invoiceDate'  => $invoice_date,
            'invoiceAmount' => $invoice_amount,
            'invoicePaid' => $invoice_paid,
            'caseName' => $case_name,
            'barristerName' => $barrister_name,
            'companyName'  => $config['settings']['company_name'],
            'companyPhone' => $config['settings']['company_phone'],
            'contactUrl'   => $config['settings']['contact_url'],
            'webUrl'       => $config['settings']['baseURL'],
            'bannerUrl'    => $config['settings']['banner_url']
        ]);

        $result = $mailer->sendMail($client_email, "Invoice - {$client_name}", $body);

        if ($result === true) {

            echo json_encode([
                'status' => 'correct',
                'msg' => 'Invoice sent successfully!'
            ]);
        } else {

            echo json_encode([
                'status' => 'incorrect',
                'msg' => 'Mail Error: ' . $result
            ]);
        }
    } catch (PDOException $e) {

        echo json_encode([
            'status' => 'incorrect',
            'msg' => 'Database Error: ' . $e->getMessage()
        ]);
    } catch (Exception $e) {

        echo json_encode([
            'status' => 'incorrect',
            'msg' => 'Server Error: ' . $e->getMessage()
        ]);
    }

    exit();
}

echo json_encode([
    'status' => 'incorrect',
    'msg' => 'Invalid request.'
]);
exit();
