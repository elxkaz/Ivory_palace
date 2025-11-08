<?php
session_start();
include 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    echo "<script>alert('Access denied, admin only'); window.location.href='login.html';</script>";
    exit();
}

// Fetch counts for bookings
$total_sql = "SELECT COUNT(*) as total FROM hotel_booking";
$total_res = $conn->query($total_sql)->fetch_assoc();

$pending_sql = "SELECT COUNT(*) as pending FROM hotel_booking WHERE booking_status='Pending Approval'";
$pending_res = $conn->query($pending_sql)->fetch_assoc();

$approved_sql = "SELECT COUNT(*) as approved FROM hotel_booking WHERE booking_status='Approved'";
$approved_res = $conn->query($approved_sql)->fetch_assoc();

// Also get rejected count if you want to display it
$rejected_sql = "SELECT COUNT(*) as rejected FROM hotel_booking WHERE booking_status='Rejected'";
$rejected_res = $conn->query($rejected_sql)->fetch_assoc();

// Get counts for all statuses for the chart
$status_counts_sql = "SELECT booking_status, COUNT(*) as count 
                      FROM hotel_booking 
                      GROUP BY booking_status";
$status_counts_result = $conn->query($status_counts_sql);

$status_counts = [];
while($row = $status_counts_result->fetch_assoc()) {
    $status_counts[$row['booking_status']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Ivory Palace</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<header>
    <h1>Admin Dashboard</h1>
    <form action="logout.php" method="POST">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</header>

<div class="main">
    <p class="dashboard-subtitle">Overview of current bookings and activity </p>
    <div class="cards">
        <a href="admin_booking.php?filter=all" class="card">
            <h3>Total <i class="fa-solid fa-address-book"></i></h3>
            <p><?= $total_res['total'] ?></p>
        </a>
        <a href="admin_booking.php?filter=pending" class="card">
            <h3>Pending <i class="fa-solid fa-hourglass-half"></i></h3>
            <p><?= $pending_res['pending'] ?></p>
        </a>
        <a href="admin_booking.php?filter=approved" class="card">
            <h3>Approved <i class="fa-solid fa-check"></i></h3>
            <p><?= $approved_res['approved'] ?></p>
        </a>
        <a href="admin_feedback.php" class="card">
            <h3>Feedback <i class="fa-solid fa-comments"></i></h3>
            <?php
            $feedback_sql = "SELECT COUNT(*) as feedback_count FROM feedback";
            $feedback_res = $conn->query($feedback_sql)->fetch_assoc();
            ?>
            <p><?= $feedback_res['feedback_count'] ?></p>
        </a>
        <a href="admin_users.php" class="card">
            <h3>Users <i class="fa-solid fa-circle-user"></i></h3>
            <?php
            $users_sql = "SELECT COUNT(*) as users_count FROM user";
            $users_res = $conn->query($users_sql)->fetch_assoc();
            ?>
            <p><?= $users_res['users_count'] ?></p>
        </a>
        <a href="admin_reports.php" class="card">
            <h3>Reports <i class="fa-solid fa-book"></i></h3>
            <p><?= $total_res['total'] ?></p>
        </a>
    </div>

    <!-- Statistics Section with Doughnut Chart -->
    <div class="statistics-section">
        <h2 class="section-title">Booking Statistics</h2>
        <div class="chart-container">
            <div class="chart-wrapper">
                <canvas id="bookingChart"></canvas>
                <!-- Manual center text overlay -->
                <div class="doughnut-center">
                    <p class="center-total">Total</p>
                    <p class="center-number"><?= $total_res['total'] ?></p>
                </div>
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <span class="color-dot" style="background-color: #4caf50;"></span>
                    <span>Approved: <?= $status_counts['Approved'] ?? 0 ?></span>
                </div>
                <div class="legend-item">
                    <span class="color-dot" style="background-color: #ff9800;"></span>
                    <span>Pending: <?= $status_counts['Pending Approval'] ?? 0 ?></span>
                </div>
                <div class="legend-item">
                    <span class="color-dot" style="background-color: #f44336;"></span>
                    <span>Rejected: <?= $status_counts['Rejected'] ?? 0 ?></span>
                </div>
                <div class="legend-item">
                    <span class="color-dot" style="background-color: #2196f3;"></span>
                    <span>Pending Payment: <?= $status_counts['Pending Payment'] ?? 0 ?></span>
                </div>
                <div class="total-item">
                    <span class="total-text">Total: <?= $total_res['total'] ?> Bookings</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Doughnut Chart for Booking Statistics
const ctx = document.getElementById('bookingChart').getContext('2d');
const bookingChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Approved', 'Pending Approval', 'Rejected', 'Pending Payment'],
        datasets: [{
            data: [
                <?= $status_counts['Approved'] ?? 0 ?>,
                <?= $status_counts['Pending Approval'] ?? 0 ?>,
                <?= $status_counts['Rejected'] ?? 0 ?>,
                <?= $status_counts['Pending Payment'] ?? 0 ?>
            ],
            backgroundColor: [
                '#4caf50', // Green for Approved
                '#ff9800', // Orange for Pending Approval
                '#f44336', // Red for Rejected
                '#2196f3'  // Blue for Pending Payment
            ],
            borderColor: '#ffd700',
            borderWidth: 3,
            hoverOffset: 15,
            cutout: '60%' // This creates the doughnut hole
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false // We'll use our custom legend
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.raw || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = Math.round((value / total) * 100);
                        return `${label}: ${value} (${percentage}%)`;
                    }
                }
            }
        },
        animation: {
            animateScale: true,
            animateRotate: true
        }
    }
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
header h1 {
    color: #ffd700;
}
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
.logout-btn:hover {
    background: #fff;
}
.main {
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 20px;
}

