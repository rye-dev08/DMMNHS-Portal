<?php
session_start();
include '../includes/functions.php';
check_login();
check_role('teacher');
include '../includes/db.php';
$page_title = 'Teacher Info';
$page_class = 'page-narrow';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT
        u.name,
        u.username,
        u.email,
        u.status,
        t.advisory_class,
        t.max_students,
        t.max_subjects
    FROM users u
    LEFT JOIN teachers t ON t.user_id = u.id
    WHERE u.id = ?
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
include '../includes/layout_start.php';
?>

<h2 style="text-align: center;">Teacher Info</h2>
<section class="profile-card">
    <div class="profile-grid">
        <div class="profile-item">
            <span class="profile-label">Full Name</span>
            <span class="profile-value"><?php echo htmlspecialchars($user['name'] ?? 'N/A'); ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Username</span>
            <span class="profile-value"><?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Email</span>
            <span class="profile-value"><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Account Status</span>
            <span class="profile-value"><?php echo htmlspecialchars($user['status'] ?? 'N/A'); ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Advisory Class</span>
            <span class="profile-value"><?php echo htmlspecialchars($user['advisory_class'] ?: 'Not set'); ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Max Students</span>
            <span class="profile-value"><?php echo (int)($user['max_students'] ?? 0) > 0 ? (int)$user['max_students'] : 'Not set'; ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Max Subjects Per Student</span>
            <span class="profile-value"><?php echo (int)($user['max_subjects'] ?? 0) > 0 ? (int)$user['max_subjects'] : 'Not set'; ?></span>
        </div>
    </div>
</section>
<a class="btn-link" href="dashboard.php">Back to Dashboard</a>
<?php include '../includes/layout_end.php'; ?>
