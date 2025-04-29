<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Lecturer Portal - Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="./css/dashboard.css">
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
                    <a href="dashboard.html" class="menu-item active">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="courses.html" class="menu-item">
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
                <h1>Dashboard</h1>
            </div>
            <div class="header-right">
                <div class="search-bar">
                    <input type="text" placeholder="Search...">
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

        <div class="dashboard-content">
            <!-- Welcome Card -->
            <div class="welcome-card">
                <div class="welcome-text">
                    <h2>Welcome back, Dr. John Doe!</h2>
                    <p>Here's what's happening with your courses today.</p>
                    <div class="welcome-actions">
                        <button class="welcome-btn primary">
                            <i class="fas fa-book"></i> View My Courses
                        </button>
                        <button class="welcome-btn secondary">
                            <i class="fas fa-upload"></i> Upload Results
                        </button>
                    </div>
                </div>
                <img src="welcome.svg" alt="Welcome" class="welcome-image">
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon courses">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3>5</h3>
                        <p>Active Courses</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon students">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-info">
                        <h3>187</h3>
                        <p>Total Students</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon results">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="stat-info">
                        <h3>3</h3>
                        <p>Pending Results</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon messages">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-info">
                        <h3>10</h3>
                        <p>New Messages</p>
                    </div>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Courses Section -->
                <div class="courses-section">
                    <div class="section-header">
                        <h2>My Courses</h2>
                        <button class="view-all-btn">View All</button>
                    </div>
                    <div class="course-list">
                        <div class="course-item">
                            <div class="course-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="course-details">
                                <h4>Introduction to Marine Engineering <span class="course-code">ME101</span></h4>
                                <p>First Semester 2023/2024</p>
                                <div class="course-meta">
                                    <span><i class="fas fa-user-graduate"></i> 45 Students</span>
                                    <span><i class="fas fa-clock"></i> Mon, Wed 9:00-10:30 AM</span>
                                </div>
                            </div>
                        </div>
                        <div class="course-item">
                            <div class="course-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="course-details">
                                <h4>Marine Propulsion Systems <span class="course-code">ME302</span></h4>
                                <p>First Semester 2023/2024</p>
                                <div class="course-meta">
                                    <span><i class="fas fa-user-graduate"></i> 32 Students</span>
                                    <span><i class="fas fa-clock"></i> Tue, Thu 1:00-3:00 PM</span>
                                </div>
                            </div>
                        </div>
                        <div class="course-item">
                            <div class="course-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="course-details">
                                <h4>Ship Design and Construction <span class="course-code">ME405</span></h4>
                                <p>First Semester 2023/2024</p>
                                <div class="course-meta">
                                    <span><i class="fas fa-user-graduate"></i> 28 Students</span>
                                    <span><i class="fas fa-clock"></i> Fri 9:00-12:00 PM</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications Section -->
                <div class="notifications-section">
                    <div class="section-header">
                        <h2>Recent Notifications</h2>
                        <button class="view-all-btn">View All</button>
                    </div>
                    <div class="notification-list">
                        <div class="notification-item unread">
                            <div class="notification-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="notification-details">
                                <h4>Results Submission Reminder</h4>
                                <p>Please submit the exam results for ME302 by December 20, 2023.</p>
                                <div class="notification-time">2 hours ago</div>
                            </div>
                        </div>
                        <div class="notification-item unread">
                            <div class="notification-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="notification-details">
                                <h4>Department Meeting</h4>
                                <p>There will be a department meeting on December 15, 2023 at 10:00 AM.</p>
                                <div class="notification-time">Yesterday</div>
                            </div>
                        </div>
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="notification-details">
                                <h4>Course Outline Updated</h4>
                                <p>The course outline for ME101 has been updated. Please review the changes.</p>
                                <div class="notification-time">3 days ago</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages Section -->
                <div class="messages-section">
                    <div class="section-header">
                        <h2>Recent Messages</h2>
                        <button class="view-all-btn">View All</button>
                    </div>
                    <div class="message-list">
                        <div class="message-item unread">
                            <div class="message-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="message-details">
                                <h4>Prof. James Wilson (HOD)</h4>
                                <p>Could you please provide an update on the research project we discussed last week?</p>
                                <div class="message-time">1 hour ago</div>
                            </div>
                        </div>
                        <div class="message-item unread">
                            <div class="message-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="message-details">
                                <h4>Jane Smith (Secretary)</h4>
                                <p>The exam schedule for next semester has been finalized. Please check your email.</p>
                                <div class="message-time">3 hours ago</div>
                            </div>
                        </div>
                        <div class="message-item">
                            <div class="message-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="message-details">
                                <h4>Student Representative (ME302)</h4>
                                <p>Sir, the class would like to request additional practice problems for the upcoming exam.</p>
                                <div class="message-time">Yesterday</div>
                            </div>
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

        // Welcome card buttons
        document.querySelector('.welcome-btn.primary').addEventListener('click', function() {
            window.location.href = 'courses.html';
        });

        document.querySelector('.welcome-btn.secondary').addEventListener('click', function() {
            window.location.href = 'results.html';
        });

        // View all buttons
        document.querySelectorAll('.view-all-btn').forEach(button => {
            button.addEventListener('click', function() {
                const section = this.closest('div').classList.contains('courses-section') ? 'courses.html' :
                    this.closest('div').classList.contains('notifications-section') ? 'notifications.html' : 'messages.html';
                window.location.href = section;
            });
        });

        // Course items
        document.querySelectorAll('.course-item').forEach(item => {
            item.addEventListener('click', function() {
                const courseCode = this.querySelector('.course-code').textContent;
                window.location.href = `course-details.html?code=${courseCode}`;
            });
        });

        // Notification items
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                this.classList.remove('unread');
                // In a real application, you would mark the notification as read in the database
                alert('Notification marked as read');
            });
        });

        // Message items
        document.querySelectorAll('.message-item').forEach(item => {
            item.addEventListener('click', function() {
                this.classList.remove('unread');
                window.location.href = 'messages.html';
            });
        });
    </script>
</body>

</html>