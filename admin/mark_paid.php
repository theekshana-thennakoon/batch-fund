<?php
include("../assets/database.php");
date_default_timezone_set('Asia/Colombo');
session_start();


// Initialize variables
$reg_no = '';
$student_name = '';
$reasons = [];
$student_found = false;
$payment_details = [];
$tot_paid = 0;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['search'])) {
        // Search for student
        $reg_no = trim($_POST['reg_no']);

        $stmt = $conn->prepare("SELECT Name FROM users WHERE reg_no = ?");
        $stmt->bind_param("s", $reg_no);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();
            $student_name = $student['Name'];
            $student_found = true;

            // Get all payment reasons
            $reason_query = "SELECT id, reason, price FROM reasons ORDER BY id";
            $reason_result = $conn->query($reason_query);
            while ($row = $reason_result->fetch_assoc()) {
                $reasons[] = $row;
            }

            // Get already paid reasons for this student
            $paid_query = "SELECT reason_id FROM payments WHERE reg_no = ?";
            $paid_stmt = $conn->prepare($paid_query);
            $paid_stmt->bind_param("s", $reg_no);
            $paid_stmt->execute();
            $paid_result = $paid_stmt->get_result();
            $paid_reasons = [];
            while ($row = $paid_result->fetch_assoc()) {
                $paid_reasons[] = $row['reason_id'];
            }
            $paid_stmt->close();
        }
        $stmt->close();
    }

    if (isset($_POST['mark_paid'])) {
        // Process payment marking
        $reg_no = $_POST['reg_no'];
        $marked_reasons = $_POST['paid_reasons'] ?? [];

        foreach ($marked_reasons as $reason_id) {
            // First get reason details (using your requested approach)
            $sql_reason_paid = "SELECT id, reason, price FROM reasons WHERE id = ?";
            $stmt_reason = $conn->prepare($sql_reason_paid);
            $stmt_reason->bind_param("i", $reason_id);
            $stmt_reason->execute();
            $result_reason_paid = $stmt_reason->get_result();

            if ($result_reason_paid->num_rows > 0) {
                while ($row_reason_paid = $result_reason_paid->fetch_assoc()) {
                    $upid_paid = $row_reason_paid["id"];
                    $reason_paid = $row_reason_paid["reason"];
                    $reason_price_paid = $row_reason_paid["price"];
                    $tot_paid += $reason_price_paid;

                    // Record the payment
                    // Sanitize inputs first (minimum protection)
                    $safe_reg_no = $conn->real_escape_string($reg_no);
                    $safe_upid_paid = (int)$upid_paid; // Force to integer

                    $sql = "INSERT INTO payments (reg_no, reason_id, paid_date) 
        VALUES ('{$safe_reg_no}', {$safe_upid_paid}, NOW())";

                    if ($conn->query($sql)) {
                        // Success - payment recorded
                    } else {
                        // Error handling
                        echo "Error: " . $conn->error;
                    }

                    // Store payment details for display
                    $payment_details[] = [
                        'reason' => $reason_paid,
                        'amount' => $reason_price_paid
                    ];
                }
            }
            $stmt_reason->close();
        }

        $_SESSION['success'] = "Payments totaling Rs. " . number_format($tot_paid, 2) . " marked as paid successfully!";
        echo "<script>window.history.back();</script>";
        exit();
    }
}

