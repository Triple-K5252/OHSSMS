<?php
require_once '../config/db.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 50");
    $stmt->execute();
    $announcements = $stmt->fetchAll();
    
    respond(true, 'Announcements retrieved', ['announcements' => $announcements]);
} catch (Exception $e) {
    respond(false, 'Error: ' . $e->getMessage());
}
?>