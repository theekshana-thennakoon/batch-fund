<?php
include("../assets/database.php");
date_default_timezone_set('Asia/Colombo');
session_start();

if (!isset($_SESSION["admin_logged_user"])) {
    header("Location:login.php");
    exit;
}

$total_students = 0;
$totalResult = $conn->query("SELECT COUNT(*) AS total_students FROM users");
if ($totalResult && $row = $totalResult->fetch_assoc()) {
    $total_students = (int) $row['total_students'];
}

$sample_students = [];
$sampleResult = $conn->query("SELECT reg_no, Name FROM users ORDER BY id DESC LIMIT 8");
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
    <title>Send Payment Email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
            overflow-x: hidden;
        }

        #content {
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
        }

        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 1.5rem;
        }

        .student-info {
            background-color: #eef2ff;
            border-left: 4px solid var(--primary-color);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            #content {
                width: 100%;
                margin: 0;
            }

            #content.active {
                margin-left: 250px;
                width: calc(100% - 250px);
            }
        }
    </style>
</head>

<body>
    <?php include("sidebar.php"); ?>

    <div id="content">
        <?php include("topbar.php"); ?>

        <div class="container py-2">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><i class="fas fa-envelope me-2"></i>Send Payment Summary Emails (All Students)</h4>
                        </div>
                        <div class="card-body">
                            <div class="student-info">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5><i class="fas fa-users me-2"></i>Bulk Email Delivery</h5>
                                        <p class="mb-1"><strong>Total Students:</strong> <?php echo number_format($total_students); ?></p>
                                        <p class="mb-0">Email format: <strong>regno_without_slashes@tec.rjt.ac.lk</strong></p>
                                    </div>
                                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                        <div class="d-grid gap-2">
                                            <form class="payment-summary-form">
                                                <input type="hidden" name="sendBulkSummaryStatus" value="1">
                                                <button type="submit" class="btn btn-success btn-lg w-100" id="sendBtn" data-reset-text="Send To All Students" data-processing-text="Sending Bulk Emails..." <?php echo $total_students === 0 ? 'disabled' : ''; ?>>
                                                    <span class="btn-text"><i class="fas fa-paper-plane me-2"></i>Send To All Students</span>
                                                    <span class="btn-spinner" style="display:none;"></span>
                                                </button>
                                            </form>
                                            <form class="payment-summary-form">
                                                <input type="hidden" name="sendSingleSummaryStatus" value="1">
                                                <input type="hidden" name="targetEmail" value="itt2021106@tec.rjt.ac.lk">
                                                <button type="submit" class="btn btn-outline-dark btn-lg w-100" data-reset-text="Send Test Email" data-processing-text="Sending Test Email..." <?php echo $total_students === 0 ? 'disabled' : ''; ?>>
                                                    <span class="btn-text"><i class="fas fa-flask me-2"></i>Send Test Email To itt2021106@tec.rjt.ac.lk</span>
                                                    <span class="btn-spinner" style="display:none;"></span>
                                                </button>
                                            </form>
                                            <form class="payment-summary-form">
                                                <input type="hidden" name="sendGroupSummaryStatus" value="1">
                                                <input type="hidden" name="targetGroup" value="ITT">
                                                <button type="submit" class="btn btn-warning btn-lg w-100" data-reset-text="Send To ITT Students" data-processing-text="Sending ITT Emails..." <?php echo $total_students === 0 ? 'disabled' : ''; ?>>
                                                    <span class="btn-text"><i class="fas fa-paper-plane me-2"></i>Send To ITT Students</span>
                                                    <span class="btn-spinner" style="display:none;"></span>
                                                </button>
                                            </form>
                                            <form class="payment-summary-form">
                                                <input type="hidden" name="sendGroupSummaryStatus" value="1">
                                                <input type="hidden" name="targetGroup" value="BST">
                                                <button type="submit" class="btn btn-info btn-lg w-100 text-white" data-reset-text="Send To BST Students" data-processing-text="Sending BST Emails..." <?php echo $total_students === 0 ? 'disabled' : ''; ?>>
                                                    <span class="btn-text"><i class="fas fa-paper-plane me-2"></i>Send To BST Students</span>
                                                    <span class="btn-spinner" style="display:none;"></span>
                                                </button>
                                            </form>
                                            <form class="payment-summary-form">
                                                <input type="hidden" name="sendGroupSummaryStatus" value="1">
                                                <input type="hidden" name="targetGroup" value="ENT">
                                                <button type="submit" class="btn btn-secondary btn-lg w-100" data-reset-text="Send To ENT Students" data-processing-text="Sending ENT Emails..." <?php echo $total_students === 0 ? 'disabled' : ''; ?>>
                                                    <span class="btn-text"><i class="fas fa-paper-plane me-2"></i>Send To ENT Students</span>
                                                    <span class="btn-spinner" style="display:none;"></span>
                                                </button>
                                            </form>
                                            <a href="payment_summary_demo.php" target="_blank" rel="noopener" class="btn btn-outline-primary btn-lg w-100">
                                                <i class="fas fa-eye me-2"></i>View Template Demo
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($sample_students)): ?>
                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered table-striped">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Reg No</th>
                                                <th>Name</th>
                                                <th>Generated Email</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($sample_students as $student): ?>
                                                <?php $generatedEmail = strtolower(str_replace('/', '', $student['reg_no'])) . '@tec.rjt.ac.lk'; ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($student['reg_no']); ?></td>
                                                    <td><?php echo htmlspecialchars($student['Name']); ?></td>
                                                    <td><?php echo htmlspecialchars($generatedEmail); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted">Showing latest <?php echo count($sample_students); ?> students preview.</small>
                            <?php else: ?>
                                <div class="alert alert-warning mt-3 mb-0">
                                    No students found in database.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/sweetalert.js"></script>
    <script src="../email/ajax/payment_summary.js"></script>
</body>

</html>