<?php
require_once '../config/db.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM non_teaching_staff WHERE is_active = 1 ORDER BY id_no ASC");
    $stmt->execute();
    $staff = $stmt->fetchAll();
    
    respond(true, 'NTS retrieved', ['staff' => $staff]);
} catch (Exception $e) {
    respond(false, 'Error: ' . $e->getMessage());
}
?>