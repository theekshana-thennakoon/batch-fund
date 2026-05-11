<?php
// dashboard.php
session_start();
include("../assets/database.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
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

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--dark-bg);
            overflow-x: hidden;
            color: var(--text-primary);
        }

        /* Sidebar Styles */
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

        /* Content Styles */
        #content {
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
        }

        /* Header Styles */
        .topbar {
            height: var(--header-height);
            background: var(--dark-card);
            border-bottom: 1px solid rgba(129, 140, 248, 0.1);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(0, 0, 0, 0.2);
            color: var(--text-primary);
        }

        .topbar .navbar-search {
            width: 25rem;
        }

        .topbar .dropdown-list {
            padding: 0;
            border: none;
            overflow: hidden;
            width: 20rem;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 0.35rem;
            border: 1px solid rgba(129, 140, 248, 0.1);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(0, 0, 0, 0.2);
            margin-bottom: 1.5rem;
            background-color: var(--dark-card);
            color: var(--text-primary);
        }

        .card-header {
            background-color: rgba(129, 140, 248, 0.05);
            border-bottom: 1px solid rgba(129, 140, 248, 0.1);
            color: var(--primary-light);
        }

        .form-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background-color: var(--dark-card);
            border-radius: 10px;
            border: 1px solid rgba(129, 140, 248, 0.1);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }

        .form-title {
            color: var(--primary-light);
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
        }

        .form-label {
            font-weight: 500;
            color: var(--text-primary);
        }

        .btn-submit {
            background-color: var(--primary-light);
            border: none;
            padding: 10px 20px;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background-color: #6d78da;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(129, 140, 248, 0.3);
        }

        /* Form Controls */
        .form-control,
        .form-select {
            background-color: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(129, 140, 248, 0.2) !important;
            color: var(--text-primary) !important;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: rgba(255, 255, 255, 0.08) !important;
            border-color: var(--primary-light) !important;
            box-shadow: 0 0 0 0.2rem rgba(129, 140, 248, 0.25) !important;
            color: var(--text-primary) !important;
        }

        .input-group-text {
            background-color: rgba(129, 140, 248, 0.1);
            border: 1px solid rgba(129, 140, 248, 0.2);
            color: var(--primary-light);
        }

        /* Responsive */
        @media (max-width: 768px) {
            #sidebar {
                margin-left: -250px;
            }

            #sidebar.active {
                margin-left: 0;
            }

            #content {
                width: 100%;
                margin: 0;
            }

            #content.active {
                margin-left: 250px;
                width: calc(100% - 250px);
            }
        }
    </style>
</head>

<body>
    <script src="../assets/sweetalert.js"></script>
    <!-- Sidebar -->
    <?php include("sidebar.php"); ?>

    <!-- Page Content -->
    <div id="content">
        <!-- Top Navigation -->
        <?php include("topbar.php"); ?>

        <!-- Begin Page Content -->
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Add Reason</h1>
            </div>

            <!-- Content Row -->
            <div class="container">
                <div class="form-container">
                    <h2 class="form-title">Add Payment Reason</h2>

                    <form method="POST" action="x.php">
                        <div class="mb-4">
                            <label for="reason" class="form-label">Reason*</label>
                            <input type="text" class="form-control" id="reason" name="reason"
                                value="<?php echo isset($_POST['reason']) ? htmlspecialchars($_POST['reason']) : ''; ?>"
                                required>
                        </div>

                        <div class="mb-4">
                            <label for="reason_type" class="form-label">Reason Type*</label>
                            <select class="form-select" id="reason_type" name="reason_type" required>
                                <option value="" disabled selected>Select reason type</option>
                                <option value="monthly" <?php echo (isset($_POST['reason_type']) && $_POST['reason_type'] === 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                                <option value="funeral" <?php echo (isset($_POST['reason_type']) && $_POST['reason_type'] === 'funeral') ? 'selected' : ''; ?>>Funeral</option>
                                <option value="function" <?php echo (isset($_POST['reason_type']) && $_POST['reason_type'] === 'function') ? 'selected' : ''; ?>>Function</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="price" class="form-label">Price*</label>
                            <div class="input-group">
                                <span class="input-group-text">LKR</span>
                                <input type="number" class="form-control" id="price" name="price"
                                    step="0.01" min="0"
                                    value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>"
                                    required>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="add_reason" class="btn btn-primary btn-submit">Add Reason</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
        <!-- /.container-fluid -->
    </div>
    <!-- End of Page Content -->

    <!-- Bootstrap core JavaScript-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom scripts -->
    <script>
        // Toggle sidebar on mobile
        $(document).ready(function() {
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('active');
            });
        });
    </script>

    <?php
    if (isset($_SESSION['success_add_reason'])) {
        echo "
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Success...',
                        text: 'Successfully added the reason!',
                    }).then(() => {
                        //window.history.back(); // Navigate back to the previous page
                    });
                </script>";
        unset($_SESSION["success_add_reason"]);
    }
    ?>
</body>

</html>