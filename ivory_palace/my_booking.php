<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>
            alert('Please log in first.');
            window.location.href='login.html';
          </script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all bookings for the user
$sql = "SELECT b.*, r.room_type, r.price_per_night 
        FROM hotel_booking b
        JOIN room r ON b.room_id = r.room_id
        WHERE b.user_id = '$user_id'
        ORDER BY b.booking_id DESC";

$result = $conn->query($sql);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings - Ivory Palace</title>
  <link rel="stylesheet" href="style.css">
  
</head>
<body>
<?php include 'nav.php'; ?>

<main>
  <h2>Booking summary</h2>

  <?php if ($result && $result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
          <?php
          $check_in = new DateTime($row['check_in_date']);
          $check_out = new DateTime($row['check_out_date']);
          $nights = $check_out->diff($check_in)->days;
          if ($nights == 0) $nights = 1;
          $depo = ($row['price_per_night'] * $nights  *0.2) ;// 20% deposit
          $remain = ($row['price_per_night'] * $nights - $depo) ;// Remaining amount after deposit
          $total = $remain + $depo;

          // Emoji based on status
          $status_emoji = '';
          if($row['booking_status'] == 'Pending Payment') $status_emoji = '⚠️';
          if($row['booking_status'] == 'Pending Approval') $status_emoji = '⏳';
          if($row['booking_status'] == 'Approved') $status_emoji = '✅';
          if($row['booking_status'] == 'Rejected') $status_emoji = '❌';
          ?>
          
 <div class="booking-card"> 
    <?php         
            // Add image based on room type
            $roomImage = '';
          switch($row['room_type']) {
            case 'Deluxe Room':
              $roomImage = 'room-deluxe.jpg';
                break;
            case 'Standard Room':
                $roomImage = 'room-standard.jpg';
                  break;
             case 'Executive Suite':
                $roomImage = 'room-Executive-Suite.jpg';
                  break;
    }
    ?>
    <img src="<?= $roomImage ?>" alt="<?= htmlspecialchars($row['room_type']) ?>" class="room-image">
    <h3><?= htmlspecialchars($row['room_type']) ?></h3>
              <div class="booking-details">
                  <p><strong>Check-in:</strong><span> <?= $row['check_in_date'] ?></span></p>
                  <p><strong>Check-out:</strong><span> <?= $row['check_out_date'] ?></span></p>
                  <p><strong>Nights:</strong><span> <?= $nights ?></span></p>
                  <p><strong>Deposit 20%:</strong><span>RM <?= number_format($depo, 2) ?></span> </p>
                  <p><strong>Remaining payment:</strong> <span>RM <?= number_format($remain, 2) ?></span></p>
                  <hr>
                  <p><strong>Total payment:</strong> <span>RM <?= number_format($total, 2) ?></span></p>
                  <hr>
                  <p class="status <?= $row['booking_status'] ?>"><?= $status_emoji ?> <?= $row['booking_status'] ?></p>

                  <?php if($row['booking_status'] == 'Pending Payment'): ?>
                  <a href="payment.php?booking_id=<?= $row['booking_id'] ?>" class="upload-btn">Upload Proof of Payment</a>
                  <?php endif; ?>
              </div>
  </div>
      <?php endwhile; ?>
  <?php else: ?>
      <p style="text-align:center;">You have no bookings yet.</p>
  <?php endif; ?>
</main>

</body>
<style>
    body {
      font-family: 'Open Sans', sans-serif;
      background: url('hotel-home.jpg') no-repeat center center/cover;
      filter: brightness(1);
      margin: 0;
      padding: 0;
      color: #fff;
      min-height: 100vh;
    }

    body::before {
      content: "";
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      background: url('hotel-home.jpg') no-repeat center center/cover;
      filter: blur(8px) brightness(0.6);
      z-index: -1;
    }

    main {
      max-width: 900px;
      margin: 50px auto;
      padding: 0 20px;
      position: relative;
      z-index: 1;
    }

    h2 {
      text-align: center;
      color: #ffd700;
      margin-bottom: 30px;
    }

    .booking-card {
      display: flex;
      flex-direction: column;
      align-items: center;
      background: rgba(0,0,0,0.85);
      color: #fff;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 0 15px rgba(0,0,0,0.7);
      border: 2px solid #ffd700;
      min-width: 500px;
      text-align: center;
    }

    .room-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 10px;
        margin-bottom: 15px;
    }

    .booking-card h3 {
      margin-top: 0;
      color: #ffd700;
    }

    .booking-details p {
    margin: 6px 0;
    font-size: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    }
    
   .booking-details span {
    margin-left: 20px; /* adds some space between the label and value */
  }

    .booking-details {
    width: 100%; /* ensures the container takes full width */
    padding: 0 20px; /* adds some padding on the sides */
    box-sizing: border-box;
  } 

    .status {
      margin-top: 10px;
      font-weight: bold;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .status[class*="Pending Payment"] { 
        color: #ff9800; 
    }
    .status[class*="Pending Approval"] { 
        color: #FACC15; 
    }
    .status[class*="Approved"] { 
        color: #4caf50; 
    }
    .status[class*="Rejected"] { 
        color: #EF4444; 
    }

    .upload-btn {
      margin-left: 20px;
      margin-top: 15px;
      display: inline-block;
      padding: 8px 15px;
      background: #ffd700;
      color: #000;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      transition: 0.3s;
    }

    .upload-btn:hover {
      background: #fff;
    }
  </style>
</html>