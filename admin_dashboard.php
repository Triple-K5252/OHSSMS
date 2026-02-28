<?php
session_start();
require_once 'config/db.php';
if ($_SESSION['role'] !== 'admin') { header('Location: index.php'); exit; }

// Handle add subject
$add_msg = '';
if (isset($_POST['add_subject'])) {
    $new_subject = trim($_POST['new_subject']);
    if ($new_subject !== '') {
        $stmt = $pdo->prepare("SELECT * FROM subjects WHERE subject_name = ?");
        $stmt->execute([$new_subject]);
        if ($stmt->rowCount() == 0) {
            $stmt = $pdo->prepare("INSERT INTO subjects (subject_name) VALUES (?)");
            $stmt->execute([$new_subject]);
            $add_msg = "<span class='success'>Subject added!</span>";
        } else {
            $add_msg = "<span class='error'>Subject already exists.</span>";
        }
    }
}

// Handle delete subject
if (isset($_GET['delete_subject'])) {
    $subject_id = intval($_GET['delete_subject']);
    $stmt = $pdo->prepare("DELETE FROM subjects WHERE subject_id = ?");
    $stmt->execute([$subject_id]);
    header("Location: admin_dashboard.php");
    exit;
}

// Get counts
$student_count = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$staff_count = $pdo->query("SELECT COUNT(*) FROM staff")->fetchColumn();
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY subject_name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-box { background: #fff; padding: 20px; margin: 20px auto; border-radius: 8px; max-width: 600px; }
        .subject-list { margin-top: 10px; }
        .subject-list li { margin-bottom: 6px; }
        .subject-list form { display: inline; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
<div class="dashboard-box">
    <h2>Admin Dashboard</h2>
    <p><b>Registered Students:</b> <?= $student_count ?></p>
    <p><b>Registered Staff:</b> <?= $staff_count ?></p>
    <hr>

    <?php
// Handle delete (soft delete)
if (isset($_GET['delete_student'])) {
    $id = intval($_GET['delete_student']);
    $pdo->prepare("UPDATE students SET is_active = 0 WHERE student_id = ?")->execute([$id]);
    $msg = "<span class='success'>Student marked as transferred (inactive).</span>";
}
if (isset($_GET['delete_staff'])) {
    $id = intval($_GET['delete_staff']);
    $pdo->prepare("UPDATE staff SET is_active = 0 WHERE staff_id = ?")->execute([$id]);
    $msg = "<span class='success'>Staff marked as inactive.</span>";
}

// Handle edit (show form)
$edit_student = null;
$edit_staff = null;
if (isset($_GET['edit_student'])) {
    $id = intval($_GET['edit_student']);
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$id]);
    $edit_student = $stmt->fetch();
}
if (isset($_GET['edit_staff'])) {
    $id = intval($_GET['edit_staff']);
    $stmt = $pdo->prepare("SELECT * FROM staff WHERE staff_id = ?");
    $stmt->execute([$id]);
    $edit_staff = $stmt->fetch();
}

// Handle update
if (isset($_POST['update_student'])) {
    $stmt = $pdo->prepare("UPDATE students SET first_name=?, middle_name=?, last_name=?, dob=?, form=?, stream=?, guardian_name=?, guardian_contact=? WHERE student_id=?");
    $stmt->execute([
        $_POST['first_name'], $_POST['middle_name'], $_POST['last_name'], $_POST['dob'],
        $_POST['form'], $_POST['stream'], $_POST['guardian_name'], $_POST['guardian_contact'], $_POST['student_id']
    ]);
    $msg = "<span class='success'>Student details updated.</span>";
}
if (isset($_POST['update_staff'])) {
    $stmt = $pdo->prepare("UPDATE staff SET first_name=?, last_name=?, dob=? WHERE staff_id=?");
    $stmt->execute([
        $_POST['first_name'], $_POST['last_name'], $_POST['dob'], $_POST['staff_id']
    ]);
    $msg = "<span class='success'>Staff details updated.</span>";
}

