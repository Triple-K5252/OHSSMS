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
        <li><a href="student_dashboard.php" class="<?= basename($_SERVER['PHP_SELF'])=='student_dashboard.php'?'active':'' ?>">Dashboard</a></li>
        <li><a href="profile.php" class="<?= basename($_SERVER['PHP_SELF'])=='profile.php'?'active':'' ?>">Profile</a></li>
        <li><a href="attendance_student.php" class="<?= basename($_SERVER['PHP_SELF'])=='attendance_student.php'?'active':'' ?>">Attendance</a></li>
        <li><a href="grades.php" class="<?= basename($_SERVER['PHP_SELF'])=='grades.php'?'active':'' ?>">Grades</a></li>
        <li><a href="report_card.php" class="<?= basename($_SERVER['PHP_SELF'])=='report_card.php'?'active':'' ?>">Report Card</a></li>
        <li><a href="announcements.php" class="<?= basename($_SERVER['PHP_SELF'])=='announcements.php'?'active':'' ?>">Announcements</a></li>
        <li style="margin-left:auto;"><a href="index.php" style="background:#dc3545;">Logout</a></li>
    </ul>
</div>