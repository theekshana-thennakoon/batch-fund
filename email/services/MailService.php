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
     * Get configured From Name according to the mail type
     * @param string $mailType
     * @return string
     */
    public function getFromNameForMail($mailType = 'default')
    {
        return $this->smtp['from_names'][$mailType]
            ?? $this->smtp['from_names']['default']
            ?? $this->smtp['from_name']
            ?? 'Batch Fund (20/21)';
    }

    /**
     * Resolve the From Name according to mail type or subject keywords
     * @param string $subject
     * @param string|null $explicitName
     * @return string
     */
    public function resolveFromName($subject = '', $explicitName = null)
    {
        if (!empty($explicitName)) {
            return $explicitName;
        }

        $fromNames = $this->smtp['from_names'] ?? [];
        if (!empty($fromNames) && !empty($subject)) {
            $subLower = mb_strtolower($subject, 'UTF-8');
            if (mb_strpos($subLower, 'පැදුර') !== false || mb_strpos($subLower, 'padura') !== false) {
                return $fromNames['padura_notice'] ?? 'Batch Fund - පැදුර 2026';
            }
            if (mb_strpos($subLower, 'batch photo') !== false) {
                return $fromNames['batch_photo_notice'] ?? 'Batch Fund - Batch Photo 2026';
            }
            if (mb_strpos($subLower, 'loan') !== false) {
                return $fromNames['loan_report_notice'] ?? 'Batch Fund - Loan Scheme';
            }
            if (mb_strpos($subLower, 'payment summary') !== false) {
                return $fromNames['payment_summary'] ?? 'Batch Fund - Payment Summary';
            }
            if (mb_strpos($subLower, 'invoice') !== false) {
                return $fromNames['invoice'] ?? 'Batch Fund - Official Invoice';
            }
            if (mb_strpos($subLower, 'reminder') !== false) {
                return $fromNames['reminder'] ?? 'Batch Fund - Payment Reminder';
            }
            if (mb_strpos($subLower, 'inquiry') !== false || mb_strpos($subLower, 'contact') !== false) {
                return $fromNames['contact'] ?? 'Batch Fund - Inquiry';
            }
            if (mb_strpos($subLower, 'newsletter') !== false) {
                return $fromNames['newsletter'] ?? 'Batch Fund - Newsletter';
            }
        }

        return $fromNames['default'] ?? $this->smtp['from_name'] ?? 'Batch Fund (20/21)';
    }

    /**
     * Send an email
     * @param string|array $to Recipient email(s)
     * @param string $subject Email subject
     * @param string $body HTML body content
     * @param array $replyTo (optional) [email, name]
     * @param string|null $fromName (optional) Specific sender display name according to the mail
     * @return bool|string Returns true if sent, or error message
     */
    public function sendMail($to, $subject, $body, $replyTo = [], $fromName = null)
    {
        try {
            $resolvedFromName = $this->resolveFromName($subject, $fromName);

            if ($this->mail === null) {
                $this->mail = new PHPMailer(true);
                $this->mail->isSMTP();
                $this->mail->Host       = $this->smtp['host'];
                $this->mail->Port       = $this->smtp['port'];
                $this->mail->SMTPSecure = $this->smtp['encryption'];
                $this->mail->SMTPAuth   = $this->smtp['auth'];
                $this->mail->Username   = $this->smtp['username'];
                $this->mail->Password   = $this->smtp['password'];
                $this->mail->CharSet    = 'UTF-8';
                $this->mail->Encoding   = 'base64';
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

            // Set the dynamic From Name according to the specific mail
            $mail->setFrom($this->smtp['from_email'], $resolvedFromName);

            $mail->clearAddresses();
            $mail->clearReplyTos();
            $mail->clearAttachments();
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

            // Auto embed company logo if template references cid:company_logo
            if (strpos($body, 'cid:company_logo') !== false) {
                $logoPath = realpath(__DIR__ . '/../../assets/logo.png');
                if ($logoPath && file_exists($logoPath)) {
                    $mail->addEmbeddedImage($logoPath, 'company_logo', 'logo.png', 'base64', 'image/png');
                }
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