// Handle search
$search_results = [];
if (isset($_POST['search'])) {
    $type = $_POST['search_type'];
    $query = trim($_POST['query']);
    if ($type == 'student') {
        $sql = "SELECT * FROM students WHERE is_active=1 AND (admission_no LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR (form = ? AND stream = ?))";
        $params = ["%$query%", "%$query%", "%$query%", $query, $query];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $search_results = $stmt->fetchAll();
    } else {
        $sql = "SELECT * FROM staff WHERE is_active=1 AND (id_no LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
        $params = ["%$query%", "%$query%", "%$query%"];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $search_results = $stmt->fetchAll();
    }
}
?>
<hr>
<h3>Search Staff or Student</h3>
<?php if (!empty($msg)) echo $msg; ?>
<form method="POST" style="margin-bottom:20px;">
    <select name="search_type">
        <option value="student">Student</option>
        <option value="staff">Staff</option>
    </select>
    <input type="text" name="query" placeholder="Name, ID, Admission No, or Class" required>
    <button type="submit" name="search">Search</button>
</form>

<?php if ($edit_student): ?>
    <h4>Edit Student</h4>
    <form method="POST">
        <input type="hidden" name="student_id" value="<?= $edit_student['student_id'] ?>">
        First Name: <input name="first_name" value="<?= htmlspecialchars($edit_student['first_name']) ?>" required><br>
        Middle Name: <input name="middle_name" value="<?= htmlspecialchars($edit_student['middle_name']) ?>"><br>
        Last Name: <input name="last_name" value="<?= htmlspecialchars($edit_student['last_name']) ?>" required><br>
        Date of Birth: <input type="date" name="dob" value="<?= htmlspecialchars($edit_student['dob']) ?>" required><br>
        Form: <input name="form" value="<?= htmlspecialchars($edit_student['form']) ?>" required><br>
        Stream: <input name="stream" value="<?= htmlspecialchars($edit_student['stream']) ?>" required><br>
        Guardian Name: <input name="guardian_name" value="<?= htmlspecialchars($edit_student['guardian_name']) ?>" required><br>
        Guardian Contact: <input name="guardian_contact" value="<?= htmlspecialchars($edit_student['guardian_contact']) ?>" required><br>
        <button type="submit" name="update_student">Update Student</button>
    </form>
<?php elseif ($edit_staff): ?>
    <h4>Edit Staff</h4>
    <form method="POST">
        <input type="hidden" name="staff_id" value="<?= $edit_staff['staff_id'] ?>">
        First Name: <input name="first_name" value="<?= htmlspecialchars($edit_staff['first_name']) ?>" required><br>
        Last Name: <input name="last_name" value="<?= htmlspecialchars($edit_staff['last_name']) ?>" required><br>
        Date of Birth: <input type="date" name="dob" value="<?= htmlspecialchars($edit_staff['dob']) ?>" required><br>
        <button type="submit" name="update_staff">Update Staff</button>
    </form>
