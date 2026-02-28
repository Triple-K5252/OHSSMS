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

// Get selected term (or default to first available)
$selected_term = isset($_GET['term']) ? $_GET['term'] : (count($terms) ? $terms[0] : '');

// Grade to points mapping
$grade_points = [
    'A'=>12, 'A-'=>11, 'B+'=>10, 'B'=>9, 'B-'=>8,
    'C+'=>7, 'C'=>6, 'C-'=>5, 'D+'=>4, 'D'=>3, 'D-'=>2, 'E'=>1
];

// Get all subjects the student is doing
$subjects = [];
if ($student['form'] == 1 || ($student['form'] == 2 && $selected_term <= 'Term 2')) {
    $compulsory = [
        'Mathematics', 'Kiswahili', 'Chemistry', 'English',
        'Physics', 'Biology', 'CRE', 'Geography', 'History & Government'
    ];
    foreach ($compulsory as $subj) $subjects[] = $subj;
    if (in_array($student['stream'], ['North', 'South'])) {
        $subjects[] = 'Computer Studies';
        $subjects[] = 'Agriculture';
    } else {
        $subjects[] = 'Business Studies';
        $subjects[] = 'Drawing & Design';
    }
    // Get subject IDs for easier matching
    $placeholders = implode(',', array_fill(0, count($subjects), '?'));
    $stmt = $pdo->prepare("SELECT subject_id, subject_name FROM subjects WHERE subject_name IN ($placeholders)");
    $stmt->execute($subjects);
    $subject_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $subject_map = [];
    foreach ($subject_rows as $row) $subject_map[$row['subject_id']] = $row['subject_name'];
} else {
    // For Form 2 (after Term 2), Form 3, and Form 4: show chosen subjects
    $stmt = $pdo->prepare("
        SELECT s.subject_id, s.subject_name
        FROM chosen_subjects cs
        JOIN subjects s ON cs.subject_id = s.subject_id
        WHERE cs.student_id = ?
    ");
    $stmt->execute([$student['student_id']]);
    $subject_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $subject_map = [];
    foreach ($subject_rows as $row) $subject_map[$row['subject_id']] = $row['subject_name'];
}

// Fetch grades for the selected term, indexed by subject_id
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

// Calculate totals
$sum_marks = 0;
$sum_points = 0;
$subject_count = count($subject_map);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Report Card</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .report-card-box { background: #fff; padding: 30px 40px; margin: 60px auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 600px; }
        .report-card-box h2 { text-align: center; color: #007bff; margin-bottom: 24px; }
        .report-table { width: 100%; margin-top: 20px; }
        .report-table th { background: #f0f6fa; }
        .report-table td { padding: 8px; text-align: center; }
        .summary-row td { font-weight: bold; background: #f8f8f8; }
    </style>
</head>
<body>
<?php include 'navbar_student.php'; ?>
<div class="report-card-box">
    <h2>Report Card</h2>
    <form method="get" style="margin-bottom:16px;">
        <label>Select Term/Assessment:</label>
        <select name="term" onchange="this.form.submit()">
            <?php foreach ($terms as $term): ?>
                <option value="<?= htmlspecialchars($term) ?>" <?= $selected_term == $term ? 'selected' : '' ?>>
                    <?= htmlspecialchars($term) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <div>
        <b>Admission No:</b> <?= htmlspecialchars($student['admission_no']) ?><br>
        <b>Name:</b> <?= htmlspecialchars($student['first_name'].' '.$student['middle_name'].' '.$student['last_name']) ?><br>
        <b>Form:</b> <?= htmlspecialchars($student['form']) ?><br>
        <b>Stream:</b> <?= htmlspecialchars($student['stream']) ?><br>
    </div>
    <table class="report-table" border="1">
        <tr><th>Subject</th><th>Marks</th><th>Grade</th><th>Points</th></tr>
        <?php
        foreach ($subject_map as $subject_id => $subject_name):
            $score = isset($grades[$subject_id]['score']) ? $grades[$subject_id]['score'] : '';
            $grade = isset($grades[$subject_id]['grade']) ? $grades[$subject_id]['grade'] : '';
            $points = ($grade && isset($grade_points[$grade])) ? $grade_points[$grade] : '';
            if ($score !== '') $sum_marks += floatval($score);
            if ($points !== '') $sum_points += $points;
        ?>
            <tr>
                <td><?= htmlspecialchars($subject_name) ?></td>
                <td><?= htmlspecialchars($score) ?></td>
                <td><?= htmlspecialchars($grade) ?></td>
                <td><?= htmlspecialchars($points) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($subject_count > 0): ?>
            <tr class="summary-row">
                <td>Total</td>
                <td><?= $sum_marks ?></td>
                <td></td>
                <td><?= $sum_points ?> pts</td>
            </tr>
            <tr class="summary-row">
                <td colspan="3">Overall Grade</td>
                <td>
                    <?php
                    $avg_points = $subject_count ? $sum_points / $subject_count : 0;
                    if ($avg_points >= 11.5) echo 'A';
                    elseif ($avg_points >= 10.5) echo 'A-';
                    elseif ($avg_points >= 9.5) echo 'B+';
                    elseif ($avg_points >= 8.5) echo 'B';
                    elseif ($avg_points >= 7.5) echo 'B-';
                    elseif ($avg_points >= 6.5) echo 'C+';
                    elseif ($avg_points >= 5.5) echo 'C';
                    elseif ($avg_points >= 4.5) echo 'C-';
                    elseif ($avg_points >= 3.5) echo 'D+';
                    elseif ($avg_points >= 2.5) echo 'D';
                    elseif ($avg_points >= 1.5) echo 'D-';
                    else echo 'E';
                    ?>
                </td>
            </tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
            
