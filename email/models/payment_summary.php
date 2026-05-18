<?php

use App\Services\MailService;

require_once __DIR__ . '/../services/MailService.php';
require_once __DIR__ . '/../../assets/database.php';

$config = require __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$isBulkRequest = isset($_POST['sendBulkSummaryStatus']);
$isSingleRequest = isset($_POST['sendSingleSummaryStatus']) && isset($_POST['targetEmail']);
$isGroupRequest = isset($_POST['sendGroupSummaryStatus']) && isset($_POST['targetGroup']);

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

    // Total expected amount from all reasons
    $totalAmount = 0;
    $totalResult = $conn->query('SELECT COALESCE(SUM(price), 0) AS total_amount FROM reasons');
    if ($totalResult && $totalRow = $totalResult->fetch_assoc()) {
        $totalAmount = (float) $totalRow['total_amount'];
    }

    // Build paid map for all students in one query
    $paidMap = [];
    $paidSummarySql = '
        SELECT p.reg_no, COALESCE(SUM(r.price), 0) AS total_paid
        FROM (
            SELECT DISTINCT reg_no, reason_id
            FROM payments
        ) p
        INNER JOIN reasons r ON r.id = p.reason_id
        GROUP BY p.reg_no
    ';
    $paidSummaryResult = $conn->query($paidSummarySql);
    if ($paidSummaryResult && $paidSummaryResult->num_rows > 0) {
        while ($row = $paidSummaryResult->fetch_assoc()) {
            $paidMap[$row['reg_no']] = (float) $row['total_paid'];
        }
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
    $targetEmail = $isSingleRequest ? strtolower(trim((string) $_POST['targetEmail'])) : '';
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

        if ($isSingleRequest && $generatedEmail !== $targetEmail) {
            continue;
        }

        if ($isSingleRequest && $generatedEmail === $targetEmail) {
            $targetMatched = true;
        }

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

        $paidAmount = $paidMap[$regNo] ?? 0;
        $balance = $totalAmount - $paidAmount;
        if ($balance < 0) {
            $balance = 0;
        }

        // Fetch all reasons with paid status for this student
        $reasonsData = [];
        $reasonsSql = 'SELECT r.id, r.reason, r.price FROM reasons r ORDER BY r.id';
        $reasonsResult = $conn->query($reasonsSql);
        if ($reasonsResult && $reasonsResult->num_rows > 0) {
            while ($reason = $reasonsResult->fetch_assoc()) {
                $reasonId = (int) $reason['id'];
                $paidSql = 'SELECT COUNT(*) as paid_count FROM payments WHERE reg_no = ? AND reason_id = ?';
                $paidCheck = $conn->prepare($paidSql);
                $paidCheck->bind_param('si', $regNo, $reasonId);
                $paidCheck->execute();
                $paidRow = $paidCheck->get_result()->fetch_assoc();
                $isPaid = $paidRow['paid_count'] > 0;
                $paidCheck->close();

                $reasonsData[] = [
                    'reason_name' => $reason['reason'],
                    'price' => (float) $reason['price'],
                    'is_paid' => $isPaid
                ];
            }
        }

        $body = $mailer->renderTemplate('payment_summary', [
            'name' => $studentName,
            'regNo' => $regNo,
            'card' => $card,
            'email' => $generatedEmail,
            'date' => date('l, F j, Y'),
            'totalAmount' => $totalAmount,
            'totalPaid' => $paidAmount,
            'balance' => $balance,
            'reasonsData' => $reasonsData,
            'companyName' => $config['settings']['company_name'],
            'companyPhone' => $config['settings']['company_phone'],
            'contactUrl' => $config['settings']['contact_url'],
            'webUrl' => $config['settings']['baseURL'],
            'bannerUrl' => $config['settings']['banner_url'],
            'companyEmail' => $config['settings']['admin_email'],
            'companyLogo' => '../assets/logo.png',
        ]);

        $subject = 'Payment Summary - ' . $regNo;
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
            ? 'No student matched for email: ' . $targetEmail
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
                ? 'Email sent successfully to ' . $targetEmail . '.'
                : 'Email sent successfully to ' . $targetGroup . ' students.';
            echo json_encode([
                'status' => 'correct',
                'msg' => $successMessage
            ]);
        } else {
            $errorMsg = $isSingleRequest
                ? 'Unable to send email to ' . $targetEmail . '.'
                : 'Unable to send emails to ' . $targetGroup . ' students.';
            if (!empty($failedReasons[$targetEmail])) {
                $errorMsg .= ' Reason: ' . $failedReasons[$targetEmail];
            } elseif (!empty($failedReasons)) {
                $errorMsg .= ' Reason: ' . reset($failedReasons);
            }
            echo json_encode([
                'status' => 'incorrect',
                'msg' => $errorMsg
            ]);
        }
        exit();
    }

    if ($sentCount > 0) {
        $message = 'Emails sent: ' . $sentCount . '. Failed: ' . $failedCount . '.';
        if (!empty($failedRegNumbers)) {
            $failedList = implode(', ', array_slice($failedRegNumbers, 0, 10));
            $message .= ' Failed Reg No(s): ' . $failedList;
            if (count($failedRegNumbers) > 10) {
                $message .= '...';
            }
            // Add first error reason if available
            $firstFailedRegNo = $failedRegNumbers[0];
            if (!empty($failedReasons[$firstFailedRegNo])) {
                $message .= ' First error: ' . $failedReasons[$firstFailedRegNo];
            }
        }

        echo json_encode([
            'status' => 'correct',
            'msg' => $message
        ]);
    } else {
        $errorMsg = 'Unable to send emails. Failed for all students.';
        // Include first error reason if available
        if (!empty($failedReasons)) {
            $firstError = reset($failedReasons);
            $errorMsg .= ' Reason: ' . $firstError;
        }
        echo json_encode([
            'status' => 'incorrect',
            'msg' => $errorMsg
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'incorrect',
        'msg' => 'Server Error: ' . $e->getMessage()
    ]);
}

exit();
