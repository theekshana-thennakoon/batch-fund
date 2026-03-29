<?php
include("../assets/database.php");
date_default_timezone_set('Asia/Colombo');
session_start();

if (isset($_POST["add_reason"])) {
    $reason = $_POST["reason"];
    $price = $_POST["price"];
    $reason_type = $_POST["reason_type"];

    $sql = "INSERT INTO reasons (reason,`type`, price)
            VALUES ('{$reason}', '{$reason_type}', {$price})";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_add_reason'] = 1;
        echo "<script>window.history.back();</script>";
    }
}

if (isset($_POST["login_btn"])) {
    $reg_number = $_POST["reg_number"];
    $password = $_POST["password"];
    if ($reg_number == "ITT/2021/109" && $password == "Batchfund1@") {
        $_SESSION['admin_logged_user'] = $reg_number;
        header("Location:./");
    } else {
        $_SESSION['wrong_pwd'] = 1;
        echo "<script>window.history.back();</script>";
    }
}

if (isset($_GET["logout"])) {
    unset($_SESSION["admin_logged_user"]);
    header("Location:./login");
}
