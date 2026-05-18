<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

class MailService
{
    private $smtp;
    private $mail;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/config.php';
        $this->smtp = $config['smtp'];
    }

    // Render template
    public function renderTemplate($templateName, $params = [])
    {
        $templateFile = __DIR__ . '/../templates/' . $templateName . '.php';
        if (!file_exists($templateFile)) {
            throw new \Exception("Template file {$templateName} not found.");
        }

        // Extract parameters as variables
        extract($params);

        // Start output buffering
        ob_start();
        include $templateFile;
        return ob_get_clean(); // Returns rendered HTML
    }

    /**
     * Send an email
     * @param string|array $to Recipient email(s)
     * @param string $subject Email subject
     * @param string $body HTML body content
     * @param array $replyTo (optional) [email, name]
     * @return bool|string Returns true if sent, or error message
     */
    public function sendMail($to, $subject, $body, $replyTo = [])
    {
        try {
            if ($this->mail === null) {
                $this->mail = new PHPMailer(true);
                $this->mail->isSMTP();
                $this->mail->Host       = $this->smtp['host'];
                $this->mail->Port       = $this->smtp['port'];
                $this->mail->SMTPSecure = $this->smtp['encryption'];
                $this->mail->SMTPAuth   = $this->smtp['auth'];
                $this->mail->Username   = $this->smtp['username'];
                $this->mail->Password   = $this->smtp['password'];
                $this->mail->setFrom($this->smtp['from_email'], $this->smtp['from_name']);
                $this->mail->SMTPDebug  = $this->smtp['debug'] ? 2 : 0;

                // Disable SSL certificate verification (required for Windows XAMPP)
                $this->mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true,
                    ]
                ];

                // Keep the SMTP connection alive so batch sends are faster and more stable.
                $this->mail->SMTPKeepAlive = true;
            }

            $mail = $this->mail;

            $mail->clearAddresses();
            $mail->clearReplyTos();
            $mail->Subject = '';
            $mail->Body = '';
            $mail->AltBody = '';

            // Recipients
            if (is_array($to)) {
                foreach ($to as $address) {
                    $mail->addAddress($address);
                }
            } else {
                $mail->addAddress($to);
            }

            if (!empty($replyTo)) {
                $mail->addReplyTo($replyTo[0], $replyTo[1] ?? '');
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            $mail->send();
            return true;
        } catch (\Throwable $e) {
            $errorInfo = ($this->mail instanceof PHPMailer && !empty($this->mail->ErrorInfo)) ? $this->mail->ErrorInfo : $e->getMessage();
            return 'Mailer Error: ' . $errorInfo;
        }
    }
}
