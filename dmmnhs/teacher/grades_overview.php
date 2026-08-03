<?php
session_start();
include '../includes/functions.php';
check_login();
check_role('teacher');
include '../includes/db.php';
$page_title = 'Grades Overview';

$teacher_user_id = (int)$_SESSION['user_id'];
$teacher_result = $conn->query("SELECT id FROM teachers WHERE user_id = {$teacher_user_id} LIMIT 1");
$teacher = $teacher_result ? $teacher_result->fetch_assoc() : null;
$teacher_id = $teacher ? (int)$teacher['id'] : 0;
if($teacher_id === 0) die('Teacher profile not found.');

// Subjects
$subjects_result = $conn->query("
    SELECT DISTINCT s.subject_name, s.course_code, MIN(s.id) as id
    FROM subjects s 
    WHERE s.teacher_id = {$teacher_id}
    GROUP BY s.subject_name, s.course_code
    ORDER BY s.subject_name
");
$subjects = $subjects_result ? $subjects_result->fetch_all(MYSQLI_ASSOC) : [];

// Students & latest grades
$students_result = $conn->query("
    SELECT DISTINCT s.id, u.name
    FROM students s 
    JOIN enrollment_requests er ON er.student_id = s.id AND er.status = 'approved'
    JOIN users u ON s.user_id = u.id
    WHERE er.teacher_id = {$teacher_id}
    ORDER BY u.name
");

$students_data = [];
if($students_result){
    while($stu = $students_result->fetch_assoc()){
        $sid = (int)$stu['id'];
        $students_data[$sid] = ['name' => $stu['name'], 'grades' => []];

        foreach($subjects as $subj){
            $grade_result = $conn->query("
                SELECT grade 
                FROM grades 
                WHERE student_id = {$sid} AND subject_id = {$subj['id']}
                ORDER BY date_submitted DESC
                LIMIT 1
            ");
            $students_data[$sid]['grades'][$subj['id']] = $grade_result ? ($grade_result->fetch_assoc()['grade'] ?? 'N/A') : 'N/A';
        }
    }
}

include '../includes/layout_start.php';
?>

<h2>Grades Matrix (<?php echo count($students_data); ?> Students x <?php echo count($subjects); ?> Unique Subjects)</h2>
<p><a href="submit_grades.php">Submit/Edit Grades →</a></p>

<?php if(empty($subjects)): ?>
<div style="padding:40px; text-align:center; background:#fef3c7;">
    <h3>📚 No Subjects Yet</h3>
    <p>Add subjects in <a href="advisory_portal.php">Advisory Portal</a> first.</p>
</div>
<?php elseif(empty($students_data)): ?>
<div style="padding:40px; text-align:center; background:#fef3c7;">
    <h3>👥 No Students</h3>
    <p>Approve enrollment requests first.</p>
</div>
<?php else: ?>
<div style="overflow-x:auto;">
<table border="1" style="min-width:800px;">
<tr>
    <th style="position:sticky;left:0;background:#3b82f6;color:white;">Student</th>
    <?php foreach($subjects as $subj): ?>
    <th style="background:#f3f4f6;white-space:nowrap;min-width:120px;">
        <?php echo htmlspecialchars($subj['subject_name']); ?>
        <?php if($subj['course_code']): ?> (<?php echo htmlspecialchars($subj['course_code']); ?>)<?php endif; ?>
    </th>
    <?php endforeach; ?>
</tr>
<?php foreach($students_data as $sid => $student): ?>
<tr>
    <td style="position:sticky;left:0;background:#f9fafb;font-weight:600;min-width:200px;">
        <?php echo htmlspecialchars($student['name']); ?>
    </td>
    <?php foreach($subjects as $subj): 
        $grade = $student['grades'][$subj['id']] ?? 'N/A';
        $mapped = map_grade_display($grade);
    ?>
    <td style="text-align:center;padding:12px;">
        <span style="background:<?php echo $mapped['color']; ?>;color:white;padding:8px 12px;border-radius:6px;font-weight:bold;">
            <?php echo htmlspecialchars($mapped['label']); ?>
        </span>
    </td>
    <?php endforeach; ?>
</tr>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<a href="dashboard.php" style="display:inline-block;padding:12px 24px;background:#6b7280;color:white;text-decoration:none;border-radius:8px;">← Dashboard</a>

<?php include '../includes/layout_end.php'; ?>