<?php
include("../assets/database.php");
date_default_timezone_set('Asia/Colombo');
session_start();

if (!isset($_SESSION["admin_logged_user"])) {
    header("Location:login.php");
    exit;
}

$total_students = 0;
$total_dropped = 0;

$countRes = $conn->query("
    SELECT 
        COUNT(*) AS total_students,
        SUM(IF(d.reg_no IS NOT NULL, 1, 0)) AS total_dropped
    FROM users u
    LEFT JOIN dropped_students d ON u.reg_no = d.reg_no
");
if ($countRes && $row = $countRes->fetch_assoc()) {
    $total_students = (int) $row['total_students'];
    $total_dropped = (int) ($row['total_dropped'] ?? 0);
}
$active_students = $total_students - $total_dropped;

// Check ITT/2021/106 specifically as sample test student
$testStudent = null;
$testResult = $conn->query("SELECT reg_no, Name, card FROM users WHERE reg_no = 'ITT/2021/106' LIMIT 1");
if ($testResult && $testResult->num_rows > 0) {
    $testStudent = $testResult->fetch_assoc();
}

$sample_students = [];
$sampleResult = $conn->query("
    SELECT u.reg_no, u.Name 
    FROM users u 
    WHERE u.reg_no NOT IN (SELECT reg_no FROM dropped_students)
    ORDER BY u.id DESC LIMIT 10
");
if ($sampleResult && $sampleResult->num_rows > 0) {
    while ($student = $sampleResult->fetch_assoc()) {
        $sample_students[] = $student;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send පැදුර 2026 (Padura Night) Notice - SCMP Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #e11d48;
            --secondary-color: #f8f9fc;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f8f9fc;
            overflow-x: hidden;
            color: #1e293b;
        }

        #content {
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
        }

        .card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .card-header-gradient {
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
            color: #ffffff;
            padding: 18px 22px;
        }

        .event-banner-card {
            background: linear-gradient(135deg, #4c0519 0%, #1e1b4b 100%);
            color: #ffffff;
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid rgba(244, 63, 94, 0.35);
            box-shadow: 0 10px 30px rgba(225, 29, 72, 0.25);
        }

        .badge-pill-custom {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .test-student-box {
            background-color: #fff1f2;
            border: 1px solid #fecdd3;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 20px;
        }

        .btn-test-send {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: #ffffff !important;
            border: none;
            font-weight: 700;
            padding: 12px 18px;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
            transition: all 0.2s ease;
        }

        .btn-test-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.45);
        }

        .btn-bulk-send {
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
            color: #ffffff !important;
            border: none;
            font-weight: 700;
            padding: 14px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(225, 29, 72, 0.35);
            transition: all 0.2s ease;
        }

        .btn-bulk-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(225, 29, 72, 0.5);
        }

        @media (max-width: 768px) {
            #content {
                width: 100%;
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <?php include("sidebar.php"); ?>

    <div id="content">
        <?php include("topbar.php"); ?>

        <div class="container py-3">
            <!-- Breadcrumb Navigation -->
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h3 class="mb-1 fw-bold text-dark"><i class="fas fa-guitar text-danger me-2"></i>පැදුර 2026 (Padura Night) Email Notice</h3>
                    <p class="text-muted mb-0 small">Send official announcement notices & invitations to Batch 20/21 members</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="padura_demo.php" target="_blank" class="btn btn-outline-danger">
                        <i class="fas fa-eye me-1"></i> Preview Template
                    </a>
                    <a href="./" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Event Highlight Banner -->
            <div class="event-banner-card">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <span class="badge-pill-custom" style="background:rgba(244,63,94,0.25);border:1px solid #fb7185;color:#fecdd3;">
                                <i class="fas fa-calendar-alt"></i> Event Date: 2026.11.23 (Monday)
                            </span>
                            <span class="badge-pill-custom" style="background:rgba(245,158,11,0.25);border:1px solid #fbbf24;color:#fde68a;">
                                <i class="fas fa-clock"></i> 06:00 PM Onwards
                            </span>
                            <span class="badge-pill-custom" style="background:rgba(59,130,246,0.25);border:1px solid #60a5fa;color:#bfdbfe;">
                                <i class="fas fa-map-marker-alt"></i> Venue: University Premises (විශ්වවිද්‍යාල පරිශ්‍රයේදී)
                            </span>
                            <span class="badge-pill-custom" style="background:rgba(16,185,129,0.25);border:1px solid #34d399;color:#a7f3d0;">
                                <i class="fas fa-users"></i> <?= number_format($active_students); ?> Active Students (<?= number_format($total_dropped); ?> Dropped Excluded)
                            </span>
                        </div>
                        <h4 class="fw-bold mb-1 text-white">පැදුර 2026 (Padura Musical Night) Announcement</h4>
                        <p class="text-white-50 mb-0 small">
                            "An Evening of Acoustic Harmony, Cultural Melodies & Unbroken Batch Bonding" &bull; Automated invitations delivered directly to student university inboxes (<code>regno@tec.rjt.ac.lk</code>).
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="padura_demo.php" target="_blank" class="btn btn-light fw-bold text-danger">
                            <i class="fas fa-external-link-alt me-1"></i> Full HTML Preview
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left Column: Delivery Actions -->
                <div class="col-lg-6 mb-4">
                    <!-- TEST SEND CARD FOR ITT/2021/106 -->
                    <div class="card mb-4 border-success">
                        <div class="card-header bg-success text-white py-3">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-vial me-2"></i>1. Test Send (ITT/2021/106)</h5>
                        </div>
                        <div class="card-body">
                            <div class="test-student-box">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong class="text-danger"><i class="fas fa-user-check me-1"></i>Target Test Student:</strong>
                                    <span class="badge bg-success">Selected for Test</span>
                                </div>
                                <div class="small mb-1">
                                    <strong>Registration No:</strong> <span class="badge bg-dark">ITT/2021/106</span>
                                </div>
                                <div class="small mb-1">
                                    <strong>Student Name:</strong> <?= htmlspecialchars($testStudent['Name'] ?? 'ITT Student') ?>
                                </div>
                                <div class="small">
                                    <strong>Target Recipient:</strong> <code class="text-dark fw-bold">itt2021106@tec.rjt.ac.lk</code>
                                </div>
                            </div>

                            <p class="text-muted small mb-3">
                                <i class="fas fa-info-circle me-1"></i> Send a test email to your inbox to check the Padura email layout, styling, and Google Calendar button before broadcasting to all batch members.
                            </p>

                            <form class="padura-email-form">
                                <input type="hidden" name="sendSinglePaduraStatus" value="1">
                                <input type="hidden" name="targetRegNo" value="ITT/2021/106">
                                <input type="hidden" name="targetEmail" value="itt2021106@tec.rjt.ac.lk">
                                <button type="submit" class="btn btn-test-send w-100" data-reset-text="Send Test Email To ITT/2021/106" data-processing-text="Sending Test to ITT/2021/106...">
                                    <span class="btn-text"><i class="fas fa-paper-plane me-2"></i>Send Test Email To ITT/2021/106</span>
                                    <span class="btn-spinner" style="display:none;"></span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- BULK & GROUP SEND CARD -->
                    <div class="card">
                        <div class="card-header-gradient">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-paper-plane me-2"></i>2. Send Notice to Batch Members</h5>
                        </div>
                        <div class="card-body">
                            <!-- BULK SEND BUTTON -->
                            <div class="mb-4">
                                <label class="fw-bold mb-2">Broadcast to All Active Batch Members:</label>
                                <form class="padura-email-form">
                                    <input type="hidden" name="sendBulkPaduraStatus" value="1">
                                    <button type="submit" class="btn btn-bulk-send w-100 btn-lg" id="sendAllBtn" data-reset-text="Send Padura Notice To ALL Active Students" data-processing-text="Sending To All Students..." <?= $active_students === 0 ? 'disabled' : ''; ?>>
                                        <span class="btn-text"><i class="fas fa-broadcast-tower me-2"></i>Send Padura Notice To ALL Active Students (<?= number_format($active_students); ?>)</span>
                                        <span class="btn-spinner" style="display:none;"></span>
                                    </button>
                                </form>
                                <small class="text-muted d-block mt-2">
                                    * Prompts for confirmation before dispatching. Dropped students are automatically excluded.
                                </small>
                            </div>

                            <hr>

                            <!-- GROUP SEND BUTTONS -->
                            <label class="fw-bold mb-2">Send by Degree / Department:</label>
                            <div class="d-grid gap-2">
                                <form class="padura-email-form">
                                    <input type="hidden" name="sendGroupPaduraStatus" value="1">
                                    <input type="hidden" name="targetGroup" value="ITT">
                                    <button type="submit" class="btn btn-warning w-100 fw-bold py-2" data-reset-text="Send To ITT Students" data-processing-text="Sending to ITT Students...">
                                        <span class="btn-text"><i class="fas fa-paper-plane me-2"></i>Send to ITT Students Only</span>
                                        <span class="btn-spinner" style="display:none;"></span>
                                    </button>
                                </form>

                                <form class="padura-email-form">
                                    <input type="hidden" name="sendGroupPaduraStatus" value="1">
                                    <input type="hidden" name="targetGroup" value="BST">
                                    <button type="submit" class="btn btn-info w-100 text-white fw-bold py-2" data-reset-text="Send To BST Students" data-processing-text="Sending to BST Students...">
                                        <span class="btn-text"><i class="fas fa-paper-plane me-2"></i>Send to BST Students Only</span>
                                        <span class="btn-spinner" style="display:none;"></span>
                                    </button>
                                </form>

                                <form class="padura-email-form">
                                    <input type="hidden" name="sendGroupPaduraStatus" value="1">
                                    <input type="hidden" name="targetGroup" value="ENT">
                                    <button type="submit" class="btn btn-secondary w-100 fw-bold py-2" data-reset-text="Send To ENT Students" data-processing-text="Sending to ENT Students...">
                                        <span class="btn-text"><i class="fas fa-paper-plane me-2"></i>Send to ENT Students Only</span>
                                        <span class="btn-spinner" style="display:none;"></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Student Recipient Preview & Template Mockup -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-list-check me-2"></i>Recipient Address Preview</h5>
                            <span class="badge bg-secondary"><?= count($sample_students); ?> Active Samples</span>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info py-2 small mb-3">
                                <i class="fas fa-info-circle me-1"></i> Generated using formula: <code>strtolower(str_replace('/', '', reg_no)) . '@tec.rjt.ac.lk'</code>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Reg No</th>
                                            <th>Name</th>
                                            <th>Generated Email</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($sample_students)): ?>
                                            <?php foreach ($sample_students as $st): ?>
                                                <?php $genMail = strtolower(str_replace('/', '', $st['reg_no'])) . '@tec.rjt.ac.lk'; ?>
                                                <tr <?= ($st['reg_no'] === 'ITT/2021/106') ? 'class="table-success fw-bold"' : '' ?>>
                                                    <td>
                                                        <?= htmlspecialchars($st['reg_no']) ?>
                                                        <?php if ($st['reg_no'] === 'ITT/2021/106'): ?>
                                                            <span class="badge bg-success ms-1">Test Target</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($st['Name']) ?></td>
                                                    <td><small><?= htmlspecialchars($genMail) ?></small></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center py-3 text-muted">No active students in database.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Live Template Preview Frame -->
                            <div class="mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong class="small text-muted text-uppercase fw-bold"><i class="fas fa-envelope-open-text me-1"></i>Template Preview:</strong>
                                    <a href="padura_demo.php" target="_blank" class="small text-danger text-decoration-none fw-bold">
                                        Open in Full Window <i class="fas fa-external-link-alt ms-1"></i>
                                    </a>
                                </div>
                                <div style="border:1px solid #cbd5e1;border-radius:12px;overflow:hidden;background:#0f172a;">
                                    <iframe src="padura_demo.php" style="width:100%;height:380px;border:none;background:#ffffff;"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/sweetalert.js"></script>
    <script src="../email/ajax/padura_notice.js"></script>
</body>

</html>

