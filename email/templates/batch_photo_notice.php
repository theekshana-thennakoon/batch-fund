<?php
$name = $name ?? 'Student';
$regNo = $regNo ?? '2021/CS/001';
$card = $card ?? '';
$date = $date ?? date('l, F j, Y');
$companyName = $companyName ?? 'Batch Fund (20/21)';
$companyPhone = $companyPhone ?? '+9478 965 1168';
$companyEmail = $companyEmail ?? 'theekshanathennakoon79@gmail.com';
$contactUrl = $contactUrl ?? '#';
$webUrl = $webUrl ?? 'https://batch-fund.great-site.net/';
$isWebPreview = $isWebPreview ?? false;
$companyLogo = $companyLogo ?? 'cid:company_logo';
$logoSrc = ($isWebPreview || empty($companyLogo) || $companyLogo === '../assets/logo.png')
    ? 'https://batch-fund.great-site.net/assets/logo.png'
    : $companyLogo;

$eventDateFormatted = 'Saturday, November 07, 2026';
$eventDateCode = '2026.11.07';
$eventTime = '08:30 AM Onwards';
$eventVenue = 'University Premises';
$calendarLink = 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=Official+Batch+Photo+2026+-+Batch+20/21&dates=20261107T030000Z/20261107T113000Z&details=Official+Batch+Photo+Session+for+Batch+20/21.+Freezing+our+unforgettable+memories+together!&location=' . urlencode($eventVenue);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>Official Batch Photo 2026 Notice</title>
    <style>
        :root {
            color-scheme: light dark;
            supported-color-schemes: light dark;
        }

        body,
        table,
        td,
        p,
        a,
        li {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        img {
            border: 0;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }

        @media only screen and (max-width: 620px) {
            .email-container {
                width: 100% !important;
                max-width: 100% !important;
                border-radius: 0 !important;
            }

            .mobile-padding {
                padding-left: 18px !important;
                padding-right: 18px !important;
            }

            .btn-mobile {
                display: block !important;
                width: 90% !important;
                margin: 8px auto !important;
                text-align: center !important;
            }

            .table-label-col {
                width: 95px !important;
                padding: 12px 10px !important;
            }

            .table-val-col {
                padding: 12px 10px !important;
            }
        }
    </style>
</head>

<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;color:#1e293b;">
    <!-- Wrapper Table -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f1f5f9" style="background-color:#f1f5f9;padding:24px 10px;">
        <tr>
            <td align="center">
                <!-- Main Container Card (600px max) -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="email-container" bgcolor="#ffffff" style="max-width:600px;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.08);border:1px solid #e2e8f0;margin:0 auto;">

                    <!-- Decorative Top Header Accent Bar -->
                    <tr>
                        <td bgcolor="#4f46e5" style="background-color:#4f46e5;height:5px;font-size:0;line-height:0;">&nbsp;</td>
                    </tr>

                    <!-- Brand Header Section -->
                    <tr>
                        <td align="center" style="padding:26px 30px 18px 30px;background-color:#ffffff;border-bottom:1px solid #f1f5f9;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <?php if (!empty($logoSrc)): ?>
                                            <img src="<?= htmlspecialchars($logoSrc) ?>" alt="Batch Fund Logo" width="60" style="width:60px;max-width:60px;height:auto;display:inline-block;margin-bottom:10px;border:0;">
                                        <?php endif; ?>
                                        <div style="color:#0f172a;font-size:20px;font-weight:800;letter-spacing:0.5px;text-transform:uppercase;">
                                            Batch Fund (20/21)
                                        </div>
                                        <div style="color:#64748b;font-size:12px;font-weight:600;letter-spacing:1px;text-transform:uppercase;margin-top:3px;">
                                            Student Contribution Management Portal
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Event Highlight Box -->
                    <tr>
                        <td class="mobile-padding" style="padding:20px 30px 10px 30px;background-color:#ffffff;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#eef2ff" style="background-color:#eef2ff;border:2px solid #c7d2fe;border-radius:14px;padding:22px 18px;text-align:center;">
                                <tr>
                                    <td align="center">
                                        <div style="display:inline-block;background-color:#4338ca;color:#ffffff;font-size:11px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;padding:5px 14px;border-radius:50px;margin-bottom:10px;">
                                            📸 OFFICIAL EVENT ANNOUNCEMENT
                                        </div>
                                        <h1 style="color:#1e1b4b;font-size:24px;font-weight:800;line-height:1.3;margin:0 0 8px 0;">
                                            Batch Photo Day 2026
                                        </h1>
                                        <div style="color:#4f46e5;font-size:14px;font-weight:700;margin:0 0 14px 0;">
                                            "Freezing Memories, Celebrating One Unbroken Bond"
                                        </div>
                                        <div style="display:inline-block;background-color:#fef3c7;border:1px solid #fde68a;color:#92400e;font-weight:800;font-size:13px;padding:8px 18px;border-radius:50px;">
                                            📅 <?= htmlspecialchars($eventDateCode) ?> &bull; <?= htmlspecialchars($eventDateFormatted) ?>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Message Body Section -->
                    <tr>
                        <td class="mobile-padding" style="padding:15px 30px 24px 30px;background-color:#ffffff;">
                            <div style="font-size:16px;font-weight:800;color:#0f172a;margin-bottom:6px;">
                                Dear <?= htmlspecialchars($name) ?>,
                            </div>
                            <div style="font-size:13px;color:#475569;font-weight:700;margin-bottom:16px;">
                                Registration No: <span style="background-color:#e0e7ff;color:#3730a3;padding:2px 8px;border-radius:6px;font-weight:800;"><?= htmlspecialchars($regNo) ?></span>
                            </div>
                            <p style="font-size:15px;line-height:1.65;color:#1e293b;margin:0 0 18px 0;">
                                The countdown has officially begun! Years will pass and chapters will turn, but in this single frame, our smiles, laughter, and brotherhood will stay timeless forever. We are thrilled to officially announce that our <strong>Batch Photo Session (Batch 20/21)</strong> will be held on:
                            </p>

                            <!-- Sinhala Touching Tagline -->
                            <div style="background-color:#faf5ff;border-left:4px solid #8b5cf6;padding:12px 16px;border-radius:0 10px 10px 0;margin:0 0 22px 0;">
                                <p style="font-size:14px;line-height:1.6;color:#6b21a8;font-weight:600;margin:0;">
                                    "නොමැකෙන මතකයන් එකම රාමුවකට... අපේ සොඳුරු බැච් ෆොටෝ (Batch Photo) මතක සටහනට ඔබත් සූදානම් වන්න!"
                                </p>
                            </div>

                            <!-- Key Event Details Grid - Properly Aligned -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f8fafc" style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin:0 0 24px 0;border-collapse:collapse;overflow:hidden;">
                                <tr>
                                    <td colspan="2" bgcolor="#4338ca" style="background-color:#4338ca;padding:12px 16px;color:#ffffff;font-size:12px;font-weight:800;letter-spacing:0.8px;text-transform:uppercase;">
                                        📌 Event Details at a Glance
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top" class="table-label-col" style="padding:14px 16px;width:110px;font-size:14px;color:#475569;font-weight:700;border-bottom:1px solid #e2e8f0;white-space:nowrap;">
                                        📅 Date
                                    </td>
                                    <td valign="top" class="table-val-col" style="padding:14px 16px;font-size:14px;color:#0f172a;font-weight:800;border-bottom:1px solid #e2e8f0;text-align:left;">
                                        <?= htmlspecialchars($eventDateFormatted) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top" class="table-label-col" style="padding:14px 16px;width:110px;font-size:14px;color:#475569;font-weight:700;border-bottom:1px solid #e2e8f0;white-space:nowrap;">
                                        ⏰ Time
                                    </td>
                                    <td valign="top" class="table-val-col" style="padding:14px 16px;font-size:14px;color:#0f172a;font-weight:800;border-bottom:1px solid #e2e8f0;text-align:left;">
                                        <?= htmlspecialchars($eventTime) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top" class="table-label-col" style="padding:14px 16px;width:110px;font-size:14px;color:#475569;font-weight:700;border-bottom:1px solid #e2e8f0;white-space:nowrap;">
                                        📍 Venue
                                    </td>
                                    <td valign="top" class="table-val-col" style="padding:14px 16px;font-size:14px;color:#0f172a;font-weight:800;border-bottom:1px solid #e2e8f0;text-align:left;">
                                        <?= htmlspecialchars($eventVenue) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top" class="table-label-col" style="padding:14px 16px;width:110px;font-size:14px;color:#475569;font-weight:700;white-space:nowrap;">
                                        👔 Dress Code
                                    </td>
                                    <td valign="top" class="table-val-col" style="padding:14px 16px;font-size:14px;color:#0f172a;font-weight:700;line-height:1.55;text-align:left;">
                                        Official / Formal<br>
                                        <span style="font-size:13px;color:#64748b;font-weight:500;">
                                            Gentlemen: Shirt &amp; Tie / Suit &bull; Ladies: Saree
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA Buttons Section -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:16px;">
                                <tr>
                                    <td align="center">
                                        <a href="<?= htmlspecialchars($calendarLink) ?>" target="_blank" class="btn-mobile" style="display:inline-block;background-color:#4f46e5;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:12px 24px;border-radius:50px;box-shadow:0 4px 12px rgba(79,70,229,0.3);margin:5px 6px;">
                                            📅 Add to Google Calendar
                                        </a>
                                        <a href="https://batch-fund.great-site.net/" target="_blank" class="btn-mobile" style="display:inline-block;background-color:#0f172a;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:12px 24px;border-radius:50px;margin:5px 6px;">
                                            🌐 View Student Dashboard
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size:13px;line-height:1.6;color:#64748b;text-align:center;margin:12px 0 0 0;">
                                If you have any questions or schedule concerns, please reach out to the Batch Fund Committee.
                            </p>
                        </td>
                    </tr>

                    <!-- Email Footer -->
                    <tr>
                        <td bgcolor="#f8fafc" style="background-color:#f8fafc;border-top:1px solid #e2e8f0;padding:22px 30px;text-align:center;">
                            <div style="font-size:14px;font-weight:800;color:#0f172a;margin-bottom:4px;">
                                <?= htmlspecialchars($companyName) ?>
                            </div>
                            <div style="font-size:12px;color:#64748b;line-height:1.6;">
                                Student Contribution Management Portal &bull; Batch 20/21<br>
                                Contact: <a href="mailto:<?= htmlspecialchars($companyEmail) ?>" style="color:#4f46e5;font-weight:700;text-decoration:none;"><?= htmlspecialchars($companyEmail) ?></a> | Tel: <strong style="color:#0f172a;"><?= htmlspecialchars($companyPhone) ?></strong>
                            </div>
                            <div style="font-size:11px;color:#94a3b8;margin-top:10px;">
                                This is an automated official announcement sent to registered batch members.
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>