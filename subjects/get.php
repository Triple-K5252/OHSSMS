<?php
require_once '../config/db.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM subjects ORDER BY subject_name ASC");
    $stmt->execute();
    $subjects = $stmt->fetchAll();
    
    respond(true, 'Subjects retrieved', ['subjects' => $subjects]);
} catch (Exception $e) {
    respond(false, 'Error: ' . $e->getMessage());
}
?>