<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM user WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            // Store user info in session
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['fullname'] = $row['full_name'];
            $_SESSION['user_type'] = $row['user_type']; // admin or user

            // Redirect based on user type
            if ($row['user_type'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            echo "<script>alert('Invalid password'); window.location.href='login.html';</script>";
        }
    } else {
        echo "<script>alert('Email not found'); window.location.href='login.html';</script>";
    }

    $conn->close();
}
?>