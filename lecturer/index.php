<?php
session_start();

if (!isset($_SESSION["staffLoginSuccess"]) || $_SESSION["staffLoginSuccess"] == false || !isset($_SESSION["staff"]["number"]) || empty($_SESSION["staff"]["number"])) {
    header("Location: ../index.php");
}

$isUser = false;
if (strtolower($_SESSION["staff"]["role"]) == "admin" || strtolower($_SESSION["staff"]["role"]) == "developers" || strtolower($_SESSION["staff"]["role"]) == "lecturer") $isUser = true;

if (isset($_GET['logout']) || !$isUser) {
    session_destroy();
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    header('Location: ../index.php');
}

$_SESSION["lastAccessed"] = time();

require_once('../bootstrap.php');

use Src\Controller\SecretaryController;

require_once('../inc/admin-database-con.php');

$admin = new SecretaryController($db, $user, $pass);

$pageTitle = "Dashboard";
$activePage = "dashboard";

?>

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
    <?php require_once '../components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">

        <?php require_once '../components/header.php'; ?>

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
            window.location.href = 'courses.php';
        });

        document.querySelector('.welcome-btn.secondary').addEventListener('click', function() {
            window.location.href = 'results.php';
        });

        // View all buttons
        document.querySelectorAll('.view-all-btn').forEach(button => {
            button.addEventListener('click', function() {
                const section = this.closest('div').classList.contains('courses-section') ? 'courses.php' :
                    this.closest('div').classList.contains('notifications-section') ? 'notifications.php' : 'messages.php';
                window.location.href = section;
            });
        });

        // Course items
        document.querySelectorAll('.course-item').forEach(item => {
            item.addEventListener('click', function() {
                const courseCode = this.querySelector('.course-code').textContent;
                window.location.href = `course-details.php?code=${courseCode}`;
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
                window.location.href = 'messages.php';
            });
        });
    </script>
</body>

</html>