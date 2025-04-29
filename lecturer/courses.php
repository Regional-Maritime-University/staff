<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Lecturer Portal - My Courses</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="./css/courses.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="logo.png" alt="RMU Logo" class="logo-img">
            <h2>RMU Portal</h2>
        </div>
        <div class="user-profile">
            <div class="avatar">
                <img src="avatar.jpg" alt="User Avatar">
            </div>
            <div class="user-info">
                <h3>Dr. John Doe</h3>
                <p>Lecturer</p>
            </div>
        </div>
        <div class="menu-groups">
            <div class="menu-group">
                <h3>Main Menu</h3>
                <div class="menu-items">
                    <a href="dashboard.html" class="menu-item">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="courses.html" class="menu-item active">
                        <i class="fas fa-book"></i>
                        <span>My Courses</span>
                    </a>
                    <a href="results.html" class="menu-item">
                        <i class="fas fa-chart-bar"></i>
                        <span>Exam Results</span>
                    </a>
                    <a href="students.html" class="menu-item">
                        <i class="fas fa-user-graduate"></i>
                        <span>Students</span>
                    </a>
                </div>
            </div>
            <div class="menu-group">
                <h3>Communication</h3>
                <div class="menu-items">
                    <a href="messages.html" class="menu-item">
                        <i class="fas fa-envelope"></i>
                        <span>Messages</span>
                        <span class="badge">3</span>
                    </a>
                    <a href="notifications.html" class="menu-item">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
                        <span class="badge">7</span>
                    </a>
                </div>
            </div>
            <div class="menu-group">
                <h3>Settings</h3>
                <div class="menu-items">
                    <a href="profile.html" class="menu-item">
                        <i class="fas fa-user-cog"></i>
                        <span>Profile</span>
                    </a>
                    <a href="change-password.html" class="menu-item">
                        <i class="fas fa-key"></i>
                        <span>Change Password</span>
                    </a>
                    <a href="login.html" class="menu-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <button class="toggle-sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>My Courses</h1>
            </div>
            <div class="header-right">
                <div class="search-bar">
                    <input type="text" placeholder="Search courses...">
                    <button class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="header-actions">
                    <button class="action-btn">
                        <i class="fas fa-bell"></i>
                        <span class="badge">7</span>
                    </button>
                    <button class="action-btn">
                        <i class="fas fa-envelope"></i>
                        <span class="badge">3</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="courses-content">
            <!-- Semester Selector -->
            <div class="semester-selector">
                <h3>Select Semester</h3>
                <div class="semester-options">
                    <div class="semester-option active" data-semester="current">First Semester 2023/2024</div>
                    <div class="semester-option" data-semester="previous">Second Semester 2022/2023</div>
                    <div class="semester-option" data-semester="upcoming">Second Semester 2023/2024</div>
                    <div class="semester-option" data-semester="all">All Semesters</div>
                </div>
            </div>

            <!-- Course Grid -->
            <div class="course-grid">
                <!-- Course Card 1 -->
                <div class="course-card">
                    <div class="course-header">
                        <div class="course-title">Introduction to Marine Engineering</div>
                        <div class="course-code">ME101</div>
                        <span class="course-status active">Active</span>
                    </div>
                    <div class="course-body">
                        <div class="course-details">
                            <div class="detail-item">
                                <div class="detail-label">Department</div>
                                <div class="detail-value">Marine Engineering</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Level</div>
                                <div class="detail-value">100</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Students</div>
                                <div class="detail-value">45</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Schedule</div>
                                <div class="detail-value">Mon, Wed 9:00-10:30 AM</div>
                            </div>
                        </div>
                        <div class="course-progress">
                            <div class="progress-label">
                                <span>Course Progress</span>
                                <span>60%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 60%;"></div>
                            </div>
                        </div>
                        <div class="course-actions">
                            <button class="course-btn primary">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <button class="course-btn secondary">
                                <i class="fas fa-download"></i> Resources
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Course Card 2 -->
                <div class="course-card">
                    <div class="course-header">
                        <div class="course-title">Marine Propulsion Systems</div>
                        <div class="course-code">ME302</div>
                        <span class="course-status active">Active</span>
                    </div>
                    <div class="course-body">
                        <div class="course-details">
                            <div class="detail-item">
                                <div class="detail-label">Department</div>
                                <div class="detail-value">Marine Engineering</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Level</div>
                                <div class="detail-value">300</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Students</div>
                                <div class="detail-value">32</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Schedule</div>
                                <div class="detail-value">Tue, Thu 1:00-3:00 PM</div>
                            </div>
                        </div>
                        <div class="course-progress">
                            <div class="progress-label">
                                <span>Course Progress</span>
                                <span>45%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 45%;"></div>
                            </div>
                        </div>
                        <div class="course-actions">
                            <button class="course-btn primary">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <button class="course-btn secondary">
                                <i class="fas fa-download"></i> Resources
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Course Card 3 -->
                <div class="course-card">
                    <div class="course-header">
                        <div class="course-title">Ship Design and Construction</div>
                        <div class="course-code">ME405</div>
                        <span class="course-status active">Active</span>
                    </div>
                    <div class="course-body">
                        <div class="course-details">
                            <div class="detail-item">
                                <div class="detail-label">Department</div>
                                <div class="detail-value">Marine Engineering</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Level</div>
                                <div class="detail-value">400</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Students</div>
                                <div class="detail-value">28</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Schedule</div>
                                <div class="detail-value">Fri 9:00-12:00 PM</div>
                            </div>
                        </div>
                        <div class="course-progress">
                            <div class="progress-label">
                                <span>Course Progress</span>
                                <span>30%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 30%;"></div>
                            </div>
                        </div>
                        <div class="course-actions">
                            <button class="course-btn primary">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <button class="course-btn secondary">
                                <i class="fas fa-download"></i> Resources
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Course Card 4 (Previous Semester) -->
                <div class="course-card previous-semester" style="display: none;">
                    <div class="course-header">
                        <div class="course-title">Fluid Mechanics for Marine Engineers</div>
                        <div class="course-code">ME203</div>
                        <span class="course-status completed">Completed</span>
                    </div>
                    <div class="course-body">
                        <div class="course-details">
                            <div class="detail-item">
                                <div class="detail-label">Department</div>
                                <div class="detail-value">Marine Engineering</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Level</div>
                                <div class="detail-value">200</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Students</div>
                                <div class="detail-value">38</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Semester</div>
                                <div class="detail-value">2022/2023 Second</div>
                            </div>
                        </div>
                        <div class="course-actions">
                            <button class="course-btn primary">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <button class="course-btn secondary">
                                <i class="fas fa-download"></i> Resources
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Course Card 5 (Upcoming Semester) -->
                <div class="course-card upcoming-semester" style="display: none;">
                    <div class="course-header">
                        <div class="course-title">Advanced Marine Engineering</div>
                        <div class="course-code">ME501</div>
                        <span class="course-status upcoming">Upcoming</span>
                    </div>
                    <div class="course-body">
                        <div class="course-details">
                            <div class="detail-item">
                                <div class="detail-label">Department</div>
                                <div class="detail-value">Marine Engineering</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Level</div>
                                <div class="detail-value">500</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Students</div>
                                <div class="detail-value">TBD</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Semester</div>
                                <div class="detail-value">2023/2024 Second</div>
                            </div>
                        </div>
                        <div class="course-actions">
                            <button class="course-btn primary">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <button class="course-btn secondary">
                                <i class="fas fa-download"></i> Resources
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle sidebar
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('expanded');
        });

        // Semester selector functionality
        document.querySelectorAll('.semester-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remove active class from all options
                document.querySelectorAll('.semester-option').forEach(opt => {
                    opt.classList.remove('active');
                });

                // Add active class to clicked option
                this.classList.add('active');

                // Get selected semester
                const semester = this.getAttribute('data-semester');

                // Show/hide courses based on selected semester
                const currentCourses = document.querySelectorAll('.course-card:not(.previous-semester):not(.upcoming-semester)');
                const previousCourses = document.querySelectorAll('.course-card.previous-semester');
                const upcomingCourses = document.querySelectorAll('.course-card.upcoming-semester');

                if (semester === 'current') {
                    currentCourses.forEach(course => course.style.display = 'block');
                    previousCourses.forEach(course => course.style.display = 'none');
                    upcomingCourses.forEach(course => course.style.display = 'none');
                } else if (semester === 'previous') {
                    currentCourses.forEach(course => course.style.display = 'none');
                    previousCourses.forEach(course => course.style.display = 'block');
                    upcomingCourses.forEach(course => course.style.display = 'none');
                } else if (semester === 'upcoming') {
                    currentCourses.forEach(course => course.style.display = 'none');
                    previousCourses.forEach(course => course.style.display = 'none');
                    upcomingCourses.forEach(course => course.style.display = 'block');
                } else {
                    currentCourses.forEach(course => course.style.display = 'block');
                    previousCourses.forEach(course => course.style.display = 'block');
                    upcomingCourses.forEach(course => course.style.display = 'block');
                }
            });
        });

        // View Details button functionality
        document.querySelectorAll('.course-btn.primary').forEach(button => {
            button.addEventListener('click', function() {
                const courseCard = this.closest('.course-card');
                const courseCode = courseCard.querySelector('.course-code').textContent;
                window.location.href = `course-details.html?code=${courseCode}`;
            });
        });

        // Resources button functionality
        document.querySelectorAll('.course-btn.secondary').forEach(button => {
            button.addEventListener('click', function() {
                const courseCard = this.closest('.course-card');
                const courseCode = courseCard.querySelector('.course-code').textContent;
                window.location.href = `course-resources.html?code=${courseCode}`;
            });
        });
    </script>
</body>

</html>