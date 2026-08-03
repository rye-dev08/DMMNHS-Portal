<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

// Handle login via PRG pattern and preserve username on failure
if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Persist username for next GET render
    $_SESSION['login_username'] = $username;

    $stmt = $conn->prepare("SELECT id, name, role, status, password_hash FROM users WHERE username=? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = false;
    }

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $hash = (string)($user['password_hash'] ?? '');
        $verified = false;

        if ($hash !== '') {
            $info = password_get_info($hash);
            if (!empty($info['algo'])) {
                // Proper hash present
                $verified = password_verify($password, $hash);
                if ($verified && password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $conn->prepare("UPDATE users SET password_hash=? WHERE id=?");
                    if ($upd) { $upd->bind_param("si", $newHash, $user['id']); $upd->execute(); }
                }
            } else {
                // Fallback: plaintext stored in DB; verify and auto-upgrade
                if (hash_equals($hash, $password)) {
                    $verified = true;
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $conn->prepare("UPDATE users SET password_hash=? WHERE id=?");
                    if ($upd) { $upd->bind_param("si", $newHash, $user['id']); $upd->execute(); }
                }
            }
        }

        if ($verified && (($user['status'] ?? 'inactive') === 'active')) {
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            unset($_SESSION['login_username']);

            if ($user['role'] === 'admin') { header("Location: admin/dashboard.php"); exit(); }
            if ($user['role'] === 'teacher') { header("Location: teacher/dashboard.php"); exit(); }
            header("Location: student/dashboard.php"); exit();
        } else {
            set_flash_notice('Incorrect password', 'error');
            header("Location: index.php");
            exit();
        }
    } else {
        set_flash_notice('User not found', 'error');
        header("Location: index.php");
        exit();
    }
}

// Prefill username after redirect
$prefill_username = '';
if (isset($_SESSION['login_username'])) {
    $prefill_username = (string)$_SESSION['login_username'];
    unset($_SESSION['login_username']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/cs/style.css">
    <script src="assets/cs/js/main.js"></script>
</head>
<body>
<div class="login-box">
    <h2>Login</h2>
    <div id="alert-host" class="alert-host"></div>
    <?php if(function_exists('render_flash_notice')){ render_flash_notice(); } ?>
    <form method="POST" class="auth-form">
        <div class="field-row">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Username" autocomplete="username" required value="<?php echo htmlspecialchars($prefill_username); ?>">
        </div>
        <div class="field-row">
            <label for="password-input">Password</label>
            <input type="password" id="password-input" name="password" placeholder="Password" autocomplete="current-password" required<?php echo $prefill_username !== '' ? ' autofocus' : ''; ?>>
        </div>
        <div class="inline-actions">
            <label class="subtle"><input type="checkbox" id="toggle-password"> Show password</label>
        </div>
        <button type="submit" name="login">Login</button>
    </form>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        var cb = document.getElementById('toggle-password');
        var pw = document.getElementById('password-input');
        if(cb && pw){
            cb.addEventListener('change', function(){
                pw.type = cb.checked ? 'text' : 'password';
            });
        }
    });
    </script>
</div>
</body>
</html>