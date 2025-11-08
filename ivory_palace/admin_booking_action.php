<?php
session_start();
include 'db_connect.php';

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    echo "<script>alert('Access denied, admin only'); window.location.href='login.html';</script>";
    exit();
}

// Get action and booking ID
$action = isset($_GET['action']) ? $_GET['action'] : '';
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

if ($booking_id > 0 && ($action == 'approve' || $action == 'reject')) {
    $new_status = ($action == 'approve') ? 'Approved' : 'Rejected';
    $sql = "UPDATE hotel_booking SET booking_status='$new_status' WHERE booking_id='$booking_id'";

    if ($conn->query($sql)) {
        echo "<script>alert('Booking $new_status successfully.'); window.location.href='admin_booking.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "<script>alert('Invalid request'); window.location.href='admin_booking.php';</script>";
}

$conn->close();
?>