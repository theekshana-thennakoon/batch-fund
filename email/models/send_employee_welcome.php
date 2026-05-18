<?php

use App\Services\MailService;

require_once __DIR__ . '/../services/MailService.php';

if (!function_exists('sendEmployeeWelcomeEmail')) {
    function sendEmployeeWelcomeEmail(array $payload): array
    {
        try {
            $config = require __DIR__ . '/../config/config.php';

            $employeeEmail = trim((string)($payload['email'] ?? ''));
            $employeeName = trim((string)($payload['name'] ?? 'Employee'));
            $plainPassword = (string)($payload['password'] ?? '');

            if ($employeeEmail === '' || !filter_var($employeeEmail, FILTER_VALIDATE_EMAIL)) {
                return [
                    'status' => false,
                    'message' => 'Invalid employee email address.'
                ];
            }

            if ($plainPassword === '') {
                return [
                    'status' => false,
                    'message' => 'Missing employee password.'
                ];
            }

            $mailer = new MailService();
            $body = $mailer->renderTemplate('employee_welcome', [
                'name' => $employeeName,
                'email' => $employeeEmail,
                'password' => $plainPassword,
                'companyName' => $config['settings']['company_name'] ?? 'Company',
                'companyPhone' => $config['settings']['company_phone'] ?? '',
                'contactUrl' => $config['settings']['contact_url'] ?? '',
                'webUrl' => $config['settings']['baseURL'] ?? '',
                'bannerUrl' => $config['settings']['banner_url'] ?? ''
            ]);

            $subject = 'Your Employee Account Credentials';
            $result = $mailer->sendMail($employeeEmail, $subject, $body);

            if ($result === true) {
                return [
                    'status' => true,
                    'message' => 'Employee welcome email sent successfully.'
                ];
            }

            return [
                'status' => false,
                'message' => 'Failed to send employee welcome email: ' . $result
            ];
        } catch (Throwable $e) {
            return [
                'status' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ];
        }
    }
}

// Optional HTTP endpoint usage pattern similar to other email/models files.
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['employeeWelcomeStatus'])) {
    header('Content-Type: application/json');

    $response = sendEmployeeWelcomeEmail([
        'name' => $_POST['name'] ?? 'Employee',
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? ''
    ]);

    echo json_encode([
        'status' => !empty($response['status']) ? 'correct' : 'incorrect',
        'msg' => $response['message'] ?? ''
    ]);
    exit();
}
