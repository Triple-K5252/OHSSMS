<?php
session_start();
require_once 'config/db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'];
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Admin login (hardcoded for first time)
    if ($role === 'admin' && $username === 'admin' && $password === 'admin') {
        $_SESSION['role'] = 'admin';
        $_SESSION['username'] = 'admin';
        header('Location: admin_dashboard.php');
        exit;
    }

    // Check in users table for teacher, student, parent
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = ? AND is_active = 1");
    $stmt->execute([$username, $role]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        header('Location: ' . $user['role'] . '_dashboard.php');
        exit;
    }

    $error = "Invalid credentials or role.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>OHSSMS Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { 
            background-image: url("AI\ Images\ \(4k\)\ -\ Freepik\ \(290324469136\).jpeg");
            background-size: cover;
            background-repeat: no-repeat;

        }
        h1{
            font-size: 3rem;
            color: #080808;
            justify-content: center;
            text-align: center;
        }
        .login-container {
            background: #302e2e;
            padding: 30px 40px;
            margin: 60px auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(233, 224, 224, 0.1);
            max-width: 400px;
        }
        h2 { text-align: center; color: #e4d6d6; }
        input, select, button {
            width: 100%;
            height: 30%;
            padding: 8px;
            margin: 8px 0 16px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1.1rem;
            font-weight: bold;
        }
        button {
            background: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        button:hover { background: #0056b3; }
        .error { color: #c00; text-align: center; }
        label { 
            font-weight: bold;
            color:white;
            font-size: 1.5em;
                
    
    }
    </style>
</head>
<body>
    <h1>Online High School Student Management System</h1>
    <div class="login-container">
        <h2>Login</h2>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <label>Role:</label>
            <select name="role" required>
                <option value="admin">Admin</option>
                <option value="teacher">Teacher</option>
                <option value="student">Student</option>
                <option value="parent">Parent</option>
            </select><br>
            <label>Username:</label>
            <input type="text" name="username" required
                placeholder="Teacher ID / Admission No / Username"><br>
            <label>Password:</label>
            <input type="password" name="password" required><br>
            <button type="submit">Login</button>
        </form>
        <p style="font-size: 0.95em; color: #d4cccc; margin-top: 10px;">
            <b>Admin:</b> Use <i>admin</i> for both username and password.<br>
            <b>Teacher:</b> Use your Teacher ID.<br>
            <b>Student:</b> Use your Admission Number.<br>
        </p>
    </div>
</body>
</html>

