<?php
require_once '../config/db.php';

try {
    if (isset($_GET['search'])) {
        $search = "%" . $_GET['search'] . "%";
        $stmt = $pdo->prepare("SELECT s.*, sub.subject_name 
                             FROM staff s
                             LEFT JOIN teacher_subjects ts ON s.staff_id = ts.staff_id
                             LEFT JOIN subjects sub ON ts.subject_id = sub.subject_id
                             WHERE s.is_active = 1
                             AND (s.id_no LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ?)
                             ORDER BY s.id_no ASC");
        $stmt->execute([$search, $search, $search]);
    } else {
        $stmt = $pdo->prepare("SELECT s.*, sub.subject_name 
                             FROM staff s
                             LEFT JOIN teacher_subjects ts ON s.staff_id = ts.staff_id
                             LEFT JOIN subjects sub ON ts.subject_id = sub.subject_id
                             WHERE s.is_active = 1
                             ORDER BY s.id_no ASC");
        $stmt->execute();
    }

    $staff = $stmt->fetchAll();
    respond(true, 'Teachers retrieved', ['staff' => $staff]);
} catch (Exception $e) {
    respond(false, 'Error: ' . $e->getMessage());
}
?>