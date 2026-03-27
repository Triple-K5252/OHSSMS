<?php
require_once '../config/db.php';

if ($method !== 'POST') {
    respond(false, 'Invalid request method');
}

$data = json_decode(file_get_contents('php://input'), true);
$subject_name = $data['subject_name'] ?? '';

if (empty($subject_name)) {
    respond(false, 'Subject name is required');
}

try {
    $stmt = $pdo->prepare("INSERT INTO subjects (subject_name) VALUES (?)");
    $stmt->execute([$subject_name]);
    
    respond(true, 'Subject created successfully');
} catch (Exception $e) {
    respond(false, 'Error: ' . $e->getMessage());
}
?>