<?php
// register_non_teaching_staff.php

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_number = $_POST['id_number'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $department = $_POST['department'];
    $designation = $_POST['designation'];

    // TODO: Process the data, save to database, etc.
    echo "Non-teaching staff registered successfully!";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Non-Teaching Staff</title>
</head>
<body>
    <h1>Register Non-Teaching Staff</h1>
    <form method="POST" action="register_non_teaching_staff.php">
        <label for="id_number">ID Number:</label><br>
        <input type="text" id="id_number" name="id_number" required><br><br>
        <label for="first_name">First Name:</label><br>
        <input type="text" id="first_name" name="first_name" required><br><br>
        <label for="last_name">Last Name:</label><br>
        <input type="text" id="last_name" name="last_name" required><br><br>
        <label for="date_of_birth">Date of Birth:</label><br>
        <input type="date" id="date_of_birth" name="date_of_birth" required><br><br>
        <label for="department">Department:</label><br>
        <input type="text" id="department" name="department" required><br><br>
        <label for="designation">Designation:</label><br>
        <input type="text" id="designation" name="designation" required><br><br>
        <input type="submit" value="Register">
    </form>
</body>
</html>