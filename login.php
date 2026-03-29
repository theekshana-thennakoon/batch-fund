<?php
date_default_timezone_set('Asia/Colombo');
session_start();
if (isset($_COOKIE["reg_no"])) {
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
        /* ========== DARK MODE (Default) ========== */
        :root {
            --bg-gradient-start: #1a1a2e;
            --bg-gradient-end: #16213e;
            --card-bg: #1e1e2f;
            --card-header-bg: #2a2a3a;
            --card-header-text: #e9ecef;
            --card-border: #2d2d44;
            --text-primary: #e9ecef;
            --text-secondary: #adb5bd;
            --input-bg: #2a2a3a;
            --input-border: #3d3d5c;
            --input-text: #e9ecef;
            --input-placeholder: #6c6c8d;
            --label-color: #cbd5e0;
            --btn-gradient-start: #4c51bf;
            --btn-gradient-end: #6b46c0;
            --btn-hover-start: #5a67d8;
            --btn-hover-end: #805ad5;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            --border-color: #2d2d44;
        }

        /* ========== LIGHT MODE ========== */
        body.light-mode {
            --bg-gradient-start: #667eea;
            --bg-gradient-end: #764ba2;
            --card-bg: white;
            --card-header-bg: rgba(255, 255, 255, 0.9);
            --card-header-text: #333;
            --card-border: rgba(0, 0, 0, 0.1);
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --input-bg: #fff;
            --input-border: #ddd;
            --input-text: #212529;
            --input-placeholder: #999;
            --label-color: #495057;
            --btn-gradient-start: #667eea;
            --btn-gradient-end: #764ba2;
            --btn-hover-start: #5a6fd1;
            --btn-hover-end: #694499;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            --border-color: rgba(0, 0, 0, 0.1);
        }

        body {
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            background-attachment: fixed;
            transition: background 0.3s ease;
            color: var(--text-primary);
        }

        .login-card {
            border: none;
            border-radius: 15px;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: transform 0.3s ease, background-color 0.3s ease;
            background-color: var(--card-bg);
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        .login-header {
            background: var(--card-header-bg);
            color: var(--card-header-text);
            padding: 25px;
            text-align: center;
            border-bottom: 1px solid var(--card-border);
            transition: background 0.3s ease, color 0.3s ease;
        }

        .login-header h1 {
            color: var(--card-header-text);
        }

        .login-body {
            padding: 30px;
            background-color: var(--card-bg);
            transition: background-color 0.3s ease;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid var(--input-border);
            transition: all 0.3s;
            background-color: var(--input-bg) !important;
            color: var(--input-text) !important;
        }

        .form-control:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.25rem rgba(118, 75, 162, 0.25);
            background-color: var(--input-bg) !important;
            color: var(--input-text) !important;
        }

        .form-control::placeholder {
            color: var(--input-placeholder);
        }

        .btn-login {
            background: linear-gradient(to right, var(--btn-gradient-start), var(--btn-gradient-end));
            border: none;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-radius: 8px;
            transition: all 0.3s;
            color: white;
        }

        .btn-login:hover {
            background: linear-gradient(to right, var(--btn-hover-start), var(--btn-hover-end));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .text-muted {
            color: var(--text-secondary) !important;
        }

        .form-label {
            font-weight: 500;
            color: var(--label-color);
            transition: color 0.3s ease;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            max-height: 110px;
            max-width: 80%;
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

        /* Theme toggle button in login page */
        .theme-toggle-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            border-radius: 50px;
            padding: 10px 20px;
            font-size: 14px;
            transition: all 0.2s ease;
            cursor: pointer;
            z-index: 1000;
            backdrop-filter: blur(10px);
            font-weight: 500;
        }

        .theme-toggle-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transform: scale(1.02);
        }

        body.light-mode .theme-toggle-btn {
            background-color: rgba(0, 0, 0, 0.2);
            color: #333;
        }

        body.light-mode .theme-toggle-btn:hover {
            background-color: rgba(0, 0, 0, 0.3);
        }

        /* SweetAlert dark mode compatibility */
        .swal2-popup {
            background-color: var(--card-bg) !important;
            color: var(--text-primary) !important;
        }

        .swal2-title {
            color: var(--text-primary) !important;
        }

        .swal2-html-container {
            color: var(--text-secondary) !important;
        }

        .swal2-confirm {
            background-color: #764ba2 !important;
        }

        body.light-mode .swal2-popup {
            background-color: white !important;
        }

        body.light-mode .swal2-title {
            color: #212529 !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .theme-toggle-btn {
                top: 10px;
                right: 10px;
                padding: 6px 12px;
                font-size: 12px;
            }

            .login-header h1 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body class="dark-mode">
    <!-- Theme Toggle Button -->
    <!-- <button id="themeToggle" class="theme-toggle-btn">
        <i class="fas fa-sun me-2"></i> Light Mode
    </button> -->

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
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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

    <!-- Dark/Light Mode Toggle JavaScript - Dark Mode Default -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get the toggle button
            const themeToggle = document.getElementById('themeToggle');
            const body = document.body;

            // Check for saved theme preference in localStorage
            const savedTheme = localStorage.getItem('scmp_theme_login');

            // Function to apply theme
            function applyTheme(theme) {
                if (theme === 'light') {
                    body.classList.remove('dark-mode');
                    body.classList.add('light-mode');
                    if (themeToggle) {
                        themeToggle.innerHTML = '<i class="fas fa-moon me-2"></i> Dark Mode';
                    }
                } else {
                    body.classList.remove('light-mode');
                    body.classList.add('dark-mode');
                    if (themeToggle) {
                        themeToggle.innerHTML = '<i class="fas fa-sun me-2"></i> Light Mode';
                    }
                }
            }

            // Apply saved theme or default to dark mode
            if (savedTheme === 'light') {
                applyTheme('light');
            } else {
                // Default to dark mode
                applyTheme('dark');
                // Save dark mode as default preference if no saved preference exists
                if (!savedTheme) {
                    localStorage.setItem('scmp_theme_login', 'dark');
                }
            }

            // Toggle theme on button click
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    if (body.classList.contains('dark-mode')) {
                        // Switch to light mode
                        applyTheme('light');
                        localStorage.setItem('scmp_theme_login', 'light');
                    } else {
                        // Switch to dark mode
                        applyTheme('dark');
                        localStorage.setItem('scmp_theme_login', 'dark');
                    }
                });
            }

            // Add transition effect
            const style = document.createElement('style');
            style.textContent = `
                .login-card, .login-header, .login-body, .form-control, .btn-login {
                    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
                }
            `;
            document.head.appendChild(style);

            console.log('Login page theme initialized: Dark Mode is default');
        });
    </script>

    <?php
    if (isset($_SESSION['wrong_user'])) {
        echo "
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'User does not exists!',
                        background: document.body.classList.contains('dark-mode') ? '#1e1e2f' : 'white',
                        color: document.body.classList.contains('dark-mode') ? '#e9ecef' : '#212529'
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
                        background: document.body.classList.contains('dark-mode') ? '#1e1e2f' : 'white',
                        color: document.body.classList.contains('dark-mode') ? '#e9ecef' : '#212529'
                    });
                </script>";
        unset($_SESSION["wrong_pwd"]);
    }
    ?>
</body>

</html>