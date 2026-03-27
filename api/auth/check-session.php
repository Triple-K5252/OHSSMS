<?php
// Start session
session_start();

// Check if user session is active
if(isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'active']);
} else {
    echo json_encode(['status' => 'inactive']);
}
?>