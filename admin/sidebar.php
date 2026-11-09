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
            --sidebar-width: 250px;
        }

        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(180deg, var(--dark-bg) 0%, var(--dark-card) 100%);
            color: var(--text-primary);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1050;
            border-right: 1px solid rgba(129, 140, 248, 0.1);
            overflow-y: auto;
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

        /* Mobile Off-canvas & 3-line Toggle */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            z-index: 1040;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-backdrop.show,
        .sidebar-backdrop.active {
            display: block;
            opacity: 1;
        }

        .sidebar-close-btn {
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-size: 1.25rem;
            padding: 4px 8px;
            cursor: pointer;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .sidebar-close-btn:hover {
            color: #ef4444;
        }

        /* 3-line hamburger button styling */
        .btn-sidebar-toggle {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: rgba(129, 140, 248, 0.12) !important;
            color: var(--primary-light, #818cf8) !important;
            border: 1px solid rgba(129, 140, 248, 0.28) !important;
            font-size: 1.15rem;
            cursor: pointer;
            padding: 0;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-sidebar-toggle:hover,
        .btn-sidebar-toggle:active {
            background: rgba(129, 140, 248, 0.25) !important;
            color: #ffffff !important;
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(-100%) !important;
                left: 0 !important;
                width: 260px !important;
                max-width: 82vw !important;
                position: fixed !important;
                top: 0 !important;
                height: 100vh !important;
                z-index: 1050 !important;
                box-shadow: none !important;
                margin-left: 0 !important;
            }

            #sidebar.active,
            #sidebar.show {
                transform: translateX(0) !important;
                box-shadow: 0 0 40px rgba(0, 0, 0, 0.7) !important;
            }

            #content {
                width: 100% !important;
                margin-left: 0 !important;
                min-height: 100vh;
            }
        }

        @media (min-width: 769px) {
            .sidebar-backdrop,
            .sidebar-close-btn,
            .btn-sidebar-toggle {
                display: none !important;
            }

            #sidebar {
                transform: none !important;
            }
        }
    </style>
</head>

<body>

    <nav id="sidebar">
        <div class="sidebar-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0">SCMP Admin</h3>
            <button type="button" class="sidebar-close-btn d-md-none" id="sidebarClose" aria-label="Close sidebar" title="Close Sidebar">
                <i class="fas fa-times"></i>
            </button>
        </div>

<?php
$currentAdminPage = basename($_SERVER['PHP_SELF']);
?>
        <ul class="list-unstyled components">
            <li class="<?php echo ($currentAdminPage == 'index.php' || $currentAdminPage == '') ? 'active' : ''; ?>">
                <a href="./"><i class="fas fa-home"></i> Dashboard</a>
            </li>
            <li class="<?php echo ($currentAdminPage == 'add_reason.php') ? 'active' : ''; ?>">
                <a href="./add_reason"><i class="fas fa-plus"></i> Add reason</a>
            </li>
            <li class="<?php echo ($currentAdminPage == 'mark_paid.php') ? 'active' : ''; ?>">
                <a href="./mark_paid"><i class="fas fa-check"></i> Mark as paid</a>
            </li>
            <li class="<?php echo ($currentAdminPage == 'pay_2024.php') ? 'active' : ''; ?>">
                <a href="./pay_2024"><i class="fas fa-calendar-check"></i> 2024 Payment</a>
            </li>
            <li class="<?php echo ($currentAdminPage == 'students.php') ? 'active' : ''; ?>">
                <a href="./students"><i class="fas fa-users-cog"></i> Manage Students</a>
            </li>

            <li class="<?php echo ($currentAdminPage == 'due_reports.php') ? 'active' : ''; ?>">
                <a href="./due_reports"><i class="fas fa-file-invoice-dollar"></i> Due Reports</a>
            </li>
            <li class="<?php echo ($currentAdminPage == 'send_email.php') ? 'active' : ''; ?>">
                <a href="./send_email"><i class="fas fa-envelope"></i> Send Email</a>
            </li>
            <li class="<?php echo ($currentAdminPage == 'batch_photo_email.php') ? 'active' : ''; ?>">
                <a href="./batch_photo_email"><i class="fas fa-camera-retro"></i> Batch Photo Notice</a>
            </li>
            <li class="<?php echo ($currentAdminPage == 'padura_email.php') ? 'active' : ''; ?>">
                <a href="./padura_email"><i class="fas fa-guitar"></i> Padura Notice</a>
            </li>
            <li class="<?php echo ($currentAdminPage == 'loan_report_email.php') ? 'active' : ''; ?>">
                <a href="./loan_report_email"><i class="fas fa-hand-holding-dollar"></i> Loan Report Notice</a>
            </li>
            <li class="<?php echo ($currentAdminPage == 'loans.php') ? 'active' : ''; ?>">
                <a href="./loans"><i class="fas fa-money-bill-wave"></i> Loan Applications</a>
            </li>
            <li class="<?php echo ($currentAdminPage == 'reason_list.php') ? 'active' : ''; ?>">
                <a href="./reason_list"><i class="fas fa-book"></i> Reasons</a>
            </li>
            <li class="<?php echo ($currentAdminPage == 'settings.php') ? 'active' : ''; ?>">
                <a href="./settings"><i class="fas fa-cog"></i> Settings</a>
            </li>
            <li>
                <a href="processing?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </nav>

    <!-- Overlay Backdrop for Mobile View -->
    <div id="sidebarBackdrop" class="sidebar-backdrop"></div>

    <!-- Universal Vanilla JS for 3-line Toggle on Mobile -->
    <script>
    (function() {
        function initAdminSidebar() {
            var sidebar = document.getElementById('sidebar');
            var backdrop = document.getElementById('sidebarBackdrop');

            function openSidebar() {
                if (!sidebar) return;
                sidebar.classList.add('active');
                sidebar.classList.add('show');
                if (backdrop) {
                    backdrop.classList.add('show');
                    backdrop.classList.add('active');
                }
                document.body.classList.add('sidebar-open');
            }

            function closeSidebar() {
                if (!sidebar) return;
                sidebar.classList.remove('active');
                sidebar.classList.remove('show');
                if (backdrop) {
                    backdrop.classList.remove('show');
                    backdrop.classList.remove('active');
                }
                document.body.classList.remove('sidebar-open');
            }

            function toggleSidebar() {
                if (!sidebar) return;
                if (sidebar.classList.contains('active') || sidebar.classList.contains('show')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            }

            // Capture click on hamburger button (3 lines), close button, or backdrop
            document.addEventListener('click', function(e) {
                var toggleBtn = e.target.closest('#sidebarCollapse, .btn-sidebar-toggle, [data-toggle="sidebar"], .sidebar-toggle-btn');
                if (toggleBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleSidebar();
                    return;
                }

                if (e.target.closest('#sidebarClose')) {
                    e.preventDefault();
                    closeSidebar();
                    return;
                }

                if (backdrop && (e.target === backdrop || backdrop.contains(e.target))) {
                    e.preventDefault();
                    closeSidebar();
                    return;
                }
            }, true);

            // Close on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebar && (sidebar.classList.contains('active') || sidebar.classList.contains('show'))) {
                    closeSidebar();
                }
            });

            // Auto close sidebar on mobile when navigating an internal link
            if (sidebar) {
                var links = sidebar.querySelectorAll('a');
                for (var i = 0; i < links.length; i++) {
                    links[i].addEventListener('click', function() {
                        if (window.innerWidth <= 768) {
                            closeSidebar();
                        }
                    });
                }
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAdminSidebar);
        } else {
            initAdminSidebar();
        }
    })();
    </script>

</body>

</html>
