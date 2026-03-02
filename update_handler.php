<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $type = $_POST['type'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];

    if ($type === 'student') {
        $form = $_POST['form'];
        $stream = $_POST['stream'];
        $guardian_contact = $_POST['guardian_contact'];

        // Update student table
        $sql = "UPDATE students SET first_name = ?, last_name = ?, form = ?, stream = ?, guardian_contact = ? WHERE student_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$first_name, $last_name, $form, $stream, $guardian_contact, $id]);

    } else if ($type === 'staff') {
        $id_no = $_POST['id_no'];

        // Update staff table
        $sql = "UPDATE staff SET first_name = ?, last_name = ?, id_no = ? WHERE staff_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$first_name, $last_name, $id_no, $id]);
    }

    // Redirect back to dashboard with a success message
    header("Location: admin_dashboard.php?msg=updated");
    exit();
}