<?php
session_start();
include 'db_connect.php';

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    echo "<script>alert('Access denied, admin only'); window.location.href='login.html';</script>";
    exit();
}

// Get booking ID
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

if ($booking_id > 0) {
    $sql = "SELECT proof_of_payment FROM payment WHERE booking_id='$booking_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $proof_path = $row['proof_of_payment'];
    } else {
        $proof_path = '';
    }
} else {
    $proof_path = '';
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Proof - Admin</title>
<link rel="stylesheet" href="style.css">
<style>
body { background: #111; color: #fff; font-family: 'Open Sans', sans-serif; text-align: center; padding: 50px; }
h1 { color: #ffd700; }
img { max-width: 80%; border: 3px solid #ffd700; border-radius: 10px; }
a { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #ffd700; color: #000; text-decoration: none; border-radius: 6px; }
a:hover { background: #fff; }
</style>
</head>
<body>
<h1>Payment Proof</h1>

<?php if ($proof_path && file_exists($proof_path)): ?>
    <img src="<?= $proof_path ?>" alt="Proof of Payment">
<?php else: ?>
    <p>No proof of payment uploaded yet.</p>
<?php endif; ?>

<br>
<a href="admin_booking.php">Back to Bookings</a>
</body>
</html>