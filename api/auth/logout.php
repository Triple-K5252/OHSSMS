<?php
// Logout function
session_start();

// Clear the session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: /login.php');
exit;
?>