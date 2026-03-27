<?php
require_once '../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$type = $data['type'] ?? '';

try {
    if ($type === 'student') {
        // Create user
        $username = $data['admission_no'];
        $password = password_hash($data['admission_no'], PASSWORD_BCRYPT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, is_active) 
                             VALUES (?, ?, 'student', 1)");
        $stmt->execute([$username, $password]);
        $user_id = $pdo->lastInsertId();

        // Create student
        $stmt = $pdo->prepare("INSERT INTO students (user_id, admission_no, first_name, last_name, dob, form, stream, guardian_name, guardian_contact, is_active) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([
            $user_id,
            $data['admission_no'],
            $data['first_name'],
            $data['last_name'],
            $data['dob'],
            $data['form'],
            $data['stream'],
            $data['guardian_name'],
            $data['guardian_contact']
        ]);

        respond(true, 'Student registered successfully');

    } elseif ($type === 'teacher') {
        // Similar logic for teacher
        $username = $data['id_no'];
        $password = password_hash($data['password'], PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, is_active) 
                             VALUES (?, ?, 'teacher', 1)");
        $stmt->execute([$username, $password]);
        $user_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO staff (user_id, id_no, first_name, last_name, dob, is_active) 
                             VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([
            $user_id,
            $data['id_no'],
            $data['first_name'],
            $data['last_name'],
            $data['dob']
        ]);
        $staff_id = $pdo->lastInsertId();

        // Add subject
        if (!empty($data['subject_id'])) {
            $stmt = $pdo->prepare("INSERT INTO teacher_subjects (staff_id, subject_id) VALUES (?, ?)");
            $stmt->execute([$staff_id, $data['subject_id']]);
        }

        respond(true, 'Teacher registered successfully');

    } elseif ($type === 'nts') {
        // Similar logic for NTS
        $username = $data['id_no'];
        $password = password_hash($data['password'], PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, is_active) 
                             VALUES (?, ?, 'nts', 1)");
        $stmt->execute([$username, $password]);
        $user_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO non_teaching_staff (user_id, id_no, first_name, last_name, position, is_active) 
                             VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([
            $user_id,
            $data['id_no'],
            $data['first_name'],
            $data['last_name'],
            $data['position']
        ]);

        respond(true, 'Non-Teaching Staff registered successfully');
    }
} catch (Exception $e) {
    respond(false, $e->getMessage());
}
?>