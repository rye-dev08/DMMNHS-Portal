<?php
session_start();
include '../includes/functions.php';
check_login();
check_role('student');
include '../includes/db.php';
$page_title = 'Grades';

// Get student ID
$student_user_id = (int)$_SESSION['user_id'];
$student_profile = $conn->query("SELECT id FROM students WHERE user_id={$student_user_id} LIMIT 1")->fetch_assoc();
$student_id = (int)($student_profile['id'] ?? 0);

// Get current semester
$settings_result = $conn->query("SELECT current_semester FROM settings WHERE id=1");
$settings = $settings_result ? $settings_result->fetch_assoc() : [];
$current_sem = (int)($settings['current_semester'] ?? 1);

// Get grades per subject, current semester only
$grades_result = $conn->query("
    SELECT s.subject_name, 
           COALESCE(
               (SELECT g2.grade 
                FROM grades g2 
                WHERE g2.subject_id = s.id 
                      AND g2.student_id = {$student_id} 
                      AND g2.quarter = 'Sem {$current_sem}'
                ORDER BY g2.date_submitted DESC 
                LIMIT 1),
               'N/A'
           ) AS grade,
           COALESCE(
               (SELECT g2.remarks 
                FROM grades g2 
                WHERE g2.subject_id = s.id 
                      AND g2.student_id = {$student_id} 
                      AND g2.quarter = 'Sem {$current_sem}'
                ORDER BY g2.date_submitted DESC 
                LIMIT 1),
               ''
           ) AS remarks
    FROM subjects s
    WHERE s.teacher_id IN (
        SELECT teacher_id 
        FROM enrollment_requests 
        WHERE student_id = {$student_id} 
              AND status = 'approved'
    )
    ORDER BY s.subject_name
");

// ✅ GWA VARIABLES
$total = 0;
$count = 0;

include '../includes/layout_start.php';
?>

<h2>My Grades - Semester <?php echo $current_sem; ?></h2>

<p style="color: #6b7280; margin-bottom: 20px;">
<strong>Current Semester:</strong> <?php echo $current_sem; ?><br>
Subjects and grades shown for active/current semester only.
</p>

<table border="1">
<tr><th>Subject</th><th>Grade</th><th>Remarks</th></tr>

<?php if (!$grades_result || $grades_result->num_rows == 0): ?>
<tr>
    <td colspan="3" style="text-align:center;padding:40px;color:#6b7280;">
        No subjects or grades for Semester <?php echo $current_sem; ?> yet.<br>
        <small>Ask your teacher/adviser to assign subjects first.</small>
    </td>
</tr>

<?php else: 
while ($g = $grades_result->fetch_assoc()): 

    // ✅ COMPUTE GWA (ignore N/A)
    if(is_numeric($g['grade'])){
        $total += (float)$g['grade'];
        $count++;
    }

?>
<tr>
    <td><?php echo htmlspecialchars($g['subject_name']); ?></td>
    <td style="text-align:center;">
        <?php 
        $mapped = map_grade_display($g['grade']);
        ?>
        <span style="background:<?php echo $mapped['color']; ?>;color:white;padding:6px 12px;border-radius:4px;font-weight:bold;min-width:50px;display:inline-block;">
            <?php echo htmlspecialchars($mapped['label']); ?>
        </span>
    </td>
    <td><?php echo htmlspecialchars($g['remarks']); ?></td>
</tr>
<?php endwhile; endif; ?>
</table>

<?php
// ✅ CALCULATE FINAL GWA
$gwa = ($count > 0) ? round($total / $count, 2) : null;
?>

<!-- ✅ DISPLAY GWA -->
<div style="margin-top:20px;padding:15px;background:#f3f4f6;border-radius:8px;text-align:center;">
    <h3>General Weighted Average (GWA)</h3>
    <?php if($gwa !== null): ?>
        <span style="font-size:24px;font-weight:bold;color:#10b981;">
            <?php echo $gwa; ?>
        </span>
    <?php else: ?>
        <span style="color:#6b7280;">No grades available</span>
    <?php endif; ?>
</div>

<a href="dashboard.php" style="display:inline-block;margin-top:20px;padding:12px 24px;background:#6b7280;color:white;text-decoration:none;border-radius:8px;">
← Dashboard
</a>

<?php include '../includes/layout_end.php'; ?>