<?php
session_start();
require_once 'config/db.php';
if ($_SESSION['role'] !== 'teacher') { header('Location: index.php'); exit; }

$user_id = $_SESSION['user_id'];
// Get staff_id
$stmt = $pdo->prepare("SELECT staff_id FROM staff WHERE user_id = ?");
$stmt->execute([$user_id]);
$staff_id = $stmt->fetchColumn();

// Get all subjects this teacher teaches
$stmt = $pdo->prepare("
    SELECT DISTINCT s.subject_id, s.subject_name
    FROM teacher_subjects ts
    JOIN subjects s ON ts.subject_id = s.subject_id
    WHERE ts.staff_id = ?
    ORDER BY s.subject_name
");
$stmt->execute([$staff_id]);
$subjects = $stmt->fetchAll();

// Handle subject selection
$selected_subject = isset($_POST['subject']) ? $_POST['subject'] : '';
$classes = [];
if ($selected_subject) {
    // Get all classes where this teacher teaches the selected subject
    $stmt = $pdo->prepare("
        SELECT DISTINCT form, stream
        FROM teacher_classes
        WHERE staff_id = ? AND subject_id = ?
        ORDER BY form, stream
    ");
    $stmt->execute([$staff_id, $selected_subject]);
    $classes = $stmt->fetchAll();
}

// Handle class selection
$selected_class = isset($_POST['class']) ? $_POST['class'] : '';
$students = [];
if ($selected_subject && $selected_class) {
    list($form, $stream) = explode('|', $selected_class);
    $stmt = $pdo->prepare("SELECT * FROM students WHERE form = ? AND stream = ? AND is_active = 1 ORDER BY first_name, last_name");
    $stmt->execute([$form, $stream]);
    $students = $stmt->fetchAll();
}

// Assessment types
$assessment_types = [
    'Term 1 Midterm',
    'Term 1 End Term',
    'Term 2 Midterm',
    'Term 2 End Term',
    'Term 3 End Term'
];
$selected_assessment = isset($_POST['assessment_type']) ? $_POST['assessment_type'] : '';

// Grading function
function calculate_grade($score) {
    if ($score >= 80) return 'A';
    if ($score >= 70) return 'B';
    if ($score >= 60) return 'C';
    if ($score >= 50) return 'D';
    if ($score >= 40) return 'E';
    return 'F';
}

// Handle grade submission
$msg = '';
if (isset($_POST['save_grades'])) {
    $subject_id = $_POST['subject'];
    $form_stream = $_POST['class'];
    $assessment_type = $_POST['assessment_type'];
    $year = date('Y');
    list($form, $stream) = explode('|', $form_stream);

    // Find or create assessment
    $stmt = $pdo->prepare("SELECT assessment_id FROM assessments WHERE subject_id = ? AND term = ? AND year = ? AND assessment_type = ?");
    $stmt->execute([$subject_id, $form, $year, $assessment_type]);
    $assessment_id = $stmt->fetchColumn();
    if (!$assessment_id) {
        $stmt = $pdo->prepare("INSERT INTO assessments (subject_id, term, year, assessment_type, max_score, weight) VALUES (?, ?, ?, ?, 100, 1)");
        $stmt->execute([$subject_id, $form, $year, $assessment_type]);
        $assessment_id = $pdo->lastInsertId();
    }

    foreach ($_POST['grades'] as $student_id => $score) {
        $score = floatval($score);
        $grade = calculate_grade($score);
        // Save score and grade (assume grades table has a 'grade' column)
        $stmt = $pdo->prepare("INSERT INTO grades (student_id, assessment_id, score, grade) VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE score = VALUES(score), grade = VALUES(grade)");
        $stmt->execute([$student_id, $assessment_id, $score, $grade]);
    }
    $msg = "Grades saved and calculated for " . count($_POST['grades']) . " students.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gradebook</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container { background: #fff; padding: 30px 40px; margin: 60px auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 700px; }
        table { width: 100%; margin-top: 20px; }
        th, td { padding: 8px; text-align: left; }
        th { background: #f0f6fa; }
        .success { color: green; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="form-container">
    <h2>Gradebook Module</h2>
    <?php if ($msg) echo "<p class='success'>$msg</p>"; ?>

    <!-- Subject selection -->
    <form method="POST">
        <label>Select Subject:</label>
        <select name="subject" onchange="this.form.submit()" required>
            <option value="">--Select--</option>
            <?php foreach ($subjects as $s):
                $sel = ($selected_subject == $s['subject_id']) ? 'selected' : '';
                echo "<option value='{$s['subject_id']}' $sel>{$s['subject_name']}</option>";
            endforeach; ?>
        </select>
    </form>

    <!-- Class selection -->
    <?php if (!empty($classes)): ?>
        <form method="POST">
            <input type="hidden" name="subject" value="<?= htmlspecialchars($selected_subject) ?>">
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
    <?php endif; ?>

    <!-- Assessment type selection -->
    <?php if (!empty($students) && $selected_subject && $selected_class): ?>
        <form method="POST">
            <input type="hidden" name="subject" value="<?= htmlspecialchars($selected_subject) ?>">
            <input type="hidden" name="class" value="<?= htmlspecialchars($selected_class) ?>">
            <label>Select Assessment/Term:</label>
            <select name="assessment_type" required>
                <option value="">--Select--</option>
                <?php foreach ($assessment_types as $type):
                    $sel = ($selected_assessment == $type) ? 'selected' : '';
                    echo "<option value='$type' $sel>$type</option>";
                endforeach; ?>
            </select>
            <table border="1" cellpadding="6">
                <tr><th>Student Name</th><th>Enter Marks</th><th>Grade</th></tr>
                <?php foreach ($students as $stu):
                    $score = isset($_POST['grades'][$stu['student_id']]) ? floatval($_POST['grades'][$stu['student_id']]) : '';
                    $grade = ($score !== '') ? calculate_grade($score) : '';
                ?>
                    <tr>
                        <td><?= htmlspecialchars($stu['first_name'].' '.$stu['last_name']) ?></td>
                        <td>
                            <input type="number" name="grades[<?= $stu['student_id'] ?>]" min="0" max="100" value="<?= htmlspecialchars($score) ?>" required>
                        </td>
                        <td><?= htmlspecialchars($grade) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <button type="submit" name="save_grades">Save Grades</button>
        </form>
    <?php endif; ?>
    <a href="teacher_dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>
