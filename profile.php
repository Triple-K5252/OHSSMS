<?php
session_start();
require_once 'config/db.php';
if ($_SESSION['role'] !== 'student') { header('Location: index.php'); exit; }

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM students WHERE user_id = ?");
$stmt->execute([$user_id]);
$student = $stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-table { width: 100%; margin-top: 20px; }
        .profile-table td { padding: 8px; }
        .profile-table td:first-child { font-weight: bold; color: #333; width: 180px; }
    </style>
</head>
<body>
<?php include 'navbar_student.php'; ?>
<div class="dashboard-box">
    <h2>My Profile</h2>
    <table class="profile-table">
        <tr><td>Admission No:</td><td><?= htmlspecialchars($student['admission_no']) ?></td></tr>
        <tr><td>Name:</td><td><?= htmlspecialchars($student['first_name'].' '.$student['middle_name'].' '.$student['last_name']) ?></td></tr>
        <tr><td>Form:</td><td><?= htmlspecialchars($student['form']) ?></td></tr>
        <tr><td>Stream:</td><td><?= htmlspecialchars($student['stream']) ?></td></tr>
        <tr><td>Date of Birth:</td><td><?= htmlspecialchars($student['dob']) ?></td></tr>
        <tr><td>Guardian Name:</td><td><?= htmlspecialchars($student['guardian_name']) ?></td></tr>
        <tr><td>Guardian Contact:</td><td><?= htmlspecialchars($student['guardian_contact']) ?></td></tr>
    </table>
</div>
</body>
</html>