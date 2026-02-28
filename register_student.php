<?php
session_start();
require_once 'config/db.php';
if ($_SESSION['role'] !== 'admin') { header('Location: index.php'); exit; }

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admission_no = $_POST['admission_no'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $dob = $_POST['dob'];
    $form = $_POST['form'];
    $stream = $_POST['stream'];
    $guardian_name = $_POST['guardian_name'];
    $guardian_contact = $_POST['guardian_contact'];

    // Create user
    $username = $admission_no;
    $password_hash = password_hash($admission_no, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, is_active) VALUES (?, ?, 'student', 1)");
    $stmt->execute([$username, $password_hash]);
    $user_id = $pdo->lastInsertId();

    // Create student
    $stmt = $pdo->prepare("INSERT INTO students (user_id, admission_no, first_name, middle_name, last_name, dob, form, stream, guardian_name, guardian_contact) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $admission_no, $first_name, $middle_name, $last_name, $dob, $form, $stream, $guardian_name, $guardian_contact]);
    $msg = "Student registered successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    // Final Project Update
    <?php include 'admin_navbar.php'; ?>
<div class="form-container">
<h3>Register Student</h3>
<?php if ($msg) echo "<p class='success'>$msg</p>"; ?>
<form method="POST">
    Admission No: <input name="admission_no" required><br>
    First Name: <input name="first_name" required><br>
    Middle Name: <input name="middle_name"><br>
    Last Name: <input name="last_name" required><br>
    Date of Birth: <input type="date" name="dob" required><br>
    Form:
    <select name="form" required>
        <option value="1">Form 1</option>
        <option value="2">Form 2</option>
        <option value="3">Form 3</option>
        <option value="4">Form 4</option>
    </select><br>
    Stream:
    <select name="stream" required>
        <option value="North">North</option>
        <option value="South">South</option>
        <option value="East">East</option>
        <option value="West">West</option>
    </select><br>
    Guardian Name: <input name="guardian_name" required><br>
    Guardian Contact: <input name="guardian_contact" required><br>
    <button type="submit">Register Student</button>
</form>
<a href="admin_dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>