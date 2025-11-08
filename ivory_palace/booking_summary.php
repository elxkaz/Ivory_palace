<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$user_id = $_SESSION['user_id'];

// Fetch booking details and ensure it belongs to logged-in user
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
$total = ($room_price * $nights) + 100; // + RM100 deposit

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking Summary</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Poppins', sans-serif;
      color: #fff;
      position: relative;
      overflow: hidden;
    }

    /* Background image with blur */
    body::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('hotel-home.jpg') no-repeat center center/cover;
      filter: blur(8px) brightness(0.6);
      z-index: -1;
    }

    .summary-container {
      background: #000; /* Solid black */
      border: 2px solid #ffd700;
      border-radius: 15px;
      padding: 40px 50px;
      width: 400px;
      text-align: center;
      box-shadow: 0 0 25px rgba(0, 0, 0, 0.8);
      z-index: 2;
      animation: fadeIn 0.8s ease-in-out;
    }

    .summary-container h2 {
      color: #ffd700;
      font-size: 24px;
      margin-bottom: 10px;
    }

    .summary-container p {
      font-size: 15px;
      color: #ddd;
      margin: 6px 0;
    }

    .summary-container .price {
      font-size: 18px;
      color: #fff;
      font-weight: bold;
      margin-top: 15px;
    }

    .summary-container .total {
      font-size: 22px;
      color: #ffd700;
      font-weight: bold;
      margin-top: 10px;
    }

    button {
      background: #ffd700;
      border: none;
      color: #000;
      padding: 10px 25px;
      border-radius: 8px;
      font-weight: bold;
      font-size: 1rem;
      margin-top: 20px;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background: #fff;
      color: #000;
      transform: translateY(-2px);
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-15px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="summary-container">
    <h2>Booking Summary</h2>
    <p><strong>Room Type:</strong> <?= htmlspecialchars($row['room_type']) ?></p>
    <p><strong>Check-in:</strong> <?= htmlspecialchars($row['check_in_date']) ?></p>
    <p><strong>Check-out:</strong> <?= htmlspecialchars($row['check_out_date']) ?></p>
    <p><strong>Nights:</strong> <?= $nights ?></p>
    <p><strong>Room Price:</strong> RM <?= number_format($room_price, 2) ?></p>
    <p class="price"><strong>Deposit:</strong> RM 100.00</p>
    <p class="total"><strong>Total:</strong> RM <?= number_format($total, 2) ?></p>

    <form action="payment.php" method="get">
      <input type="hidden" name="booking_id" value="<?= $booking_id ?>">
      <input type="hidden" name="amount" value="<?= $total ?>">
      <button type="submit">Proceed to Payment</button>
    </form>
  </div>
</body>
</html>