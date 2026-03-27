<?php
require_once '../config/db.php';

session_destroy();
respond(true, 'Logged out successfully');
?>