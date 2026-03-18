<?php
session_start();
require_once 'config/db.php';
if ($_SESSION['role'] !== 'student') { header('Location: index.php'); exit; }

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM students WHERE user_id = ?");
$stmt->execute([$user_id]);
$student = $stmt->fetch();

// Get all available terms/assessments for this student
$stmt = $pdo->prepare("
    SELECT DISTINCT a.assessment_type
    FROM grades g
    JOIN assessments a ON g.assessment_id = a.assessment_id
    WHERE g.student_id = ?
    ORDER BY a.assessment_type
");
$stmt->execute([$student['student_id']]);
$terms = $stmt->fetchAll(PDO::FETCH_COLUMN);

$selected_term = isset($_GET['term']) ? $_GET['term'] : (count($terms) ? $terms[0] : '');

$grade_points = [
    'A'=>12, 'A-'=>11, 'B+'=>10, 'B'=>9, 'B-'=>8,
    'C+'=>7, 'C'=>6, 'C-'=>5, 'D+'=>4, 'D'=>3, 'D-'=>2, 'E'=>1
];

// FETCH ONLY ASSIGNED SUBJECTS FROM THE NEW TABLE
$stmt = $pdo->prepare("
    SELECT s.subject_id, s.subject_name
    FROM subjects s
    JOIN student_subjects ss ON s.subject_id = ss.subject_id
    WHERE ss.student_id = ?
");
$stmt->execute([$student['student_id']]);
$subject_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$subject_map = [];
foreach ($subject_rows as $row) {
    $subject_map[$row['subject_id']] = $row['subject_name'];
}

$grades = [];
if ($selected_term && !empty($subject_map)) {
    $subject_ids = array_keys($subject_map);
    $placeholders = implode(',', array_fill(0, count($subject_ids), '?'));
    $params = array_merge([$student['student_id'], $selected_term], $subject_ids);
    $stmt = $pdo->prepare("
        SELECT s.subject_id, g.score, g.grade
        FROM grades g
        JOIN assessments a ON g.assessment_id = a.assessment_id
        JOIN subjects s ON a.subject_id = s.subject_id
        WHERE g.student_id = ? AND a.assessment_type = ? AND s.subject_id IN ($placeholders)
    ");
    $stmt->execute($params);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $g) {
        $grades[$g['subject_id']] = $g;
    }
}

$subject_count = count($subject_map);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Grades</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .grades-table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        .grades-table th { background: #f0f6fa; padding: 10px; }
        .grades-table td { padding: 8px; text-align: center; border: 1px solid #ddd; }
        .summary-row td { font-weight: bold; background: #f8f8f8; }
    </style>
</head>
<body>
<?php include 'navbar_student.php'; ?>
<div class="dashboard-box">
    <h2>Performance Report</h2>
    <form method="get" style="margin-bottom:16px;">
        <label>Select Term:</label>
        <select name="term" onchange="this.form.submit()">
            <?php foreach ($terms as $term): ?>
                <option value="<?= htmlspecialchars($term) ?>" <?= $selected_term == $term ? 'selected' : '' ?>>
                    <?= htmlspecialchars($term) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <table class="grades-table">
        <tr><th>Subject</th><th>Marks</th><th>Grade</th><th>Points</th></tr>
        <?php
        $sum_marks = 0;
        $sum_points = 0;
        foreach ($subject_map as $subject_id => $subject_name):
            $score = isset($grades[$subject_id]['score']) ? $grades[$subject_id]['score'] : '-';
            $grade = isset($grades[$subject_id]['grade']) ? $grades[$subject_id]['grade'] : '-';
            $points = ($grade && isset($grade_points[$grade])) ? $grade_points[$grade] : 0;
            if ($score !== '-') $sum_marks += floatval($score);
            $sum_points += $points;
        ?>
            <tr>
                <td style="text-align: left; padding-left: 15px;"><?= htmlspecialchars($subject_name) ?></td>
                <td><?= htmlspecialchars($score) ?></td>
                <td><?= htmlspecialchars($grade) ?></td>
                <td><?= $points ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($subject_count > 0): ?>
            <tr class="summary-row">
                <td>Total</td>
                <td><?= $sum_marks ?></td>
                <td></td>
                <td><?= $sum_points ?> pts</td>
            </tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>