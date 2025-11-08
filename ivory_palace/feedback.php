<?php
session_start();
include 'db_connect.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $feedback_text = $_POST['feedback_text'];

    $stmt = $conn->prepare("INSERT INTO feedback (user_id, email, feedback_text, date_submitted, feedback_status) VALUES (?, ?, ?, NOW(), 'Pending')");
    $stmt->bind_param("iss", $user_id, $email, $feedback_text);
    
    if ($stmt->execute()) {
        echo "<script>alert('Feedback submitted successfully!'); window.location.href='feedback.php';</script>";
    } else {
        echo "<script>alert('Error submitting feedback.');</script>";
    }
    
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Feedback - Aurora Palace</title>
  <link rel="stylesheet" href="style.css">
  <!-- import font from google -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <!-- import icon from google -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>

<?php include 'nav.php'; ?> 

<section class="feedback">
    <h2>We'd Love To Hear From You</h2>
<form action="feedback.php" method="POST">
  <label for="name">Full Name</label>
  <input type="text" id="name" placeholder="Enter your name">

  <label for="email">Email</label>
  <input type="email" name="email" placeholder="Enter your email" required>

  <label for="opinion">Feedback</label>
  <textarea id="opinion" name="feedback_text" placeholder="Share your feedback here" required></textarea>

  <button type="submit">Submit</button>
</form>
  </section>

<main>
<section class="contact">
  <P class="here">Here For You — Anytime You Need Us.</P>

  <div class="contact-container">
  <div class="contact-info">
    <p><i class="fa-solid fa-hotel fa-beat"></i></i><b>VISIT OUR PLACE</b></p>
    <p>No. 12, Jalan Bintang Indah, 55100 Kuala Lumpu</p>
    </div>
    <div class="contact-info">
    <p><i class="fas fa-envelope fa-bounce"></i><b>EMAIL SUPPORT</b></p>
    <p>IvoryPalace@gmail.com</p>
    </div>
    <div class="contact-info">
    <p><i class="fas fa-phone fa-shake"></i><b>CALL US DIRECTLY</b></p>
    <p> +60 3-2789 1122</p>
    </div>
    <div class="contact-info">
    <p><i class="fa-solid fa-clock fa-spin"></i><b>OPERATING HOURS</b></p>
    <p> Open 24-hours</p>
    </div>
  </div>
</section>
</main>

<footer>
  <p>© 2025 Ivory Palace. All rights reserved.</p>
</footer>

</body>
</html>
