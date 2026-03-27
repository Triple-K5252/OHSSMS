<?php
require_once '../config/db.php';

if ($method !== 'POST') {
    respond(false, 'Invalid request method');
}

$data = json_decode(file_get_contents('php://input'), true);
$title = $data['title'] ?? '';
$message = $data['message'] ?? '';
$target = $data['target'] ?? 'all';
$form = $data['form'] ?? null;

if (empty($title) || empty($message)) {
    respond(false, 'Title and message are required');
}

try {
    $stmt = $pdo->prepare("INSERT INTO announcements (title, message, target, form, created_at) 
                         VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$title, $message, $target, $form]);
    
    respond(true, 'Announcement created successfully');
} catch (Exception $e) {
    respond(false, 'Error: ' . $e->getMessage());
}
?>