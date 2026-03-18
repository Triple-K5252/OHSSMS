<?php
session_start();
require_once 'config/db.php';
if ($_SESSION['role'] !== 'teacher') { header('Location: index.php'); exit; }

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT staff_id FROM staff WHERE user_id = ?");
$stmt->execute([$user_id]);
$staff_id = $stmt->fetchColumn();

// Get subjects teacher handles
$stmt = $pdo->prepare("
    SELECT DISTINCT s.subject_id, s.subject_name
    FROM teacher_subjects ts
    JOIN subjects s ON ts.subject_id = s.subject_id
    WHERE ts.staff_id = ?
    ORDER BY s.subject_name
");
$stmt->execute([$staff_id]);
$subjects = $stmt->fetchAll();

$selected_subject = isset($_POST['subject']) ? $_POST['subject'] : '';
$classes = [];
if ($selected_subject) {
    $stmt = $pdo->prepare("SELECT DISTINCT form, stream FROM teacher_classes WHERE staff_id = ? AND subject_id = ? ORDER BY form, stream");
    $stmt->execute([$staff_id, $selected_subject]);
    $classes = $stmt->fetchAll();
}

$selected_class = isset($_POST['class']) ? $_POST['class'] : '';
$students = [];
if ($selected_subject && $selected_class) {
    list($form, $stream) = explode('|', $selected_class);
    // ONLY FETCH STUDENTS REGISTERED FOR THIS SUBJECT
    $stmt = $pdo->prepare("
        SELECT s.* FROM students s
        JOIN student_subjects ss ON s.student_id = ss.student_id
        WHERE s.form = ? AND s.stream = ? AND ss.subject_id = ? AND s.is_active = 1 
        ORDER BY s.first_name, s.last_name
    ");
    $stmt->execute([$form, $stream, $selected_subject]);
    $students = $stmt->fetchAll();
}

$assessment_types = ['Term 1 Midterm', 'Term 1 End Term', 'Term 2 Midterm', 'Term 2 End Term', 'Term 3 End Term'];
$selected_assessment = isset($_POST['assessment_type']) ? $_POST['assessment_type'] : '';

function calculate_grade($score) {
    if ($score >= 80) return 'A';
    if ($score >= 70) return 'B';
    if ($score >= 60) return 'C';
    if ($score >= 50) return 'D';
    return 'E';
}

$msg = '';
if (isset($_POST['save_grades'])) {
    $subject_id = $_POST['subject'];
    $assessment_type = $_POST['assessment_type'];
    list($form, $stream) = explode('|', $_POST['class']);
    $year = date('Y');

    $stmt = $pdo->prepare("SELECT assessment_id FROM assessments WHERE subject_id = ? AND term = ? AND year = ? AND assessment_type = ?");
    $stmt->execute([$subject_id, $form, $year, $assessment_type]);
    $assessment_id = $stmt->fetchColumn();
    if (!$assessment_id) {
        $stmt = $pdo->prepare("INSERT INTO assessments (subject_id, term, year, assessment_type, max_score, weight) VALUES (?, ?, ?, ?, 100, 1)");
        $stmt->execute([$subject_id, $form, $year, $assessment_type]);
        $assessment_id = $pdo->lastInsertId();
    }

    foreach ($_POST['grades'] as $student_id => $score) {
        $grade = calculate_grade($score);
        $stmt = $pdo->prepare("INSERT INTO grades (student_id, assessment_id, score, grade) VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE score = VALUES(score), grade = VALUES(grade)");
        $stmt->execute([$student_id, $assessment_id, floatval($score), $grade]);
    }
    $msg = "Successfully saved grades for " . count($_POST['grades']) . " students.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teacher Gradebook</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container { background: #fff; padding: 25px; margin: 40px auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 800px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border: 1px solid #eee; text-align: left; }
        .success { color: #27ae60; font-weight: bold; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="form-container">
    <h2>Subject Gradebook</h2>
    <?php if ($msg) echo "<p class='success'>$msg</p>"; ?>

    <form method="POST">
        <label>Subject:</label>
        <select name="subject" onchange="this.form.submit()" required>
            <option value="">--Select Subject--</option>
            <?php foreach ($subjects as $s): ?>
                <option value="<?= $s['subject_id'] ?>" <?= $selected_subject == $s['subject_id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['subject_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($selected_subject): ?>
    <form method="POST">
        <input type="hidden" name="subject" value="<?= $selected_subject ?>">
        <label>Class:</label>
        <select name="class" onchange="this.form.submit()" required>
            <option value="">--Select Class--</option>
            <?php foreach ($classes as $c): $v = $c['form'].'|'.$c['stream']; ?>
                <option value="<?= $v ?>" <?= $selected_class == $v ? 'selected' : '' ?>>Form <?= $c['form'] ?> - <?= $c['stream'] ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php endif; ?>

    <?php if ($students): ?>
    <form method="POST">
        <input type="hidden" name="subject" value="<?= $selected_subject ?>">
        <input type="hidden" name="class" value="<?= $selected_class ?>">
        <label>Assessment Type:</label>
        <select name="assessment_type" required>
            <option value="">--Select--</option>
            <?php foreach ($assessment_types as $type): ?>
                <option value="<?= $type ?>" <?= $selected_assessment == $type ? 'selected' : '' ?>><?= $type ?></option>
            <?php endforeach; ?>
        </select>
        <table>
            <tr><th>Student Name</th><th>Marks (0-100)</th></tr>
            <?php foreach ($students as $stu): ?>
                <tr>
                    <td><?= htmlspecialchars($stu['first_name'].' '.$stu['last_name']) ?></td>
                    <td><input type="number" name="grades[<?= $stu['student_id'] ?>]" min="0" max="100" required></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <button type="submit" name="save_grades" style="margin-top:15px; background: #2c3e50; color: white; padding: 10px;">Save All Grades</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>