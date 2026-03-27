<?php
require_once '../config/db.php';

if ($method !== 'POST') {
    respond(false, 'Invalid request method');
}

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
        // Create user
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

        respond(true, 'Teacher registered successfully');

    } elseif ($type === 'nts') {
        // Create user
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
    } else {
        respond(false, 'Invalid registration type');
    }
} catch (Exception $e) {
    respond(false, $e->getMessage());
}
?>