<?php
// dashboard.php
session_start();
include("../assets/database.php");

// Set UTF-8 encoding for the database connection
$conn->set_charset("utf8");

// Handle Excel export
if (isset($_POST['export_excel'])) {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="student_expenses_' . date('Y-m-d') . '.xls"');

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
                        <x:Name>Student Expenses</x:Name>
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
                font-size: 12pt;
            }
            th {
                background-color: #4e73df;
                color: white;
                font-weight: bold;
                padding: 8px;
                text-align: left;
            }
            td {
                border: 1px solid #dddddd;
                padding: 8px;
                text-align: left;
            }
            .price-cell {
                font-weight: 500;
                color: #2e59d9;
            }
            .total-row {
                background-color: #f8f9fc;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
    <table>';

    // Table headers
    echo '<tr>
            <th>Reason</th>
            <th>Price</th>
            <th>Amount Received</th>
          </tr>';

    // Get data from database
    $sql_reason = "SELECT * FROM reasons";
    $result_reason = $conn->query($sql_reason);

    $total_price = 0;
    $grand_total = 0;

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

            // Output table row with Sinhala text
            echo '<tr>
                    <td>' . $reason . '</td>
                    <td class="price-cell">Rs. ' . number_format($reason_price, 2) . '</td>
                    <td class="price-cell">Rs. ' . number_format($price, 2) . '</td>
                  </tr>';
        }
    }

    // Output totals row
    echo '<tr class="total-row">
            <td><strong>Total Amount Due</strong></td>
            <td><strong>Rs. ' . number_format($total_price, 2) . '</strong></td>
            <td><strong>Rs. ' . number_format($grand_total, 2) . '</strong></td>
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

        /* Content Styles */
        #content {
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
            padding: 20px;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table td {
            vertical-align: middle;
        }

        .price-cell {
            font-weight: 500;
            color: #2e59d9;
        }

        .total-row {
            background-color: #f8f9fc;
            font-weight: bold;
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
                            <h6 class="m-0 font-weight-bold text-primary">Payment Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="50%">Reason</th>
                                            <th width="30%">Price</th>
                                            <th width="20%">Amount received</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql_reason = "SELECT * FROM reasons";
                                        $result_reason = $conn->query($sql_reason);
                                        $total_price = 0;
                                        $grand_total = 0;

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
                                        ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($reason, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="price-cell">Rs. <?php echo number_format($reason_price, 2); ?></td>
                                                    <td class="price-cell">Rs. <?php echo number_format($price, 2); ?></td>
                                                </tr>
                                        <?php
                                            }
                                        }
                                        ?>
                                        <tr class="total-row">
                                            <td>Total Amount Due</td>
                                            <td class="price-cell">Rs. <?php echo number_format($total_price, 2); ?></td>
                                            <td class="price-cell">Rs. <?php echo number_format($grand_total, 2); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
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