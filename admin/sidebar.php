<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        :root {
            --dark-bg: #0f1419;
            --dark-card: #1a1f2e;
            --primary-light: #818cf8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --text-primary: #e5e7eb;
            --text-secondary: #9ca3af;
        }

        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(180deg, var(--dark-bg) 0%, var(--dark-card) 100%);
            color: var(--text-primary);
            transition: all 0.3s;
            z-index: 1000;
            border-right: 1px solid rgba(129, 140, 248, 0.1);
        }

        #sidebar .sidebar-header {
            padding: 20px;
            background: rgba(129, 140, 248, 0.05);
            text-align: center;
            border-bottom: 1px solid rgba(129, 140, 248, 0.1);
        }

        #sidebar .sidebar-header h3 {
            color: var(--primary-light);
            margin: 0;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        #sidebar ul.components {
            padding: 20px 0;
        }

        #sidebar ul li a {
            padding: 12px 20px;
            font-size: 1em;
            display: block;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        #sidebar ul li a:hover {
            color: var(--primary-light);
            background: rgba(129, 140, 248, 0.1);
            border-left-color: var(--primary-light);
        }

        #sidebar ul li.active>a {
            color: var(--primary-light);
            background: rgba(129, 140, 248, 0.15);
            border-left-color: var(--primary-light);
        }

        #sidebar ul li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
    </style>
</head>

<body>

    <nav id="sidebar">
        <div class="sidebar-header">
            <h3>SCMP Admin</h3>
        </div>

        <ul class="list-unstyled components">
            <li class="active">
                <a href="./"><i class="fas fa-home"></i> Dashboard</a>
            </li>
            <li>
                <a href="./add_reason"><i class="fas fa-plus"></i> Add reason</a>
            </li>
            <li>
                <a href="./mark_paid"><i class="fas fa-check"></i> Mark as paid</a>
            </li>
            <li>
                <a href="./send_email"><i class="fas fa-envelope"></i> Send Email</a>
            </li>
            <li>
                <a href="./loans"><i class="fas fa-money-bill-wave"></i> Loan Applications</a>
            </li>
            <li>
                <a href="./reason_list"><i class="fas fa-book"></i> Reasons</a>
            </li>
            <!-- <li>
                <a href="#"><i class="fas fa-calendar-alt"></i> Schedule</a>
            </li>
            <li>
                <a href="#"><i class="fas fa-file-invoice-dollar"></i> Payments</a>
            </li>
            <li>
                <a href="#"><i class="fas fa-chart-bar"></i> Results</a>
            </li>
            <li>
                <a href="#"><i class="fas fa-cog"></i> Settings</a>
            </li> -->
            <li>
                <a href="processing?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </nav>

</body>

</html>