<?php
session_start();
$page_title = 'Contact Us';
include 'includes/layout_start.php';
?>

<h2>Contact Us</h2>
<nav>
    <a href="about.php">About Us</a>
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
    <h4>School Office</h4>
    <p>Email: registrar@dmnhs.edu</p>
    <p>Phone: +63 900 000 0000</p>
    <p>Address: Don Mariano National High School, Philippines</p>
</div>
<h3 style="text-align: center;">Send a Message</h3>
<form class="stack-form auth-form" method="POST" action="#">
    <div class="field-row">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" placeholder="Your name">
    </div>
    <div class="field-row">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Your email">
    </div>
    <div class="field-row">
        <label for="message">Message</label>
        <textarea id="message" name="message" rows="4" placeholder="Type your message"></textarea>
    </div>
    <button type="button" onclick="showNotice('Template only: connect this form to backend/email later.', 'info')">Send Message</button>
</form>
<?php include 'includes/layout_end.php'; ?>