    <?php
$page_title = $page_title ?? 'School Portal';
$page_class = $page_class ?? '';
$suppress_flash_at_top = $suppress_flash_at_top ?? false;
$script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$asset_prefix = preg_match('#/(admin|teacher|student)$#', $script) ? '../' : '';

$role = $_SESSION['role'] ?? '';
$in_role_dir = preg_match('#/(admin|teacher|student)$#', $script) === 1;
$role_prefix = $in_role_dir ? '' : ($role !== '' ? $role.'/' : '');
$logout_url = $in_role_dir ? '../logout.php' : 'logout.php';

$home_url = 'index.php';
$menu_links = [];

if($role === 'admin'){
    $home_url = $role_prefix.'dashboard.php';
    $menu_links = [
        ['label' => 'Dashboard', 'href' => $role_prefix.'dashboard.php'],
        ['label' => 'Manage Accounts', 'href' => $role_prefix.'accounts.php'],
        ['label' => 'Teachers Approval', 'href' => $role_prefix.'approve_teachers.php'],
        ['label' => 'Enrollment Settings', 'href' => $role_prefix.'enrollment_settings.php'],
        ['label' => 'Change Password', 'href' => $role_prefix.'change_password.php'],
        ['label' => 'Logout', 'href' => $logout_url]
    ];
}elseif($role === 'teacher'){
    $home_url = $role_prefix.'dashboard.php';
    $menu_links = [
        ['label' => 'Dashboard', 'href' => $role_prefix.'dashboard.php'],
        ['label' => 'Advisory Portal', 'href' => $role_prefix.'advisory_portal.php'],
        ['label' => 'Enrollment Requests', 'href' => $role_prefix.'enrollment_requests.php'],
        ['label' => 'Submit Grades', 'href' => $role_prefix.'submit_grades.php'],
        ['label' => 'Grades Overview', 'href' => $role_prefix.'grades_overview.php'],

        ['label' => 'Teacher Info', 'href' => $role_prefix.'info.php'],
        ['label' => 'Change Password', 'href' => $role_prefix.'change_password.php'],
        ['label' => 'Logout', 'href' => $logout_url]
    ];
}elseif($role === 'student'){
    $home_url = $role_prefix.'dashboard.php';
    $menu_links = [
        ['label' => 'Dashboard', 'href' => $role_prefix.'dashboard.php'],
        ['label' => 'Student Info', 'href' => $role_prefix.'student_info.php'],
        ['label' => 'Class Schedule', 'href' => $role_prefix.'class_schedule.php'],
        ['label' => 'Grades', 'href' => $role_prefix.'grades.php'],
        ['label' => 'Enrollment Request', 'href' => $role_prefix.'enrollment_request.php'],
        ['label' => 'Change Password', 'href' => $role_prefix.'change_password.php'],
        ['label' => 'About Us', 'href' => $asset_prefix.'about.php'],
        ['label' => 'Contact Us', 'href' => $asset_prefix.'contact.php'],
        ['label' => 'Logout', 'href' => $logout_url]
    ];
}else{
    $home_url = 'index.php';
    $menu_links = [
        ['label' => 'Home', 'href' => 'index.php'],
        ['label' => 'About Us', 'href' => 'about.php'],
        ['label' => 'Contact Us', 'href' => 'contact.php']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="icon" href="<?php echo $asset_prefix; ?>assets/cs/dmnhs-no-bg.jpg" type="image/jpeg">
    <link rel="apple-touch-icon" href="<?php echo $asset_prefix; ?>assets/cs/dmnhs-no-bg.jpg">
    <link rel="stylesheet" href="<?php echo $asset_prefix; ?>assets/cs/style.css?v=20260310c">
    <script src="<?php echo $asset_prefix; ?>assets/cs/js/main.js?v=20260309e"></script>
</head>
<body>
<header class="site-header">
    <div class="header-left">
        <div class="header-actions">
            <a class="home-icon-link" href="<?php echo htmlspecialchars($home_url); ?>" title="Home / Dashboard">&#8962;</a>
            <button class="menu-toggle" id="menu-toggle" type="button" aria-label="Open Menu">&#9776;</button>
        </div>
        <div class="brand-wrap">
            <img src="<?php echo $asset_prefix; ?>assets/cs/dmnhs-no-bg.jpg" alt="School Logo" class="brand-logo">
            <div class="brand-text">
                <h1>Don Mariano Marcos National High School Portal</h1>
                <p>Student Information and Grade Management System</p>
            </div>
        </div>
    </div>
    <div class="header-nav-panel" id="header-nav-panel">
        <?php foreach($menu_links as $lnk): ?>
            <a href="<?php echo htmlspecialchars($lnk['href']); ?>"><?php echo htmlspecialchars($lnk['label']); ?></a>
        <?php endforeach; ?>
    </div>
</header>
<main class="page-shell<?php echo $page_class !== '' ? ' '.htmlspecialchars($page_class) : ''; ?>">
<div id="alert-host" class="alert-host"></div>
<?php if(!$suppress_flash_at_top && function_exists('render_flash_notice')){ render_flash_notice(); } ?>
