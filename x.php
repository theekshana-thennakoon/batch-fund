<?php
include("./assets/database.php");
date_default_timezone_set('Asia/Colombo');
session_start();

if (isset($_POST["login_btn"])) {
    $reg_number = $_POST["reg_number"];
    $password = $_POST["password"];
    $sql_user = "SELECT * FROM users WHERE reg_no = '{$reg_number}'";
    $result_user = $conn->query($sql_user);
    if ($result_user->num_rows > 0) {
        while ($row_user = $result_user->fetch_assoc()) {
            if ($password == "R2025") {
                $_SESSION['reg_no'] = $reg_number;
                header("Location:./");
            } else {
                $_SESSION['wrong_pwd'] = 1;
                echo "<script>window.history.back();</script>";
            }
        }
    } else {
        $_SESSION['wrong_user'] = 1;
        echo "<script>window.history.back();</script>";
    }
}

if (isset($_GET["logout"])) {
    unset($_SESSION["reg_no"]);
    header("Location:login");
}
