<?php

// Start session safely
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function app_login_url(){
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if(preg_match('#^(.*?)/(admin|teacher|student)(/|$)#', $script, $m)){
        return $m[1].'/index.php';
    }
    return rtrim(dirname($script), '/').'/index.php';
}

function load_active_user($user_id, $expectedRole = null){
    if(!$user_id){
        return false;
    }

    $dbFile = __DIR__.'/db.php';
    if(!file_exists($dbFile)){
        return false;
    }

    include $dbFile;
    if(!isset($conn)){
        return false;
    }

    $user_id = (int)$user_id;
    $sql = "SELECT id, name, role, status FROM users WHERE id=? ";
    if($expectedRole !== null){
        $sql .= "AND role=? ";
    }
    $sql .= "LIMIT 1";
    $stmt = $conn->prepare($sql);
    if(!$stmt){
        return false;
    }
    if($expectedRole !== null){
        $stmt->bind_param("is", $user_id, $expectedRole);
    }else{
        $stmt->bind_param("i", $user_id);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    if(!$res || $res->num_rows !== 1){
        return false;
    }

    $user = $res->fetch_assoc();
    if(($user['status'] ?? 'inactive') !== 'active'){
        return false;
    }

    return $user;
}

function set_session_context($user){
    $_SESSION['role'] = $user['role'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['user_id'] = (int)$user['id'];

    if(!isset($_SESSION['auth']) || !is_array($_SESSION['auth'])){
        $_SESSION['auth'] = [];
    }

    $_SESSION['auth'][$user['role']] = [
        'user_id' => (int)$user['id'],
        'name' => $user['name'],
        'ts' => time()
    ];
}

function restore_role_session($role){
    // 1) Current context already on this role.
    if(isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === $role){
        $user = load_active_user((int)$_SESSION['user_id'], $role);
        if($user){
            set_session_context($user);
            return true;
        }
    }

    // 2) Restore from role-scoped auth cache for multi-tab multi-role usage.
    if(isset($_SESSION['auth']) && is_array($_SESSION['auth']) && isset($_SESSION['auth'][$role]['user_id'])){
        $user = load_active_user((int)$_SESSION['auth'][$role]['user_id'], $role);
        if($user){
            set_session_context($user);
            return true;
        }
    }

    return false;
}

// Check login
function check_login(){
    // Allow request to proceed if at least one role session exists.
    if(!isset($_SESSION['user_id'])){
        $hasRoleSession = isset($_SESSION['auth']) && is_array($_SESSION['auth']) && count($_SESSION['auth']) > 0;
        if(!$hasRoleSession){
            header("Location: ".app_login_url());
            exit();
        }
    }else{
        // Validate current user if present.
        $user = load_active_user((int)$_SESSION['user_id']);
        if(!$user){
            unset($_SESSION['user_id'], $_SESSION['role'], $_SESSION['name']);
            $hasRoleSession = isset($_SESSION['auth']) && is_array($_SESSION['auth']) && count($_SESSION['auth']) > 0;
            if(!$hasRoleSession){
                header("Location: ".app_login_url());
                exit();
            }
        }else{
            set_session_context($user);
        }
    }
}

// Check role
function check_role($role){
    if(!restore_role_session($role)){
        header("Location: ".app_login_url());
        exit();
    }
}

// Password validator
function validate_password($password){

    if(strlen($password) < 8){
        return false;
    }

    $hasUpper = preg_match('/[A-Z]/', $password) === 1;
    $hasSymbol = preg_match('/[^A-Za-z0-9]/', $password) === 1;
    if(!$hasUpper && !$hasSymbol){
        return false;
    }

    return true;
}

// Resolve current term label based on settings quarter range.
function get_current_term_label($settings){
    $today = date('Y-m-d');
    if(
        !empty($settings['current_quarter_start']) &&
        !empty($settings['current_quarter_end']) &&
        $today >= $settings['current_quarter_start'] &&
        $today <= $settings['current_quarter_end']
    ){
        return 'current';
    }
    return 'previous';
}

// Standardized grade display and color rules.
function map_grade_display($rawGrade){
    if($rawGrade === null || $rawGrade === '' || strtoupper((string)$rawGrade) === 'N/A'){
        return ['label' => 'N/A', 'color' => '#9ca3af'];
    }

    if(is_numeric($rawGrade)){
        $grade = (int)$rawGrade;
        if($grade >= 83){
            return ['label' => (string)$grade, 'color' => '#22c55e']; // green
        }
        if($grade >= 75){
            return ['label' => (string)$grade, 'color' => '#f97316']; // orange
        }
        if($grade >= 1){
            return ['label' => 'INC', 'color' => '#eab308']; // yellow
        }
        return ['label' => 'DROPPED', 'color' => '#ef4444']; // red
    }

    $text = strtoupper(trim((string)$rawGrade));
    if($text === 'DROPPED'){
        return ['label' => 'DROPPED', 'color' => '#ef4444'];
    }
    if($text === 'INC'){
        return ['label' => 'INC', 'color' => '#eab308'];
    }

    return ['label' => $text, 'color' => '#9ca3af'];
}

function set_flash_notice($message, $type = 'info'){
    $_SESSION['flash_notice'] = [
        'message' => (string)$message,
        'type' => (string)$type
    ];
}

function render_flash_notice(){
    if(empty($_SESSION['flash_notice']) || !is_array($_SESSION['flash_notice'])){
        return;
    }
    $message = $_SESSION['flash_notice']['message'] ?? '';
    $type = $_SESSION['flash_notice']['type'] ?? 'info';
    unset($_SESSION['flash_notice']);
    $m = json_encode($message);
    $t = json_encode($type);
    echo "<script>(function(){var run=function(){if(typeof showNotice==='function'){showNotice($m,$t);return true;}return false;};if(!run()){document.addEventListener('DOMContentLoaded',run);setTimeout(run,200);}})();</script>";
}

function ensure_assessment_scores_table($conn){
    if(!$conn){
        return false;
    }

    $sql = "
        CREATE TABLE IF NOT EXISTS assessment_scores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            teacher_id INT NOT NULL,
            student_id INT NOT NULL,
            score_type ENUM('activity','quiz','exam') NOT NULL,
            item_no INT NOT NULL,
            score DECIMAL(10,2) NOT NULL DEFAULT 0,
            max_score DECIMAL(10,2) NOT NULL DEFAULT 100,
            remarks VARCHAR(255) DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_teacher_student_type_item (teacher_id, student_id, score_type, item_no),
            INDEX idx_student (student_id),
            INDEX idx_teacher (teacher_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";

    return (bool)$conn->query($sql);
}

?>
