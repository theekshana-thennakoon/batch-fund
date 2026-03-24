<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="refresh" content="2;url=./login">
    <script>
        // Change SVG circle color every 400ms
        document.addEventListener("DOMContentLoaded", function() {
            const colors = ["#5D6773", "#54A9D1", "#EEDD5F", "#80BF93", "#E06758"];
            const circle = document.querySelector("svg circle");
            let idx = 0;
            setInterval(() => {
                circle.setAttribute("stroke", colors[idx]);
                idx = (idx + 1) % colors.length;
            }, 400);
        });
    </script>
    <title>Student Contribution Management Portal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #0a0a0f 0%, #1a1a2e 100%);
            margin: 0;
            padding: 0;
        }

        .splash {
            text-align: center;
            animation: fadeIn 0.8s ease-in-out;
        }

        .splash h1 {
            font-size: 2.5em;
            color: #e9ecef;
            margin-bottom: 20px;
            font-weight: 600;
            letter-spacing: 1px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .splash p {
            font-size: 1.2em;
            color: #adb5bd;
            margin-top: 20px;
            font-weight: 400;
        }

        .splash img {
            width: 120px;
            margin-bottom: 20px;
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.3));
            animation: pulse 2s ease-in-out infinite;
        }

        /* Loading spinner container */
        .loader-container {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        svg {
            filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.3));
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        /* Loading text animation */
        .loading-text {
            display: inline-block;
            animation: blink 1.4s infinite;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .splash h1 {
                font-size: 1.8em;
                padding: 0 20px;
            }

            .splash p {
                font-size: 1em;
            }

            .splash img {
                width: 90px;
            }
        }

        @media (max-width: 480px) {
            .splash h1 {
                font-size: 1.4em;
            }

            .splash p {
                font-size: 0.9em;
            }
        }
    </style>
</head>

<body>
    <div class="splash">
        <img src="assets/logo.png" alt="Student Contribution Management Portal Logo">
        <h1>Welcome to Student Contribution Management Portal</h1>
        <div class="loader-container">
            <svg width="60" height="60" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
                <circle cx="25" cy="25" r="20" stroke="#54A9D1" stroke-width="5" fill="none" stroke-linecap="round">
                    <animate
                        attributeName="stroke-dasharray"
                        values="10,40;40,10;10,40"
                        dur="1s"
                        repeatCount="indefinite" />
                    <animateTransform
                        attributeName="transform"
                        type="rotate"
                        from="0 25 25"
                        to="360 25 25"
                        dur="1s"
                        repeatCount="indefinite" />
                </circle>
            </svg>
            <p><span class="loading-text">Loading, please wait...</span></p>
        </div>
    </div>
</body>

</html>
<?php
exit;
?>