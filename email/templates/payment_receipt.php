<?php
// Variables available: $student_name, $reg_no, $items (array of ['reason','amount']),
// $total_paid_this_time, $full_total_paid
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: #224abe;
            color: white;
            padding: 10px 15px;
            border-radius: 6px 6px 0 0;
        }

        .content {
            border: 1px solid #e1e1e1;
            border-top: 0;
            padding: 15px;
            border-radius: 0 0 6px 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: #f7f7f7;
        }

        .totals td {
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Payment Receipt</h2>
        </div>
        <div class="content">
            <p>Dear <?php echo htmlspecialchars($student_name); ?>,</p>
            <p>Thank you. We have received your payment for the following item(s):</p>

            <table>
                <thead>
                    <tr>
                        <th>Reason</th>
                        <th style="width:150px; text-align:right;">Amount (Rs.)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($it['reason']); ?></td>
                            <td style="text-align:right;"><?php echo number_format((float)$it['amount'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="totals">
                        <td style="text-align:right">Total Paid (this transaction):</td>
                        <td style="text-align:right"><?php echo number_format((float)$total_paid_this_time, 2); ?></td>
                    </tr>
                    <tr class="totals">
                        <td style="text-align:right">Full Total Paid:</td>
                        <td style="text-align:right"><?php echo number_format((float)$full_total_paid, 2); ?></td>
                    </tr>
                </tfoot>
            </table>

            <p>If you have any questions, reply to this email or contact the administration office.</p>
            <p>Registration No: <?php echo htmlspecialchars($reg_no); ?></p>
            <p>Regards,<br>Administration</p>
        </div>
    </div>
</body>

</html>