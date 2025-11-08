<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    die("Access denied. Admin only.");
}

// Fixed query to get email from user table
$sql = "SELECT b.*, u.email, u.full_name, r.room_type
        FROM hotel_booking b
        JOIN user u ON b.user_id = u.user_id
        LEFT JOIN room r ON b.room_id = r.room_id
        ORDER BY b.booking_id DESC";
        
$result = $conn->query($sql);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Reports - Ivory Palace</title>
<link rel="stylesheet" href="style.css">

</head>
<body>

<header>
<h1>Admin Reports</h1>
<form action="logout.php" method="POST">
<button type="submit" class="logout-btn">Logout</button>
</form>
</header>

<main class="main-content">
<a href="admin_dashboard.php" class="back-btn">⬅️ Back to Dashboard</a>

<table>
<tr>
    <th>Booking ID</th>
    <th>Full Name</th>
    <th>Email</th>
    <th>Room Type</th>
    <th>Status</th>
    <th>Check-in</th>
    <th>Check-out</th>
</tr>

<?php if($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['booking_id'] ?></td>
    <td><?= $row['full_name'] ?></td>
    <td><?= $row['email'] ?></td>
    <td><?= $row['room_type'] ?? 'N/A' ?></td>
    <td><?= $row['booking_status'] ?></td>
    <td><?= $row['check_in_date'] ?></td>
    <td><?= $row['check_out_date'] ?></td>
</tr>
    <?php endwhile; ?>
<?php else: ?>
<tr><td colspan="7">No bookings found.</td></tr>
<?php endif; ?>
</table>
</main>

</body>

<style>
body { 
    font-family: 'Open Sans', sans-serif;
    background: linear-gradient(to bottom, #000, #302501ff, #857100ff);
    color: #fff;
    margin:0;
    padding:0; }

header { display:flex; 
    justify-content: space-between;
    align-items:center;
    padding:20px;
    background:#000;
    border-bottom:2px solid #ffd700; }

header h1 { 
    color:#ffd700; 
    margin:0; }

.logout-btn { 
    background:#ffd700; 
    color:#000; 
    border:none;
    padding:8px 15px; 
    border-radius:5px; 
    cursor:pointer;
}
.main-content { max-width:1200px; 
    margin:100px auto 30px auto; 
    padding:0 20px; 
} /* add margin-top so logout doesn't overlap */

table { 
    width:100%; 
    border-collapse:collapse;
    background: #252525ff;
}

th, td { 
    padding:12px;
    border:1px solid #ffd700; 
    text-align:center; 
}
th {     
    background: #ffd700;
    color: #000;}

td { 
    background:#222; 
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
</html>