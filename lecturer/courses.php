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

$pageTitle = "Courses - RMU Lecturer Portal";
$activePage = "courses";

?>

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
    <?php require_once '../components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">

        <?php require_once '../components/header.php'; ?>

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
                window.location.href = `course-details.php?code=${courseCode}`;
            });
        });

        // Resources button functionality
        document.querySelectorAll('.course-btn.secondary').forEach(button => {
            button.addEventListener('click', function() {
                const courseCard = this.closest('.course-card');
                const courseCode = courseCard.querySelector('.course-code').textContent;
                window.location.href = `course-resources.php?code=${courseCode}`;
            });
        });
    </script>
</body>

</html>