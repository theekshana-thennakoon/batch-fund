<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
            color: white;
            transition: all 0.3s;
            z-index: 1000;
        }

        #sidebar .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        #sidebar .sidebar-header h3 {
            color: white;
            margin: 0;
        }

        #sidebar ul.components {
            padding: 20px 0;
        }

        #sidebar ul li a {
            padding: 12px 20px;
            font-size: 1em;
            display: block;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }

        #sidebar ul li a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        #sidebar ul li.active>a {
            color: white;
            background: rgba(255, 255, 255, 0.2);
        }

        #sidebar ul li a i {
            margin-right: 10px;
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