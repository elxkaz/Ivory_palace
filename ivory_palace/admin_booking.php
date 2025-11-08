<?php
session_start();
include 'db_connect.php';

// Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    echo "<script>alert('Access denied, admin only'); window.location.href='login.html';</script>";
    exit();
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$sql = "SELECT b.*, u.full_name, u.email as user_email, r.room_type, p.proof_of_payment
        FROM hotel_booking b
        JOIN user u ON b.user_id = u.user_id
        JOIN room r ON b.room_id = r.room_id
        LEFT JOIN payment p ON p.booking_id = b.booking_id";

if ($filter == 'pending') {
    $sql .= " WHERE booking_status='Pending Approval'";
} elseif ($filter == 'approved') {
    $sql .= " WHERE booking_status='Approved'";
}

$sql .= " ORDER BY b.booking_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Bookings</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>Admin Bookings</h1>
    <form action="logout.php" method="POST">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</header>

<div class="main">
    <a href="admin_dashboard.php" class="back-btn">⬅️ Back to Dashboard</a>
    <table>
        <tr>
            <th>Booking ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Room</th>
            <th>Check-in</th>
            <th>Check-out</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php if($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['booking_id'] ?></td>
                    <td><?= $row['full_name'] ?></td>
                    <td><?= $row['user_email'] ?></td>
                    <td><?= $row['room_type'] ?></td>
                    <td><?= $row['check_in_date'] ?></td>
                    <td><?= $row['check_out_date'] ?></td>
                    <td><?= $row['booking_status'] ?></td>
                    <td>
                        <?php if ($row['booking_status'] == 'Pending Approval'): ?>
                            <a href="view_proof.php?booking_id=<?= $row['booking_id'] ?>">
                                <button class="view-proof">View Proof</button>
                            </a>

                            <button class="approve" onclick="showActionPopup('approve', <?= $row['booking_id'] ?>)">Approve</button>

                            <button class="reject" onclick="showActionPopup('reject', <?= $row['booking_id'] ?>)">Reject</button>
                        <?php else: ?>
                            <a href="view_proof.php?booking_id=<?= $row['booking_id'] ?>">
                                <button class="view-proof">View Proof</button>
                            </a>
                            <p class="no-action">No further actions</p>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8">No bookings found.</td></tr>
        <?php endif; ?>
    </table>
</div>

<!-- Action Confirmation Popup -->
<div id="actionPopup" class="action-popup" style="display: none;">
    <div class="popup-overlay" onclick="hideActionPopup()"></div>
    <div class="popup-content">
        <div class="popup-icon" id="popupIcon">❓</div>
        <h3 id="popupTitle">Confirm Action</h3>
        <p id="popupMessage">Are you sure you want to perform this action?</p>
        <div class="popup-buttons">
            <button id="confirmButton" class="confirm-btn">Confirm</button>
            <button onclick="hideActionPopup()" class="cancel-btn">Cancel</button>
        </div>
    </div>
</div>

<script>
let currentBookingId = null;
let currentAction = null;

function showActionPopup(action, bookingId) {
    currentBookingId = bookingId;
    currentAction = action;
    
    const popup = document.getElementById('actionPopup');
    const icon = document.getElementById('popupIcon');
    const title = document.getElementById('popupTitle');
    const message = document.getElementById('popupMessage');
    const confirmButton = document.getElementById('confirmButton');
    
    if (action === 'approve') {
        icon.textContent = '✅';
        title.textContent = 'Approve Booking';
        message.textContent = `Are you sure you want to approve booking #${bookingId}?`;
        confirmButton.style.backgroundColor = '#4caf50';
        confirmButton.textContent = 'Approve';
    } else {
        icon.textContent = '❌';
        title.textContent = 'Reject Booking';
        message.textContent = `Are you sure you want to reject booking #${bookingId}?`;
        confirmButton.style.backgroundColor = '#f44336';
        confirmButton.textContent = 'Reject';
    }
    
    popup.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function hideActionPopup() {
    const popup = document.getElementById('actionPopup');
    popup.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function confirmAction() {
    if (currentBookingId && currentAction) {
        window.location.href = `admin_booking_action.php?action=${currentAction}&booking_id=${currentBookingId}`;
    }
}

// Set up event listeners
document.addEventListener('DOMContentLoaded', function() {
    const confirmButton = document.getElementById('confirmButton');
    confirmButton.addEventListener('click', confirmAction);
    
    // Close popup when clicking outside
    const popup = document.getElementById('actionPopup');
    popup.addEventListener('click', function(e) {
        if (e.target === popup) {
            hideActionPopup();
        }
    });
});
</script>

</body>

<style>
body {
    margin: 0;
    font-family: 'Open Sans', sans-serif;
    background: linear-gradient(to bottom, #000, #302501ff, #857100ff);
    color: #fff;
}
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #000;
    padding: 15px 30px;
    border-bottom: 2px solid #ffd700;
}
header h1 { color: #ffd700; }
.logout-btn {
    background: #ffd700;
    color: #000;
    border: none;
    padding: 8px 15px;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}
.logout-btn:hover { background: #fff; }
.main {
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 20px;
}
table {
    width: 100%;
    border-collapse: collapse;
    background: #252525ff;
    color: #fff;
    border: 2px solid #ffd700;
}
th, td {
    padding: 10px;
    border: 1px solid #ffd700;
    text-align: center;
}
th { 
    background: #ffd700;
    color: #000;
}
button {
    padding: 5px 10px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    font-weight: bold;
    margin: 2px;
}
.approve {  
    background: #4caf50; 
    color: white;
  }
.reject { 
    background:  #df2417ff; 
    color: white;
}
.view-proof { 
    background: #2196f3;
    color: white;
 }
.no-action{
    color: #6575ffff;
    font-weight: bold;
}

.back-btn {
    display: inline-block;
    margin-bottom: 20px;
    padding: 8px 15px;
    background: #ffd700;
    color: #000;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    transition: 0.3s;
}
.back-btn:hover { background: #fff; }

/* Action Popup Styles */
.action-popup {
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

.popup-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.confirm-btn, .cancel-btn {
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'Montserrat', sans-serif;
    min-width: 100px;
}

.confirm-btn {
    background: #4caf50;
    color: white;
}

.confirm-btn:hover {
    background: #45a049;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
}

.cancel-btn {
    background: #6c757d;
    color: white;
}

.cancel-btn:hover {
    background: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
}

.confirm-btn:active, .cancel-btn:active {
    transform: translateY(0);
}
</style>
</html>