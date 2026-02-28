<!-- navbar.php -->
<style>
.navbar {
    background: #007bff;
    padding: 0;
    margin-bottom: 30px;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
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
        <li><a href="teacher_dashboard.php" class="<?= basename($_SERVER['PHP_SELF'])=='teacher_dashboard.php'?'active':'' ?>">Dashboard</a></li>
        <li><a href="attendance.php" class="<?= basename($_SERVER['PHP_SELF'])=='attendance.php'?'active':'' ?>">Attendance</a></li>
        <li><a href="gradebook.php" class="<?= basename($_SERVER['PHP_SELF'])=='gradebook.php'?'active':'' ?>">Gradebook</a></li>
        <li><a href="announcements.php" class="<?= basename($_SERVER['PHP_SELF'])=='announcements.php'?'active':'' ?>">Announcements</a></li>
        <li><a href="report.php" class="<?= basename($_SERVER['PHP_SELF'])=='report.php'?'active':'' ?>">Reports</a></li>
        <li><a href="class_list.php" class="<?= basename($_SERVER['PHP_SELF'])=='class_list.php'?'active':'' ?>">Class List</a></li>
        <li style="margin-left:auto;"><a href="index.php" style="background:#dc3545;">Logout</a></li>
    </ul>
</div>
