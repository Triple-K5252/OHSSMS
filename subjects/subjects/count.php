<?php
require_once '../config/db.php';

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM subjects");
    $stmt->execute();
    $result = $stmt->fetch();
    
    respond(true, 'Count retrieved', ['count' => $result['count']]);
} catch (Exception $e) {
    respond(false, 'Error: ' . $e->getMessage());
}
?>