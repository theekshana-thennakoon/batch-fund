<?php
// dashboard.php
session_start();
include("../assets/database.php");

// Set UTF-8 encoding for the database connection
$conn->set_charset("utf8");

// Handle Excel export
if (isset($_POST['export_excel'])) {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="student_payments_' . date('Y-m-d') . '.xls"');

    // Get all reasons
    $sql_reasons = "SELECT id, reason FROM reasons ORDER BY id";
    $result_reasons = $conn->query($sql_reasons);
    $reasons = [];
    while ($row = $result_reasons->fetch_assoc()) {
        $reasons[] = $row;
    }

    // Get all students with payments
    $sql_students = "SELECT DISTINCT u.id, u.reg_no, u.Name, u.card FROM users u 
                     INNER JOIN payments p ON u.reg_no = p.reg_no 
                     ORDER BY u.reg_no";
    $result_students = $conn->query($sql_students);
    $students = [];
    while ($row = $result_students->fetch_assoc()) {
        $students[] = $row;
    }

    // Start HTML output with proper encoding and Excel formatting
    echo '<!DOCTYPE html>
    <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <!--[if gte mso 9]>
        <xml>
            <x:ExcelWorkbook>
                <x:ExcelWorksheets>
                    <x:ExcelWorksheet>
                        <x:Name>Student Payments</x:Name>
                        <x:WorksheetOptions>
                            <x:DisplayGridlines/>
                        </x:WorksheetOptions>
                    </x:ExcelWorksheet>
                </x:ExcelWorksheets>
            </x:ExcelWorkbook>
        </xml>
        <![endif]-->
        <style>
            table {
                border-collapse: collapse;
                width: 100%;
                font-family: "Iskoola Pota", "FMAbhaya", Arial, sans-serif;
                font-size: 11pt;
            }
            th {
                background-color: #4e73df;
                color: white;
                font-weight: bold;
                padding: 8px;
                text-align: center;
                border: 1px solid #333;
            }
            td {
                border: 1px solid #dddddd;
                padding: 8px;
                text-align: center;
            }
            .student-name {
                text-align: left;
            }
            .total-row {
                background-color: #e8e8e8;
                font-weight: bold;
            }
            .reason-total {
                background-color: #f0f0f0;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
    <table>';

    // Table headers
    echo '<tr>
            <th>Student Name</th>
            <th>Reg No</th>
            <th>Card</th>';

    // Add reason headers
    foreach ($reasons as $reason) {
        echo '<th>' . htmlspecialchars($reason['reason']) . '</th>';
    }
    echo '<th>Student Total</th>
          </tr>';

    $reason_totals = [];
    foreach ($reasons as $reason) {
        $reason_totals[$reason['id']] = 0;
    }
    $grand_total = 0;

    // Add student data
    foreach ($students as $student) {
        $student_total = 0;
        echo '<tr>
                <td class="student-name">' . htmlspecialchars($student['Name']) . '</td>
                <td>' . htmlspecialchars($student['reg_no']) . '</td>
                <td>' . htmlspecialchars($student['card'] ?? '-') . '</td>';

        // Get payment for each reason
        foreach ($reasons as $reason) {
            $sql_payment = "SELECT COUNT(id) as count FROM payments WHERE reg_no = ? AND reason_id = ?";
            $stmt = $conn->prepare($sql_payment);
            $stmt->bind_param("si", $student['reg_no'], $reason['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $payment_row = $result->fetch_assoc();
            $count = $payment_row['count'];
            $stmt->close();

            // Get reason price
            $sql_price = "SELECT price FROM reasons WHERE id = ?";
            $stmt = $conn->prepare($sql_price);
            $stmt->bind_param("i", $reason['id']);
            $stmt->execute();
            $price_result = $stmt->get_result();
            $price_row = $price_result->fetch_assoc();
            $price = $price_row['price'];
            $stmt->close();

            $amount = $count > 0 ? $count * $price : 0;
            if ($amount > 0) {
                echo '<td>Rs. ' . number_format($amount, 2) . '</td>';
            } else {
                // leave empty cell for unpaid reasons to improve clarity
                echo '<td></td>';
            }

            $student_total += $amount;
            $reason_totals[$reason['id']] += $amount;
            $grand_total += $amount;
        }

        echo '<td><strong>Rs. ' . number_format($student_total, 2) . '</strong></td>
              </tr>';
    }

    // Reason totals row
    echo '<tr class="reason-total">
            <td colspan="3">REASON TOTALS</td>';

    foreach ($reasons as $reason) {
        echo '<td><strong>Rs. ' . number_format($reason_totals[$reason['id']], 2) . '</strong></td>';
    }

    echo '<td><strong>Rs. ' . number_format($grand_total, 2) . '</strong></td>
          </tr>';

    echo '</table></body></html>';
    exit();
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
            --dark-bg: #0f1419;
            --dark-card: #1a1f2e;
            --dark-border: #2d3748;
            --text-primary: #e9ecef;
            --text-secondary: #a8b2bf;
            --primary-color: #6366f1;
            --primary-light: #818cf8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-primary);
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        #sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(135deg, #1a1f2e 0%, #0f1419 100%);
            border-right: 2px solid var(--dark-border);
            color: white;
            transition: all 0.3s;
            z-index: 1000;
        }

        #sidebar .sidebar-header {
            padding: 25px;
            background: rgba(0, 0, 0, 0.1);
            text-align: center;
            border-bottom: 2px solid var(--dark-border);
        }

        #sidebar .sidebar-header h3 {
            color: var(--primary-light);
            margin: 0;
            font-weight: 700;
        }

        #sidebar ul.components {
            padding: 20px 0;
        }

        #sidebar ul li a {
            padding: 15px 25px;
            font-size: 0.95rem;
            display: block;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        #sidebar ul li a:hover {
            color: var(--primary-light);
            background: rgba(99, 102, 241, 0.1);
            border-left-color: var(--primary-light);
            padding-left: 30px;
        }

        #sidebar ul li.active>a {
            color: var(--primary-light);
            background: rgba(99, 102, 241, 0.1);
            border-left-color: var(--primary-light);
        }

        #sidebar ul li a i {
            margin-right: 12px;
            width: 20px;
        }

        /* Content Styles */
        #content {
            width: calc(100% - 260px);
            margin-left: 260px;
            min-height: 100vh;
            transition: all 0.3s;
            padding: 30px;
        }

        .card {
            background: var(--dark-card);
            border: 2px solid var(--dark-border);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
            background-color: transparent;
        }

        .table {
            background-color: transparent;
            color: var(--text-primary);
            margin-bottom: 0;
        }

        .table thead {
            background-color: rgba(99, 102, 241, 0.1);
            border-bottom: 2px solid var(--dark-border);
        }

        .table th {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            color: var(--primary-light);
            padding: 15px;
            text-align: center;
            border-color: var(--dark-border);
        }

        .table td {
            vertical-align: middle;
            padding: 12px 15px;
            text-align: center;
            border-color: var(--dark-border);
            color: var(--text-primary);
        }

        .table tbody tr {
            transition: all 0.3s;
            border-bottom-color: var(--dark-border);
        }

        .table tbody tr:hover {
            background-color: rgba(99, 102, 241, 0.05);
        }

        .price-cell {
            font-weight: 700;
            color: var(--success);
        }

        .total-row {
            background-color: rgba(99, 102, 241, 0.1);
            font-weight: bold;
            border-top: 2px solid var(--primary-light);
            border-bottom: 2px solid var(--primary-light);
        }

        .total-row td {
            color: var(--primary-light);
        }

        /* Reason Cards */
        .reason-card {
            background: var(--dark-card);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .reason-card:hover {
            border-color: var(--primary-light);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.15);
            transform: translateY(-2px);
        }

        .reason-info {
            flex: 1;
        }

        .reason-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-light);
            margin-bottom: 8px;
        }

        .reason-type {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 10px;
        }

        .reason-stats {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .stat-value.highlight {
            color: var(--success);
            font-size: 1.2rem;
        }

        /* Button */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            border: none;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(99, 102, 241, 0.3);
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            #sidebar {
                margin-left: -260px;
            }

            #sidebar.active {
                margin-left: 0;
            }

            #content {
                width: 100%;
                margin: 0;
            }

            #content.active {
                margin-left: 260px;
                width: calc(100% - 260px);
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
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Student Expenses</h1>
                <form method="post" action="">
                    <button type="submit" name="export_excel" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                        <i class="fas fa-download fa-sm text-white-50"></i> Export to Excel
                    </button>
                </form>
            </div>

            <!-- Content Row -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Payment Reasons Overview</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $sql_reason = "SELECT * FROM reasons";
                            $result_reason = $conn->query($sql_reason);
                            $total_price = 0;
                            $grand_total = 0;
                            $all_reasons = [];

                            if ($result_reason->num_rows > 0) {
                                while ($row_reason = $result_reason->fetch_assoc()) {
                                    $reason_id = $row_reason["id"];
                                    $reason_price = $row_reason["price"];
                                    $reason = $row_reason["reason"];

                                    // Get payment count for this reason (using prepared statement)
                                    $sql_payment = "SELECT COUNT(id) as r_c_id FROM payments WHERE reason_id = ?";
                                    $stmt = $conn->prepare($sql_payment);
                                    $stmt->bind_param("i", $reason_id);
                                    $stmt->execute();
                                    $result_payment = $stmt->get_result();
                                    $r_c_id = 0;

                                    if ($result_payment->num_rows > 0) {
                                        $row_payment = $result_payment->fetch_assoc();
                                        $r_c_id = $row_payment["r_c_id"];
                                    }
                                    $stmt->close();

                                    $price = $reason_price * $r_c_id;
                                    $total_price += $reason_price;
                                    $grand_total += $price;

                                    $all_reasons[] = [
                                        'id' => $reason_id,
                                        'name' => $reason,
                                        'price' => $reason_price,
                                        'count' => $r_c_id,
                                        'total' => $price
                                    ];
                                }
                            }
                            ?>

                            <!-- Reason Cards -->
                            <?php foreach ($all_reasons as $reason): ?>
                                <div class="reason-card">
                                    <div class="reason-info">
                                        <div class="reason-name">
                                            <i class="fas fa-receipt" style="margin-right: 8px; color: var(--primary-light);"></i>
                                            <?php echo htmlspecialchars($reason['name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                        <div class="reason-stats">
                                            <div class="stat-item">
                                                <div class="stat-label">Price Per Payment</div>
                                                <div class="stat-value">Rs. <?php echo number_format($reason['price'], 2); ?></div>
                                            </div>
                                            <div class="stat-item">
                                                <div class="stat-label">Payment Count</div>
                                                <div class="stat-value"><?php echo $reason['count']; ?></div>
                                            </div>
                                            <div class="stat-item">
                                                <div class="stat-label">Total Collected</div>
                                                <div class="stat-value highlight">Rs. <?php echo number_format($reason['total'], 2); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <!-- Summary Card -->
                            <div style="margin-top: 30px; padding: 20px; background: rgba(99, 102, 241, 0.1); border-radius: 12px; border: 2px solid var(--primary-light);">
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                                    <div>
                                        <div class="stat-label">Total Reasons</div>
                                        <div class="stat-value" style="color: var(--primary-light); font-size: 1.5rem;">
                                            <?php echo count($all_reasons); ?>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="stat-label">Total Payments</div>
                                        <div class="stat-value" style="color: var(--success); font-size: 1.5rem;">
                                            <?php $total_payments = 0;
                                            foreach ($all_reasons as $r) {
                                                $total_payments += $r['count'];
                                            }
                                            echo $total_payments; ?>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="stat-label">Grand Total Collected</div>
                                        <div class="stat-value" style="color: var(--warning); font-size: 1.5rem;">
                                            Rs. <?php echo number_format($grand_total, 2); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
</body>

</html>
<?php $conn->close(); ?>