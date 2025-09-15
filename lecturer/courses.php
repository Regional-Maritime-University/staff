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

use Src\Controller\LecturerController;

require_once('../inc/admin-database-con.php');

$lecturer = new LecturerController($db, $user, $pass);

$pageTitle = "Courses";
$activePage = "courses";

$departmentId = $_SESSION["staff"]["department_id"] ?? null;
$lecturerId = $_SESSION["staff"]["number"];
$semesterId = 2; //$_SESSION["semester"] ?? null;
$archived = false;

$activeCourses = $lecturer->getActiveCourses($lecturerId);

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
                    <div class="semester-option active" data-semester="all">All Courses</div>
                    <div class="semester-option" data-semester="active">Active</div>
                    <div class="semester-option" data-semester="marking">Marking</div>
                    <div class="semester-option" data-semester="marked">Marked</div>
                </div>
            </div>

            <!-- Course Grid -->
            <div class="course-grid">
                <!-- Course Card 1 -->
                <?php foreach ($activeCourses as $course) { ?>
                    <div class="course-card <?= $course["status"] ?>" data-course="<?= $course["course_code"] ?>" data-semester="<?= $course["semester_id"] ?>">
                        <div class="course-header">
                            <div class="course-title"><?= $course["course_name"] ?></div>
                            <div class="course-code"><?= $course["course_code"] ?></div>
                            <span class="course-status <?= $course["status"] ?>"><?= $course["status"] ?></span>
                        </div>
                        <div class="course-body">
                            <div class="course-details">
                                <div class="detail-item">
                                    <div class="detail-label">Semester</div>
                                    <div class="detail-value"><?= $course["semester_name"] ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Level</div>
                                    <div class="detail-value"><?= $course["course_code"] ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Students</div>
                                    <div class="detail-value"><?= $course["total_students"] ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Classes</div>
                                    <div class="detail-value"><?= $course["class_codes"] ? $course["class_codes"] : "None" ?></div>
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
                <?php
                } ?>
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
                const activeCourses = document.querySelectorAll('.course-card:not(.marking):not(.marked)');
                const markingCourses = document.querySelectorAll('.course-card.marking');
                const markedCourses = document.querySelectorAll('.course-card.marked');

                if (semester === 'active') {
                    activeCourses.forEach(course => course.style.display = 'block');
                    markingCourses.forEach(course => course.style.display = 'none');
                    markedCourses.forEach(course => course.style.display = 'none');
                } else if (semester === 'marking') {
                    activeCourses.forEach(course => course.style.display = 'none');
                    markingCourses.forEach(course => course.style.display = 'block');
                    markedCourses.forEach(course => course.style.display = 'none');
                } else if (semester === 'marked') {
                    activeCourses.forEach(course => course.style.display = 'none');
                    markingCourses.forEach(course => course.style.display = 'none');
                    markedCourses.forEach(course => course.style.display = 'block');
                } else {
                    activeCourses.forEach(course => course.style.display = 'block');
                    markingCourses.forEach(course => course.style.display = 'block');
                    markedCourses.forEach(course => course.style.display = 'block');
                }
            });
        });

        // View Details button functionality
        document.querySelectorAll('.course-btn.primary').forEach(button => {
            button.addEventListener('click', function() {
                const courseCard = this.closest('.course-card');
                const courseCode = courseCard.querySelector('.course-code').textContent;
                const semesterId = courseCard.getAttribute('data-semester');
                window.location.href = `course-details.php?code=${courseCode}&semester=${semesterId}`;
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