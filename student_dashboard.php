<?php
session_start();
require_once 'config/db.php';
if ($_SESSION['role'] !== 'student') { header('Location: index.php'); exit; }

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM students WHERE user_id = ?");
$stmt->execute([$user_id]);
$student = $stmt->fetch();

// Set current year and term (customize as needed)
$current_year = date('Y');
$current_term = 'Term 1'; // Change as needed

// Subject logic
$subjects = [];
if ($student['form'] == 1 || ($student['form'] == 2 && $current_term <= 'Term 2')) {
    // Form 1 and Form 2 (up to Term 2): default 11 subjects
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
} else {
    // Form 2 (after Term 2), Form 3, Form 4: show chosen subjects
    $stmt = $pdo->prepare("
        SELECT s.subject_name
        FROM chosen_subjects cs
        JOIN subjects s ON cs.subject_id = s.subject_id
        WHERE cs.student_id = ? AND cs.year = ? AND cs.term = ?
    ");
    $stmt->execute([$student['student_id'], $current_year, $current_term]);
    $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-box {
            background: #fff;
            padding: 30px 40px;
            margin: 60px auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 600px;
        }
        .dashboard-box h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 24px;
        }
        .dashboard-info label {
            font-weight: bold;
            color: #333;
            display: inline-block;
            width: 160px;
        }
        .subject-list { margin: 0 0 16px 0; padding: 0; list-style: none; }
        .subject-list li { background: #f4f8fc; margin-bottom: 6px; padding: 6px 12px; border-radius: 4px; color: #222; }
        .btn {
            display: inline-block;
            background: #007bff;
            color: #fff;
            padding: 10px 18px;
            margin: 6px 4px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
<?php include 'navbar_student.php'; ?>
<div class="dashboard-box">
    <h2>Welcome, <?= htmlspecialchars($student['first_name']) ?>!</h2>
    <div class="dashboard-info">
        <label>Admission No:</label> <?= htmlspecialchars($student['admission_no']) ?><br>
        <label>Name:</label> <?= htmlspecialchars($student['first_name'].' '.$student['middle_name'].' '.$student['last_name']) ?><br>
        <label>Date of Birth:</label> <?= htmlspecialchars($student['dob']) ?><br>
        <label>Form:</label> <?= htmlspecialchars($student['form']) ?><br>
        <label>Stream:</label> <?= htmlspecialchars($student['stream']) ?><br>
        <label>Guardian:</label> <?= htmlspecialchars($student['guardian_name']) ?><br>
        <label>Guardian Contact:</label> <?= htmlspecialchars($student['guardian_contact']) ?><br>
    </div>
    <div class="dashboard-info">
        <label>Subjects:</label>
        <?php if (!empty($subjects)): ?>
            <ul class="subject-list">
                <?php foreach ($subjects as $subj): ?>
                    <li><?= htmlspecialchars($subj) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <span style="color:#888;">No subjects found.</span>
        <?php endif; ?>
    
</body>
</html>