.dashboard-subtitle {
    text-align: center;
    font-weight: bold;
    margin-bottom: 30px;
    font-size: 2rem;
    color: #080808ff;
    border: 2px solid #ffd700;
    padding: 10px;
    border-radius: 12px;
    text-shadow: 0 0 10px #ffd700;
    background: linear-gradient(
    90deg,
    #000000,
    #4b3800,
    #8b6f00,
    #ffd700,
    #ffcc00,
    #8b6f00,
    #4b3800,
    #000000
  );
    background-size: 300% 300%;
    animation: goldFlow 6s ease infinite;
}

@keyframes goldFlow {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

.cards {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
    margin-bottom: 40px;
}
.card {
    background: #000;
    border: 2px solid #ffd700;
    border-radius: 12px;
    padding: 30px 40px;
    text-align: center;
    width: 200px;
    cursor: pointer;
    transition: transform 0.3s;
    text-decoration: none;
    color: #fff;
    font-size: 1.5rem
}

.card i{
    font-size: 2.5rem;
}
.card:hover {
   transform: scale(1.03);
  box-shadow: 0 0 20px #ffd700;
}
.card h3 {
    color: #ffd700;
    margin-bottom: 15px;
}
.card p {
    font-size: 24px;
    font-weight: bold;
}

/* Statistics Section */
.statistics-section {
    background: #000;
    border: 2px solid #ffd700;
    border-radius: 12px;
    padding: 30px;
    margin-top: 30px;
}

.section-title {
    text-align: center;
    color: #ffd700;
    margin-bottom: 30px;
    font-size: 1.8rem;
    border-bottom: 1px solid #ffd700;
    padding-bottom: 10px;
}

.chart-container {
    display: flex;
    flex-wrap: wrap;
    gap: 40px;
    align-items: center;
    justify-content: center;
}

.chart-wrapper {
    width: 320px;
    height: 320px;
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Manual center text for doughnut chart */
.doughnut-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    pointer-events: none; /* Make sure it doesn't interfere with chart interactions */
}

.center-total {
    font-size: 18px;
    color: #ffd700;
    margin: 0;
    font-weight: bold;
}

.center-number {
    font-size: 32px;
    font-weight: bold;
    color: #ffd700;
    margin: 0;
    text-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
}

.chart-legend {
    display: flex;
    flex-direction: column;
    gap: 15px;
    background: #111;
    padding: 25px;
    border-radius: 8px;
    border: 1px solid #ffd700;
    min-width: 250px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 16px;
    color: #fff;
    padding: 5px 0;
}

.color-dot {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.total-item {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #ffd700;
    text-align: center;
}

.total-text {
    font-weight: bold;
    color: #ffd700;
    font-size: 18px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .chart-container {
        flex-direction: column;
    }
    
    .chart-wrapper {
        width: 280px;
        height: 280px;
    }
    
    .cards {
        gap: 15px;
    }
    
    .card {
        width: 150px;
        padding: 20px 25px;
    }
    
    .chart-legend {
        min-width: 200px;
    }
    
    .center-total {
        font-size: 16px;
    }
    
    .center-number {
        font-size: 28px;
    }
}
</style>
</html>