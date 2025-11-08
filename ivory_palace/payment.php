<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$user_id = $_SESSION['user_id'];

// Fetch booking details to get total
$sql = "SELECT b.*, r.room_type, r.price_per_night 
        FROM hotel_booking b 
        JOIN room r ON b.room_id = r.room_id 
        WHERE b.booking_id='$booking_id' AND b.user_id='$user_id'";

$result = $conn->query($sql);
if ($result->num_rows == 0) {
    die("Booking not found or is not yours.");
}

$row = $result->fetch_assoc();

// Calculate total
$check_in = new DateTime($row['check_in_date']);
$check_out = new DateTime($row['check_out_date']);
$nights = $check_out->diff($check_in)->days;
if ($nights == 0) $nights = 1;

$room_price = $row['price_per_night'];
$depo = ($row['price_per_night'] * $nights  *0.2) ;// 20% deposit
$total = ($row['price_per_night'] * $nights ) ;// Remaining amount after deposit

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment - Ivory Palace</title>
  <link rel="stylesheet" href="payment.css">
</head>
<body>
  <div class="payment-container">
    <h2>Payment Details</h2>
    <p>Please scan the QR code below to make your payment.</p>

    <div class="qr-section">
      <img src="qr.jpg" alt="QR Code for Payment">
    </div>

    <form action="upload_payment.php" method="post" enctype="multipart/form-data" class="upload-section">
      <input type="hidden" name="booking_id" value="<?= $booking_id ?>">

      <label for="proof">Upload Proof of Payment of Deposit:</label><br><br>
      <input type="file" name="proof" id="proof" required><br><br>

      <button type="submit">Upload</button>
    </form>

    <p class="status"><strong>Status:</strong> <?= $row['booking_status'] ?></p>
    <p><strong>Deposit Amount:</strong> RM <?= number_format($depo, 2) ?></p>
    <p><strong>Total Amount:</strong> RM <?= number_format($total, 2) ?></p>
  </div>
</body>
</html>