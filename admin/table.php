<?php
include("../assets/database.php");
date_default_timezone_set('Asia/Colombo');
session_start();

if (!isset($_SESSION["admin_logged_user"])) {
    header("Location:login.php");
    exit;
}

// Set headers for Excel download
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=payment_report_" . date('Y-m-d_His') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Start HTML output
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">

<head>
    <meta charset="UTF-8">
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Payment Report</x:Name>
                    <x:WorksheetOptions>
                        <x:DisplayGridlines/>
                        <x:FreezePanes/>
                        <x:FrozenNoSplit/>
                        <x:SplitHorizontal>1</x:SplitHorizontal>
                        <x:TopRowBottomPane>1</x:TopRowBottomPane>
                        <x:ActivePane>2</x:ActivePane>
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
        }

        th {
            background-color: #4F81BD;
            color: white;
            font-weight: bold;
            padding: 8px;
            border: 1px solid #DDDDDD;
            text-align: center;
        }

        td {
            padding: 5px;
            border: 1px solid #DDDDDD;
            text-align: center;
        }

        .paid {
            color: #008000;
            font-weight: bold;
        }

        .unpaid {
            color: #FF0000;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th>Registration Number</th>
                <?php
                $sql_reason = "SELECT * FROM reasons";
                $result_reason = $conn->query($sql_reason);
                if ($result_reason->num_rows > 0) {
                    while ($row_reason = $result_reason->fetch_assoc()) {
                        $reason = $row_reason["reason"];
                        echo "<th>{$reason}</th>";
                    }
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql_user = "SELECT * FROM users";
            $result_user = $conn->query($sql_user);
            if ($result_user->num_rows > 0) {
                while ($row_user = $result_user->fetch_assoc()) {
                    $reg_no = $row_user["reg_no"];
                    echo "<tr>";
                    echo "<td>{$reg_no}</td>";

                    $sql_reason_payment = "SELECT * FROM reasons";
                    $result_reason_payment = $conn->query($sql_reason_payment);
                    if ($result_reason_payment->num_rows > 0) {
                        while ($row_reason_payment = $result_reason_payment->fetch_assoc()) {
                            $reason_id = $row_reason_payment["id"];
                            $sql_reason_paid = "SELECT * FROM payments WHERE reg_no = '{$reg_no}' and reason_id = {$reason_id}";
                            $result_reason_paid = $conn->query($sql_reason_paid);
                            $status = ($result_reason_paid->num_rows > 0) ? 'Paid' : 'Unpaid';
                            $class = ($result_reason_paid->num_rows > 0) ? 'paid' : 'unpaid';
                            echo "<td class='{$class}'>{$status}</td>";
                        }
                    }
                    echo "</tr>";
                }
            }
            ?>
        </tbody>
    </table>
</body>

</html>
<?php exit; ?>