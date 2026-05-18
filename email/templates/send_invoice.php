<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }

        table {
            border: none;
            width: 100%;
        }

        .invoice_table {
            border: 1px solid #42c5a6;
            border-collapse: collapse;
        }

        .invoice_table th {
            background-color: #42c5a6;
            color: #fff;
        }

        .invoice_table .invoice_table td,
        .invoice_table th {
            padding: 8px;
            border: 1px solid #ddd;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <!-- HEADER - Minimalist PDF-Friendly -->
    <div style="margin-bottom: 30px;">
        <table style="width:100%; border-collapse: collapse;">
            <tr>
                <td style="width:60%; padding:10px 0;">
                    <h2 style="margin:0; color:#2c3e50;"><?= $companyName ?></h2>
                    <p style="margin:5px 0 0 0; color:#666; font-size:12px;">Tel: <?= $companyPhone ?></p>
                    <p style="margin:5px 0 0 0; color:#666; font-size:12px;">Email: <?= $companyEmail ?></p>
                </td>
                <td style="width:40%; text-align:right; padding:10px 0;">
                    <h1 style="margin:0; color:#42c5a6;">FEE NOTE</h1>
                    <p style="margin:5px 0 0 0;">
                        <strong>#<?= $invoice_id ?></strong><br>
                        <!-- <span style="font-size:11px; color:#666;">Date: <?= $date ?></span> -->
                        <span style="font-size:11px; color:#666;">
                            Date: <?= date("F d, Y", strtotime($date)) ?>
                        </span>
                    </p>
                </td>
            </tr>
        </table>
        <hr style="margin:10px 0 0 0; border:none; border-top:2px solid #42c5a6;">
    </div>

    <!-- CLIENT INFO - Professional Invoice Style -->
    <div style="margin-bottom: 30px;">
        <table style="width:100%; border-collapse: collapse;">
            <tr>
                <!-- Billed To Section with Border -->
                <td style="width:50%; vertical-align:top; border:1px solid #e0e0e0; border-right:none; padding:15px;">
                    <div style="margin-bottom:10px;">
                        <strong style="color:#42c5a6; font-size:11px; text-transform:uppercase; letter-spacing:1px;">BILLED TO</strong>
                    </div>
                    <div style="font-size:14px; font-weight:bold; margin-bottom:8px; color:#333;">
                        <?= $name ?>
                    </div>
                    <div style="color:#666; font-size:12px; line-height:1.5;">
                        <?= $email ?>
                    </div>
                    <div style="color:#666; font-size:12px; line-height:1.5;">
                        <?= $clientaddress ?>
                    </div>
                </td>

                <!-- Invoice Details Section with Border -->
                <td style="width:50%; vertical-align:top; border:1px solid #e0e0e0; border-left:none; padding:15px;">
                    <div style="margin-bottom:10px;">
                        <strong style="color:#42c5a6; font-size:11px; text-transform:uppercase; letter-spacing:1px;">FEE NOTE DETAILS</strong>
                    </div>
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding:5px 0; color:#666; font-size:12px; width:100px;">
                                <strong>Invoice Id:</strong>
                            </td>
                            <td style="padding:5px 0; font-size:12px; color:#333;">
                                <?= $invoice_id ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:5px 0; color:#666; font-size:12px;">
                                <strong>Barrister:</strong>
                            </td>
                            <td style="padding:5px 0; font-size:12px; color:#333;">
                                <?= $barristerName ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:5px 0; color:#666; font-size:12px;">
                                <strong>Invoice Date:</strong>
                            </td>
                            <td style="padding:5px 0; font-size:12px; color:#333;">
                                <?= $invoiceDate ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <br>

    <!-- INVOICE TABLE -->
    <div style="width:100%; border:1px solid #ddd; border-radius: 4px;">
        <table style="width:100%; border-collapse: collapse; margin:0; padding:0;">
            <!-- HEADER -->
            <thead>
                <tr style="background:#42c5a6; color:#fff;">
                    <th style="padding:12px 8px; text-align:left; width:70%; font-weight:bold;">
                        PARTICULARS
                    </th>
                    <th style="padding:12px 8px; text-align:right; width:30%; font-weight:bold;">
                        Amount
                    </th>
                </tr>
            </thead>
            <tbody>
                <!-- ROW - Legal Service -->
                <!-- <tr style="height: 100vh; vertical-align: top; border-bottom:1px solid #eee;">
                    <td style="padding:10px 8px; text-align:left; width:50%; text-align: justify;">
                        <?= $caseName ?>
                    </td>
                    <td style="padding:10px 8px; text-align:right;">
                        $<?= number_format($invoiceAmount, 2) ?>
                    </td>
                </tr> -->


                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:10px 8px;width:50%; height: 350px; vertical-align: top; text-align: justify;">
                        <?= $caseName ?>
                    </td>
                    <td style="padding:10px 8px;text-align:right;">
                        $<?= number_format($invoiceAmount, 2) ?>
                    </td>
                </tr>

                <!-- PAID -->
                <tr style="border-bottom:1px solid #eee; background:#f9f9f9;">
                    <td style="padding:10px 8px; text-align:right;">
                        <b>Paid</b>
                    </td>
                    <td style="padding:10px 8px; text-align:right;">
                        $<?= number_format($invoicePaid, 2) ?>
                    </td>
                </tr>

                <!-- BALANCE -->
                <tr style="border-top:2px solid #42c5a6; background:#fff;">
                    <td style="padding:12px 8px; text-align:right;">
                        <b style="font-size: 14px;">Balance Due</b>
                    </td>
                    <td style="padding:12px 8px; text-align:right;">
                        <b style="font-size: 16px; color:#42c5a6;">$<?= number_format($invoiceAmount - $invoicePaid, 2) ?></b>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <br><br>

    <div class="footer">
        <h4>Thank you!</h4>
        <p><?= $companyName ?> | <?= $companyPhone ?><br>
            <a href="<?= $contactUrl ?>">Contact Us</a>
        </p>
        <p>
        </p>
    </div>

</body>

</html>