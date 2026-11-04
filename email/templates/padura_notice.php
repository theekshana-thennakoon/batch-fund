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

$eventDateFormatted = 'Monday, November 23, 2026';
$eventDateCode = '2026.11.23';
$eventTime = '06:00 PM Onwards';
$eventVenue = 'University Premises (විශ්වවිද්‍යාල පරිශ්‍රයේදී)';
$calendarLink = 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=Batch+20%2F21+-+%E0%B6%B4%E0%B7%90%E0%B6%AF%E0%B7%94%E0%B6%BB+2026+(Padura)&dates=20261123T123000Z/20261123T183000Z&details=Official+Batch+Padura+Musical+Night+for+Batch+20%2F21.+An+evening+of+acoustic+melodies%2C+cultural+harmony%2C+and+memorable+batch+bonding!&location=' . urlencode($eventVenue);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>Official පැදුර 2026 (Padura Musical Night) Notice</title>
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

<body style="margin:0;padding:0;background-color:#0f172a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;color:#1e293b;">
    <!-- Wrapper Table -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#0f172a" style="background-color:#0f172a;padding:24px 10px;">
        <tr>
            <td align="center">
                <!-- Main Container Card (600px max) -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="email-container" bgcolor="#ffffff" style="max-width:600px;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 12px 35px rgba(0,0,0,0.25);border:1px solid #e2e8f0;margin:0 auto;">

                    <!-- Decorative Top Header Accent Bar (Padura Coral / Rose Theme) -->
                    <tr>
                        <td bgcolor="#e11d48" style="background:linear-gradient(90deg, #f43f5e 0%, #e11d48 50%, #fb7185 100%);height:6px;font-size:0;line-height:0;">&nbsp;</td>
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

                    <!-- Event Highlight Box (Padura Theme) -->
                    <tr>
                        <td class="mobile-padding" style="padding:20px 30px 10px 30px;background-color:#ffffff;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#fff1f2" style="background-color:#fff1f2;border:2px solid #fecdd3;border-radius:14px;padding:24px 18px;text-align:center;">
                                <tr>
                                    <td align="center">
                                        <div style="display:inline-block;background-color:#e11d48;color:#ffffff;font-size:11px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;padding:5px 14px;border-radius:50px;margin-bottom:10px;">
                                            🪕 CULTURAL & MUSICAL EVE • BATCH 20/21
                                        </div>
                                        <h1 style="color:#881337;font-size:25px;font-weight:800;line-height:1.3;margin:0 0 8px 0;">
                                            පැදුර 2026 (Padura Musical Night)
                                        </h1>
                                        <div style="color:#e11d48;font-size:14px;font-weight:700;margin:0 0 14px 0;">
                                            "An Evening of Acoustic Harmony, Cultural Melodies & Unbroken Batch Bonding"
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
                                Student Reg No: <span style="color:#e11d48;font-weight:800;"><?= htmlspecialchars($regNo) ?></span>
                                <?php if (!empty($card)): ?>
                                    &bull; Card / Nick: <span style="color:#0f172a;font-weight:800;"><?= htmlspecialchars($card) ?></span>
                                <?php endif; ?>
                            </div>

                            <p style="font-size:14px;line-height:1.65;color:#334155;margin:0 0 16px 0;">
                                We are thrilled to invite you to our official <strong>Batch 20/21 පැදුර 2026 (Padura Night)</strong>! As our university journey approaches its milestone conclusion, let us gather together under the evening sky for an unforgettable session of heartfelt acoustic melodies, cultural performances, and everlasting memories.
                            </p>

                            <!-- Event Details Summary Card -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f8fafc" style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin:18px 0;overflow:hidden;">
                                <tr>
                                    <td class="table-label-col" style="padding:12px 16px;font-size:13px;font-weight:800;color:#64748b;border-bottom:1px solid #e2e8f0;width:120px;background-color:#f1f5f9;">
                                        📅 Date
                                    </td>
                                    <td class="table-val-col" style="padding:12px 16px;font-size:13px;font-weight:800;color:#0f172a;border-bottom:1px solid #e2e8f0;">
                                        <?= htmlspecialchars($eventDateFormatted) ?> (<span style="color:#e11d48;"><?= htmlspecialchars($eventDateCode) ?></span>)
                                    </td>
                                </tr>
                                <tr>
                                    <td class="table-label-col" style="padding:12px 16px;font-size:13px;font-weight:800;color:#64748b;border-bottom:1px solid #e2e8f0;background-color:#f1f5f9;">
                                        ⏰ Time
                                    </td>
                                    <td class="table-val-col" style="padding:12px 16px;font-size:13px;font-weight:800;color:#0f172a;border-bottom:1px solid #e2e8f0;">
                                        <?= htmlspecialchars($eventTime) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="table-label-col" style="padding:12px 16px;font-size:13px;font-weight:800;color:#64748b;border-bottom:1px solid #e2e8f0;background-color:#f1f5f9;">
                                        📍 Venue
                                    </td>
                                    <td class="table-val-col" style="padding:12px 16px;font-size:13px;font-weight:800;color:#0f172a;border-bottom:1px solid #e2e8f0;">
                                        <?= htmlspecialchars($eventVenue) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="table-label-col" style="padding:12px 16px;font-size:13px;font-weight:800;color:#64748b;background-color:#f1f5f9;">
                                        🪕 Event Style
                                    </td>
                                    <td class="table-val-col" style="padding:12px 16px;font-size:13px;font-weight:700;color:#0f172a;">
                                        Traditional Padura / Sarala Gee / Acoustic Jam & Cultural Bonding
                                    </td>
                                </tr>
                            </table>

                            <!-- Important Highlights & Guidelines -->
                            <div style="background-color:#fffbeb;border-left:4px solid #f59e0b;padding:14px 16px;border-radius:4px;margin:20px 0;">
                                <div style="font-size:13px;font-weight:800;color:#92400e;margin-bottom:6px;">
                                    📌 Padura Night Highlights:
                                </div>
                                <ul style="margin:0;padding-left:18px;font-size:12.5px;color:#78350f;line-height:1.6;">
                                    <li><strong>Acoustic & Folk Melodies:</strong> Featuring live instrumentalists (Violin, Flute, Tabla & Acoustic Guitars) and our very own batch talents.</li>
                                    <li><strong>Open Microphone:</strong> Dedicated stage for batch musicians, vocalists, and poets to perform favorite songs together.</li>
                                    <li><strong>Refreshments:</strong> Traditional tea and evening snacks will be provided for all attendees.</li>
                                    <li><strong>Dress Code:</strong> Comfortable traditional or smart casual wear suitable for an open mat evening.</li>
                                </ul>
                            </div>

                            <!-- Calendar & Portal Action Buttons -->
                            <div style="text-align:center;margin:26px 0 16px 0;">
                                <a href="<?= htmlspecialchars($calendarLink) ?>" target="_blank" class="btn-mobile" style="display:inline-block;background-color:#e11d48;color:#ffffff;font-size:14px;font-weight:800;text-decoration:none;padding:14px 28px;border-radius:50px;box-shadow:0 4px 14px rgba(225,29,72,0.35);margin:4px 6px;">
                                    📅 Add to Google Calendar
                                </a>
                                <a href="https://batch-fund.great-site.net" target="_blank" class="btn-mobile" style="display:inline-block;background-color:#0f172a;color:#ffffff;font-size:14px;font-weight:800;text-decoration:none;padding:14px 28px;border-radius:50px;margin:4px 6px;">
                                    🌐 Visit Student Portal
                                </a>
                            </div>

                            <p style="font-size:13px;line-height:1.6;color:#64748b;margin:20px 0 0 0;text-align:center;">
                                Let's make this night harmonious, vibrant, and cherished for decades to come.<br>
                                <em>We look forward to singing and celebrating with every single one of you!</em>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer Section -->
                    <tr>
                        <td align="center" style="padding:22px 30px;background-color:#0f172a;color:#94a3b8;font-size:11.5px;line-height:1.6;border-top:1px solid #1e293b;">
                            <div style="font-weight:700;color:#f8fafc;font-size:13px;margin-bottom:4px;">
                                <?= htmlspecialchars($companyName) ?> &bull; Organizing Committee
                            </div>
                            <div>
                                Faculty of Technology, Rajarata University of Sri Lanka
                            </div>
                            <div style="margin-top:8px;">
                                Helpline: <a href="tel:<?= htmlspecialchars($companyPhone) ?>" style="color:#fb7185;text-decoration:none;font-weight:700;"><?= htmlspecialchars($companyPhone) ?></a>
                                &bull; Email: <a href="mailto:<?= htmlspecialchars($companyEmail) ?>" style="color:#fb7185;text-decoration:none;"><?= htmlspecialchars($companyEmail) ?></a>
                            </div>
                            <div style="margin-top:10px;color:#64748b;font-size:10.5px;">
                                This notice was automatically generated for student <strong><?= htmlspecialchars($regNo) ?></strong> via SCMP.
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>

