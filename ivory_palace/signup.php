<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $fullname = $_POST['fullname'];
  $email = $_POST['email'];
  $password = $_POST['password'];

  // Hash password
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  // Insert new user
  $sql = "INSERT INTO user (full_name, email, password) VALUES ('$fullname', '$email', '$hashed_password')";

  if ($conn->query($sql) === TRUE) {
    $_SESSION['user_id'] = $conn->insert_id;
    $_SESSION['full_name'] = $fullname;
    $_SESSION['email'] = $email;

    echo "<script>
            alert('Signup successful! Welcome, $fullname');
            window.location.href='index.php';
          </script>";
  } else {
    echo "Error: " . $conn->error;
  }
}

$conn->close();
?>