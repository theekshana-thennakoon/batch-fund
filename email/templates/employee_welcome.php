<?php
// Variables available: $name, $email, $password, $companyName, $webUrl
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to <?= htmlspecialchars($companyName) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            color: #2c3e50;
            line-height: 1.6;
        }

        .wrapper {
            background-color: #f0f2f5;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 40px 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .header p {
            font-size: 14px;
            opacity: 0.95;
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .greeting strong {
            color: #667eea;
            font-weight: 600;
        }

        .intro-text {
            color: #555;
            font-size: 15px;
            margin-bottom: 30px;
            line-height: 1.7;
        }

        .section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .section-icon {
            margin-right: 10px;
            font-size: 18px;
        }

        .credentials-box {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .credential-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(102, 126, 234, 0.1);
            font-size: 14px;
        }

        .credential-row:last-child {
            border-bottom: none;
        }

        .credential-label {
            color: #7c8aa4;
            font-weight: 500;
        }

        .credential-value {
            background-color: #ffffff;
            padding: 8px 12px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #667eea;
            font-weight: 600;
            word-break: break-all;
            min-width: 200px;
            text-align: right;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 15px;
            margin-top: 15px;
            transition: transform 0.2s, box-shadow 0.2s;
            text-align: center;
            display: block;
            width: 100%;
            box-sizing: border-box;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
            text-decoration: none;
            color: #ffffff;
        }

        .security-notice {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 13px;
            color: #856404;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
            color: #7c8aa4;
            font-size: 12px;
            line-height: 1.8;
        }

        .footer-brand {
            font-weight: 700;
            color: #2c3e50;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .footer-divider {
            height: 1px;
            background-color: #e9ecef;
            margin: 15px 0;
        }

        .footer-text {
            color: #a0aec0;
        }

        @media (max-width: 600px) {
            .header {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .content {
                padding: 25px 20px;
            }

            .credential-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .credential-value {
                width: 100%;
                text-align: left;
                margin-top: 8px;
            }

            .footer {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <h1>Welcome to the Viyola!</h1>
                <!-- <p><?= htmlspecialchars($companyName) ?></p> -->
            </div>

            <!-- Content -->
            <div class="content">
                <div class="greeting">
                    Hello <strong><?= htmlspecialchars($name) ?></strong>,
                </div>

                <p class="intro-text">
                    We're excited to have you on board! Your employee account has been successfully created and is ready to use.
                    Below you'll find your login credentials to access the system.
                </p>

                <!-- Login Credentials Section -->
                <div class="section">
                    <div class="section-title">
                        <span class="section-icon">🔐</span>
                        Your Login Credentials
                    </div>
                    <div class="credentials-box">
                        <div class="credential-row">
                            <span class="credential-label">Email / Username</span>
                            <span class="credential-value"><?= htmlspecialchars($email) ?></span>
                        </div>
                        <div class="credential-row">
                            <span class="credential-label">Password</span>
                            <span class="credential-value"><?= htmlspecialchars($password) ?></span>
                        </div>
                    </div>
                </div>

                <!-- CTA Button -->
                <a href="<?= htmlspecialchars($webUrl) ?>" class="cta-button">
                    🚀 Login to Your Account
                </a>

                <!-- Security Notice -->
                <div class="security-notice">
                    <strong>🔒 Security Tip:</strong> Please change your password after your first login for enhanced security.
                    Never share your credentials with anyone.
                </div>

                <p style="margin-top: 25px; font-size: 14px; color: #7c8aa4;">
                    If you have any questions or need assistance, please don't hesitate to reach out to the admin team.
                </p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <div class="footer-brand">Viyola <?= htmlspecialchars($companyName) ?></div>
                <div class="footer-divider"></div>
                <div class="footer-text">
                    This is an automated message. Please do not reply to this email.<br>
                    © <?= date('Y') ?> Viyola<?= htmlspecialchars($companyName) ?>. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</body>

</html>