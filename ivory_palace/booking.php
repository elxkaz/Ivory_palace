<?php
session_start();
include 'db_connect.php'; // Your database connection

// Redirect if user not logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>
            alert('Please log in before booking.');
            window.location.href='login.html';
          </script>";
    exit();
}

// Generate unique token for form submission
if (!isset($_SESSION['booking_token'])) {
    $_SESSION['booking_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if token matches to prevent duplicate submission
    if (!isset($_POST['booking_token']) || $_POST['booking_token'] !== $_SESSION['booking_token']) {
        echo "<script>
                alert('Invalid form submission. Please try again.');
                window.location.href='booking.php';
              </script>";
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $room_id = $_POST['room_id'];
    $check_in_date = $_POST['check_in_date'];
    $check_out_date = $_POST['check_out_date'];
    $number_of_guest = $_POST['number_of_guest'];
    $booking_status = "Pending Payment";

    // Get user email
    $email_sql = "SELECT email FROM user WHERE user_id = '$user_id'";
    $email_result = $conn->query($email_sql);
    if ($email_result->num_rows > 0) {
        $user_row = $email_result->fetch_assoc();
        $email = $user_row['email'];
    } else {
        echo "<script>
                alert('User not found.');
                window.location.href='booking.php';
              </script>";
        exit();
    }

    // Get room price and calculate total
    $room_sql = "SELECT price_per_night FROM room WHERE room_id = '$room_id'";
    $room_result = $conn->query($room_sql);
    if ($room_result->num_rows > 0) {
        $room_row = $room_result->fetch_assoc();
        $price_per_night = $room_row['price_per_night'];
        
        // Calculate number of nights and total price
        $check_in = new DateTime($check_in_date);
        $check_out = new DateTime($check_out_date);
        $interval = $check_in->diff($check_out);
        $number_of_nights = $interval->days;
        $total_price = $number_of_nights * $price_per_night;
    } else {
        echo "<script>
                alert('Room not found.');
                window.location.href='booking.php';
              </script>";
        exit();
    }

    // Check if similar booking already exists (prevent duplicates)
    $check_sql = "SELECT booking_id FROM hotel_booking 
                  WHERE user_id = '$user_id' 
                  AND room_id = '$room_id' 
                  AND check_in_date = '$check_in_date' 
                  AND check_out_date = '$check_out_date' 
                  AND booking_status = 'Pending Payment'";
    
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        echo "<script>
                alert('You already have a pending booking for these dates.');
                window.location.href='booking.php';
              </script>";
        exit();
    }

    // Insert booking with email and total_price
    $sql = "INSERT INTO hotel_booking (user_id, email, room_id, check_in_date, check_out_date, number_of_guest, booking_status, total_price)
            VALUES ('$user_id', '$email', '$room_id', '$check_in_date', '$check_out_date', '$number_of_guest', '$booking_status', '$total_price')";

    if ($conn->query($sql) === TRUE) {
        $booking_id = $conn->insert_id;
        
        // Invalidate the token to prevent duplicate submissions
        unset($_SESSION['booking_token']);
        
        // Show success popup then redirect
        echo "<script>
                setTimeout(function() {
                    showSuccessPopup();
                }, 100);
                
                function showSuccessPopup() {
                    const popup = document.createElement('div');
                    popup.className = 'success-popup';
                    popup.innerHTML = `
                        <div class='popup-content'>
                            <div class='popup-icon'>✅</div>
                            <h3>Booking Successful!</h3>
                            <p>Your booking has been recorded. Please proceed to payment.</p>
                            <p><strong>Total Amount: RM $total_price</strong></p>
                            <button onclick='redirectToPayment($booking_id)'>Proceed to Payment</button>
                        </div>
                        <div class='popup-overlay'></div>
                    `;
                    document.body.appendChild(popup);
                    document.body.style.overflow = 'hidden';
                }
                
                function redirectToPayment(bookingId) {
                    window.location.href = 'payment.php?booking_id=' + bookingId;
                }
              </script>";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Generate new token if not exists
if (!isset($_SESSION['booking_token'])) {
    $_SESSION['booking_token'] = bin2hex(random_bytes(32));
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking - Ivory Palace</title>
  <link rel="stylesheet" href="style.css">
  
</head>
<body>

<?php include 'nav.php'; ?>

<main>
  <section class="booking">
    <h2>Book Your Stay</h2>
    <form action="booking.php" method="POST" id="bookingForm">
        <!-- CSRF Token to prevent duplicate submissions -->
        <input type="hidden" name="booking_token" value="<?= $_SESSION['booking_token'] ?>">
        
        <label for="room">Select Room</label>
        <select id="room" name="room_id" required onchange="updatePrice()">
            <option value="">Select a room</option>
            <option value="1" data-price="200">Standard Room - RM200</option>
            <option value="2" data-price="380">Deluxe Room - RM380</option>
            <option value="3" data-price="900">Executive Suite - RM900</option>
        </select>

        <label for="checkin">Check-in Date</label>
        <input type="date" id="checkin" name="check_in_date" required onchange="calculateTotal()">

        <label for="checkout">Check-out Date</label>
        <input type="date" id="checkout" name="check_out_date" required onchange="calculateTotal()">

        <label for="guests">Number of Guests</label>
        <input type="number" id="guests" name="number_of_guest" min="1" max="4" required>

        <!-- Display calculated price -->
        <div id="priceDisplay" style="margin: 15px 0; padding: 10px; background: rgb(40, 40, 41); border-radius: 12px; display: none;">
            <strong>Estimated Total: RM <span id="totalAmount">0</span></strong>
            <br>
            <small id="nightsCount">0 nights</small>
        </div>

        <button type="submit" id="submitBtn">Confirm Booking</button>
    </form>
  </section>
</main>

<footer>
  <p>© 2025 Ivory Palace. All rights reserved.</p>
</footer>

<script>
let roomPrices = {
    '1': 200,
    '2': 380,
    '3': 900
};

function updatePrice() {
    calculateTotal();
}

function calculateTotal() {
    const roomSelect = document.getElementById('room');
    const checkin = document.getElementById('checkin');
    const checkout = document.getElementById('checkout');
    const priceDisplay = document.getElementById('priceDisplay');
    const totalAmount = document.getElementById('totalAmount');
    const nightsCount = document.getElementById('nightsCount');
    
    const selectedRoom = roomSelect.value;
    const checkinDate = new Date(checkin.value);
    const checkoutDate = new Date(checkout.value);
    
    if (selectedRoom && checkin.value && checkout.value && checkoutDate > checkinDate) {
        const timeDiff = checkoutDate.getTime() - checkinDate.getTime();
        const nights = Math.ceil(timeDiff / (1000 * 3600 * 24));
        const pricePerNight = roomPrices[selectedRoom];
        const total = nights * pricePerNight;
        
        totalAmount.textContent = total;
        nightsCount.textContent = nights + ' night' + (nights !== 1 ? 's' : '');
        priceDisplay.style.display = 'block';
    } else {
        priceDisplay.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');
    const bookingForm = document.getElementById('bookingForm');
    const submitBtn = document.getElementById('submitBtn');
    
    // Set min date to today
    let today = new Date().toISOString().split('T')[0];
    checkinInput.min = today;
    checkoutInput.min = today;
    
    // Update checkout min date when checkin changes
    checkinInput.addEventListener('change', function() {
        const nextDay = new Date(this.value);
        nextDay.setDate(nextDay.getDate() + 1);
        const minCheckout = nextDay.toISOString().split('T')[0];
        
        checkoutInput.min = minCheckout;
        
        // Clear checkout date if it's before or equal to checkin date
        if (checkoutInput.value && checkoutInput.value <= this.value) {
            checkoutInput.value = minCheckout;
        }
        
        calculateTotal();
    });
    
    checkoutInput.addEventListener('change', calculateTotal);
    
    // Prevent form submission if dates are invalid
    bookingForm.addEventListener('submit', function(e) {
        if (checkoutInput.value <= checkinInput.value) {
            e.preventDefault();
            alert('Check-out date must be after check-in date');
            return false;
        }
        
        // Disable submit button to prevent double submission
        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing...';
    });
    
    // Re-enable button if user goes back (though this shouldn't happen with our popup)
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Confirm Booking';
        }
    });
});
</script>

</body>

<style>
    /* Success Popup Styles */
    .success-popup {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .popup-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.8);
      backdrop-filter: blur(5px);
    }

    .popup-content {
      position: relative;
      background: linear-gradient(135deg, #1a1a1a, #2d2d2d);
      border: 2px solid #ffd700;
      border-radius: 15px;
      padding: 2rem;
      text-align: center;
      max-width: 400px;
      width: 90%;
      box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
      animation: popupAppear 0.5s ease-out;
      z-index: 1001;
    }

    @keyframes popupAppear {
      0% {
        opacity: 0;
        transform: scale(0.8) translateY(-20px);
      }
      100% {
        opacity: 1;
        transform: scale(1) translateY(0);
      }
    }

    .popup-icon {
      font-size: 3rem;
      margin-bottom: 1rem;
      animation: bounce 1s ease-in-out;
    }

    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
      }
      40% {
        transform: translateY(-10px);
      }
      60% {
        transform: translateY(-5px);
      }
    }

    .popup-content h3 {
      color: #ffd700;
      margin-bottom: 1rem;
      font-size: 1.5rem;
      font-family: 'Montserrat', sans-serif;
    }

    .popup-content p {
      color: #fff;
      margin-bottom: 1.5rem;
      line-height: 1.5;
      font-size: 1rem;
    }

    .popup-content button {
      background: #ffd700;
      color: #000;
      border: none;
      padding: 12px 30px;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s ease;
      font-family: 'Montserrat', sans-serif;
    }

    .popup-content button:hover {
      background: #fff;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(255, 215, 0, 0.4);
    }

    .popup-content button:active {
      transform: translateY(0);
    }
    
    /* Style for disabled button */
    button:disabled {
      background-color: #cccccc;
      cursor: not-allowed;
    }
  </style>
</html>