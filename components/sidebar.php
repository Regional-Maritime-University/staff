<div class="sidebar" id="sidebar">
    <div class="logo">
        <img src="../assets/img/logo.png" alt="RMU Logo" class="logo-img">
        <h2>RMU Portal</h2>
    </div>

    <div class="user-profile">
        <div class="avatar">
            <img src="../uploads/profiles/me.jpg" alt="User Avatar">
        </div>
        <div class="user-info">
            <h3><?= $_SESSION["staff"]["first_name"] . " " . $_SESSION["staff"]["last_name"] ?></h3>
            <p>Secretary</p>
        </div>
    </div>

    <div class="menu-groups">
        <div class="menu-group">
            <h3>Main Menu</h3>
            <?php
            if ($_SESSION["staff"]["role"] == "secretary" || $_SESSION["staff"]["role"] == "hod") {
            ?>
                <div class="menu-items">
                    <a href="index.php" class="menu-item <?= $activePage == 'dashboard' ? 'active' : '' ?>">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="programs.php" class="menu-item  <?= $activePage == 'programs' ? 'active' : '' ?>">
                        <i class="fas fa-th-list"></i>
                        <span>Programs</span>
                    </a>
                    <a href="classes.php" class="menu-item  <?= $activePage == 'classes' ? 'active' : '' ?>">
                        <i class="fas fa-th-large"></i>
                        <span>Classes</span>
                    </a>
                    <a href="courses.php" class="menu-item  <?= $activePage == 'courses' ? 'active' : '' ?>">
                        <i class="fas fa-book"></i>
                        <span>Courses</span>
                    </a>
                    <a href="students.php" class="menu-item  <?= $activePage == 'students' ? 'active' : '' ?>">
                        <i class="fas fa-users"></i>
                        <span>Students</span>
                    </a>
                    <a href="lecturers.php" class="menu-item <?= $activePage == 'lecturers' ? 'active' : '' ?>">
                        <i class="fas fa-user-graduate"></i>
                        <span>Lecturers</span>
                    </a>
                    <a href="results.php" class="menu-item <?= $activePage == 'results' ? 'active' : '' ?>">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Exam Results</span>
                    </a>
                    <a href="deadlines.php" class="menu-item <?= $activePage == 'deadlines' ? 'active' : '' ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Deadlines</span>
                    </a>
                </div>
            <?php
            } else if ($_SESSION["staff"]["role"] == "lecturer") { ?>
                <div class="menu-items">
                    <a href="index.php" class="menu-item <?= $activePage == 'dashboard' ? 'active' : '' ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="courses.php" class="menu-item <?= $activePage == 'courses' ? 'active' : '' ?>">
                        <i class="fas fa-book"></i>
                        <span>My Courses</span>
                    </a>
                    <a href="results.php" class="menu-item <?= $activePage == 'results' ? 'active' : '' ?>">
                        <i class="fas fa-chart-bar"></i>
                        <span>Exam Results</span>
                    </a>
                    <a href="students.php" class="menu-item <?= $activePage == 'students' ? 'active' : '' ?>">
                        <i class="fas fa-user-graduate"></i>
                        <span>Students</span>
                    </a>
                </div>
            <?php } ?>
        </div>

        <div class="menu-group">
            <h3>Reports & Communication</h3>
            <div class="menu-items">
                <a href="#" class="menu-item <?= $activePage == 'Reports' ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
                <a href="messages.php" class="menu-item <?= $activePage == 'messages' ? 'active' : '' ?>">
                    <i class="fas fa-comments"></i>
                    <span>Messages</span>
                    <span class="badge">3</span>
                </a>
                <a href="notifications.php" class="menu-item <?= $activePage == 'notifications' ? 'active' : '' ?>">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                    <span class="badge">5</span>
                </a>
            </div>
        </div>

        <div class="menu-group">
            <h3>Settings</h3>
            <div class="menu-items">
                <a href="account.php" class="menu-item <?= $activePage == 'account' ? 'active' : '' ?>">
                    <i class="fas fa-user-cog"></i>
                    <span>Account</span>
                </a>
                <a href="?logout=true" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</div>