// If reg_no passed in URL (after successful submission)
if (isset($_GET['reg_no'])) {
    $reg_no = $_GET['reg_no'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
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

        /* Sidebar Styles */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
            color: white;
            transition: all 0.3s;
            z-index: 1000;
        }

        #sidebar .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        #sidebar .sidebar-header h3 {
            color: white;
            margin: 0;
        }

        #sidebar ul.components {
            padding: 20px 0;
        }

        #sidebar ul li a {
            padding: 12px 20px;
            font-size: 1em;
            display: block;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }

        #sidebar ul li a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        #sidebar ul li.active>a {
            color: white;
            background: rgba(255, 255, 255, 0.2);
        }

        #sidebar ul li a i {
            margin-right: 10px;
        }

        /* Content Styles */
        #content {
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
        }

        /* Header Styles */
        .topbar {
            height: var(--header-height);
            background: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .topbar .navbar-search {
            width: 25rem;
        }

        .topbar .dropdown-list {
            padding: 0;
            border: none;
            overflow: hidden;
            width: 20rem;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }

        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }

        .table thead {
            background-color: #4e73df;
            color: white;
        }

        .table th {
            font-weight: 600;
        }

        .student-info {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .paid-checkbox {
            transform: scale(1.5);
            margin: 0 auto;
            display: block;
        }

        .paid-badge {
            background-color: #28a745;
        }

        /* Responsive */
        @media (max-width: 768px) {
            #sidebar {
                margin-left: -250px;
            }

            #sidebar.active {
                margin-left: 0;
            }

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
    <!-- Sidebar -->
    <?php include("sidebar.php"); ?>

    <!-- Page Content -->
    <div id="content">
        <!-- Top Navigation -->
        <?php include("topbar.php"); ?>

        <!-- Begin Page Content -->
        <div class="container py-2">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><i class="fas fa-money-check-alt me-2"></i>Payment Processing System</h4>
                        </div>
                        <div class="card-body">
                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success alert-dismissible fade show">
                                    <?php echo $_SESSION['success'];
                                    unset($_SESSION['success']); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="mb-4">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-9">
                                        <label for="reg_no" class="form-label">Student Registration Number</label>
                                        <input type="text" class="form-control form-control-lg" id="reg_no" name="reg_no"
                                            value="<?php echo htmlspecialchars($reg_no); ?>" required
                                            placeholder="Enter registration number (e.g., BST/2021/001)"
                                            oninput="formatRegistrationNumber(this)">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" name="search" class="btn btn-primary btn-lg w-100">
                                            <i class="fas fa-search me-2"></i> Search
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <?php if ($student_found): ?>
                                <div class="student-info mb-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5><i class="fas fa-user-graduate me-2"></i>Student Details</h5>
                                            <p class="mb-1"><strong>Registration No:</strong> <?php echo htmlspecialchars($reg_no); ?></p>
                                            <p class="mb-0"><strong>Name:</strong> <?php echo htmlspecialchars($student_name); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h5><i class="fas fa-info-circle me-2"></i>Payment Summary</h5>
                                            <?php
                                            $tot_paid = 0;
                                            $sql_payment = "SELECT * FROM payments WHERE reg_no = '{$reg_no}'";
                                            $result_payment = $conn->query($sql_payment);
                                            if ($result_payment->num_rows > 0) {
                                                while ($row_payment = $result_payment->fetch_assoc()) {
                                                    $pid = $row_payment["id"];
                                                    $reason_id = $row_payment["reason_id"];

                                                    $sql_reason_paid = "SELECT * FROM reasons WHERE id = {$reason_id}";
                                                    $result_reason_paid = $conn->query($sql_reason_paid);
                                                    if ($result_reason_paid->num_rows > 0) {
                                                        while ($row_reason_paid = $result_reason_paid->fetch_assoc()) {
                                                            $upid_paid = $row_reason_paid["id"];
                                                            $reason_paid = $row_reason_paid["reason"];
                                                            $reason_price_paid = $row_reason_paid["price"];
                                                            $tot_paid += $reason_price_paid;
                                                        }
                                                    }
                                                }
                                            }
                                            ?>
                                            <p class="mb-0"><strong>Total Paid:</strong> Rs. <?php echo number_format($tot_paid, 2); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <form method="POST">
                                    <input type="hidden" name="reg_no" value="<?php echo htmlspecialchars($reg_no); ?>">
                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered table-hover">
                                            <!-- Add this right after the opening <tbody> tag -->
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th width="60%">Payment Reason</th>
                                                    <th width="20%">Amount</th>
                                                    <th width="20%">
                                                        <div class="form-check d-flex justify-content-center">
                                                            <center>
                                                            <input type="checkbox" class="form-check-input paid-checkbox" id="selectAll" style="border: 1px solid #427BFF;">
                                                            <label class="form-check-label text-primary ms-2" for="selectAll">Select All</label>
                                                            </center>
                                                        </div>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($reasons as $reason):
                                                    $is_paid = in_array($reason['id'], $paid_reasons ?? []);
                                                ?>
                                                    <tr class="<?php echo $is_paid ? 'table-success' : ''; ?>">
                                                        <td><?php echo htmlspecialchars($reason['reason']); ?></td>
                                                        <td>Rs. <?php echo number_format($reason['price'], 2); ?></td>
                                                        <td class="text-center">
                                                            <?php if ($is_paid): ?>
                                                                <span class="badge paid-badge"><i class="fas fa-check-circle me-1"></i> Paid</span>
                                                            <?php else: ?>
                                                                <div class="form-check d-flex justify-content-center">
                                                                    <center>

                                                                        <input type="checkbox" class="form-check-input paid-checkbox" style="border: 1px solid #427BFF;"
                                                                            name="paid_reasons[]" value="<?php echo $reason['id']; ?>">
                                                                    </center>
                                                                </div>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <a href="./mark_paid.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left me-2"></i> Back to Students
                                        </a>
                                        <button type="submit" name="mark_paid" class="btn btn-success px-4">
                                            <i class="fas fa-check-circle me-2"></i> Mark Selected as Paid
                                        </button>
                                    </div>
                                </form>
                            <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$student_found): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    No student found with registration number: <?php echo htmlspecialchars($reg_no); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </div>
    
    <script>
        function formatRegistrationNumber(input) {
            // Get current cursor position
            let cursorPos = input.selectionStart;
            let originalValue = input.value;
            
            // Remove all slashes first to avoid duplicates
            let value = originalValue.replace(/\//g, '');
            
            // Check if the input starts with BST, ITT, or ENT (case insensitive)
            const prefixMatch = value.match(/^(BST|ITT|ENT)/i);
            if (prefixMatch) {
                const prefix = prefixMatch[0].toUpperCase();
                
                // Insert first slash after prefix
                value = prefix + (value.length > prefix.length ? '/' + value.substring(prefix.length) : '');
                
                // If we have at least 4 more characters (for year), insert second slash
                if (value.length > prefix.length + 5) {
                    const beforeYear = value.substring(0, prefix.length + 1);
                    const yearAndAfter = value.substring(prefix.length + 1);
                    
                    // Insert slash after year (assuming year is 4 digits)
                    if (/^\d{4}/.test(yearAndAfter)) {
                        value = beforeYear + yearAndAfter.substring(0, 4) + 
                               (yearAndAfter.length > 4 ? '/' + yearAndAfter.substring(4) : '');
                    }
                }
            }
            
            // Only update if the value has changed to avoid cursor jumping
            if (value !== originalValue) {
                input.value = value;
                
                // Adjust cursor position
                if (cursorPos === originalValue.length) {
                    // If cursor was at end, keep it at end
                    cursorPos = value.length;
                } else {
                    // Otherwise, try to maintain relative position
                    // This is a simplified approach - you might need more complex logic
                    const addedSlashes = (value.match(/\//g) || []).length - (originalValue.match(/\//g) || []).length;
                    cursorPos += addedSlashes;
                }
                
                input.setSelectionRange(cursorPos, cursorPos);
            }
        }
    </script>
    <!-- End of Page Content -->
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
    
    
    <!-- Bootstrap core JavaScript-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom scripts -->
    <script>
        // Toggle sidebar on mobile
        $(document).ready(function() {
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('active');
            });
        });
    </script>
    <script>
        // Select All functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('tbody .paid-checkbox');
            checkboxes.forEach(checkbox => {
                if (!checkbox.closest('tr').classList.contains('table-success')) {
                    checkbox.checked = this.checked;
                }
            });
        });

        // Uncheck "Select All" if any checkbox is unchecked
        document.querySelectorAll('tbody .paid-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (!this.checked) {
                    document.getElementById('selectAll').checked = false;
                } else {
                    // Check if all checkboxes are now checked
                    const allChecked = Array.from(document.querySelectorAll('tbody .paid-checkbox'))
                        .every(cb => cb.checked || cb.closest('tr').classList.contains('table-success'));
                    document.getElementById('selectAll').checked = allChecked;
                }
            });
        });
    </script>
</body>

</html>