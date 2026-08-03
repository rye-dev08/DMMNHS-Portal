<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/functions.php';
include '../includes/db.php';

check_login();
check_role('admin');

$page_title = 'Enrollment Settings';

// =========================
// 💾 SAVE ADVISORY CLASS
// =========================
if(isset($_POST['save_advisory'])){
    $teacher_user_id = (int)($_POST['teacher_user_id'] ?? 0);
    $advisory_class = trim($_POST['advisory_class'] ?? '');

    if($teacher_user_id > 0){
        $stmt = $conn->prepare("UPDATE teachers SET advisory_class = ? WHERE user_id = ?");
        $stmt->bind_param("si", $advisory_class, $teacher_user_id);
        if($stmt->execute()){
            set_flash_notice('Advisory class saved!', 'success');
        } else {
            set_flash_notice('Failed to save advisory class: ' . $stmt->error, 'error');
        }
        $stmt->close();
    }
    header("Location: enrollment_settings.php");
    exit();
}

// =========================
// 📚 NEW SEMESTER
// =========================
if(isset($_POST['end_semester'])){
    $conn->begin_transaction();
    try {
        $settings = $conn->query("SELECT current_semester, current_school_year FROM settings WHERE id=1")->fetch_assoc();
        $curr_sem = (int)($settings['current_semester'] ?? 1);
        $curr_year = $settings['current_school_year'];

        // Archive subjects
        $conn->query("
            INSERT INTO previous_semester_subjects 
            (original_subject_id, student_id, teacher_id, subject_name, course_code, teacher_code, room_no, archived_semester, archived_school_year)
            SELECT id, student_id, teacher_id, subject_name, course_code, teacher_code, room_no, {$curr_sem}, '{$curr_year}'
            FROM subjects
        ");

        // Archive grades
        $conn->query("
            INSERT INTO previous_semester_grades 
            (original_grade_id, student_id, subject_id, grade, quarter, archived_semester, archived_school_year)
            SELECT id, student_id, subject_id, grade, quarter, {$curr_sem}, '{$curr_year}'
            FROM grades
        ");

        // Clear current semester data
        $conn->query("DELETE FROM enrollment_requests");
        $conn->query("DELETE FROM grades");
        $conn->query("DELETE FROM subjects");
        $conn->query("DELETE FROM teacher_subjects");

        // Reset teachers
        $conn->query("UPDATE teachers SET advisory_class = NULL");

        // Reset students
        $conn->query("UPDATE students SET needs_reenrollment='yes' WHERE status='active'");

        // Next semester
        $next_sem = $curr_sem + 1;
        $conn->query("UPDATE settings SET current_semester = {$next_sem} WHERE id=1");

        $conn->commit();
        set_flash_notice("Semester {$curr_sem} archived. New Semester {$next_sem} - System fully reset!", 'success');

    } catch (Exception $e) {
        $conn->rollback();
        set_flash_notice('Error: ' . $e->getMessage(), 'error');
    }

    header("Location: enrollment_settings.php");
    exit();
}

// =========================
// 🎓 END SCHOOL YEAR
// =========================
if(isset($_POST['end_school_year'])){
    $conn->begin_transaction();
    try {
        $settings = $conn->query("SELECT current_semester, current_school_year FROM settings WHERE id=1")->fetch_assoc();
        $curr_sem = (int)($settings['current_semester'] ?? 1);
        $curr_year = $settings['current_school_year'] ?? '2024-2025';

        // Archive subjects
        $conn->query("
            INSERT INTO previous_semester_subjects 
            (original_subject_id, student_id, teacher_id, subject_name, course_code, teacher_code, room_no, archived_semester, archived_school_year)
            SELECT id, student_id, teacher_id, subject_name, course_code, teacher_code, room_no, {$curr_sem}, '{$curr_year}'
            FROM subjects
        ");

        // Archive grades
        $conn->query("
            INSERT INTO previous_semester_grades 
            (original_grade_id, student_id, subject_id, grade, quarter, archived_semester, archived_school_year)
            SELECT id, student_id, subject_id, grade, quarter, {$curr_sem}, '{$curr_year}'
            FROM grades
        ");

        // Update student grade levels
        $students = $conn->query("SELECT id, grade_level FROM students WHERE status='active'");
        while ($s = $students->fetch_assoc()) {
            $student_id = (int)$s['id'];
            $new_grade = (int)$s['grade_level'] + 1;

            if($new_grade >= 14){
                $conn->query("DELETE FROM students WHERE id={$student_id}");
            } else {
                $conn->query("UPDATE students SET grade_level={$new_grade}, needs_reenrollment='yes' WHERE id={$student_id}");
            }
        }

        // Clear current semester data
        $conn->query("DELETE FROM enrollment_requests WHERE status IN ('pending', 'approved')");
        $conn->query("DELETE FROM subjects");
        $conn->query("DELETE FROM teacher_subjects");
        $conn->query("DELETE FROM grades");

        // Reset school year
        $year_parts = explode('-', $curr_year);
        $start_year = (int)$year_parts[0];
        $next_year = ($start_year + 1) . '-' . ($start_year + 2);
        $conn->query("UPDATE settings SET current_semester=1, current_school_year='{$next_year}' WHERE id=1");

        $conn->commit();
        set_flash_notice("School year ended, new year reset!", 'success');

    } catch (Exception $e) {
        $conn->rollback();
        set_flash_notice('Error: ' . $e->getMessage(), 'error');
    }

    header("Location: enrollment_settings.php");
    exit();
}

// =========================
// Load settings & teachers
// =========================
$settings_result = $conn->query("SELECT * FROM settings WHERE id=1");
$settings = $settings_result ? $settings_result->fetch_assoc() : [
    'current_semester' => 1,
    'current_school_year' => '2024-2025',
    'max_students_per_class' => 30
];

$teachers_result = $conn->query("
    SELECT u.id AS user_id, u.name, t.advisory_class
    FROM users u
    JOIN teachers t ON t.user_id = u.id
    WHERE u.role='teacher' AND u.status='active' AND t.status='active'
    ORDER BY u.name
");

include '../includes/layout_start.php';
?>

<h2>Enrollment & Semester Management</h2>

<h3>Current: Sem <?php echo (int)$settings['current_semester']; ?> - <?php echo htmlspecialchars($settings['current_school_year']); ?></h3>

<div style="display: flex; gap: 20px; margin: 20px 0;">
    <form method="POST" style="flex: 1;">
        <button type="submit" name="end_semester" onclick="return confirm('Reset to new semester?')" style="width: 100%; padding: 15px; font-size: 18px; background: #ff9800; color: white; border: none; border-radius: 8px;">📚 New Semester</button>
    </form>
    <form method="POST" style="flex: 1;">
        <button type="submit" name="end_school_year" onclick="return confirm('Reset new school year?')" style="width: 100%; padding: 15px; font-size: 18px; background: #f44336; color: white; border: none; border-radius: 8px;">🎓 New School Year</button>
    </form>
</div>

<h3>Teachers Advisory</h3>
<table border="1">
<tr><th>Teacher</th><th>Advisory Class</th><th>Action</th></tr>
<?php while($t = $teachers_result->fetch_assoc()): ?>
<form method="POST">
<tr>
    <td><?php echo htmlspecialchars($t['name']); ?></td>
    <td>
        <input type="hidden" name="teacher_user_id" value="<?php echo (int)$t['user_id']; ?>">
        <input type="text" name="advisory_class" value="<?php echo htmlspecialchars($t['advisory_class'] ?? ''); ?>" placeholder="e.g. Grade 11-A" size="15">
    </td>
    <td><button type="submit" name="save_advisory">Save</button></td>
</tr>
</form>
<?php endwhile; ?>
</table>

<a href="dashboard.php">Dashboard</a>

<?php include '../includes/layout_end.php'; ?>