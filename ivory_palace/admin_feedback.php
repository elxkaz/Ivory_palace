<?php
session_start();
include 'db_connect.php';

// Only allow admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    echo "<script>alert('Access denied. Admin only.'); window.location.href='login.html';</script>";
    exit();
}

// Handle "Mark as Done" action
if (isset($_GET['mark_done'])) {
    $feedback_id = intval($_GET['mark_done']);
    $update = "UPDATE feedback SET feedback_status='Done' WHERE feedback_id='$feedback_id'";
    $conn->query($update);
    header("Location: admin_feedback.php");
    exit();
}

// Fetch all feedback
$sql = "SELECT * FROM feedback ORDER BY date_submitted DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Feedback - Ivory Palace</title>
<link rel="stylesheet" href="style.css">

<header>
    <h1>Admin Feedback</h1>
    <form action="logout.php" method="POST">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</header>

</head>
<body>
<main>
    <a href="admin_dashboard.php" class="back-btn">⬅️ Back to Dashboard</a>
    <h2>User Feedback</h2>

    <div class="feedback-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <?php
                    $status_emoji = ($row['feedback_status'] == 'Done') ? '✅' : '⏳';
                ?>
                <div class="feedback-card">
                    <p><strong>Email:</strong> <?= htmlspecialchars($row['email'] ?? 'Guest') ?></p>
                    <span><hr></span>
                    <p><strong>Feedback:</strong> <?= htmlspecialchars($row['feedback_text']) ?></p>
                    <p class="status <?= $row['feedback_status'] ?? 'Pending' ?>"><?= $status_emoji ?> <?= $row['feedback_status'] ?? 'Pending' ?></p>
                    <?php if(($row['feedback_status'] ?? 'Pending') != 'Done'): ?>
                        <a href="admin_feedback.php?mark_done=<?= $row['feedback_id'] ?>" class="done-btn">Mark as Done</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;">No feedback available.</p>
        <?php endif; ?>
    </div>
</main>
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
header h1 { 
    color: #ffd700; 
    margin: 0;
}

    h2 {
        text-align: center;
        color: #ffd700;
        margin-bottom: 30px;
    }

    /* Container for side-by-side layout */
    .feedback-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 350px));
        gap: 20px;
        align-items: start;
    }

    .feedback-card {
        background: rgba(0,0,0,0.8);
        border: 2px solid #ffd700;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        word-wrap: break-word;
        overflow-wrap: break-word;
        height: fit-content; /* Card height fits content */
    }
    .feedback-card p {
        margin: 6px 0;
        font-size: 15px;
        word-wrap: break-word; /* Also apply to paragraphs */
        overflow-wrap: break-word;
    }
    .status {
        font-weight: bold;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .status.Pending { color: #ff9800; }
    .status.Done { color: #4caf50; }
    .done-btn {
        display: inline-block;
        margin-top: 10px;
        padding: 6px 12px;
        background: #ffd700;
        color: #000;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
        transition: 0.3s;
    }
    .done-btn:hover { background: #fff; }
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
</style>

</html>