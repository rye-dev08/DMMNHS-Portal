<?php
session_start();
$page_title = 'About Us';
include 'includes/layout_start.php';
?>

<h2>About Us</h2>
<nav>
    <a href="contact.php">Contact Us</a>
    <?php if(!empty($_SESSION['role'])): ?>
        <?php if($_SESSION['role'] === 'admin'): ?>
            <a href="admin/dashboard.php">Back to Dashboard</a>
        <?php elseif($_SESSION['role'] === 'teacher'): ?>
            <a href="teacher/dashboard.php">Back to Dashboard</a>
        <?php else: ?>
            <a href="student/dashboard.php">Back to Dashboard</a>
        <?php endif; ?>
    <?php endif; ?>
</nav>

<div class="card">
    <h4>Our Mission</h4>
    <p>
        Don Mariano National High School Portal helps admins, teachers, and students manage enrollment,
        advisory classes, schedules, and grade tracking in one centralized system.
    </p>
</div>
<br>
<div class="card">
    <h4>Our Vision</h4>
    <p>
        Don Mariano National High School Portal helps admins, teachers, and students manage enrollment,
        advisory classes, schedules, and grade tracking in one centralized system.
    </p>
</div>
<br>
<div class="card">
    <h4>What This Portal Supports</h4>
    <p>
        Account management, teacher approval with limits, student enrollment requests, class scheduling,
        and grading workflows with transparent status updates.
    </p>
</div>

<?php include 'includes/layout_end.php'; ?>