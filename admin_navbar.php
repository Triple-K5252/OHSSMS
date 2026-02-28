<style>
.navbar {
    background: #007bff;
    padding: 0;
    margin-bottom: 30px;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    position: static;
}
.navbar ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
}
.navbar li {
    margin: 0;
}
.navbar a {
    display: block;
    padding: 16px 24px;
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    transition: background 0.2s;
}
.navbar a:hover, .navbar .active {
    background: #0056b3;
}
</style>
<div class="navbar">
    <ul>
        <li><a href="admin_dashboard.php" class="<?= basename($_SERVER['PHP_SELF'])=='admin_dashboard.php'?'active':'' ?>">Dashboard</a></li>
        <li><a href="register_student.php" class="<?= basename($_SERVER['PHP_SELF'])=='register_student.php'?'active':'' ?>">Register Student</a></li>
        <li><a href="register_staff.php" class="<?= basename($_SERVER['PHP_SELF'])=='register_staff.php'?'active':'' ?>">Register Staff</a></li>
        
       <br> <li><a href="index.php" style="background:#dc3545;">Logout</a></li>
    </ul>
</div>