<?php elseif (!empty($search_results)): ?>
    <h4>Search Results</h4>
    <table border="1" cellpadding="6" style="width:100%;">
        <tr>
            <?php if ($_POST['search_type'] == 'student'): ?>
                <th>Admission No</th><th>Name</th><th>Form</th><th>Stream</th><th>Guardian</th><th>Contact</th>
            <?php else: ?>
                <th>ID No</th><th>Name</th><th>Date of Birth</th>
            <?php endif; ?>
            <th>Actions</th>
        </tr>
        <?php foreach ($search_results as $row): ?>
            <tr>
                <?php if ($_POST['search_type'] == 'student'): ?>
                    <td><?= htmlspecialchars($row['admission_no']) ?></td>
                    <td><?= htmlspecialchars($row['first_name'].' '.$row['middle_name'].' '.$row['last_name']) ?></td>
                    <td><?= htmlspecialchars($row['form']) ?></td>
                    <td><?= htmlspecialchars($row['stream']) ?></td>
                    <td><?= htmlspecialchars($row['guardian_name']) ?></td>
                    <td><?= htmlspecialchars($row['guardian_contact']) ?></td>
                    <td>
                        <a href="?edit_student=<?= $row['student_id'] ?>">Edit</a> |
                        <a href="?delete_student=<?= $row['student_id'] ?>" onclick="return confirm('Mark this student as transferred?');">Delete</a>
                    </td>
                <?php else: ?>
                    <td><?= htmlspecialchars($row['id_no']) ?></td>
                    <td><?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></td>
                    <td><?= htmlspecialchars($row['dob']) ?></td>
                    <td>
                        <a href="?edit_staff=<?= $row['staff_id'] ?>">Edit</a> |
                        <a href="?delete_staff=<?= $row['staff_id'] ?>" onclick="return confirm('Mark this staff as inactive?');">Delete</a>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
    <h3>Subjects</h3>
    <?= $add_msg ?>
    <form method="POST" style="margin-bottom: 10px;">
        <input type="text" name="new_subject" placeholder="Add new subject" required>
        <button type="submit" name="add_subject">Add Subject</button>
    </form>
    <ul class="subject-list">
        <?php foreach ($subjects as $subject): ?>
            <li>
                <?= htmlspecialchars($subject['subject_name']) ?>
                <form method="GET" style="display:inline;">
                    <input type="hidden" name="delete_subject" value="<?= $subject['subject_id'] ?>">
                    <button type="submit" onclick="return confirm('Delete this subject?');" style="color:red;">Remove</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
// Handle announcement submission
$announce_msg = '';
if (isset($_POST['post_announcement'])) {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $audience = $_POST['audience'];
    $form = !empty($_POST['form']) ? $_POST['form'] : null;
    $stream = !empty($_POST['stream']) ? $_POST['stream'] : null;

    if ($title && $message && $audience) {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, message, audience, form, stream) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $message, $audience, $form, $stream]);
        $announce_msg = "<span class='success'>Announcement posted!</span>";
    } else {
        $announce_msg = "<span class='error'>Please fill all required fields.</span>";
    }
}

// Handle timetable upload (as a file)
$timetable_msg = '';
if (isset($_POST['upload_timetable']) && isset($_FILES['timetable_file'])) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $file_name = basename($_FILES["timetable_file"]["name"]);
    $target_file = $target_dir . time() . "_" . $file_name;
    if (move_uploaded_file($_FILES["timetable_file"]["tmp_name"], $target_file)) {
        $stmt = $pdo->prepare("INSERT INTO timetable (file_path, uploaded_at) VALUES (?, NOW())");
        $stmt->execute([$target_file]);
        $timetable_msg = "<span class='success'>Timetable uploaded!</span>";
    } else {
        $timetable_msg = "<span class='error'>Failed to upload timetable.</span>";
    }
}
?>
<!-- Announcement Form -->
<hr>
<h3>Post Announcement</h3>
<?= $announce_msg ?>
<form method="POST">
    <input type="text" name="title" placeholder="Title" required><br>
    <textarea name="message" placeholder="Announcement message" required style="width:100%;height:80px;"></textarea><br>
    <label>Audience:</label>
    <select name="audience" required>
        <option value="all">All</option>
        <option value="students">Students</option>
        <option value="teachers">Teachers</option>
        <option value="parents">Parents</option>
    </select>
    <label>Form (optional):</label>
    <select name="form">
        <option value="">--Any--</option>
        <option value="1">Form 1</option>
        <option value="2">Form 2</option>
        <option value="3">Form 3</option>
        <option value="4">Form 4</option>
    </select>
    <label>Stream (optional):</label>
    <select name="stream">
        <option value="">--Any--</option>
        <option value="North">North</option>
        <option value="South">South</option>
        <option value="East">East</option>
        <option value="West">West</option>
    </select>
    <button type="submit" name="post_announcement">Post Announcement</button>
</form>

<!-- Timetable Upload -->
<hr>
<h3>Upload Timetable</h3>
<?= $timetable_msg ?>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="timetable_file" accept=".pdf,.jpg,.png,.doc,.docx" required>
    <button type="submit" name="upload_timetable">Upload Timetable</button>
</form>
   
</body>
</html>