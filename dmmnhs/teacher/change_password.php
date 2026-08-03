<?php
session_start();
include '../includes/functions.php';
check_login();
check_role('teacher');
include '../includes/db.php';
$page_title = 'Teacher Change Password';
$suppress_flash_at_top = true;

if(isset($_POST['submit'])){
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id=?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if(!$user || !password_verify($old, $user['password_hash'])){
        set_flash_notice('Old password incorrect', 'error');
    } elseif($new !== $confirm){
        set_flash_notice('New passwords do not match', 'error');
    } elseif(!validate_password($new)){
        set_flash_notice('Password must be at least 8 chars and include uppercase or symbol', 'error');
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $up = $conn->prepare("UPDATE users SET password_hash=? WHERE id=?");
        $up->bind_param("si", $hash, $_SESSION['user_id']);
        $up->execute();
        set_flash_notice('Password updated successfully', 'success');
    }
    header("Location: change_password.php");
    exit();
}
include '../includes/layout_start.php';
?>

<h2>Change Password</h2>

<form method="POST" class="stack-form auth-form">
    <div class="field-row">
        <label for="old_password">Old Password</label>
        <input type="password" id="old_password" name="old_password" required>
    </div>
    <div class="field-row">
        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" required>
        <p class="subtle">At least 8 chars, with uppercase or symbol.</p>
    </div>
    <div class="field-row">
        <label for="confirm_password">Confirm New Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
    </div>
    <label class="subtle"><input type="checkbox" id="show-pass-teacher"> <span>Show passwords</span></label>
    <div class="inline-actions">
        <button type="submit" name="submit">Change Password</button>
        <a class="btn-link" href="dashboard.php">Back to Dashboard</a>
    </div>
</form>
<?php render_flash_notice(); ?>
<script>
document.getElementById('show-pass-teacher').addEventListener('change', function(){
    var type = this.checked ? 'text' : 'password';
    document.getElementById('old_password').type = type;
    document.getElementById('new_password').type = type;
    document.getElementById('confirm_password').type = type;
});
</script>
<?php include '../includes/layout_end.php'; ?>
