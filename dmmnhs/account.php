<?php
include 'includes/db.php';

$name = 'admins';
$username = 'admin3';
$email = 'admintest2@gmail.com';
$plain_pass = 'Admin3!'; // plain password
$role = 'admin';
$status = 'active';

$hash = password_hash($plain_pass, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (name, username, email, password_hash, role, status) VALUES (?,?,?,?,?,?)");
$stmt->bind_param("ssssss", $name, $username, $email, $hash, $role, $status);
$stmt->execute();

echo "Admin account created successfully!";
?>      