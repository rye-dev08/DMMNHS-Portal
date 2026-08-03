<?php
session_start();
include '../includes/functions.php';
check_login();
check_role('student');
include '../includes/db.php';
$page_title = 'Class Schedule';

$student_user_id = (int)$_SESSION['user_id'];
$student_result = $conn->query("SELECT id FROM students WHERE user_id = {$student_user_id} LIMIT 1");
$student = $student_result ? $student_result->fetch_assoc() : null;
$student_id = (int)($student['id'] ?? 0);

if($student_id === 0){
    die('Student profile not found.');
}

$settings_result = $conn->query("SELECT current_semester FROM settings WHERE id=1");
$settings = $settings_result ? $settings_result->fetch_assoc() : [];
$current_sem = (int)($settings['current_semester'] ?? 1);

$schedule_result = $conn->query("
    SELECT s.subject_name, s.course_code, s.teacher_code, s.room_no, u.name AS teacher_name
    FROM subjects s 
    JOIN teachers t ON t.id = s.teacher_id
    JOIN users u ON u.id = t.user_id
    WHERE s.student_id = {$student_id}
    ORDER BY s.subject_name
");
$schedule = $schedule_result ?: false;
$schedule_num_rows = $schedule ? $schedule->num_rows : 0;

include '../includes/layout_start.php';
?>

<h2>My Class Schedule - Semester <?php echo $current_sem; ?></h2>

<p style="color: #6b7280; margin-bottom: 20px;"><strong>Current Semester:</strong> <?php echo $current_sem; ?><br>
Schedule shown for active/current semester only.</p>

<?php if($schedule_num_rows === 0): ?>
<div style="text-align:center; padding:40px; background:#fef3c7; border-radius:8px;">
    <h3>No Classes for Semester <?php echo $current_sem; ?> Yet</h3>
    <p>Await teacher/adviser enrollment. <a href="enrollment_request.php">Submit Request</a>.</p>
</div>
<?php else: ?>
<table border="1" style="width:100%;">
<tr>
    <th>Subject</th>
    <th>Course Code</th>
    <th>Teacher Code</th>
    <th>Room</th>
    <th>Teacher</th>
</tr>
<?php while($row = $schedule->fetch_assoc()): ?>
<tr>
    <td><strong><?php echo htmlspecialchars($row['subject_name']); ?></strong></td>
    <td><?php echo htmlspecialchars($row['course_code']); ?></td>
    <td><?php echo htmlspecialchars($row['teacher_code']); ?></td>
    <td><?php echo htmlspecialchars($row['room_no']); ?></td>
    <td><?php echo htmlspecialchars($row['teacher_name']); ?></td>
</tr>
<?php endwhile; ?>
</table>
<?php endif; ?>

<a href="dashboard.php" style="display:inline-block; margin-top:20px; padding:10px 20px; background:#3b82f6; color:white; text-decoration:none; border-radius:6px;">← Dashboard</a>

<?php include '../includes/layout_end.php'; ?>

