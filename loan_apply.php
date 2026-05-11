<?php
include("./assets/database.php");
date_default_timezone_set('Asia/Colombo');
session_start();
if (!isset($_COOKIE["reg_no"])) {
    header("Location:splash");
} else {
    $reg_no = $_COOKIE["reg_no"];

    $sql_user = "SELECT * FROM users WHERE reg_no = '{$reg_no}'";
    $result_user = $conn->query($sql_user);
    if ($result_user->num_rows > 0) {
        while ($row_user = $result_user->fetch_assoc()) {
            $uid = $row_user["id"];
            $name = $row_user["Name"];
        }
    }

    // Handle form submission
    $message = "";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $reason = $conn->real_escape_string($_POST["reason"]);
        $total = $conn->real_escape_string($_POST["total"]);

        if (empty($reason) || empty($total)) {
            $message = '<div class="alert alert-danger" role="alert">Please fill in all fields</div>';
        } else {
            $sql_insert = "INSERT INTO loans (reason, total, paid, balance) VALUES ('{$reason}', {$total}, 0, {$total})";

            if ($conn->query($sql_insert) === TRUE) {
                $message = '<div class="alert alert-success" role="alert">Loan application submitted successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger" role="alert">Error submitting application: ' . $conn->error . '</div>';
            }
        }
    }
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Apply for Loan</title>
        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root {
                --primary-color: #4e73df;
                --secondary-color: #f8f9fc;
            }

            body {
                font-family: 'Nunito', sans-serif;
                background: linear-gradient(135deg, var(--primary-color) 0%, #224abe 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }

            .container {
                max-width: 600px;
            }

            .card {
                border: none;
                border-radius: 10px;
                box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            }

            .card-header {
                background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
                color: white;
                border-bottom: none;
                border-radius: 10px 10px 0 0;
                padding: 1.5rem;
            }

            .card-header h4 {
                margin: 0;
                font-weight: 600;
            }

            .form-group label {
                font-weight: 600;
                color: #333;
                margin-bottom: 0.5rem;
            }

            .form-control {
                border-radius: 5px;
                border: 1px solid #ddd;
                padding: 0.75rem;
                font-size: 0.95rem;
            }

            .form-control:focus {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
            }

            .btn {
                border-radius: 5px;
                padding: 0.75rem 1.5rem;
                font-weight: 600;
                width: 100%;
            }

            .btn-primary {
                background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
                border: none;
            }

            .btn-primary:hover {
                background: linear-gradient(180deg, #3860d6 0%, #1a3a8e 100%);
                color: white;
            }

            .btn-secondary {
                background: #6c757d;
                border: none;
                margin-top: 10px;
            }

            .user-info {
                background: #f8f9fc;
                padding: 15px;
                border-radius: 5px;
                margin-bottom: 20px;
                border-left: 4px solid var(--primary-color);
            }

            .user-info p {
                margin: 0;
                color: #555;
            }

            .user-info strong {
                color: #333;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-money-bill-wave"></i> Apply for Loan</h4>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>

                    <form method="POST" action="">
                        <div class="form-group mb-3">
                            <label for="reason">Reason for Loan</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Enter the reason for your loan" required></textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label for="total">Loan Amount (Rs.)</label>
                            <input type="number" class="form-control" id="total" name="total" step="0.01" placeholder="Enter the total loan amount" required>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit Application
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bootstrap 5 JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>

    </html>
<?php
}
?>