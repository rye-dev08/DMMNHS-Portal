<?php
session_start();
include '../includes/functions.php';
check_login();
check_role('admin');
include '../includes/db.php';
ensure_assessment_scores_table($conn);
$page_title = 'Manage Accounts';

// Create user
if(isset($_POST['create'])){

    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $plain_password = $_POST['password'] ?? '';
    if(!validate_password($plain_password)){
        set_flash_notice('Password must be at least 8 chars and include uppercase or symbol.', 'error');
        header("Location: accounts.php");
        exit();
    }
    $password = password_hash($plain_password, PASSWORD_DEFAULT);

    $sex = $_POST['sex'] ?? null;
    $birthday = $_POST['birthday'] ?? null;
    $age = $_POST['age'] ?? null;
    $grade_level = $_POST['grade_level'] ?? null;
    $status = ($role === 'teacher') ? 'inactive' : 'active';

    if($role === 'student'){
        if($sex !== 'M' && $sex !== 'F'){
            $sex = null;
        }
        if($birthday === ''){
            $birthday = null;
        }
        if($age === '' || (int)$age <= 0){
            $age = null;
        }else{
            $age = (int)$age;
        }
        if($grade_level === '' || (int)$grade_level <= 0){
            $grade_level = null;
        }else{
            $grade_level = (int)$grade_level;
        }
    }

    // insert user
    $stmt = $conn->prepare("INSERT INTO users (name, username, email, password_hash, role, status) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("ssssss",$name,$username,$email,$password,$role,$status);
    if(!$stmt->execute()){
        set_flash_notice('Error creating user: '.$conn->error, 'error');
        header("Location: accounts.php");
        exit();
    }else{

        $user_id = $stmt->insert_id;

        // if student, create student profile
        if($role == "student"){

            $student_status = "active";
            $stmt2 = $conn->prepare("INSERT INTO students (user_id,sex,birthday,age,grade_level,status) VALUES (?,?,?,?,?,?)");
            $stmt2->bind_param("issiis",$user_id,$sex,$birthday,$age,$grade_level,$student_status);
            $stmt2->execute();
        }

        // if teacher, create profile record for approval limits
        if($role == "teacher"){
            $teacher_status = "inactive";
            $stmt3 = $conn->prepare("INSERT INTO teachers (user_id, max_subjects, max_students, status) VALUES (?,?,?,?)");
            $max_subjects = 0;
            $max_students = 0;
            $stmt3->bind_param("iiis",$user_id,$max_subjects,$max_students,$teacher_status);
            $stmt3->execute();
        }

        set_flash_notice('User created', 'success');
        header("Location: accounts.php");
        exit();
    }
}


// Delete user
if(isset($_GET['delete'])){

    $id = (int)$_GET['delete'];

    $conn->begin_transaction();
    try{
        // Resolve linked profile IDs.
        $studentRow = $conn->query("SELECT id FROM students WHERE user_id={$id} LIMIT 1")->fetch_assoc();
        $teacherRow = $conn->query("SELECT id FROM teachers WHERE user_id={$id} LIMIT 1")->fetch_assoc();
        $student_id = (int)($studentRow['id'] ?? 0);
        $teacher_id = (int)($teacherRow['id'] ?? 0);

        if($student_id > 0){
            $conn->query("DELETE FROM grades WHERE student_id={$student_id}");
            $conn->query("DELETE FROM subjects WHERE student_id={$student_id}");
            $conn->query("DELETE FROM enrollment_requests WHERE student_id={$student_id}");
            $conn->query("DELETE FROM assessment_scores WHERE student_id={$student_id}");
        }

        if($teacher_id > 0){
            $conn->query("DELETE FROM grades WHERE subject_id IN (SELECT id FROM subjects WHERE teacher_id={$teacher_id})");
            $conn->query("DELETE FROM subjects WHERE teacher_id={$teacher_id}");
            $conn->query("DELETE FROM enrollment_requests WHERE teacher_id={$teacher_id}");
            $conn->query("DELETE FROM teacher_approval WHERE teacher_id={$teacher_id}");
            $conn->query("DELETE FROM assessment_scores WHERE teacher_id={$teacher_id}");
        }

        // delete student profile if exists
        $stmt = $conn->prepare("DELETE FROM students WHERE user_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // delete teacher profile if exists
        $stmt2 = $conn->prepare("DELETE FROM teachers WHERE user_id=?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();

        // delete user
        $stmt3 = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt3->bind_param("i", $id);
        $stmt3->execute();

        $conn->commit();
    }catch(Throwable $e){
        $conn->rollback();
    }

    set_flash_notice('User deleted', 'success');
    header("Location: accounts.php");
    exit();
}


// Fetch grouped users for organized display.
$admins = $conn->query("
    SELECT id, name, username, email, status
    FROM users
    WHERE role='admin'
    ORDER BY name
");

$students = $conn->query("
    SELECT
        u.id,
        u.name,
        u.username,
        u.email,
        u.status,
        s.grade_level,
        MAX(t.advisory_class) AS section
    FROM users u
    LEFT JOIN students s ON s.user_id=u.id
    LEFT JOIN enrollment_requests er ON er.student_id=s.id AND er.status='approved'
    LEFT JOIN teachers t ON t.id=er.teacher_id
    WHERE u.role='student'
    GROUP BY u.id, u.name, u.username, u.email, u.status, s.grade_level
    ORDER BY s.grade_level ASC, section ASC, u.name ASC
");

$teachers = $conn->query("
    SELECT
        u.id,
        u.name,
        u.username,
        u.email,
        u.status,
        t.advisory_class
    FROM users u
    LEFT JOIN teachers t ON t.user_id=u.id
    WHERE u.role='teacher'
    ORDER BY t.advisory_class ASC, u.name ASC
");

include '../includes/layout_start.php';
?>

<h2>Accounts</h2>

<form method="POST">

<div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: end;">
    <input type="text" name="name" placeholder="Name" required style="flex: 1; min-width: 120px;">
    <input type="text" name="username" placeholder="Username" required style="flex: 1; min-width: 120px;">
    <input type="email" name="email" placeholder="Email" required style="flex: 1; min-width: 150px;">
    <select name="role" required style="flex: 1; min-width: 100px;">
        <option value="teacher">Teacher</option>
        <option value="student">Student</option>
    </select>
    <input type="password" id="password" name="password" placeholder="Password" required style="flex: 1; min-width: 120px;">
    <label class="show-password-toggle" style="white-space: nowrap; margin: 0; cursor: pointer; font-size: 13px; display: flex; align-items: center; padding: 8px 12px; background: #f0f0f0; border: 1px solid #ddd; border-radius: 6px; transition: all 0.2s; font-weight: 500;">
        <input type="checkbox" id="show-create-pass" style="margin-right: 6px; accent-color: #007bff; width: 16px; height: 16px;"> Show Password
    </label>
</div>
<p class="subtle">Password rule: at least 8 chars, with uppercase or symbol.</p>

    <h4>Student Info (only for students)</h4>

    <select name="sex">
        <option value="">Sex</option>
        <option value="M">Male</option>
        <option value="F">Female</option>
    </select>

    <input type="date" name="birthday">

    <input type="number" name="age" placeholder="Age">
    <input type="number" name="grade_level" placeholder="Grade Level">

    <button type="submit" name="create">Create</button>

</form>


<h3>Existing Users</h3>

<h4>Admins</h4>
<table border="1">
<tr>
    <th>Name</th>
    <th>Username</th>
    <th>Email</th>
    <th>Status</th>
    <th>Action</th>
</tr>
<?php while($u = $admins->fetch_assoc()){ ?>
<tr>
    <td><?php echo htmlspecialchars($u['name']); ?></td>
    <td><?php echo htmlspecialchars($u['username']); ?></td>
    <td><?php echo htmlspecialchars($u['email']); ?></td>
    <td><?php echo htmlspecialchars($u['status']); ?></td>
    <td><a href="?delete=<?php echo (int)$u['id']; ?>" class="btn-delete" onclick="return confirm('Delete user?')">Delete</a></td>
</tr>
<?php } ?>
</table>

<h4>Students (By Year and Section)</h4>
<table border="1">
<tr>
    <th>Name</th>
    <th>Username</th>
    <th>Email</th>
    <th>Year</th>
    <th>Section</th>
    <th>Status</th>
    <th>Action</th>
</tr>
<?php while($u = $students->fetch_assoc()){ ?>
<tr>
    <td><?php echo htmlspecialchars($u['name']); ?></td>
    <td><?php echo htmlspecialchars($u['username']); ?></td>
    <td><?php echo htmlspecialchars($u['email']); ?></td>
    <td><?php echo $u['grade_level'] !== null ? (int)$u['grade_level'] : 'N/A'; ?></td>
    <td><?php echo !empty($u['section']) ? htmlspecialchars($u['section']) : 'Unassigned'; ?></td>
    <td><?php echo htmlspecialchars($u['status']); ?></td>
    <td><a href="?delete=<?php echo (int)$u['id']; ?>" class="btn-delete" onclick="return confirm('Delete user?')">Delete</a></td>
</tr>
<?php } ?>
</table>

<h4>Teachers (By Year Advisory)</h4>
<table border="1">
<tr>
    <th>Name</th>
    <th>Username</th>
    <th>Email</th>
    <th>Advisory</th>
    <th>Status</th>
    <th>Action</th>
</tr>
<?php while($u = $teachers->fetch_assoc()){ ?>
<tr>
    <td><?php echo htmlspecialchars($u['name']); ?></td>
    <td><?php echo htmlspecialchars($u['username']); ?></td>
    <td><?php echo htmlspecialchars($u['email']); ?></td>
    <td><?php echo !empty($u['advisory_class']) ? htmlspecialchars($u['advisory_class']) : 'Not set'; ?></td>
    <td><?php echo htmlspecialchars($u['status']); ?></td>
    <td><a href="?delete=<?php echo (int)$u['id']; ?>" class="btn-delete" onclick="return confirm('Delete user?')">Delete</a></td>
</tr>
<?php } ?>
</table>

<a class="btn-link" href="dashboard.php">Back to Dashboard</a>
<script>
document.getElementById('show-create-pass').addEventListener('change', function(){
    document.getElementById('password').type = this.checked ? 'text' : 'password';
    this.parentElement.style.background = this.checked ? '#e3f2fd' : '#f0f0f0';
    this.parentElement.style.borderColor = this.checked ? '#007bff' : '#ddd';
});
</script>
<?php include '../includes/layout_end.php'; ?>
