<?php
// Start session once at the top
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home - Ivory Palace</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>

  <?php include 'nav.php'; ?>
  

  <main>
    <section class="home">
      <div class="text-image">
        <h2>Unwind. Relax. Enjoy. <br>Stay in <span class="glow">Ivory Palace</span></h2>
        <img src="hotel-home.jpg" alt="hotel" class="responsive-img"> 
      </div>
    </section>

    <section class="statistic">
      <div class="statistic-card">
        <h1>96%<span>+</span></h1>
        <p>Positive Feedback</p>
      </div>
      <div class="statistic-card">
        <h1>5<span>+</span></h1>
        <p>Years of Expertise</p>
      </div>
      <div class="statistic-card">
        <h1>10K<span>+</span></h1>
        <p>Happy Clients</p>
      </div>
    </section>

    <section class="about">
      <h2>About Us</h2>
      <p>
        Welcome to <strong>Ivory Palace</strong>, a luxury hotel where elegance meets comfort. 
        We are dedicated to providing exceptional hospitality, modern amenities, and unforgettable 
        experiences for every guest.
      </p>
    </section>
  </main>

  <footer>
    <p>© 2025 Ivory Palace. All rights reserved.</p>
  </footer>
</body>

</html>