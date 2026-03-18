<?php
session_start();
require_once 'config/db.php';
if ($_SESSION['role'] !== 'teacher') { header('Location: index.php'); exit; }

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT staff_id FROM staff WHERE user_id = ?");
$stmt->execute([$user_id]);
$staff_id = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT DISTINCT form, stream FROM teacher_classes WHERE staff_id = ? ORDER BY form, stream");
$stmt->execute([$staff_id]);
$classes = $stmt->fetchAll();

$selected_class = isset($_POST['class']) ? $_POST['class'] : '';
$students = [];
if ($selected_class) {
    list($form, $stream) = explode('|', $selected_class);
    $stmt = $pdo->prepare("SELECT * FROM students WHERE form = ? AND stream = ? ORDER BY admission_no");
    $stmt->execute([$form, $stream]);
    $students = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Class List View</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container { max-width: 900px; margin: 30px auto; background: white; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        .sub-list { font-size: 0.85em; color: #555; font-style: italic; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="form-container">
    <h2>Class Management</h2>
    <form method="POST">
        <label>View Class:</label>
        <select name="class" onchange="this.form.submit()">
            <option value="">--Select--</option>
            <?php foreach ($classes as $c): $v = $c['form'].'|'.$c['stream']; ?>
                <option value="<?= $v ?>" <?= $selected_class == $v ? 'selected' : '' ?>>Form <?= $c['form'] ?> - <?= $c['stream'] ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($students): ?>
        <table>
            <thead>
                <tr><th>Adm No</th><th>Full Name</th><th>Assigned Subjects</th></tr>
            </thead>
            <tbody>
                <?php foreach ($students as $stu): ?>
                <tr>
                    <td><?= htmlspecialchars($stu['admission_no']) ?></td>
                    <td><?= htmlspecialchars($stu['first_name'].' '.$stu['last_name']) ?></td>
                    <td class="sub-list">
                        <?php
                        $sub_stmt = $pdo->prepare("
                            SELECT s.subject_name 
                            FROM subjects s 
                            JOIN student_subjects ss ON s.subject_id = ss.subject_id 
                            WHERE ss.student_id = ?
                        ");
                        $sub_stmt->execute([$stu['student_id']]);
                        $assigned = $sub_stmt->fetchAll(PDO::FETCH_COLUMN);
                        echo count($assigned) > 0 ? implode(', ', $assigned) : '<span style="color:red;">No subjects assigned</span>';
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>