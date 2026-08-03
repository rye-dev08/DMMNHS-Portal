<?php
session_start();
include '../includes/functions.php';
check_login();
check_role('admin');
include '../includes/db.php';
$page_title = 'Approve Teachers';
$settings_result = $conn->query("SELECT max_students_per_class, max_subjects_per_teacher FROM settings WHERE id=1");
$settings = $settings_result ? $settings_result->fetch_assoc() : null;
$default_max_students = 30;
$default_max_subjects = 8;
if ($settings) {
    $default_max_students = (int)($settings['max_students_per_class'] ?? 30);
    $default_max_subjects = (int)($settings['max_subjects_per_teacher'] ?? 8);
}

// Handle approval
if(isset($_POST['approve'])){
    $user_id = (int)($_POST['user_id'] ?? 0);
    $max_students = (int)($_POST['max_students'] ?? 0);
    $max_subjects = (int)($_POST['max_subjects'] ?? 0);
    $advisory_class = trim($_POST['advisory_class'] ?? '');

    if($user_id > 0 && $max_students > 0 && $max_subjects > 0){
        $conn->begin_transaction();

        try{
            // Ensure teacher profile exists and store limits.
            $stmt = $conn->prepare("
                INSERT INTO teachers (user_id, advisory_class, max_students, max_subjects, status)
                VALUES (?, ?, ?, ?, 'active')
                ON DUPLICATE KEY UPDATE
                    advisory_class = VALUES(advisory_class),
                    max_students = VALUES(max_students),
                    max_subjects = VALUES(max_subjects),
                    status = 'active'
            ");
            $stmt->bind_param("isii", $user_id, $advisory_class, $max_students, $max_subjects);
            $stmt->execute();

            $teacher_row = $conn->query("SELECT id FROM teachers WHERE user_id={$user_id} LIMIT 1")->fetch_assoc();
            $teacher_id = (int)($teacher_row['id'] ?? 0);
            if($teacher_id <= 0){
                throw new Exception('Teacher profile not found.');
            }

            // Keep teacher approval table in sync with admin limits.
            $existing = $conn->query("SELECT id FROM teacher_approval WHERE teacher_id={$teacher_id} LIMIT 1")->fetch_assoc();
            if($existing){
                $approval_id = (int)$existing['id'];
                $stmtA = $conn->prepare("
                    UPDATE teacher_approval
                    SET max_students=?, max_subjects=?, status='approved'
                    WHERE id=?
                ");
                $stmtA->bind_param("iii", $max_students, $max_subjects, $approval_id);
                $stmtA->execute();
            }else{
                $stmtA = $conn->prepare("
                    INSERT INTO teacher_approval (teacher_id, max_students, max_subjects, status)
                    VALUES (?, ?, ?, 'approved')
                ");
                $stmtA->bind_param("iii", $teacher_id, $max_students, $max_subjects);
                $stmtA->execute();
            }

            // Activate teacher login after approval.
            $stmt2 = $conn->prepare("UPDATE users SET status='active' WHERE id=? AND role='teacher'");
            $stmt2->bind_param("i", $user_id);
            $stmt2->execute();

            $conn->commit();
            set_flash_notice('Teacher approved successfully.', 'success');
            header("Location: approve_teachers.php");
            exit();
        }catch(Throwable $e){
            $conn->rollback();
            set_flash_notice('Approval failed. Please try again.', 'error');
            header("Location: approve_teachers.php");
            exit();
        }
    }else{
        set_flash_notice('Please enter valid limits.', 'error');
        header("Location: approve_teachers.php");
        exit();
    }
}

// Fetch teachers waiting for approval (inactive teacher accounts).
$teachers_result = $conn->query("
    SELECT users.id AS user_id, users.name, COALESCE(teachers.advisory_class, '') AS advisory_class
    FROM users
    LEFT JOIN teachers ON teachers.user_id = users.id
    WHERE users.role = 'teacher'
      AND (users.status = 'inactive' OR teachers.id IS NULL)
    ORDER BY users.name
");

include '../includes/layout_start.php';
?>

<h2>Approve Teachers</h2>

<table border="1">
<tr>
    <th>Teacher</th>
    <th>Max Students</th>
    <th>Max Subjects Per Student</th>
    <th>Advisory Class</th>
    <th>Action</th>
</tr>

<?php if (!$teachers_result) { ?>
<tr><td colspan="5">No pending teachers or query failed: <?php echo $conn->error; ?></td></tr>
<?php } else { ?>
<?php while($t = $teachers_result->fetch_assoc()): ?>
<form method="POST">
<tr>
    <td><?php echo htmlspecialchars($t['name']); ?></td>
    <td><input type="number" name="max_students" min="1" value="<?php echo $default_max_students; ?>" required></td>
    <td><input type="number" name="max_subjects" min="1" value="<?php echo $default_max_subjects; ?>" required></td>
    <td><input type="text" name="advisory_class" value="<?php echo htmlspecialchars($t['advisory_class']); ?>" placeholder="e.g. 7-A"></td>
    <td>
        <input type="hidden" name="user_id" value="<?php echo (int)$t['user_id']; ?>">
        <button type="submit" name="approve">Approve</button>
    </td>
</tr>
</form>
<?php endwhile; ?>
<?php } ?>
</table>

<br>
<a class="btn-link" href="dashboard.php">Back to Dashboard</a>
<?php include '../includes/layout_end.php'; ?>
