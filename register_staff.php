<?php
session_start();
require_once 'config/db.php';
if ($_SESSION['role'] !== 'admin') { header('Location: index.php'); exit; }

// Fetch subjects for the form
$subjects = $pdo->query("SELECT * FROM subjects")->fetchAll();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_no = $_POST['id_no'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $dob = $_POST['dob'];
    $selected_subjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];
    $assignments = isset($_POST['assignments']) ? $_POST['assignments'] : [];

    // Create user
    $username = $id_no;
    $password_hash = password_hash($id_no, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, is_active) VALUES (?, ?, 'teacher', 1)");
    $stmt->execute([$username, $password_hash]);
    $user_id = $pdo->lastInsertId();

    // Create staff
    $stmt = $pdo->prepare("INSERT INTO staff (user_id, id_no, first_name, last_name, dob) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $id_no, $first_name, $last_name, $dob]);
    $staff_id = $pdo->lastInsertId();

    // Insert teacher_subjects
    $stmt = $pdo->prepare("INSERT INTO teacher_subjects (staff_id, subject_id) VALUES (?, ?)");
    foreach ($selected_subjects as $subject_id) {
        $stmt->execute([$staff_id, $subject_id]);
    }

    // Insert teacher_classes
    $stmt = $pdo->prepare("INSERT INTO teacher_classes (staff_id, form, stream, subject_id) VALUES (?, ?, ?, ?)");
    foreach ($assignments as $subject_id => $class_list) {
        foreach ($class_list as $class) {
            list($form, $stream) = explode('|', $class);
            $stmt->execute([$staff_id, $form, $stream, $subject_id]);
        }
    }

    $msg = "Staff registered successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register Staff</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .multi-select { height: 120px; }
        .class-assign-table td, .class-assign-table th { padding: 4px 8px; }
        .hidden { display: none; }
    </style>
    <script src="register_staff.js"></script>
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
<div class="form-container">
<h3>Register Staff (Teacher)</h3>
<?php if ($msg) echo "<p class='success'>$msg</p>"; ?>
<form method="POST">
    Teacher ID No: <input name="id_no" required><br>
    First Name: <input name="first_name" required><br>
    Last Name: <input name="last_name" required><br>
    Date of Birth: <input type="date" name="dob" required><br>
    Subjects (hold Ctrl to select multiple):<br>
    <select name="subjects[]" id="subjects" multiple class="multi-select" required onchange="updateClassAssignments()">
        <?php foreach ($subjects as $subject): ?>
            <option value="<?= $subject['subject_id'] ?>"><?= htmlspecialchars($subject['subject_name']) ?></option>
        <?php endforeach; ?>
    </select><br><br>
    <b>For each selected subject, assign classes (Form/Stream):</b><br>
    <?php
    $streams = ['North','South','East','West'];
    foreach ($subjects as $subject) {
        echo "<div class='subject-classes hidden' id='classes-for-{$subject['subject_id']}'>";
        echo "<b>{$subject['subject_name']}</b><br>";
        echo "<table class='class-assign-table' border='1'><tr><th>Form</th><th>Stream</th><th>Select</th></tr>";
        for ($form = 1; $form <= 4; $form++) {
            foreach ($streams as $stream) {
                echo "<tr>
                    <td>Form $form</td>
                    <td>$stream</td>
                    <td><input type='checkbox' name='assignments[{$subject['subject_id']}][]' value='$form|$stream'></td>
                </tr>";
            }
        }
        echo "</table><br></div>";
    }
    ?>
    <button type="submit">Register Staff</button>
</form>
<a href="admin_dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>

