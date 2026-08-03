<?php
session_start();
include '../includes/functions.php';
check_login();
check_role('admin');
include '../includes/db.php';
$page_title = 'Admin Dashboard';
include '../includes/layout_start.php';
?>

<h2>Welcome Admin, <?php echo $_SESSION['name']; ?></h2>



<?php include '../includes/layout_end.php'; ?>
