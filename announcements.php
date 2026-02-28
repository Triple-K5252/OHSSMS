<?php
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['student', 'teacher'])) {
    header('Location: index.php');
    exit;
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Determine user's form and stream(s)
$form_streams = [];

if ($role === 'student') {
    $stmt = $pdo->prepare("SELECT form, stream FROM students WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $student = $stmt->fetch();
    if ($student) {
        $form_streams[] = ['form' => $student['form'], 'stream' => $student['stream']];
    }
} elseif ($role === 'teacher') {
    $stmt = $pdo->prepare("SELECT staff_id FROM staff WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $staff_id = $stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT DISTINCT form, stream FROM teacher_classes WHERE staff_id = ?");
    $stmt->execute([$staff_id]);
    $classes = $stmt->fetchAll();
    foreach ($classes as $c) {
        $form_streams[] = ['form' => $c['form'], 'stream' => $c['stream']];
    }
}

// Build query for announcements
$query = "SELECT * FROM announcements WHERE (audience = 'all' OR audience = ?)";
$params = [$role . 's']; // 'students' or 'teachers'

// Add form/stream filtering if user has any
if (!empty($form_streams)) {
    $query .= " AND (";
    $or = [];
    foreach ($form_streams as $fs) {
        $or[] = "(form = ? AND stream = ?)";
        $params[] = $fs['form'];
        $params[] = $fs['stream'];
    }
    $query .= implode(' OR ', $or);
    $query .= " OR (form IS NULL AND stream IS NULL))";
} else {
    $query .= " AND (form IS NULL AND stream IS NULL)";
}
$query .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$announcements = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Announcements</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
if ($role === 'student') {
    include 'navbar_student.php';
} else {
    include 'navbar.php';
}
?>
<div class="dashboard-box">
    <h2>Announcements</h2>
    <?php if (empty($announcements)): ?>
        <p>No announcements at this time.</p>
    <?php else: ?>
        <?php foreach ($announcements as $a): ?>
            <div style="border:1px solid #e0e0e0; border-radius:6px; margin-bottom:18px; padding:12px;">
                <h3><?= htmlspecialchars($a['title']) ?></h3>
                <div style="color:#555;"><?= nl2br(htmlspecialchars($a['message'])) ?></div>
                <div style="font-size:0.9em; color:#888; margin-top:8px;">
                    Posted: <?= htmlspecialchars($a['created_at']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>