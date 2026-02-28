<?php
session_start();
require_once 'config/db.php';
if ($_SESSION['role'] !== 'student') { header('Location: index.php'); exit; }

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT student_id FROM students WHERE user_id = ?");
$stmt->execute([$user_id]);
$student_id = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT date, status FROM attendance WHERE student_id = ? ORDER BY date DESC");
$stmt->execute([$student_id]);
$attendance = $stmt->fetchAll();

$summary = ['present'=>0, 'absent'=>0, 'late'=>0, 'excused'=>0];
foreach ($attendance as $a) {
    $summary[$a['status']] = isset($summary[$a['status']]) ? $summary[$a['status']] + 1 : 1;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Attendance</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .summary-table, .attendance-table { width: 100%; margin-top: 20px; }
        .summary-table th, .attendance-table th { background: #f0f6fa; }
        .summary-table td, .attendance-table td { padding: 8px; text-align: center; }
    </style>
</head>
<body>
<?php include 'navbar_student.php'; ?>
<div class="dashboard-box">
    <h2>Attendance</h2>
    <h3>Summary</h3>
    <table class="summary-table" border="1">
        <tr>
            <th>Present</th><th>Absent</th><th>Late</th><th>Excused</th>
        </tr>
        <tr>
            <td><?= $summary['present'] ?></td>
            <td><?= $summary['absent'] ?></td>
            <td><?= $summary['late'] ?></td>
            <td><?= $summary['excused'] ?></td>
        </tr>
    </table>
    <h3>Detailed Records</h3>
    <table class="attendance-table" border="1">
        <tr><th>Date</th><th>Status</th></tr>
        <?php foreach ($attendance as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['date']) ?></td>
                <td><?= ucfirst(htmlspecialchars($a['status'])) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>
