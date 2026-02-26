<nav class="sidebar" id="sidebar" role="navigation" aria-label="Main navigation">
    <div class="logo">
        <img src="../assets/img/logo.png" alt="RMU Logo" class="logo-img">
        <h2>RMU Portal</h2>
    </div>

    <div class="user-profile">
        <div class="avatar">
            <img src="../uploads/profiles/me.jpg" alt="User Avatar">
        </div>
        <div class="user-info">
            <h3><?= $_SESSION["staff"]["prefix"] . " " .  $_SESSION["staff"]["first_name"] . " " . $_SESSION["staff"]["last_name"] ?></h3>
            <p><?= ucfirst($_SESSION["staff"]["role"]) ?></p>
        </div>
    </div>

    <div class="menu-groups">
        <div class="menu-group">
            <h3>Main Menu</h3>
            <?php
            if ($_SESSION["staff"]["role"] == "secretary" || $_SESSION["staff"]["role"] == "hod") {
            ?>
                <div class="menu-items">
                    <a href="index.php" class="menu-item <?= $activePage == 'dashboard' ? 'active' : '' ?>" <?= $activePage == 'dashboard' ? 'aria-current="page"' : '' ?>>
                        <i class="fas fa-home" aria-hidden="true"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="programs.php" class="menu-item  <?= $activePage == 'programs' ? 'active' : '' ?>" <?= $activePage == 'programs' ? 'aria-current="page"' : '' ?>>
                        <i class="fas fa-th-list" aria-hidden="true"></i>
                        <span>Programs</span>
                    </a>
                    <a href="courses.php" class="menu-item  <?= $activePage == 'courses' ? 'active' : '' ?>" <?= $activePage == 'courses' ? 'aria-current="page"' : '' ?>>
                        <i class="fas fa-book" aria-hidden="true"></i>
                        <span>Courses</span>
                    </a>
                    <a href="classes.php" class="menu-item  <?= $activePage == 'classes' ? 'active' : '' ?>" <?= $activePage == 'classes' ? 'aria-current="page"' : '' ?>>
                        <i class="fas fa-th-large" aria-hidden="true"></i>
                        <span>Classes</span>
                    </a>
                    <a href="students.php" class="menu-item  <?= $activePage == 'students' ? 'active' : '' ?>" <?= $activePage == 'students' ? 'aria-current="page"' : '' ?>>
                        <i class="fas fa-users" aria-hidden="true"></i>
                        <span>Students</span>
                    </a>
                    <a href="lecturers.php" class="menu-item <?= $activePage == 'lecturers' ? 'active' : '' ?>" <?= $activePage == 'lecturers' ? 'aria-current="page"' : '' ?>>
                        <i class="fas fa-user-graduate" aria-hidden="true"></i>
                        <span>Lecturers</span>
                    </a>
                    <a href="results.php" class="menu-item <?= $activePage == 'results' ? 'active' : '' ?>" <?= $activePage == 'results' ? 'aria-current="page"' : '' ?>>
                        <i class="fas fa-clipboard-check" aria-hidden="true"></i>
                        <span>Exam Results</span>
                    </a>
                </div>
            <?php
            } else if ($_SESSION["staff"]["role"] == "lecturer") { ?>
                <div class="menu-items">
                    <a href="index.php" class="menu-item <?= $activePage == 'dashboard' ? 'active' : '' ?>" <?= $activePage == 'dashboard' ? 'aria-current="page"' : '' ?>>
                        <i class="fas fa-tachometer-alt" aria-hidden="true"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="courses.php" class="menu-item <?= $activePage == 'courses' ? 'active' : '' ?>" <?= $activePage == 'courses' ? 'aria-current="page"' : '' ?>>
                        <i class="fas fa-book" aria-hidden="true"></i>
                        <span>My Courses</span>
                    </a>
                    <a href="results.php" class="menu-item <?= $activePage == 'results' ? 'active' : '' ?>" <?= $activePage == 'results' ? 'aria-current="page"' : '' ?>>
                        <i class="fas fa-chart-bar" aria-hidden="true"></i>
                        <span>Exam Results</span>
                    </a>
                </div>
            <?php } ?>
        </div>

        <div class="menu-group">
            <h3>Reports & Communication</h3>
            <div class="menu-items">

                <?php
                if ($_SESSION["staff"]["role"] == "secretary" || $_SESSION["staff"]["role"] == "hod") {
                ?>
                    <a href="#" class="menu-item <?= $activePage == 'Reports' ? 'active' : '' ?>" <?= $activePage == 'Reports' ? 'aria-current="page"' : '' ?>>
                        <i class="fas fa-chart-bar" aria-hidden="true"></i>
                        <span>Reports</span>
                    </a>
                <?php } ?>
                <a href="messages.php" class="menu-item <?= $activePage == 'messages' ? 'active' : '' ?>" <?= $activePage == 'messages' ? 'aria-current="page"' : '' ?>>
                    <i class="fas fa-comments" aria-hidden="true"></i>
                    <span>Messages</span>
                    <span class="badge" aria-label="3 unread messages">3</span>
                </a>
                <a href="notifications.php" class="menu-item <?= $activePage == 'notifications' ? 'active' : '' ?>" <?= $activePage == 'notifications' ? 'aria-current="page"' : '' ?>>
                    <i class="fas fa-bell" aria-hidden="true"></i>
                    <span>Notifications</span>
                    <span class="badge" aria-label="5 unread notifications">5</span>
                </a>
            </div>
        </div>

        <div class="menu-group">
            <h3>Settings</h3>
            <div class="menu-items">
                <a href="account.php" class="menu-item <?= $activePage == 'account' ? 'active' : '' ?>" <?= $activePage == 'account' ? 'aria-current="page"' : '' ?>>
                    <i class="fas fa-user-cog" aria-hidden="true"></i>
                    <span>Account</span>
                </a>
                <a href="?logout=true" class="menu-item">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</nav>
