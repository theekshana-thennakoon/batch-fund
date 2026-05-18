<?php

use App\Services\MailService;
use PDO;
use DateTime;

require_once __DIR__ . '/../services/MailService.php';
require_once __DIR__ . '/../../app/config/Database.php';
$dbconfig = require __DIR__ . '/../../app/config/config.php';

$config = require __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remindStatus'])) {

    try {

        // Validate ID
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            echo json_encode([
                'status' => 'incorrect',
                'msg' => 'Invalid appointment ID.'
            ]);
            exit();
        }

        $id = (int) $_POST['id'];

        // Create DB connection
        $db = new Database($dbconfig['db'], $dbconfig['settings']);
        $pdo = $db->getConnection();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch client email
        $stmt = $pdo->prepare("
            SELECT C.email , C.name AS clientName, A.date AS appointment_date, A.case_study, B.name_en AS barristerName
            FROM client C
            INNER JOIN appoinment A ON C.id = A.client_id
            INNER JOIN barrister B ON A.barrister_id = B.id
            WHERE A.id = :id
        ");

        $stmt->execute(['id' => $id]);

        $client_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $client_email = $client_data['email'];
        $client_name = $client_data['clientName'];
        $appointment_date = $client_data['appointment_date'];
        $case_study = $client_data['case_study'];
        $barrister_name = $client_data['barristerName'];

        if (!$client_email) {
            echo json_encode([
                'status' => 'incorrect',
                'msg' => 'Client email not found for this appointment.'
            ]);
            exit();
        }

        // Current date
        $currentDateTime = new DateTime('now');
        $currentDate = $currentDateTime->format('l, F j, Y');

        // Send Email
        $mailer = new MailService();

        $body = $mailer->renderTemplate('reminder', [
            'email'        => $client_email,
            'name'         => $client_name,
            'date'         => $currentDate,
            'caseStudy'    => $case_study,
            'barristerName' => $barrister_name,
            'companyName'  => $config['settings']['company_name'],
            'companyPhone' => $config['settings']['company_phone'],
            'contactUrl'   => $config['settings']['contact_url'],
            'webUrl'       => $config['settings']['baseURL'],
            'bannerUrl'    => $config['settings']['banner_url']
        ]);

        $result = $mailer->sendMail($client_email, "Appointment Reminder", $body);

        if ($result === true) {

            echo json_encode([
                'status' => 'correct',
                'msg' => 'Appointment reminder sent successfully!'
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
