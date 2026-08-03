<?php
session_start();
include '../includes/functions.php';
check_login();
check_role('teacher');
include '../includes/db.php';
$page_title = 'Submit Grades';

// Get teacher id
$teacher_user_id = (int)$_SESSION['user_id'];
$teacher = $conn->query("SELECT id FROM teachers WHERE user_id = {$teacher_user_id} LIMIT 1")->fetch_assoc();
$teacher_id = (int)($teacher['id'] ?? 0);

if(isset($_POST['submit_grade'])){
    $student_id = (int)$_POST['student_id'];
    $subject_id = (int)$_POST['subject_id'];
    $grade = trim($_POST['grade'] ?? '');
    $grade = $grade === '' ? 'N/A' : ((int)$grade > 100 ? 100 : ((int)$grade < 0 ? 0 : $grade));
    $remarks = trim($_POST['remarks'] ?? '');

    // Get current semester
    $settings_result = $conn->query("SELECT current_semester FROM settings WHERE id=1");
    $quarter = 'Sem ' . (int)($settings_result ? $settings_result->fetch_assoc()['current_semester'] : 1);

    // ✅ Save grade using ON DUPLICATE KEY (requires UNIQUE on student+subject+quarter)
    $upsert = $conn->prepare("
        INSERT INTO grades (student_id, subject_id, grade, remarks, quarter, date_submitted) 
        VALUES (?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
            grade = VALUES(grade),
            remarks = VALUES(remarks),
            date_submitted = NOW()
    ");
    if($upsert){
        $upsert->bind_param("iisss", $student_id, $subject_id, $grade, $remarks, $quarter);
        $upsert->execute();
        $upsert->close();

        // Get student name
        $student_name = '';
        $get_student = $conn->prepare("
            SELECT u.name 
            FROM students s 
            JOIN users u ON s.user_id = u.id 
            WHERE s.id = ?
        ");
        $get_student->bind_param("i", $student_id);
        $get_student->execute();
        $res = $get_student->get_result();
        if($row = $res->fetch_assoc()) $student_name = $row['name'];
        $get_student->close();

        // Get subject name
        $subject_name = '';
        $get_subject = $conn->prepare("SELECT subject_name FROM subjects WHERE id = ?");
        $get_subject->bind_param("i", $subject_id);
        $get_subject->execute();
        $res2 = $get_subject->get_result();
        if($row2 = $res2->fetch_assoc()) $subject_name = $row2['subject_name'];
        $get_subject->close();

        set_flash_notice("Grade saved for {$student_name} - {$subject_name}", 'success');
    } else {
        set_flash_notice('Error saving grade.', 'error');
    }

    header("Location: submit_grades.php");
    exit();
}

include '../includes/layout_start.php';
?>

<h2>Submit Grade</h2>
<form method="POST">
    <div style="max-width:500px;">
        <div style="margin-bottom:20px;">
            <label>Student:</label>
            <select name="student_id" id="student_id" required onchange="loadSubjects()">
                <option value="">Select Student</option>
                <?php
                $students = $conn->query("
                    SELECT DISTINCT s.id, u.name
                    FROM students s 
                    JOIN enrollment_requests er ON er.student_id = s.id AND er.status = 'approved'
                    JOIN users u ON s.user_id = u.id
                    WHERE er.teacher_id = {$teacher_id}
                    ORDER BY u.name
                ");
                while($stu = $students->fetch_assoc()):
                ?>
                <option value="<?php echo (int)$stu['id']; ?>">
                    <?php echo htmlspecialchars($stu['name']); ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div style="margin-bottom:20px;">
            <label>Subject:</label>
            <select name="subject_id" id="subject_id" required disabled>
                <option value="">Select Subject</option>
            </select>
        </div>

        <div style="margin-bottom:20px;">
            <label>Grade (0-100 or N/A):</label>
            <input type="text" name="grade" style="width:100%;padding:10px;" placeholder="85" pattern="[0-9]{1,3}|N/A">
        </div>

        <div style="margin-bottom:20px;">
            <label>Remarks:</label>
            <textarea name="remarks" style="width:100%;padding:10px;height:80px;"></textarea>
        </div>

        <button type="submit" name="submit_grade" style="width:100%;padding:12px;background:#10b981;color:white;border:none;border-radius:6px;font-size:16px;">
            Save Grade
        </button>
    </div>
</form>

<script>
function loadSubjects() {
    const studentId = document.getElementById('student_id').value;
    const subjectSelect = document.getElementById('subject_id');
    
    subjectSelect.innerHTML = '<option value="">Loading...</option>';
    subjectSelect.disabled = true;

    if(studentId){
        fetch(`../get_subjects.php?student_id=${studentId}&teacher_id=<?php echo $teacher_id; ?>`)
        .then(r => r.json())
        .then(data => {
            subjectSelect.innerHTML = '<option value="">Select Subject</option>';
            data.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.text = s.name + (s.current_grade ? ` (Current: ${s.current_grade})` : '');
                subjectSelect.appendChild(opt);
            });
            subjectSelect.disabled = data.length === 0;
        }).catch(e => {
            subjectSelect.innerHTML = '<option value="">Error loading</option>';
        });
    }
}
</script>

<a href="grades_overview.php" style="display:inline-block;margin-top:20px;padding:10px 20px;background:#3b82f6;color:white;">
    ← Grades Overview
</a>

<?php include '../includes/layout_end.php'; ?>