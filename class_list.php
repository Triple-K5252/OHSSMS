<?php
session_start();
require_once 'config/db.php';
if ($_SESSION['role'] !== 'teacher') { header('Location: index.php'); exit; }

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT staff_id FROM staff WHERE user_id = ?");
$stmt->execute([$user_id]);
$staff_id = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT DISTINCT form, stream
    FROM teacher_classes
    WHERE staff_id = ?
    ORDER BY form, stream
");
$stmt->execute([$staff_id]);
$classes = $stmt->fetchAll();

$selected_class = isset($_POST['class']) ? $_POST['class'] : '';
$students = [];
if ($selected_class) {
    list($form, $stream) = explode('|', $selected_class);
    $stmt = $pdo->prepare("SELECT * FROM students WHERE form = ? AND stream = ?");
    $stmt->execute([$form, $stream]);
    $students = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Class List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
<div class="form-container">
    <h2>Class List</h2>
    <form method="POST">
        <label>Select Class:</label>
        <select name="class" onchange="this.form.submit()" required>
            <option value="">--Select--</option>
            <?php foreach ($classes as $c):
                $val = $c['form'].'|'.$c['stream'];
                $sel = ($selected_class == $val) ? 'selected' : '';
                echo "<option value='$val' $sel>Form {$c['form']} - {$c['stream']}</option>";
            endforeach; ?>
        </select>
    </form>
    <?php if ($students): ?>
        <h3>Students in Class</h3>
        <table border="1" cellpadding="6">
            <tr><th>Admission No</th><th>Name</th><th>Form</th><th>Stream</th></tr>
            <?php foreach ($students as $stu): ?>
                <tr>
                    <td><?= htmlspecialchars($stu['admission_no']) ?></td>
                    <td><?= htmlspecialchars($stu['first_name'].' '.$stu['last_name']) ?></td>
                    <td><?= htmlspecialchars($stu['form']) ?></td>
                    <td><?= htmlspecialchars($stu['stream']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    <a href="teacher_dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>