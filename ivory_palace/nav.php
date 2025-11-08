

<header>

  <p>
    <span class="ivory-glow">Ivory</span><br>
    <span class="palace-bold">Palace</span>
  </p>

  <nav>
    <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a>
    <a href="rooms.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'active' : ''; ?>">Rooms</a>
    <a href="booking.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'booking.php' ? 'active' : ''; ?>">Booking</a>
    <?php if (isset($_SESSION['user_id'])): ?>
    <a href="my_booking.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'my_booking.php' ? 'active' : ''; ?>">My Bookings</a>
    <?php endif; ?>
    <a href="feedback.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'feedback.php' ? 'active' : ''; ?>">Feedback</a>
  </nav>

  <div class="auth-buttons">
    <?php if (isset($_SESSION['user_id'])): ?>
      <form action="logout.php" method="POST">
        <button type="submit" class="btn logout">Logout</button>
      </form>
    <?php else: ?>
      <a href="login.html" class="btn login">Login</a>
      <a href="signup.html" class="btn signup">Sign Up</a>
    <?php endif; ?>
  </div>
</header>