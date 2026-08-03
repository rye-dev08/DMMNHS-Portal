<?php
session_start();
include '../includes/functions.php';
check_login();
check_role('student');
include '../includes/db.php';
$page_title = 'Enrollment Request';

// ✅ Enable error reporting for debugging (remove on production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get student info
$student_user_id = (int)$_SESSION['user_id'];
$student_profile_query = $conn->query("
    SELECT id, status, grade_level, needs_reenrollment 
    FROM students 
    WHERE user_id = {$student_user_id} 
    LIMIT 1
");

if(!$student_profile_query){
    die("Database error: ".$conn->error);
}

$student_profile = $student_profile_query->fetch_assoc();
$student_id = (int)($student_profile['id'] ?? 0);

if($student_id === 0){
    die('Student profile not found. Contact admin.');
}

// Check if graduated or inactive
$is_graduate_or_inactive = ($student_profile['grade_level'] >= 13 || $student_profile['status'] != 'active');

$message = '';

if(isset($_POST['send_request'])){
    $teacher_id = (int)($_POST['teacher_id'] ?? 0);

    if($student_profile['needs_reenrollment'] === 'yes'){
        $update = $conn->prepare("UPDATE students SET needs_reenrollment = 'no' WHERE id = ?");
        if($update){
            $update->bind_param("i", $student_id);
            $update->execute();
            $update->close();
        }
    }

    if($is_graduate_or_inactive){
        $message = 'Cannot enroll. Graduated or inactive.';
    } else {
        // Check if already enrolled
        $existing_query = $conn->prepare("
            SELECT 1 FROM enrollment_requests 
            WHERE student_id = ? AND status IN ('pending','approved') LIMIT 1
        ");
        if($existing_query){
            $existing_query->bind_param("i", $student_id);
            $existing_query->execute();
            $existing_result = $existing_query->get_result();
            if($existing_result && $existing_result->num_rows > 0){
                $message = 'Already enrolled/pending. Wait for approval.';
            }
            $existing_query->close();
        }

        if(empty($message) && $teacher_id > 0){
            // Check teacher capacity (fix `limit` reserved word)
            $teacher_cap_result = $conn->query("
                SELECT COALESCE(ta.max_students, t.max_students, 30) as `limit`
                FROM teachers t 
                LEFT JOIN teacher_approval ta ON ta.teacher_id = t.id AND ta.status = 'approved'
                WHERE t.id = {$teacher_id} AND t.status = 'active'
                LIMIT 1
            ");
            $teacher_cap = $teacher_cap_result ? $teacher_cap_result->fetch_assoc() : ['limit' => 30];
            $limit = (int)($teacher_cap['limit'] ?? 30);

            $approved_count_result = $conn->query("
                SELECT COUNT(*) as count 
                FROM enrollment_requests 
                WHERE teacher_id = {$teacher_id} AND status = 'approved'
            ");
            $approved_count = $approved_count_result ? (int)$approved_count_result->fetch_assoc()['count'] : 0;

            if($approved_count < $limit){
                $ins = $conn->prepare("
                    INSERT INTO enrollment_requests (student_id, teacher_id, status, date_requested) 
                    VALUES (?, ?, 'pending', NOW())
                ");
                if($ins){
                    $ins->bind_param("ii", $student_id, $teacher_id);
                    $ins->execute();
                    $ins->close();
                    $message = 'Enrollment request sent! Wait for teacher approval.';
                } else {
                    error_log("Prepare failed: ".$conn->error);
                    $message = 'Error sending enrollment request. Contact admin.';
                }
            } else {
                $message = 'Class full (max '.$limit.'). Try another teacher.';
            }
        } elseif(empty($message)){
            $message = 'Select a teacher.';
        }
    }

    if($message){
        $type = strpos($message, 'sent') !== false ? 'success' : 'error';
        set_flash_notice($message, $type);
    }

    header("Location: enrollment_request.php");
    exit();
}

include '../includes/layout_start.php';
?>

<h2>Enroll in Class</h2>

<?php if($is_graduate_or_inactive): ?>
    <div style="background:#fef3c7;padding:15px;border-radius:8px;color:#b45309;margin-bottom:20px;">
        You cannot enroll because you are either <strong>graduated</strong> or <strong>inactive</strong>.
    </div>
<?php endif; ?>

<form method="POST">
    <select name="teacher_id" required <?php echo $is_graduate_or_inactive ? 'disabled' : ''; ?>>
        <option value="">Select Teacher (Active)</option>
        <?php
        $teachers = $conn->query("
            SELECT t.id, u.name, t.advisory_class
            FROM teachers t 
            JOIN users u ON t.user_id = u.id 
            LEFT JOIN teacher_approval ta ON ta.teacher_id = t.id AND ta.status = 'approved'
            WHERE t.status = 'active' AND u.status = 'active' AND u.role = 'teacher'
            ORDER BY u.name
        ");
        while($t = $teachers->fetch_assoc()):
        ?>
        <option value="<?php echo (int)$t['id']; ?>">
            <?php echo htmlspecialchars($t['name']); ?> <?php echo $t['advisory_class'] ? '('.$t['advisory_class'].')' : ''; ?>
        </option>
        <?php endwhile; ?>
    </select>
    <button type="submit" name="send_request" 
        <?php echo $is_graduate_or_inactive ? 'disabled style="background:#9ca3af;cursor:not-allowed;"' : ''; ?>>
        <?php echo $is_graduate_or_inactive ? "Can't Enroll" : "Send Enrollment Request"; ?>
    </button>
</form>

<h3>Your Requests</h3>
<table border="1" style="border-collapse:collapse;width:100%;">
<tr style="background:#3b82f6;color:white;">
    <th style="padding:8px;">Teacher</th>
    <th style="padding:8px;">Status</th>
    <th style="padding:8px;">Date</th>
</tr>
<?php
$requests = $conn->query("
    SELECT u.name, er.status, er.date_requested
    FROM enrollment_requests er 
    JOIN teachers t ON er.teacher_id = t.id 
    JOIN users u ON t.user_id = u.id
    WHERE er.student_id = {$student_id} 
    ORDER BY er.date_requested DESC
");
if($requests && $requests->num_rows > 0):
    while($r = $requests->fetch_assoc()):
?>
<tr>
    <td style="padding:8px;"><?php echo htmlspecialchars($r['name']); ?></td>
    <td style="padding:8px;"><?php echo ucfirst($r['status']); ?></td>
    <td style="padding:8px;"><?php echo htmlspecialchars($r['date_requested']); ?></td>
</tr>
<?php 
    endwhile;
else: ?>
<tr>
    <td colspan="3" style="text-align:center;padding:15px;color:#6b7280;">
        No enrollment requests found.
    </td>
</tr>
<?php endif; ?>
</table>

<a href="dashboard.php" style="display:inline-block;margin-top:20px;padding:12px 24px;background:#6b7280;color:white;text-decoration:none;border-radius:8px;">
    ← Dashboard
</a>

<?php include '../includes/layout_end.php'; ?>