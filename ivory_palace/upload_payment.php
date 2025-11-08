<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit();
}

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : (isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0);
$user_id = $_SESSION['user_id'];

// Verify booking exists and belongs to user
$check_sql = "SELECT booking_id FROM hotel_booking WHERE booking_id = ? AND user_id = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>
            alert('Booking not found or unauthorized access.');
            window.location.href='my_booking.php';
          </script>";
    exit();
}
$stmt->close();

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['proof'])) {
    $file = $_FILES['proof'];

    // Basic validation
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo 'Upload error. Code: ' . $file['error'];
        exit();
    }

    $maxSize = 5 * 1024 * 1024; // 5 MB
    if ($file['size'] > $maxSize) {
        echo 'File too large. Max 5MB.';
        exit();
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo 'Invalid file type. Allowed: JPG, PNG, PDF.';
        exit();
    }

    $target_dir = __DIR__ . "/uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $target_path = $target_dir . $safeName;
    $db_path = 'uploads/' . $safeName; // path to store in DB (relative)

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // Insert payment record (prepared statement)
        $conn->begin_transaction();
        $ins = $conn->prepare("INSERT INTO payment (booking_id, proof_of_payment) VALUES (?, ?)");
        $ins->bind_param("is", $booking_id, $db_path);
        $ok1 = $ins->execute();
        $ins->close();

        $upd = $conn->prepare("UPDATE hotel_booking SET booking_status = 'Pending Approval' WHERE booking_id = ?");
        $upd->bind_param("i", $booking_id);
        $ok2 = $upd->execute();
        $upd->close();

        if ($ok1 && $ok2) {
            $conn->commit();
            header("Location: my_booking.php");
            exit();
        } else {
            $conn->rollback();
            echo 'Database error: ' . $conn->error;
            exit();
        }
    } else {
        echo 'Failed to move uploaded file.';
        exit();
    }
}

$conn->close();
?>
