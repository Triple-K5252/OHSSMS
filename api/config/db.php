<?php

// Database configuration
$host = '127.0.0.1'; // Database host
$user = 'username'; // Database username
$password = 'password'; // Database password
$dbname = 'database_name'; // Database name

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
echo 'Connected successfully';
?>