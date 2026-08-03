<?php
session_start();
include '../includes/functions.php';
check_login();
check_role('teacher');
include '../includes/db.php';
$page_title = 'Class Subjects';

$teacher_user_id = (int)$_SESSION['user_id'];
$teacher_profile = $conn->query("SELECT id FROM teachers WHERE user_id = {$teacher_user_id} LIMIT 1")->fetch_assoc();
$teacher_id = (int)($teacher_profile['id'] ?? 0);

// ✅ ADD SUBJECT
if(isset($_POST['add_subject'])){
    $subject_name = trim($_POST['subject_name']);
    $course_code = trim($_POST['course_code'] ?? '');
    $teacher_code = trim($_POST['teacher_code'] ?? '');
    $room_no = trim($_POST['room_no'] ?? '');

    if(!$subject_name){
        set_flash_notice('Subject name required.', 'error');
        header("Location: advisory_portal.php");
        exit();
    }

    // ✅ CHECK DUPLICATE: same teacher, same subject name OR course code
    $dup_check = $conn->prepare("
        SELECT id FROM teacher_subjects 
        WHERE teacher_id = ? AND (subject_name = ? OR course_code = ?) LIMIT 1
    ");
    $dup_check->bind_param("iss", $teacher_id, $subject_name, $course_code);
    $dup_check->execute();
    $dup_res = $dup_check->get_result();
    if($dup_res && $dup_res->num_rows > 0){
        set_flash_notice("Cannot add subject: same name or course code already exists.", 'error');
        header("Location: advisory_portal.php");
        exit();
    }
    $dup_check->close();

    // ✅ ADD TO TEACHER_SUBJECTS
    $ins = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject_name, course_code, teacher_code, room_no) VALUES (?, ?, ?, ?, ?)");
    $ins->bind_param("issss", $teacher_id, $subject_name, $course_code, $teacher_code, $room_no);
    $ins->execute();

    // ✅ AUTO APPLY TO ALL CURRENT APPROVED STUDENTS
    $students = $conn->query("SELECT DISTINCT student_id FROM enrollment_requests WHERE teacher_id = {$teacher_id} AND status = 'approved'");
    $applied = 0;
    while($student = $students->fetch_assoc()){
        $student_id = (int)$student['student_id'];
        
        $check = $conn->prepare("SELECT 1 FROM subjects WHERE student_id = ? AND teacher_id = {$teacher_id} AND subject_name = ?");
        $check->bind_param("is", $student_id, $subject_name);
        $check->execute();
        
        if(($check->get_result()->num_rows ?? 0) === 0) {
            $apply = $conn->prepare("INSERT INTO subjects (teacher_id, student_id, subject_name, course_code, teacher_code, room_no) VALUES ({$teacher_id}, ?, ?, ?, ?, ?)");
            $apply->bind_param("issss", $student_id, $subject_name, $course_code, $teacher_code, $room_no);
            $apply->execute();
            $applied++;
        }
    }

    set_flash_notice("Subject '{$subject_name}' added & applied to {$applied} students.", 'success');
    header("Location: advisory_portal.php");
    exit();
}

// ✅ DELETE SUBJECT
if(isset($_POST['delete_subject'])){
    $subject_id = (int)$_POST['subject_id'];

    // ✅ CHECK IF ANY STUDENT HAS GRADES IN THIS SUBJECT
    $check_grades = $conn->prepare("
        SELECT 1 FROM grades g
        JOIN subjects s ON g.subject_id = s.id
        WHERE s.teacher_id = ? AND s.id = ? LIMIT 1
    ");
    $check_grades->bind_param("ii", $teacher_id, $subject_id);
    $check_grades->execute();
    $res = $check_grades->get_result();
    if($res && $res->num_rows > 0){
        set_flash_notice("Cannot delete subject: one or more students already have grades for this subject.", 'error');
        header("Location: advisory_portal.php");
        exit();
    }
    $check_grades->close();

    // ✅ SAFE TO DELETE
    $subject_name = $conn->query("SELECT subject_name FROM teacher_subjects WHERE id = {$subject_id} AND teacher_id = {$teacher_id}")->fetch_assoc()['subject_name'] ?? '';
    if($subject_name){
        $conn->query("DELETE FROM teacher_subjects WHERE id = {$subject_id} AND teacher_id = {$teacher_id}");
        $conn->query("DELETE FROM subjects WHERE teacher_id = {$teacher_id} AND subject_name = '{$conn->real_escape_string($subject_name)}'");
        set_flash_notice("Subject '{$subject_name}' deleted safely.", 'success');
    }

    header("Location: advisory_portal.php");
    exit();
}

// ✅ GET CURRENT SUBJECTS
$subjects = $conn->query("SELECT * FROM teacher_subjects WHERE teacher_id = {$teacher_id} ORDER BY subject_name");

// ✅ COUNT APPROVED STUDENTS
$approved_count = $conn->query("SELECT COUNT(DISTINCT student_id) FROM enrollment_requests WHERE teacher_id = {$teacher_id} AND status = 'approved'")->fetch_assoc()['COUNT(DISTINCT student_id)'];

include '../includes/layout_start.php';
?>

<h2>Class Subjects (Applies to <?php echo $approved_count; ?> Students)</h2>

<form method="POST">
    <table border="1">
    <tr><th>Subject</th><th>Course Code</th><th>Teacher Code</th><th>Room</th><th>Add</th></tr>
    <tr>
        <td><input type="text" name="subject_name" required placeholder="Mathematics" style="width:150px;"></td>
        <td><input type="text" name="course_code" placeholder="MATH101"></td>
        <td><input type="text" name="teacher_code" placeholder="T001"></td>
        <td><input type="text" name="room_no" placeholder="Rm 101"></td>
        <td><button type="submit" name="add_subject">Add Subject</button></td>
    </tr>
    </table>
</form>

<h3>Current Subjects</h3>
<table border="1">
<tr><th>Subject</th><th>Code</th><th>Teacher</th><th>Room</th><th>Delete?</th></tr>
<?php while($s = $subjects->fetch_assoc()): ?>
<tr>
    <td><?php echo htmlspecialchars($s['subject_name']); ?></td>
    <td><?php echo htmlspecialchars($s['course_code']); ?></td>
    <td><?php echo htmlspecialchars($s['teacher_code']); ?></td>
    <td><?php echo htmlspecialchars($s['room_no']); ?></td>
    <td>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="subject_id" value="<?php echo (int)$s['id']; ?>">
            <button type="submit" name="delete_subject" onclick="return confirm('Delete & remove from ALL students?')" style="background:red;color:white;border:none;padding:5px;">Delete</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>

<a href="dashboard.php">← Dashboard</a>

<?php include '../includes/layout_end.php'; ?>