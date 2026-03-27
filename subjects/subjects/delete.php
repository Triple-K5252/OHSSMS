<?php
require_once '../config/db.php';

if ($method !== 'DELETE') {
    respond(false, 'Invalid request method');
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? '';

if (empty($id)) {
    respond(false, 'Missing subject ID');
}

try {
    $stmt = $pdo->prepare("DELETE FROM subjects WHERE subject_id = ?");
    $stmt->execute([$id]);
    
    respond(true, 'Subject deleted successfully');
} catch (Exception $e) {
    respond(false, 'Error: ' . $e->getMessage());
}
?>