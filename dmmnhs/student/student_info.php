<?php
session_start();

include '../includes/functions.php';
check_login();
check_role('student');
include '../includes/db.php';
$page_title = 'Student Info';
$page_class = 'page-narrow';

$user_id = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT s.*, u.name, u.username, u.email, u.status
    FROM students s
    JOIN users u ON s.user_id = u.id
    WHERE u.id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student_result = $stmt->get_result();

if($student_result && $student_result->num_rows > 0){
    $student = $student_result->fetch_assoc();
    $has_profile = true;
}else{
    $has_profile = false;
    $student = [
        'name' => $_SESSION['name'] ?? 'N/A',
        'sex' => 'N/A',
        'birthday' => 'N/A',
        'age' => 'N/A',
        'username' => 'N/A',
        'email' => 'N/A',
        'status' => 'N/A'
    ];
}

$advisory = [
    'teacher_name' => 'N/A',
    'advisory_class' => 'N/A',
    'max_subjects' => 'N/A'
];
$adv_stmt = $conn->prepare("
    SELECT tu.name AS teacher_name, t.advisory_class, t.max_subjects
    FROM enrollment_requests er
    JOIN students s ON s.id = er.student_id
    JOIN teachers t ON t.id = er.teacher_id
    JOIN users tu ON tu.id = t.user_id
    WHERE s.user_id = ? AND er.status = 'approved'
    ORDER BY er.id DESC
    LIMIT 1
");
$adv_stmt->bind_param("i", $user_id);
$adv_stmt->execute();
$adv_result = $adv_stmt->get_result();
if($adv_result && $adv_result->num_rows > 0){
    $advisory = $adv_result->fetch_assoc();
    if($advisory['advisory_class'] === '' || $advisory['advisory_class'] === null){
        $advisory['advisory_class'] = 'Not set';
    }
    if((int)$advisory['max_subjects'] <= 0){
        $advisory['max_subjects'] = 'Not set';
    }
}
include '../includes/layout_start.php';
?>

<h2 style="text-align: center;">Student Info</h2>

<?php if(!$has_profile): ?>
<p style="color:red;">Your student profile is not yet complete. Contact admin to complete your personal info.</p>
<?php endif; ?>

<section class="profile-card">
    <div class="profile-grid">
        <div class="profile-item">
            <span class="profile-label">Full Name</span>
            <span class="profile-value"><?php echo htmlspecialchars($student['name']); ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Username</span>
            <span class="profile-value"><?php echo htmlspecialchars($student['username']); ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Email</span>
            <span class="profile-value"><?php echo htmlspecialchars($student['email']); ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Account Status</span>
            <span class="profile-value"><?php echo htmlspecialchars($student['status']); ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Sex</span>
            <span class="profile-value"><?php echo htmlspecialchars($student['sex'] ?? 'N/A'); ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Birthday</span>
            <span class="profile-value"><?php echo htmlspecialchars($student['birthday'] ?? 'N/A'); ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Age</span>
            <span class="profile-value"><?php echo htmlspecialchars((string)($student['age'] ?? 'N/A')); ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Grade Level</span>
            <span class="profile-value"><?php echo htmlspecialchars((string)($student['grade_level'] ?? 'N/A')); ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Adviser</span>
            <span class="profile-value"><?php echo htmlspecialchars($advisory['teacher_name'] ?? 'N/A'); ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Advisory Class</span>
            <span class="profile-value"><?php echo htmlspecialchars($advisory['advisory_class'] ?? 'N/A'); ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Max Subjects Allowed</span>
            <span class="profile-value"><?php echo htmlspecialchars((string)($advisory['max_subjects'] ?? 'N/A')); ?></span>
        </div>
    </div>
</section>

<a class="btn-link" href="dashboard.php">Back to Dashboard</a>

<?php include '../includes/layout_end.php'; ?>
