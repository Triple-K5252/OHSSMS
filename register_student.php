<?php
session_start();
require_once 'config/db.php';
if ($_SESSION['role'] !== 'admin') { header('Location: index.php'); exit; }

// Fetch subjects available in the school
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY subject_name ASC")->fetchAll();

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
    $selected_subjects = isset($_POST['student_subjects']) ? $_POST['student_subjects'] : [];

    try {
        $pdo->beginTransaction();

        // 1. Create user account
        $password_hash = password_hash($admission_no, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, is_active) VALUES (?, ?, 'student', 1)");
        $stmt->execute([$admission_no, $password_hash]);
        $user_id = $pdo->lastInsertId();

        // 2. Create student record
        $stmt = $pdo->prepare("INSERT INTO students (user_id, admission_no, first_name, middle_name, last_name, dob, form, stream, guardian_name, guardian_contact) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $admission_no, $first_name, $middle_name, $last_name, $dob, $form, $stream, $guardian_name, $guardian_contact]);
        $student_id = $pdo->lastInsertId();

        // 3. Register the student for selected subjects
        if (!empty($selected_subjects)) {
            $stmt = $pdo->prepare("INSERT INTO student_subjects (student_id, subject_id) VALUES (?, ?)");
            foreach ($selected_subjects as $subject_id) {
                $stmt->execute([$student_id, $subject_id]);
            }
        }

        $pdo->commit();
        $msg = "Student and subjects registered successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Student with Subjects</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <div class="form-container">
        <h3>Register New Student</h3>
        <?php if ($msg) echo "<p class='message'>$msg</p>"; ?>
        
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

            <h4>Assign Subjects</h4>
            <p><small>Hold Ctrl (Cmd on Mac) to select multiple subjects</small></p>
            <select name="student_subjects[]" multiple style="height: 150px; width: 100%;" required>
                <?php foreach ($subjects as $s): ?>
                    <option value="<?= $s['subject_id'] ?>"><?= htmlspecialchars($s['subject_name']) ?></option>
                <?php endforeach; ?>
            </select><br><br>

            Guardian Name: <input name="guardian_name"><br>
            Guardian Contact: <input name="guardian_contact"><br>

            <button type="submit">Register Student</button>
        </form>
    </div>
</body>
</html>