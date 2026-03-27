<?php
require_once '../config/db.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $stmt = $pdo->prepare("SELECT user_id, username, role FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        respond(true, 'Session valid', [
            'user' => $user
        ]);
    }
}

respond(false, 'No active session');
?>