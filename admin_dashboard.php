<?php
session_start();
require_once 'config/db.php';
$edit_data = null;
if (isset($_GET['edit_student'])) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$_GET['edit_student']]);
    $edit_data = $stmt->fetch();
    $edit_type = 'student';
} elseif (isset($_GET['edit_staff'])) {
    $stmt = $pdo->prepare("SELECT * FROM staff WHERE staff_id = ?");
    $stmt->execute([$_GET['edit_staff']]);
    $edit_data = $stmt->fetch();
    $edit_type = 'staff';
}

// Handle Search Logic
$search_results = [];
if (isset($_POST['search'])) {
    $type = $_POST['search_type'];
    $query = trim($_POST['query']);
    $searchTerm = "%$query%";

    if ($type == 'student') {
        $sql = "SELECT * FROM students WHERE is_active=1 AND (admission_no LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR form LIKE ? OR stream LIKE ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        $search_results = $stmt->fetchAll();
    } else {
        $sql = "SELECT * FROM staff WHERE is_active=1 AND (id_no LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $search_results = $stmt->fetchAll();
    }
}

// Fetch subjects for the management list
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY subject_name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - OHSSMS</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .dashboard-container { padding: 20px; max-width: 1200px; margin: auto; }
        .dashboard-box { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        .btn-delete { background: #ff4d4d; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-edit { background: #3498db; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-weight: bold; margin-right: 5px; font-size: 13px; display: inline-block; }
        .chart-container { position: relative; height:250px; width:250px; margin: 0 auto 30px auto; }
    </style>
</head>
<body>
    <?php include 'admin_navbar.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-box">
            <h2>System Overview</h2>
            <div class="chart-container">
                <canvas id="ohssmsChart"></canvas>
            </div>
        </div>
 
        <?php if ($edit_data): ?>
<div class="dashboard-box" style="border: 2px solid #3498db; background: #f0f7ff;">
    <h3>Edit Details for: <?= htmlspecialchars($edit_data['first_name']) ?></h3>
    <form method="POST" action="update_handler.php">
        <input type="hidden" name="id" value="<?= $edit_type == 'student' ? $edit_data['student_id'] : $edit_data['staff_id'] ?>">
        <input type="hidden" name="type" value="<?= $edit_type ?>">
        
        <label>First Name:</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($edit_data['first_name']) ?>" required style="width:100%; padding:8px; margin-bottom:10px;">
        
        <label>Last Name:</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($edit_data['last_name']) ?>" required style="width:100%; padding:8px; margin-bottom:10px;">

        <?php if ($edit_type == 'student'): ?>
            <label>Form:</label>
            <input type="text" name="form" value="<?= htmlspecialchars($edit_data['form']) ?>" style="width:100%; padding:8px; margin-bottom:10px;">
            <label>Stream:</label>
            <input type="text" name="stream" value="<?= htmlspecialchars($edit_data['stream']) ?>" style="width:100%; padding:8px; margin-bottom:10px;">
            <label>Guardian Contact:</label>
            <input type="text" name="guardian_contact" value="<?= htmlspecialchars($edit_data['guardian_contact']) ?>" style="width:100%; padding:8px; margin-bottom:10px;">
        <?php else: ?>
            <label>ID Number:</label>
            <input type="text" name="id_no" value="<?= htmlspecialchars($edit_data['id_no']) ?>" style="width:100%; padding:8px; margin-bottom:10px;">
        <?php endif; ?>

        <button type="submit" name="update" style="background: #27ae60; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px;">Save Changes</button>
        <a href="admin_dashboard.php" style="margin-left:10px; color: #e74c3c;">Cancel</a>
    </form>
</div>
<?php endif; ?>
        <div class="dashboard-box">
            <h3>Search Records</h3>
            <form method="POST">
                <select name="search_type" style="width: 100%; padding: 10px; margin-bottom: 10px;">
                    <option value="student">Student</option>
                    <option value="staff">Staff</option>
                </select>
                <input type="text" name="query" placeholder="Search by name, ID, or Class..." style="width: 100%; padding: 10px; margin-bottom: 10px;">
                <button type="submit" name="search" style="width: 100%; padding: 10px; background: #2c3e50; color: white; border: none; cursor: pointer;">Search</button>
            </form>

            <?php if (!empty($search_results)): ?>
                <table>
                    <thead>
                        <tr>
                            <?php if ($_POST['search_type'] == 'student'): ?>
                                <th>Adm No</th><th>Name</th><th>Class</th><th>Actions</th>
                            <?php else: ?>
                                <th>ID No</th><th>Name</th><th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($search_results as $row): ?>
                            <tr>
                                <?php if ($_POST['search_type'] == 'student'): ?>
                                    <td><?= htmlspecialchars($row['admission_no']) ?></td>
                                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                    <td><?= htmlspecialchars($row['form'] . ' ' . $row['stream']) ?></td>
                                    <td>
                                        <a href="?edit_student=<?= $row['student_id'] ?>" class="btn-edit">Edit</a>
                                        <button class="btn-delete" onclick="confirmAction('student', <?= $row['student_id'] ?>)">Delete</button>
                                    </td>
                                <?php else: ?>
                                    <td><?= htmlspecialchars($row['id_no']) ?></td>
                                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                    <td>
                                        <a href="?edit_staff=<?= $row['staff_id'] ?>" class="btn-edit">Edit</a>
                                        <button class="btn-delete" onclick="confirmAction('staff', <?= $row['staff_id'] ?>)">Delete</button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="dashboard-box">
            <h3>Manage Subjects</h3>
            <form method="POST" action="add_subject.php">
                <input type="text" name="subject_name" placeholder="New Subject Name" required style="width: 70%; padding: 10px;">
                <button type="submit" style="padding: 10px;">Add Subject</button>
            </form>
            <ul style="list-style: none; padding: 0; margin-top: 15px;">
                <?php foreach ($subjects as $subject): ?>
                    <li style="padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between;">
                        <?= htmlspecialchars($subject['subject_name']) ?>
                        <button class="btn-delete" onclick="confirmAction('subject', <?= $subject['subject_id'] ?>)">Remove</button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <script>
        // 1. Initialize the Chart
        const ctx = document.getElementById('ohssmsChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Students', 'Staff'],
                datasets: [{
                    data: [10, 5], // You can replace these with real counts later
                    backgroundColor: ['#3498db', '#f39c12']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // 2. SweetAlert Delete Confirmation
        function confirmAction(type, id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff4d4d',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `delete_handler.php?type=${type}&id=${id}`;
                }
            });
        }
    </script>
</body>
</html>