<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $type = $_POST['type'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];

    try {
        $pdo->beginTransaction();

        if ($type === 'student') {
            $form = $_POST['form'];
            $stream = $_POST['stream'];
            $guardian_contact = $_POST['guardian_contact'];
            $selected_subjects = isset($_POST['student_subjects']) ? $_POST['student_subjects'] : [];

            // 1. Update student table
            $sql = "UPDATE students SET first_name = ?, last_name = ?, form = ?, stream = ?, guardian_contact = ? WHERE student_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$first_name, $last_name, $form, $stream, $guardian_contact, $id]);

            // 2. Clear existing subjects for this student
            $delete_stmt = $pdo->prepare("DELETE FROM student_subjects WHERE student_id = ?");
            $delete_stmt->execute([$id]);

            // 3. Add the newly selected subjects
            if (!empty($selected_subjects)) {
                $insert_stmt = $pdo->prepare("INSERT INTO student_subjects (student_id, subject_id) VALUES (?, ?)");
                foreach ($selected_subjects as $subject_id) {
                    $insert_stmt->execute([$id, $subject_id]);
                }
            }

        } else if ($type === 'staff') {
            $id_no = $_POST['id_no'];

            // Update staff table
            $sql = "UPDATE staff SET first_name = ?, last_name = ?, id_no = ? WHERE staff_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$first_name, $last_name, $id_no, $id]);
        }

        $pdo->commit();
        header("Location: admin_dashboard.php?msg=updated");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error updating record: " . $e->getMessage();
    }
} else {
    header("Location: admin_dashboard.php");
    exit();
}