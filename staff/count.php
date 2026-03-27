<?php
require_once '../config/db.php';

try {
    $type = $_GET['type'] ?? 'teacher';

    if ($type === 'teacher') {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM staff WHERE is_active = 1");
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM non_teaching_staff WHERE is_active = 1");
    }
    
    $stmt->execute();
    $result = $stmt->fetch();
    
    respond(true, 'Count retrieved', ['count' => $result['count']]);
} catch (Exception $e) {
    respond(false, 'Error: ' . $e->getMessage());
}
?>