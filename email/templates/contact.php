<html>

<head></head>

<body link="#ff8300" vlink="#ff8300" alink="#ff8300">
    <table class="main contenttable" align="center" style="font-weight:400;border-collapse:collapse;border:0;margin-left:auto;margin-right:auto;padding:5px 0 0 0;font-family:Arial,sans-serif;color:#555559;background-color:#fff;font-size:16px;line-height:26px;width:600px">
        <tr>
            <td class="border" style="border-collapse:collapse;border:1px solid #eeeff0;margin:0;padding:0;-webkit-text-size-adjust:none;color:#555559;font-family:Arial,sans-serif;font-size:16px;line-height:26px">
                <table style="font-weight:400;border-collapse:collapse;border:0;margin:0;padding:0;font-family:Arial,sans-serif">
                    <tr>
                        <td colspan="4" valign="top" class="image-section" style="border-collapse:collapse;border:0;margin:0;padding:0;-webkit-text-size-adjust:none;color:#555559;font-family:Arial,sans-serif;font-size:16px;line-height:26px;background-color:#fff;border-bottom:4px solid #ff8316">
                            <a href="<?= $webUrl ?>"><img src="<?= $bannerUrl ?>" alt="<?= $companyName ?>"></a>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="side title" style="border-collapse:collapse;border:0;margin:0;padding:20px 20px 0 20px;-webkit-text-size-adjust:none;color:#555559;font-family:Arial,sans-serif;font-size:16px;line-height:26px;vertical-align:top;background-color:#fff;border-top:none">
                            <table style="font-weight:400;border-collapse:collapse;border:0;margin:0;padding:0;font-family:Arial,sans-serif">
                                <tr>
                                    <td class="head-title" style="border-collapse:collapse;border:0;margin:0;padding:0;-webkit-text-size-adjust:none;color:#555559;font-family:Arial,sans-serif;font-size:22px;line-height:34px;font-weight:700;text-align:center">
                                        <div class="mktEditable" id="main_title">New inquiry</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="grey-block" style="border-collapse:collapse;border:0;margin:0;-webkit-text-size-adjust:none;color:#555559;font-family:Arial,sans-serif;font-size:16px;line-height:26px;background-color:#fff;text-align:center">
                                        <div class="mktEditable" id="cta">
                                            <p>Dear Sir/Madam,</p>
                                            <p>This message informs you that you have received a new inquiry. Please find the inquiry details below.</p>
                                            <table cellpadding="10" align="center" style="margin-bottom:5px;color:#555559;font-family:Arial,sans-serif;font-size:16px;text-align: left;line-height:20px">
                                                <tr>
                                                    <td><strong>Name</strong></td>
                                                    <td><?= $name ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Email</strong></td>
                                                    <td><?= $email ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Phone</strong></td>
                                                    <td><?= $phone ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Subject</strong></td>
                                                    <td><?= $subject ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Message</strong></td>
                                                    <td><?= $message ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Date</strong></td>
                                                    <td><?= $date ?></td>
                                                </tr>
                                            </table>

                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr bgcolor="#fff" style="border-top:4px solid #ff8300">
                        <td valign="top" class="footer" style="border-collapse:collapse;border:0;margin:0;padding:0;-webkit-text-size-adjust:none;color:#555559;font-family:Arial,sans-serif;font-size:16px;line-height:26px;background:#fff;text-align:center">
                            <table style="font-weight:400;border-collapse:collapse;border:0;margin:0;padding:0;font-family:Arial,sans-serif">
                                <tr>
                                    <td class="inside-footer" align="center" valign="middle" style="border-collapse:collapse;border:0;margin:0;padding:20px;-webkit-text-size-adjust:none;color:#555559;font-family:Arial,sans-serif;font-size:12px;line-height:16px;vertical-align:middle;text-align:center;width:580px">
                                        <div id="address" class="mktEditable"><b><?= $companyName ?></b><br> <br>Tel : <?= $companyPhone ?> <br><a style="color:#ff8300" href="<?= $contactUrl ?>">Contact Us</a></div>
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