<?php
require_once '../config/db.php';

try {
    if (isset($_GET['search'])) {
        $search = "%" . $_GET['search'] . "%";
        $stmt = $pdo->prepare("SELECT * FROM students WHERE is_active = 1 
                             AND (admission_no LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR form LIKE ?)
                             ORDER BY admission_no ASC");
        $stmt->execute([$search, $search, $search, $search]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE is_active = 1 ORDER BY admission_no ASC");
        $stmt->execute();
    }

    $students = $stmt->fetchAll();
    respond(true, 'Students retrieved', ['students' => $students]);
} catch (Exception $e) {
    respond(false, 'Error fetching students: ' . $e->getMessage());
}
?>