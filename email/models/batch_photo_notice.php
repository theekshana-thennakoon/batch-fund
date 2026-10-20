<?php

use App\Services\MailService;

require_once __DIR__ . '/../services/MailService.php';
require_once __DIR__ . '/../../assets/database.php';

$config = require __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$isBulkRequest = isset($_POST['sendBulkBatchPhotoStatus']);
$isSingleRequest = isset($_POST['sendSingleBatchPhotoStatus']);
$isGroupRequest = isset($_POST['sendGroupBatchPhotoStatus']) && isset($_POST['targetGroup']);

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

    // Fetch all students
    $students = [];
    $studentResult = $conn->query('SELECT reg_no, Name, card FROM users ORDER BY id ASC');
    if ($studentResult && $studentResult->num_rows > 0) {
        while ($student = $studentResult->fetch_assoc()) {
            $students[] = $student;
        }
    }

    if (empty($students)) {
        echo json_encode([
            'status' => 'incorrect',
            'msg' => 'No students found in database.'
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

        // Render attractive template
        $body = $mailer->renderTemplate('batch_photo_notice', [
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
            'companyLogo' => '../assets/logo.png',
        ]);

        $subject = '📸 Official Batch Photo 2026 Announcement - ' . $regNo;
        $result = $mailer->sendMail($generatedEmail, $subject, $body);

        if ($result === true) {
            $sentCount++;
        } else {
            $failedCount++;
            $failedRegNumbers[] = $regNo;
            $failedReasons[$regNo] = $result;
        }

        if ($isSingleRequest) {
            break;
        }
    }

    if (($isSingleRequest || $isGroupRequest) && !$targetMatched) {
        $notFoundMessage = $isSingleRequest
            ? 'No student found for ' . $targetRegNo . ' (' . $targetEmail . ').'
            : 'No ' . $targetGroup . ' students found in database.';
        echo json_encode([
            'status' => 'incorrect',
            'msg' => $notFoundMessage
        ]);
        exit();
    }

    if ($isSingleRequest || $isGroupRequest) {
        if ($sentCount > 0) {
            $successMessage = $isSingleRequest
                ? 'Test Batch Photo email sent successfully to ' . $targetRegNo . ' (' . $targetEmail . ')!'
                : 'Batch Photo notice emails sent successfully to ' . $targetGroup . ' students!';
            echo json_encode([
                'status' => 'correct',
                'msg' => $successMessage
            ]);
            exit();
        }

        $singleErrorDetail = '';
        if ($isSingleRequest && isset($failedReasons[$targetRegNo])) {
            $singleErrorDetail = ' Error: ' . $failedReasons[$targetRegNo];
        }

        echo json_encode([
            'status' => 'incorrect',
            'msg' => 'Failed to send test email to ' . $targetRegNo . '.' . $singleErrorDetail
        ]);
        exit();
    }

    // Bulk result handling
    if ($sentCount > 0) {
        $msg = "Batch Photo notice sent successfully to {$sentCount} students.";
        if ($failedCount > 0) {
            $msg .= " Failed: {$failedCount} students.";
        }
        echo json_encode([
            'status' => 'correct',
            'msg' => $msg
        ]);
    } else {
        $firstError = !empty($failedReasons) ? reset($failedReasons) : 'Unknown mailer error';
        echo json_encode([
            'status' => 'incorrect',
            'msg' => 'Failed to send emails. Error: ' . $firstError
        ]);
    }
} catch (\Throwable $e) {
    echo json_encode([
        'status' => 'incorrect',
        'msg' => 'System error: ' . $e->getMessage()
    ]);
}
