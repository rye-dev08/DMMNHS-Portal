<?php
session_start();

// 🔥 optional debug (pwede mo alisin later)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/functions.php';
include '../includes/db.php';

check_login();
check_role('teacher');

$page_title = 'Enrollment Requests';

$teacher_user_id = (int)$_SESSION['user_id'];
$teacher_profile_result = $conn->query("SELECT id FROM teachers WHERE user_id = {$teacher_user_id} LIMIT 1");
$teacher_profile = $teacher_profile_result ? $teacher_profile_result->fetch_assoc() : null;
$teacher_id = $teacher_profile ? (int)$teacher_profile['id'] : 0;

if($teacher_id === 0){
    die('Teacher profile not found.');
}

if(isset($_POST['approve'])){
    $request_id = (int)$_POST['request_id'];
    
    // 🔥 FIXED: limit → max_limit
    $cap_result = $conn->query("
        SELECT COALESCE(ta.max_students, t.max_students, 30) as max_limit 
        FROM teachers t 
        LEFT JOIN teacher_approval ta 
            ON ta.teacher_id = t.id AND ta.status = 'approved' 
        WHERE t.id = {$teacher_id}
        LIMIT 1
    ");

    $cap = $cap_result ? $cap_result->fetch_assoc() : ['max_limit' => 30];
    $limit = (int)($cap['max_limit'] ?? 30);
    
    $current_result = $conn->query("SELECT COUNT(*) as count FROM enrollment_requests WHERE teacher_id = {$teacher_id} AND status = 'approved'");
    $current = $current_result ? (int)$current_result->fetch_assoc()['count'] : 0;
    
    if($current < $limit){

        // ✅ approve request
        $update_stmt = $conn->prepare("UPDATE enrollment_requests SET status = 'approved' WHERE id = ? AND teacher_id = {$teacher_id}");
        if($update_stmt){
            $update_stmt->bind_param("i", $request_id);
            $update_stmt->execute();
            $update_stmt->close();
        }

        // ✅ get student id safely
        $student_result = $conn->query("SELECT student_id FROM enrollment_requests WHERE id = {$request_id} LIMIT 1");
        $student_row = $student_result ? $student_result->fetch_assoc() : null;
        $student_id = $student_row ? (int)$student_row['student_id'] : 0;

        if($student_id === 0) {
            set_flash_notice('Could not get student ID.', 'error');
        } else {

            $teacher_subjects_result = $conn->query("SELECT * FROM teacher_subjects WHERE teacher_id = {$teacher_id}");

            if($teacher_subjects_result) {
                while($ts = $teacher_subjects_result->fetch_assoc()){

                    $subject_name = $conn->real_escape_string($ts['subject_name']);

                    // ✅ check duplicate
                    $check_result = $conn->query("
                        SELECT 1 FROM subjects 
                        WHERE student_id = {$student_id} 
                        AND teacher_id = {$teacher_id} 
                        AND subject_name = '{$subject_name}' 
                        LIMIT 1
                    ");

                    if($check_result && $check_result->num_rows === 0){

                        // ✅ INSERT (improved: no direct variable injection except teacher_id)
                        $ins = $conn->prepare("
                            INSERT INTO subjects 
                            (teacher_id, student_id, subject_name, course_code, teacher_code, room_no) 
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");

                        if($ins){
                            $ins->bind_param(
                                "iissss",
                                $teacher_id,
                                $student_id,
                                $ts['subject_name'],
                                $ts['course_code'],
                                $ts['teacher_code'],
                                $ts['room_no']
                            );
                            $ins->execute();
                            $ins->close();
                        }
                    }
                }
            } else {
                set_flash_notice('No teacher subjects. Add in advisory portal first.', 'info');
            }

            set_flash_notice('Approved and subjects auto-applied!', 'success');
        }

    }else{
        set_flash_notice('Class full! Cannot approve.', 'error');
    }

    header("Location: enrollment_requests.php");
    exit();
}

if(isset($_POST['reject'])){
    $request_id = (int)$_POST['request_id'];

    $reject_stmt = $conn->prepare("UPDATE enrollment_requests SET status = 'rejected' WHERE id = ? AND teacher_id = {$teacher_id}");
    if($reject_stmt){
        $reject_stmt->bind_param("i", $request_id);
        $reject_stmt->execute();
        $reject_stmt->close();
        set_flash_notice('Rejected.', 'info');
    }

    header("Location: enrollment_requests.php");
    exit();
}

$requests_result = $conn->query("
    SELECT er.*, s.id as student_id, u.name as student_name
    FROM enrollment_requests er 
    JOIN students s ON er.student_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE er.teacher_id = {$teacher_id}
    ORDER BY er.status = 'pending' DESC, er.date_requested DESC
") ?: false;

if($requests_result === false){
    error_log("Teacher requests query failed: " . $conn->error);
}

include '../includes/layout_start.php';
?>

<h2>Enrollment Requests (<?php 
$pending_result = $conn->query("SELECT COUNT(*) as count FROM enrollment_requests WHERE teacher_id = {$teacher_id} AND status = 'pending'");
$pending_count = $pending_result ? (int)$pending_result->fetch_assoc()['count'] : 0;
echo $pending_count; ?> Pending)</h2>

<table border="1">
<tr><th>Student</th><th>Date</th><th>Status</th><th>Action</th></tr>
<?php if($requests_result): while($r = $requests_result->fetch_assoc()): ?>
<tr class="<?php echo $r['status']; ?>">
    <td><?php echo htmlspecialchars($r['student_name']); ?></td>
    <td><?php echo htmlspecialchars($r['date_requested']); ?></td>
    <td><?php echo ucfirst($r['status']); ?></td>
    <td>
        <?php if($r['status'] === 'pending'): ?>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="request_id" value="<?php echo (int)$r['id']; ?>">
            <button type="submit" name="approve" style="background:#10b981; color:white; border:none; padding:8px 16px; border-radius:4px;">Approve</button>
        </form> /
        <form method="POST" style="display:inline;">
            <input type="hidden" name="request_id" value="<?php echo (int)$r['id']; ?>">
            <button type="submit" name="reject" style="background:#ef4444; color:white; border:none; padding:8px 16px; border-radius:4px;">Reject</button>
        </form>
        <?php else: ?>
        --
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; endif; ?>
</table>

<a href="dashboard.php" style="display:inline-block; padding:12px 24px; background:#6b7280; color:white; text-decoration:none; border-radius:8px; margin-top:20px;">← Dashboard</a>

<?php include '../includes/layout_end.php'; ?>