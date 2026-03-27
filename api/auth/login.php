<?php
require_once '../config/db.php';

if ($method !== 'POST') {
    respond(false, 'Invalid request method');
}

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';
$role = $data['role'] ?? '';

// Validate input
if (empty($username) || empty($password) || empty($role)) {
    respond(false, 'Missing required fields');
}

// Admin hardcoded login
if ($role === 'admin' && $username === 'admin' && $password === 'admin123') {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['role'] = 'admin';
    
    respond(true, 'Login successful', [
        'user' => [
            'id' => 1,
            'username' => 'admin',
            'role' => 'admin'
        ]
    ]);
}

// Check in users table
try {
    $stmt = $pdo->prepare("
        SELECT user_id, username, role
        FROM users
        WHERE username = ? AND role = ? AND is_active = 1
    ");
    
    $stmt->execute([$username, $role]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        respond(true, 'Login successful', [
            'user' => [
                'id' => $user['user_id'],
                'username' => $user['username'],
                'role' => $user['role']
            ]
        ]);
    } else {
        respond(false, 'Invalid credentials');
    }
} catch (Exception $e) {
    respond(false, $e->getMessage());
}
?>