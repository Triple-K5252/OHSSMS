<?php
session_start();
require_once 'config/db.php';
if ($_SESSION['role'] !== 'teacher') { header('Location: index.php'); exit; }

$user_id = $_SESSION['user_id'];

// Get teacher info
$stmt = $pdo->prepare("SELECT * FROM staff WHERE user_id = ?");
$stmt->execute([$user_id]);
$staff = $stmt->fetch();

// Get all class assignments with subjects
$stmt = $pdo->prepare("
    SELECT tc.form, tc.stream, s.subject_name
    FROM teacher_classes tc
    JOIN subjects s ON tc.subject_id = s.subject_id
    WHERE tc.staff_id = ?
    ORDER BY tc.form, tc.stream, s.subject_name
");
$stmt->execute([$staff['staff_id']]);
$class_subjects = $stmt->fetchAll();

// Group by class (form+stream)
$classes = [];
foreach ($class_subjects as $row) {
    $key = "Form {$row['form']} - {$row['stream']}";
    if (!isset($classes[$key])) $classes[$key] = [];
    $classes[$key][] = $row['subject_name'];
}

// Get teacher's subjects (all unique subjects)
$stmt = $pdo->prepare("SELECT DISTINCT s.subject_name FROM teacher_subjects ts JOIN subjects s ON ts.subject_id = s.subject_id WHERE ts.staff_id = ?");
$stmt->execute([$staff['staff_id']]);
$subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-box {
            background: #fff;
            padding: 30px 40px;
            margin: 60px auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        .dashboard-box h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 24px;
        }
        .dashboard-info {
            margin-bottom: 20px;
        }
        .dashboard-info label {
            font-weight: bold;
            color: #333;
            display: inline-block;
            width: 140px;
        }
        .subject-list, .class-list {
            margin: 0 0 16px 0;
            padding: 0;
            list-style: none;
        }
        .subject-list li, .class-list li {
            background: #f4f8fc;
            margin-bottom: 6px;
            padding: 6px 12px;
            border-radius: 4px;
            color: #222;
        }
        .logout-btn {
            display: block;
            width: 100%;
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 10px 0;
            border-radius: 4px;
            font-size: 1em;
            margin-top: 20px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
        .logout-btn:hover {
            background: #b52a37;
        }
        .class-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .class-table th, .class-table td {
            border: 1px solid #e0e0e0;
            padding: 8px;
            text-align: left;
        }
        .class-table th {
            background: #f0f6fa;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
<div class="dashboard-box">
    <h2>Teacher Dashboard</h2>
    <div class="dashboard-info">
        <label>Name:</label> <?= htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) ?><br>
        <label>Teacher ID:</label> <?= htmlspecialchars($staff['id_no']) ?><br>
    </div>
    <div class="dashboard-info">
        <label>Subjects Taught:</label>
        <?php if (count($subjects)): ?>
            <ul class="subject-list">
                <?php foreach ($subjects as $subj): ?>
                    <li><?= htmlspecialchars($subj) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <span style="color:#888;">No subjects assigned.</span>
        <?php endif; ?>
    </div>
    <div class="dashboard-info">
        <label>Classes & Subjects:</label>
        <?php if (count($classes)): ?>
            <table class="class-table">
                <tr>
                    <th>Class (Form/Stream)</th>
                    <th>Subject(s) Taught</th>
                </tr>
                <?php foreach ($classes as $class => $subs): ?>
                    <tr>
                        <td><?= htmlspecialchars($class) ?></td>
                        <td><?= htmlspecialchars(implode(', ', $subs)) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <span style="color:#888;">No class assignments.</span>
        <?php endif; ?>

       
    </div>
    <a href="index.php" class="logout-btn">Logout</a>
</div>

</body>
</html>