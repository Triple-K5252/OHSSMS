<?php

// Database configuration
$host = '127.0.0.1'; // Database host
$user = 'root'; // Database username (update as needed)
$password = ''; // Database password (update as needed)
$dbname = 'ohssms'; // Database name

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Start session
session_start();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Response helper function
function respond($success, $message, $data = null) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

?>