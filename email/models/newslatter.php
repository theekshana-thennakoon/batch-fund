<?php

use App\Services\MailService;

require_once __DIR__ . '/../services/MailService.php';

$config = require __DIR__ . '/../config/config.php';
$adminEmail = $config['settings']['admin_email'];  // Get admin email
 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];
    $currentDateTime = new DateTime('now');
    $currentDate = $currentDateTime->format('l, F j, Y');
 
    $mailer = new MailService();

    // Render template with dynamic data
    $body = $mailer->renderTemplate('newsletter', [
        'email' => $email,
        'date'  => $currentDate,
        'companyName' => $config['settings']['company_name'],
        'companyPhone' => $config['settings']['company_phone'],
        'contactUrl' => $config['settings']['contact_url'],
        'webUrl' => $config['settings']['baseURL'],
        'bannerUrl' => $config['settings']['banner_url']
    ]);

    $result = $mailer->sendMail($adminEmail, "Newsletter Subscription", $body, [$email]);

    $response = [];

    if ($result === true) {
        $response['status'] = 'correct';
        $response['msg'] = 'You’ve successfully subscribed to our newsletter!';
    } else {
        $response['status'] = 'incorrect';
        $response['msg'] = 'Failed to send email: ' . $result;
    }

    echo json_encode($response);
    exit();
} 