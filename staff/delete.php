<?php
require_once '../config/db.php';

if ($method !== 'DELETE') {
    respond(false, 'Invalid request method');
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? '';
$type = $data['type'] ?? '';

if (empty($id) || empty($type)) {
    respond(false, 'Missing required fields');
}

try {
    if ($type === 'teacher') {
        $stmt = $pdo->prepare("UPDATE staff SET is_active = 0 WHERE staff_id = ?");
    } else {
        $stmt = $pdo->prepare("UPDATE non_teaching_staff SET is_active = 0 WHERE nts_id = ?");
    }
    
    $stmt->execute([$id]);
    respond(true, 'Staff member deleted successfully');
} catch (Exception $e) {
    respond(false, 'Error: ' . $e->getMessage());
}
?>