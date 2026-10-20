<?php
$name = $name ?? 'Student';
$regNo = $regNo ?? '2021/CS/001';
$card = $card ?? '';
$date = $date ?? date('l, F j, Y');
$companyName = $companyName ?? 'Batch Fund (20/21)';
$companyPhone = $companyPhone ?? '+9478 965 1168';
$companyEmail = $companyEmail ?? 'theekshanathennakoon79@gmail.com';
$contactUrl = $contactUrl ?? '#';
$webUrl = $webUrl ?? 'http://localhost/batchfund';
$companyLogo = $companyLogo ?? '';
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
    <title>Official Batch Photo 2026 Notice</title>
</head>

<body style="margin:0;padding:0;background-color:#0f172a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;color:#334155;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#0f172a;padding:24px 10px;">
        <tr>
            <td align="center">
                <!-- Main Container (600px) -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:620px;background-color:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 20px 45px rgba(0,0,0,0.4);border:1px solid #1e293b;">

                    <!-- Decorative Top Header Gradient Bar -->
                    <tr>
                        <td style="background:linear-gradient(90deg,#6366f1,#8b5cf6,#ec4899);height:6px;font-size:0;line-height:0;">&nbsp;</td>
                    </tr>

                    <!-- Brand Header -->
                    <tr>
                        <td align="center" style="padding:28px 30px 20px 30px;background:linear-gradient(180deg,#1e1b4b 0%,#0f172a 100%);">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <?php if (!empty($companyLogo)): ?>
                                            <img src="<?= htmlspecialchars($companyLogo) ?>" alt="Logo" style="height:55px;width:auto;display:inline-block;margin-bottom:10px;">
                                        <?php endif; ?>
                                        <div style="color:#ffffff;font-size:20px;font-weight:800;letter-spacing:0.5px;text-transform:uppercase;">
                                            Batch Fund (20/21)
                                        </div>
                                        <div style="color:#94a3b8;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;margin-top:4px;">
                                            Student Contribution Management Portal
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Hero Milestone Announcement Banner -->
                    <tr>
                        <td style="padding:0 30px;background:linear-gradient(180deg,#0f172a 0%,#1e1b4b 100%);">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background:linear-gradient(135deg,rgba(99,102,241,0.25) 0%,rgba(168,85,247,0.2) 100%);border:1px solid rgba(165,180,252,0.35);border-radius:16px;padding:26px 22px;text-align:center;">
                                <tr>
                                    <td>
                                        <div style="display:inline-block;background:#4338ca;color:#e0e7ff;font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:5px 14px;border-radius:50px;margin-bottom:12px;border:1px solid #6366f1;">
                                            📸 OFFICIAL EVENT ANNOUNCEMENT
                                        </div>
                                        <h1 style="color:#ffffff;font-size:26px;font-weight:800;line-height:1.3;margin:0 0 10px 0;">
                                            Batch Photo Day 2026
                                        </h1>
                                        <div style="color:#a5b4fc;font-size:15px;font-weight:600;margin:0 0 14px 0;">
                                            "Freezing Memories, Celebrating One Unbroken Bond"
                                        </div>
                                        <div style="display:inline-block;background:#fbbf24;color:#78350f;font-weight:800;font-size:14px;padding:8px 18px;border-radius:50px;">
                                            📅 <?= htmlspecialchars($eventDateCode) ?> &bull; <?= htmlspecialchars($eventDateFormatted) ?>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Message Body -->
                    <tr>
                        <td style="padding:32px 32px 20px 32px;background-color:#ffffff;">
                            <div style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:12px;">
                                Dear <?= htmlspecialchars($name) ?>,
                            </div>
                            <div style="font-size:12px;color:#64748b;font-weight:600;margin-bottom:18px;text-transform:uppercase;letter-spacing:0.8px;">
                                Registration No: <span style="color:#4338ca;"><?= htmlspecialchars($regNo) ?></span>
                            </div>
                            <p style="font-size:15px;line-height:1.65;color:#334155;margin:0 0 16px 0;">
                                The countdown has officially begun! Years will pass and chapters will turn, but in this single frame, our smiles, laughter, and brotherhood will stay timeless forever. We are thrilled to officially announce that our <strong>Batch Photo Session (Batch 20/21)</strong> will be held on:
                            </p>

                            <!-- Sinhala Touching Tagline -->
                            <div style="background-color:#f5f3ff;border-left:4px solid #8b5cf6;padding:12px 16px;border-radius:0 10px 10px 0;margin-bottom:24px;">
                                <p style="font-size:14px;line-height:1.6;color:#5b21b6;margin:0;font-weight:500;">
                                    "නොමැකෙන මතකයන් එකම රාමුවකට... අපේ සොඳුරු බැච් ෆොටෝ (Batch Photo) මතක සටහනට ඔබත් සූදානම් වන්න!"
                                </p>
                            </div>

                            <!-- Key Event Details Grid -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;margin-bottom:24px;overflow:hidden;">
                                <tr>
                                    <td colspan="2" style="background-color:#e0e7ff;padding:12px 18px;font-weight:700;font-size:13px;color:#312e81;letter-spacing:0.5px;text-transform:uppercase;">
                                        📌 Event Details at a Glance
                                    </td>
                                </tr>
                                <tr>
                                    <td width="35%" style="padding:12px 18px;font-size:14px;color:#64748b;font-weight:600;border-bottom:1px solid #edf2f7;">
                                        Date
                                    </td>
                                    <td width="65%" style="padding:12px 18px;font-size:14px;color:#0f172a;font-weight:700;border-bottom:1px solid #edf2f7;">
                                        <?= htmlspecialchars($eventDateFormatted) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 18px;font-size:14px;color:#64748b;font-weight:600;border-bottom:1px solid #edf2f7;">
                                        Time
                                    </td>
                                    <td style="padding:12px 18px;font-size:14px;color:#0f172a;font-weight:700;border-bottom:1px solid #edf2f7;">
                                        <?= htmlspecialchars($eventTime) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 18px;font-size:14px;color:#64748b;font-weight:600;border-bottom:1px solid #edf2f7;">
                                        Venue
                                    </td>
                                    <td style="padding:12px 18px;font-size:14px;color:#0f172a;font-weight:700;border-bottom:1px solid #edf2f7;">
                                        <?= htmlspecialchars($eventVenue) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 18px;font-size:14px;color:#64748b;font-weight:600;">
                                        Dress Code
                                    </td>|
                                    <td style="padding:12px 18px;font-size:14px;color:#0f172a;font-weight:700;">
                                        Official / Formal (Gentlemen: Shirt & Tie / Suit &bull; Ladies: Saree)
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA Buttons Section -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:20px;">
                                <tr>
                                    <td align="center">
                                        <a href="<?= htmlspecialchars($calendarLink) ?>" target="_blank" style="display:inline-block;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:13px 26px;border-radius:50px;box-shadow:0 4px 14px rgba(79,70,229,0.35);margin:6px 6px;">
                                            📅 Add to Google Calendar
                                        </a>
                                        <a href="<?= htmlspecialchars($webUrl) ?>" target="_blank" style="display:inline-block;background:#0f172a;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:13px 24px;border-radius:50px;margin:6px 6px;">
                                            🌐 View Student Dashboard
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size:14px;line-height:1.6;color:#64748b;text-align:center;margin:16px 0 0 0;">
                                If you have any questions or schedule concerns, please reach out to the Batch Fund Committee.
                            </p>
                        </td>
                    </tr>

                    <!-- Email Footer -->
                    <tr>
                        <td style="background-color:#f1f5f9;border-top:1px solid #e2e8f0;padding:22px 30px;text-align:center;">
                            <div style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:4px;">
                                <?= htmlspecialchars($companyName) ?>
                            </div>
                            <div style="font-size:12px;color:#64748b;line-height:1.5;">
                                Student Contribution Management Portal &bull; Batch 20/21<br>
                                Contact: <a href="mailto:<?= htmlspecialchars($companyEmail) ?>" style="color:#4f46e5;text-decoration:none;"><?= htmlspecialchars($companyEmail) ?></a> | Tel: <?= htmlspecialchars($companyPhone) ?>
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