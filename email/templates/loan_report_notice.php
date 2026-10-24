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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>Batch 20/21: Loans & Repayments Report Notice</title>
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
                width: 100px !important;
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

                    <!-- Decorative Top Header Accent Bar (Emerald) -->
                    <tr>
                        <td bgcolor="#059669" style="background:linear-gradient(90deg, #059669 0%, #10b981 50%, #06b6d4 100%);height:5px;font-size:0;line-height:0;">&nbsp;</td>
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
                                        <div style="color:#059669;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-top:3px;">
                                            Student Contribution Management Portal
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Notice Highlight Box -->
                    <tr>
                        <td class="mobile-padding" style="padding:20px 30px 10px 30px;background-color:#ffffff;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#ecfdf5" style="background-color:#ecfdf5;border:2px solid #a7f3d0;border-radius:14px;padding:22px 18px;text-align:center;">
                                <tr>
                                    <td align="center">
                                        <div style="display:inline-block;background-color:#047857;color:#ffffff;font-size:11px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;padding:5px 14px;border-radius:50px;margin-bottom:10px;">
                                            📋 OFFICIAL NOTICE • FINANCIAL TRANSPARENCY
                                        </div>
                                        <h1 style="color:#064e3b;font-size:22px;font-weight:800;line-height:1.3;margin:0 0 8px 0;">
                                            ණය සහ නැවත ගෙවීම් වාර්තාව
                                        </h1>
                                        <div style="color:#059669;font-size:14px;font-weight:700;margin:0 0 12px 0;">
                                            Batch 20/21: Loans & Repayments Report Notice
                                        </div>
                                        <div style="display:inline-block;background-color:#d1fae5;border:1px solid #6ee7b7;color:#065f46;font-weight:800;font-size:12px;padding:6px 16px;border-radius:50px;">
                                            ⏳ Coming Soon • ලඟදීම මෙහි දර්ශනය කෙරේ
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
                                Registration No: <span style="background-color:#d1fae5;color:#065f46;padding:2px 8px;border-radius:6px;font-weight:800;"><?= htmlspecialchars($regNo) ?></span>
                            </div>

                            <!-- Primary Sinhala Official Notice Box -->
                            <div style="background-color:#f0fdf4;border-left:4px solid #10b981;padding:16px 18px;border-radius:0 12px 12px 0;margin:0 0 20px 0;">
                                <p style="font-size:15px;line-height:1.75;color:#064e3b;font-weight:600;margin:0;">
                                    📢 <strong>20/21 කණ්ඩායම විසින්</strong> විවිධ හේතුන් නිසා ලබාගන්නා ලද ණය මුදල් සහ එහි නැවත ගෙවිම් විස්තර පිලිබද වාර්තාවක් <strong>ලගදීම මෙහි දර්ශනය කෙරේ</strong>.
                                </p>
                            </div>

                            <!-- English Explanation -->
                            <p style="font-size:14px;line-height:1.65;color:#334155;margin:0 0 20px 0;">
                                Please be informed that a comprehensive, transparent report regarding all loans obtained by <strong>Batch 20/21</strong> for various batch activities and their up-to-date repayment/settlement statuses is currently being compiled and will be published on the student portal shortly.
                            </p>

                            <!-- Key Features / What the report includes -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f8fafc" style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin:0 0 24px 0;border-collapse:collapse;overflow:hidden;">
                                <tr>
                                    <td colspan="2" bgcolor="#065f46" style="background-color:#065f46;padding:12px 16px;color:#ffffff;font-size:12px;font-weight:800;letter-spacing:0.8px;text-transform:uppercase;">
                                        🔍 මෙම වාර්තාවෙහි අන්තර්ගත වන කරුණු (Report Highlights)
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top" class="table-label-col" style="padding:14px 16px;width:120px;font-size:13px;color:#065f46;font-weight:700;border-bottom:1px solid #e2e8f0;white-space:nowrap;">
                                        🪙 Loan Details
                                    </td>
                                    <td valign="top" class="table-val-col" style="padding:14px 16px;font-size:13px;color:#1e293b;border-bottom:1px solid #e2e8f0;text-align:left;">
                                        විවිධ හේතුන් සඳහා ලබාගත් සියලුම ණය මුදල් සහ ඒවා ලබාගත් පාර්ශ්වයන් පිලිබඳ සවිස්තරාත්මක සටහන.
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top" class="table-label-col" style="padding:14px 16px;width:120px;font-size:13px;color:#065f46;font-weight:700;border-bottom:1px solid #e2e8f0;white-space:nowrap;">
                                        🧾 Repayments
                                    </td>
                                    <td valign="top" class="table-val-col" style="padding:14px 16px;font-size:13px;color:#1e293b;border-bottom:1px solid #e2e8f0;text-align:left;">
                                        මේ දක්වා සාර්ථකව පියවා ඇති මුදල් සහ ඉදිරියට පියවීමට ඇති ශේෂයන් (Settlement Breakdown).
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top" class="table-label-col" style="padding:14px 16px;width:120px;font-size:13px;color:#065f46;font-weight:700;white-space:nowrap;">
                                        🛡️ Transparency
                                    </td>
                                    <td valign="top" class="table-val-col" style="padding:14px 16px;font-size:13px;color:#1e293b;text-align:left;">
                                        කණ්ඩායමේ සාමාජිකයන්ගේ සම්පූර්ණ විශ්වාසනීයත්වය හා විගණන නිරවද්‍යතාවය තහවුරු කිරීම.
                                    </td>
                                </tr>
                            </table>

                            <!-- Action CTA Button -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin:20px 0 10px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="<?= htmlspecialchars($webUrl) ?>" target="_blank" class="btn-mobile" style="display:inline-block;background:linear-gradient(135deg, #059669 0%, #10b981 100%);color:#ffffff;font-size:14px;font-weight:800;text-decoration:none;padding:14px 30px;border-radius:10px;box-shadow:0 6px 18px rgba(16,185,129,0.35);text-transform:uppercase;letter-spacing:0.5px;">
                                            🌐 ශිෂ්‍ය පද්ධතිය වෙත පිවිසෙන්න &bull; Visit Portal
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer Section -->
                    <tr>
                        <td class="mobile-padding" bgcolor="#0f172a" style="background-color:#0f172a;color:#94a3b8;padding:26px 30px;font-size:12px;line-height:1.6;border-top:1px solid #1e293b;text-align:center;">
                            <div style="color:#ffffff;font-weight:700;font-size:14px;margin-bottom:6px;">
                                <?= htmlspecialchars($companyName) ?>
                            </div>
                            <div style="color:#64748b;font-size:12px;margin-bottom:14px;">
                                Rajarata University of Sri Lanka &bull; Faculty of Technology
                            </div>
                            <div style="margin-bottom:14px;">
                                Support: <a href="mailto:<?= htmlspecialchars($companyEmail) ?>" style="color:#34d399;text-decoration:none;font-weight:600;"><?= htmlspecialchars($companyEmail) ?></a> &bull;
                                Hotline: <a href="tel:<?= htmlspecialchars($companyPhone) ?>" style="color:#34d399;text-decoration:none;font-weight:600;"><?= htmlspecialchars($companyPhone) ?></a>
                            </div>
                            <div style="font-size:11px;color:#475569;border-top:1px solid #1e293b;padding-top:14px;margin-top:10px;">
                                This is an automated official announcement sent from the Student Contribution Management System.<br>
                                &copy; <?= date('Y') ?> Batch Fund (20/21). All Rights Reserved.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
