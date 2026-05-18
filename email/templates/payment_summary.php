<?php
$name = $name ?? '';
$regNo = $regNo ?? '';
$card = $card ?? '';
$date = $date ?? '';
$totalAmount = $totalAmount ?? 0;
$totalPaid = $totalPaid ?? 0;
$balance = $balance ?? 0;
$companyName = $companyName ?? '';
$companyPhone = $companyPhone ?? '';
$companyEmail = $companyEmail ?? '';
$contactUrl = $contactUrl ?? '#';
$companyLogo = $companyLogo ?? '';
$reasonsData = $reasonsData ?? [];
?>
<html>

<head></head>

<body link="#ff8300" vlink="#ff8300" alink="#ff8300">
    <table class="main contenttable" align="center" style="font-weight:400;border-collapse:collapse;border:0;margin-left:auto;margin-right:auto;padding:5px 0 0 0;font-family:Arial,sans-serif;color:#555559;background-color:#fff;font-size:16px;line-height:26px;width:600px">
        <tr>
            <td class="border" style="border-collapse:collapse;border:1px solid #eeeff0;margin:0;padding:0;-webkit-text-size-adjust:none;color:#555559;font-family:Arial,sans-serif;font-size:16px;line-height:26px">
                <table style="font-weight:400;border-collapse:collapse;border:0;margin:0;padding:0;font-family:Arial,sans-serif;width:100%">
                    <tr>
                        <td colspan="2" valign="top" class="image-section" style="border-collapse:collapse;border:0;margin:0;padding:0;-webkit-text-size-adjust:none;color:#555559;font-family:Arial,sans-serif;font-size:16px;line-height:26px;background-color:#fff;border-bottom:4px solid #ff8316">
                            <?php if (!empty($companyLogo)): ?>
                                <div style="padding:18px 20px 14px 20px;text-align:center;">
                                    <img src="<?= htmlspecialchars($companyLogo) ?>" alt="<?= htmlspecialchars($companyName) ?>" style="max-width:120px;height:auto;display:inline-block;">
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="side title" style="border-collapse:collapse;border:0;margin:0;padding:20px;-webkit-text-size-adjust:none;color:#555559;font-family:Arial,sans-serif;font-size:16px;line-height:26px;vertical-align:top;background-color:#fff;border-top:none">
                            <table style="font-weight:400;border-collapse:collapse;border:0;margin:0;padding:0;font-family:Arial,sans-serif;width:100%">
                                <tr>
                                    <td class="head-title" style="border-collapse:collapse;border:0;margin:0;padding:0 0 10px 0;-webkit-text-size-adjust:none;color:#333;font-family:Arial,sans-serif;font-size:22px;line-height:34px;font-weight:700;text-align:center">
                                        Payment Summary
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 0 12px 0;text-align:left;font-size:15px;line-height:24px;">
                                        Dear <?= htmlspecialchars($name) ?>,
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 0 10px 0;text-align:left;font-size:14px;line-height:22px;">
                                        Here is your payment summary as of <?= htmlspecialchars($date) ?>.
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table cellpadding="10" align="center" style="width:100%;margin:10px 0 15px 0;color:#555559;font-family:Arial,sans-serif;font-size:15px;text-align:left;line-height:20px;border:1px solid #f0f0f0;border-collapse:collapse;">
                                            <tr>
                                                <td style="border:1px solid #f0f0f0;"><strong>Student Name</strong></td>
                                                <td style="border:1px solid #f0f0f0;"><?= htmlspecialchars($name) ?></td>
                                            </tr>
                                            <tr>
                                                <td style="border:1px solid #f0f0f0;"><strong>Registration No</strong></td>
                                                <td style="border:1px solid #f0f0f0;"><?= htmlspecialchars($regNo) ?></td>
                                            </tr>
                                            <tr>
                                                <td style="border:1px solid #f0f0f0;"><strong>Card</strong></td>
                                                <td style="border:1px solid #f0f0f0;"><?= htmlspecialchars($card ?? '') ?></td>
                                            </tr>
                                            <tr>
                                                <td style="border:1px solid #f0f0f0;"><strong>Total Amount</strong></td>
                                                <td style="border:1px solid #f0f0f0;">Rs. <?= number_format($totalAmount, 2) ?></td>
                                            </tr>
                                            <tr>
                                                <td style="border:1px solid #f0f0f0;"><strong>Paid Amount</strong></td>
                                                <td style="border:1px solid #f0f0f0;color:#0f9d58;"><strong>Rs. <?= number_format($totalPaid, 2) ?></strong></td>
                                            </tr>
                                            <tr>
                                                <td style="border:1px solid #f0f0f0;"><strong>Balance</strong></td>
                                                <td style="border:1px solid #f0f0f0;color:#e65100;"><strong>Rs. <?= number_format($balance, 2) ?></strong></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0 0 0;">
                                        <table cellpadding="10" align="center" style="width:100%;margin:10px 0 15px 0;color:#555559;font-family:Arial,sans-serif;font-size:14px;text-align:left;line-height:20px;border:1px solid #f0f0f0;border-collapse:collapse;">
                                            <tr style="background-color:#f5f5f5;">
                                                <td style="border:1px solid #f0f0f0;"><strong>Reason</strong></td>
                                                <td style="border:1px solid #f0f0f0;text-align:right;"><strong>Amount</strong></td>
                                                <td style="border:1px solid #f0f0f0;text-align:center;"><strong>Status</strong></td>
                                            </tr>
                                            <?php foreach ($reasonsData as $reason): ?>
                                                <tr>
                                                    <td style="border:1px solid #f0f0f0;"><?= htmlspecialchars($reason['reason_name']) ?></td>
                                                    <td style="border:1px solid #f0f0f0;text-align:right;">Rs. <?= number_format($reason['price'], 2) ?></td>
                                                    <td style="border:1px solid #f0f0f0;text-align:center;color:<?= $reason['is_paid'] ? '#0f9d58' : '#e65100' ?>;font-weight:bold;">
                                                        <?= $reason['is_paid'] ? 'Paid' : 'Pending' ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 0 8px 0;text-align:left;font-size:14px;line-height:22px;">
                                        Please settle the outstanding balance if any. If you have already paid, kindly ignore this email.
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0;text-align:left;font-size:14px;line-height:22px;">
                                        Best regards,<br>
                                        <?= htmlspecialchars($companyName) ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr bgcolor="#fff" style="border-top:4px solid #ff8300">
                        <td valign="top" class="footer" style="border-collapse:collapse;border:0;margin:0;padding:0;-webkit-text-size-adjust:none;color:#555559;font-family:Arial,sans-serif;font-size:16px;line-height:26px;background:#fff;text-align:center" colspan="2">
                            <table style="font-weight:400;border-collapse:collapse;border:0;margin:0;padding:0;font-family:Arial,sans-serif;width:100%">
                                <tr>
                                    <td class="inside-footer" align="center" valign="middle" style="border-collapse:collapse;border:0;margin:0;padding:20px;-webkit-text-size-adjust:none;color:#555559;font-family:Arial,sans-serif;font-size:12px;line-height:16px;vertical-align:middle;text-align:center;width:580px">
                                        <b><?= htmlspecialchars($companyName) ?></b>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>