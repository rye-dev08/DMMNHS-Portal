<?php
session_start();
include '../includes/functions.php';
check_login();
check_role('teacher');
include '../includes/db.php';
$page_title = 'Teacher Dashboard';
include '../includes/layout_start.php';
$teacher_user_id = (int)$_SESSION['user_id'];
$teacher = $conn->query("SELECT * FROM teachers WHERE user_id=".$teacher_user_id)->fetch_assoc();
$teacher_profile_id = (int)($teacher['id'] ?? 0);
?>
<h2>Welcome, <?php echo $_SESSION['name']; ?> (Teacher)</h2>
<div class="privacy-note">
    <strong>Privacy Notice:</strong> Screenshotting, recording, or sharing student Grades, Profile Info, Scores, and similar records without authorization is strictly prohibited for data privacy. The administration also maintains privacy and confidentiality of all user records.
</div>

<div class="card">
    <h4>Dashboard Summary</h4>
    <?php
    $advisoryLabel = isset($teacher['advisory_class']) && $teacher['advisory_class'] !== '' ? $teacher['advisory_class'] : 'Not set';
    echo "<p><strong>Advisory:</strong> ".htmlspecialchars($advisoryLabel)."</p>";
    $subjects_result = $conn->query("SELECT COUNT(*) AS total FROM subjects WHERE teacher_id=".$teacher_profile_id);
    $subjects_count = $subjects_result ? (int)$subjects_result->fetch_assoc()['total'] : 0;
    echo "<p><strong>Total Subject Entries:</strong> ".$subjects_count."</p>";

    $approved_result = $conn->query("SELECT COUNT(*) AS total FROM enrollment_requests WHERE teacher_id=".$teacher_profile_id." AND status='approved'");
    $approved_count = $approved_result ? (int)$approved_result->fetch_assoc()['total'] : 0;
    echo "<p><strong>Approved Students:</strong> ".$approved_count."</p>";
    ?>
</div>

<div class="card" style="margin-top:12px;">
    <h4>Assessment Scores Module</h4>
    <p>Use <strong>Assessment Scores</strong> to add Activity, Quiz, and Exam items per student with auto-increment item numbers, then update or delete entries when needed.</p>
</div>
<?php include '../includes/layout_end.php'; ?>
