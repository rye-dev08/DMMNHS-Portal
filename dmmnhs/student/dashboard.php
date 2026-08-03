<?php
session_start();
include '../includes/functions.php';
check_login();
check_role('student');
include '../includes/db.php';
$page_title = 'Student Dashboard';
include '../includes/layout_start.php';
?>

<h2>Welcome, <?php echo $_SESSION['name']; ?> (Student)</h2>
<div class="main-lobby">
    <h3>Don Mariano Marcos National HighSchool Portal</h3>
    <p>Welcome to the student portal. Navigate through the links above to access your information, grades, and enrollment options.</p>

    <div class="privacy-note">
    <strong>Privacy Notice:</strong> Screenshotting, recording, or sharing Grades, Profile Info, Scores, and other student records without permission is strictly prohibited for data privacy. The administration also commits to protecting each user's private data.
</div>
    <div class="info-cards">


        <div class="card">
            <h4>Student Info</h4>
            <p>View and update your personal information, change password, and check your status.</p>
        </div>
        <div class="card">
            <h4>Student Grades</h4>
            <p>Check your current and previous grades with color-coded performance.</p>
        </div>
        <div class="card">
            <h4>Enrollment Request</h4>
            <p>Submit enrollment requests to your advisers for approval.</p>
        </div>
        <div class="card">
            <h4>My Scores</h4>
            <p>View your Activity, Quiz, and Exam scores submitted by your adviser.</p>
        </div>
    </div>
</div>
<?php include '../includes/layout_end.php'; ?>
