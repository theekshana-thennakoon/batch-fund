<?php
date_default_timezone_set('Asia/Colombo');
session_start();
if (isset($_SESSION["reg_no"])) {
    header("Location:./");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="./assets/logo.png">
    <title>Student Contribution Management Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            background-attachment: fixed;
        }

        .login-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        .login-header {
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            padding: 25px;
            text-align: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .login-body {
            padding: 30px;
            background-color: white;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            transition: all 0.3s;
            background-color: #fff !important;
        }

        .form-control:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.25rem rgba(118, 75, 162, 0.25);
        }

        .btn-login {
            background: linear-gradient(to right, #667eea, #764ba2);
            border: none;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: linear-gradient(to right, #5a6fd1, #694499);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .text-muted {
            color: #6c757d !important;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            max-height: 110px;
            max-width: 80%;
        }
    </style>
</head>

<body>
    <script src="./assets/sweetalert.js"></script>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card login-card">
                    <div class="login-header">
                        <h1 class="h4 mb-0 fw-bold" id="studentContributionManagementPortal">Student Contribution Management Portal</h1>
                        <span id="studentContributionManagementPortal" class="studentContributionManagementPortal"></span>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const text = "Student Contribution Portal";
                                const el = document.getElementById('studentContributionManagementPortal');

                                function type() {
                                    el.textContent = "";
                                    let idx = 0;

                                    function typing() {
                                        if (idx < text.length) {
                                            el.textContent += text[idx];
                                            idx++;
                                            setTimeout(typing, 60);
                                        }
                                    }
                                    typing();
                                }
                                type();
                                setInterval(type, 3000);
                            });
                        </script>
                    </div>
                    <div class="card-body login-body">
                        <div class="logo-container">
                            <img src="assets/logo.png" alt="Instruction logo" class="logo">
                            <style>
                                .logo {
                                    animation: rotateLogo 35s linear infinite;
                                }

                                @keyframes rotateLogo {
                                    from {
                                        transform: rotate(0deg);
                                    }

                                    to {
                                        transform: rotate(360deg);
                                    }
                                }
                            </style>
                        </div>
                        <h2 class="h5 text-center mb-4 fw-bold">LOGIN</h2>

                        <form action="processing" method="post" autocomplete="off">
                            <div class="mb-4">
                                <label for="reg_number" class="form-label">Registration Number</label>
                                <input type="text" class="form-control" id="reg_number" name="reg_number"
                                    placeholder="BST/2021/000" required oninput="formatRegistrationNumber(this)"
                                    onkeydown="handleBackspace(event)" autocomplete="off" readonly
                                    onfocus="this.removeAttribute('readonly')">
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="********" required autocomplete="new-password" readonly
                                    onfocus="this.removeAttribute('readonly')">
                            </div>

                            <button type="submit" name="login_btn"
                                class="btn btn-login btn-block w-100 text-white mt-3">
                                Login
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function formatRegistrationNumber(input) {
            let value = input.value.toUpperCase();
            const selectionStart = input.selectionStart;
            let cursorAdjustment = 0;

            // Auto-format department code
            if ((value.startsWith("ITT") || value.startsWith("BST") || value.startsWith("ENT")) && value.length === 3 && !value.includes("/")) {
                value = value + "/";
                // If cursor was at the end, adjust it forward after adding slash
                if (selectionStart === 3) {
                    cursorAdjustment = 1;
                }
            }

            // Auto-format year
            if (value.includes("/") && value.indexOf("/") === 3) {
                const afterFirstSlash = value.substring(4);
                if (afterFirstSlash.startsWith("2021") && afterFirstSlash.length === 4 && !afterFirstSlash.includes("/")) {
                    value = value.substring(0, 8) + "/";
                    // If cursor was at the end, adjust it forward after adding slash
                    if (selectionStart === 8) {
                        cursorAdjustment = 1;
                    }
                } else if (afterFirstSlash.startsWith("2020") && afterFirstSlash.length === 4 && !afterFirstSlash.includes("/")) {
                    value = value.substring(0, 8) + "/";
                    // If cursor was at the end, adjust it forward after adding slash
                    if (selectionStart === 8) {
                        cursorAdjustment = 1;
                    }
                }
            }

            // Direct handling of 2021 at the beginning (optional case)
            if (value.startsWith("2021") && value.length === 4 && !value.includes("/")) {
                value = value + "/";
                // If cursor was at the end, adjust it forward after adding slash
                if (selectionStart === 4) {
                    cursorAdjustment = 1;
                }
            }

            // Update the input value
            input.value = value;

            // Adjust cursor position if needed
            if (cursorAdjustment > 0) {
                setTimeout(() => {
                    input.setSelectionRange(selectionStart + cursorAdjustment, selectionStart + cursorAdjustment);
                }, 0);
            }
        }

        function handleBackspace(event) {
            const input = event.target;
            const value = input.value;
            const selectionStart = input.selectionStart;

            // Prevent backspace on slashes to maintain format
            if (event.key === "Backspace" && selectionStart > 0) {
                if (value.charAt(selectionStart - 1) === "/") {
                    event.preventDefault();
                }
            }
        }
    </script>

    <?php
    if (isset($_SESSION['wrong_user'])) {
        echo "
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'User does not exists!',
                    }).then(() => {
                        //window.history.back(); // Navigate back to the previous page
                    });
                </script>";
        unset($_SESSION["wrong_user"]);
    }

    if (isset($_SESSION['wrong_pwd'])) {
        echo "
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please enter correct Password!',
                    }).then(() => {
                        //window.history.back(); // Navigate back to the previous page
                    });
                </script>";
        unset($_SESSION["wrong_pwd"]);
    }
    ?>
</body>

</html>