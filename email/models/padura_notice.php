<?php

use App\Services\MailService;

require_once __DIR__ . '/../services/MailService.php';
require_once __DIR__ . '/../../assets/database.php';

$config = require __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$isBulkRequest = isset($_POST['sendBulkPaduraStatus']);
$isSingleRequest = isset($_POST['sendSinglePaduraStatus']);
$isGroupRequest = isset($_POST['sendGroupPaduraStatus']) && isset($_POST['targetGroup']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || (!$isBulkRequest && !$isSingleRequest && !$isGroupRequest)) {
    echo json_encode([
        'status' => 'incorrect',
        'msg' => 'Invalid request.'
    ]);
    exit();
}

try {
    if ($isBulkRequest || $isGroupRequest) {
        @set_time_limit(0);
    }

    // Fetch students excluding dropped students for bulk/group
    $students = [];
    if ($isSingleRequest) {
        $studentResult = $conn->query('SELECT reg_no, Name, card FROM users ORDER BY id ASC');
    } else {
        $studentResult = $conn->query('
            SELECT u.reg_no, u.Name, u.card 
            FROM users u 
            WHERE u.reg_no NOT IN (SELECT reg_no FROM dropped_students)
            ORDER BY u.id ASC
        ');
    }

    if ($studentResult && $studentResult->num_rows > 0) {
        while ($student = $studentResult->fetch_assoc()) {
            $students[] = $student;
        }
    }

    if (empty($students)) {
        echo json_encode([
            'status' => 'incorrect',
            'msg' => 'No active students found in database.'
        ]);
        exit();
    }

    $mailer = new MailService();
    $sentCount = 0;
    $failedCount = 0;
    $failedRegNumbers = [];
    $failedReasons = [];

    // Target specifications for single or group sends
    $targetEmail = isset($_POST['targetEmail']) ? strtolower(trim((string) $_POST['targetEmail'])) : 'itt2021106@tec.rjt.ac.lk';
    $targetRegNo = isset($_POST['targetRegNo']) ? strtoupper(trim((string) $_POST['targetRegNo'])) : 'ITT/2021/106';
    $targetGroup = $isGroupRequest ? strtoupper(trim((string) $_POST['targetGroup'])) : '';
    $targetMatched = false;

    $allowedGroups = ['ITT', 'BST', 'ENT'];
    if ($isGroupRequest && !in_array($targetGroup, $allowedGroups, true)) {
        echo json_encode([
            'status' => 'incorrect',
            'msg' => 'Invalid student group: ' . $targetGroup
        ]);
        exit();
    }

    foreach ($students as $student) {
        $regNo = $student['reg_no'];
        $studentName = $student['Name'];
        $card = $student['card'] ?? '';

        // Example: ITT/2021/106 => itt2021106@tec.rjt.ac.lk
        $generatedEmailLocal = strtolower(str_replace('/', '', $regNo));
        $generatedEmail = $generatedEmailLocal . '@tec.rjt.ac.lk';

        // Check if single request match
        if ($isSingleRequest) {
            $cleanedRegNo = strtolower(str_replace('/', '', $regNo));
            $cleanedTargetRegNo = strtolower(str_replace('/', '', $targetRegNo));
            $isMatch = ($generatedEmail === $targetEmail) || ($cleanedRegNo === $cleanedTargetRegNo);

            if (!$isMatch) {
                continue;
            }
            $targetMatched = true;
        }

        // Check if group request match
        if ($isGroupRequest) {
            $studentGroup = strtoupper(substr($regNo, 0, 3));
            if ($studentGroup !== $targetGroup) {
                continue;
            }
            $targetMatched = true;
        }

        if (!filter_var($generatedEmail, FILTER_VALIDATE_EMAIL)) {
            $failedCount++;
            $failedRegNumbers[] = $regNo;
            continue;
        }

        // Render attractive Padura notice template
        $body = $mailer->renderTemplate('padura_notice', [
            'name' => $studentName,
            'regNo' => $regNo,
            'card' => $card,
            'email' => $generatedEmail,
            'date' => date('l, F j, Y'),
            'companyName' => $config['settings']['company_name'] ?? 'Batch Fund (20/21)',
            'companyPhone' => '+9478 965 1168',
            'contactUrl' => $config['settings']['contact_url'] ?? '#',
            'webUrl' => $config['settings']['baseURL'] ?? 'http://localhost/batchfund',
            'companyEmail' => 'theekshanathennakoon79@gmail.com',
            'companyLogo' => 'cid:company_logo',
        ]);

        $subject = '🪕 Official පැදුර 2026 Announcement - ' . $regNo;
        $result = $mailer->sendMail($generatedEmail, $subject, $body);

        if ($result === true) {
            $sentCount++;
        } else {
            $failedCount++;
            $failedRegNumbers[] = $regNo;
            $failedReasons[] = $result;
        }

        // Stop early if single request was processed
        if ($isSingleRequest && $targetMatched) {
            break;
        }

        // Delay to prevent SMTP rate-limits
        usleep(300000); // 0.3s
    }

    if ($isSingleRequest && !$targetMatched) {
        echo json_encode([
            'status' => 'incorrect',
            'msg' => 'Student with registration number ' . $targetRegNo . ' not found.'
        ]);
        exit();
    }

    if ($sentCount > 0) {
        $msg = '';
        if ($isSingleRequest) {
            $msg = 'Official Padura 2026 notice sent successfully to ' . $targetRegNo . ' (' . $targetEmail . ').';
        } elseif ($isGroupRequest) {
            $msg = 'Dispatched Padura 2026 notices to ' . $sentCount . ' ' . $targetGroup . ' students.';
            if ($failedCount > 0) {
                $msg .= ' (' . $failedCount . ' failed).';
            }
        } else {
            $msg = 'Successfully broadcast Padura 2026 notices to ' . $sentCount . ' students!';
            if ($failedCount > 0) {
                $msg .= ' (' . $failedCount . ' failed).';
            }
        }

        echo json_encode([
            'status' => 'correct',
            'msg' => $msg,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'failed_reg' => $failedRegNumbers,
        ]);
        exit();
    }

    echo json_encode([
        'status' => 'incorrect',
        'msg' => 'Failed to send Padura emails. Error: ' . (!empty($failedReasons) ? reset($failedReasons) : 'Unknown SMTP error'),
        'failed_reg' => $failedRegNumbers
    ]);
    exit();

} catch (\Throwable $e) {
    echo json_encode([
        'status' => 'incorrect',
        'msg' => 'Server error: ' . $e->getMessage()
    ]);
    exit();
}

