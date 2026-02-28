<?php
session_start();
require_once 'config/db.php';
if ($_SESSION['role'] !== 'teacher') { header('Location: index.php'); exit; }

$user_id = $_SESSION['user_id'];
// Get teacher's staff_id
$stmt = $pdo->prepare("SELECT staff_id FROM staff WHERE user_id = ?");
$stmt->execute([$user_id]);
$staff_id = $stmt->fetchColumn();

// Get classes where this teacher is the class teacher
$stmt = $pdo->prepare("
    SELECT DISTINCT form, stream
    FROM teacher_classes
    WHERE staff_id = ?
    ORDER BY form, stream
");
$stmt->execute([$staff_id]);
$class_teacher_classes = $stmt->fetchAll();

$selected_class = isset($_POST['class']) ? $_POST['class'] : '';
$students = [];
if ($selected_class) {
    list($form, $stream) = explode('|', $selected_class);
    // Get students in the selected class
    $stmt = $pdo->prepare("SELECT * FROM students WHERE form = ? AND stream = ?");
    $stmt->execute([$form, $stream]);
    $students = $stmt->fetchAll();
}

// Handle attendance submission
$msg = '';
if (isset($_POST['mark_attendance'])) {
    $date = date('Y-m-d');
    foreach ($_POST['attendance'] as $student_id => $status) {
        $stmt = $pdo->prepare("INSERT INTO attendance (student_id, date, status, marked_by) VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE status = VALUES(status), marked_by = VALUES(marked_by)");
        $stmt->execute([$student_id, $date, $status, $staff_id]);
    }
    $msg = "Attendance marked for " . count($_POST['attendance']) . " students.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Attendance</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container { background: #fff; padding: 30px 40px; margin: 60px auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 600px; }
        table { width: 100%; margin-top: 20px; }
        th, td { padding: 8px; text-align: left; }
        th { background: #f0f6fa; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="form-container">
    <h2>Attendance Module</h2>
    <?php if ($msg) echo "<p class='success'>$msg</p>"; ?>
    <form method="POST">
        <label>Select Class:</label>
        <select name="class" required>
            <option value="">--Select--</option>
            <?php foreach ($class_teacher_classes as $c):
                $val = $c['form'].'|'.$c['stream'];
                $sel = ($selected_class == $val) ? 'selected' : '';
                echo "<option value='$val' $sel>Form {$c['form']} - {$c['stream']}</option>";
            endforeach; ?>
        </select>
        <button type="submit" name="show_students">Show Students</button>
    </form>
    <?php if ($students): ?>
        <form method="POST">
            <input type="hidden" name="class" value="<?= htmlspecialchars($selected_class) ?>">
            <table border="1" cellpadding="6">
                <tr><th>Student Name</th><th>Status</th></tr>
                <?php foreach ($students as $stu): ?>
                    <tr>
                        <td><?= htmlspecialchars($stu['first_name'].' '.$stu['last_name']) ?></td>
                        <td>
                            <select name="attendance[<?= $stu['student_id'] ?>]">
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                                <option value="excused">Excused</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <button type="submit" name="mark_attendance">Mark Attendance</button>
        </form>
    <?php endif; ?>
    <a href="teacher_dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>

