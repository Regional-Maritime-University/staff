<?php
session_name("rmu_staff_portal");
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
$lecturerId = $_SESSION["staff"]["number"] ?? null;
$lecturerName = $_SESSION["staff"]["prefix"] . " " .  $_SESSION["staff"]["first_name"] . " " . $_SESSION["staff"]["last_name"];

$activeCourses = $lecturer->getActiveCourses($lecturerId);

$pageTitle = "Profile - " . $lecturerName;
$activePage = "profile";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Staff Portal - Lecturer Profile</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Sidebar -->
    <?php require_once '../components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">

        <?php require_once '../components/header.php'; ?>

        <div class="dashboard-content">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-image">
                    <img src="../uploads/profiles/<?= $_SESSION["staff"]["avatar"] ?>" alt="<?= $lecturerName ?>">
                </div>
                <div class="profile-info">
                    <h2 class="profile-name"><?= $lecturerName ?></h2>
                    <div class="profile-title"><?= $_SESSION["staff"]["designation"] . ", " . $_SESSION["staff"]["department_name"] ?></div>
                    <div class="profile-meta">
                        <div class="meta-item">
                            <i class="fas fa-id-card"></i>
                            <span><?= $_SESSION["staff"]["number"] ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-envelope"></i>
                            <span><?= $_SESSION["staff"]["email"] ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-phone"></i>
                            <span><?= $_SESSION["staff"]["phone_number"] ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-calendar-check"></i>
                            <span class="availability-status available">
                                <span class="status-dot available"></span>
                                Available
                            </span>
                        </div>
                    </div>
                    <div class="profile-actions">
                        <!-- <button class="profile-btn primary">
                            <i class="fas fa-envelope"></i>
                            Send Message
                        </button> -->
                        <button class="profile-btn secondary" id="<?= $lecturerId ?>">
                            <i class="fas fa-edit"></i>
                            Edit Profile
                        </button>
                        <!-- <button class="profile-btn secondary" id="<?= $lecturerId ?>">
                            <i class="fas fa-print"></i>
                            Print Profile
                        </button> -->
                    </div>
                </div>
            </div>

            <!-- Profile Tabs -->
            <div class="profile-tabs">
                <button class="profile-tab active" data-tab="overview">Overview</button>
                <!-- <button class="profile-tab" data-tab="courses">Courses</button>
                <button class="profile-tab" data-tab="performance">Performance</button>
                <button class="profile-tab" data-tab="documents">Documents</button> -->
            </div>

            <!-- Profile Content -->
            <div class="profile-content">
                <!-- Overview Tab -->
                <div class="tab-pane active" id="overview">
                    <h3 class="section-title">Personal Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Full Name</div>
                            <div class="info-value"><?= htmlspecialchars($lecturerName) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Employee ID</div>
                            <div class="info-value"><?= htmlspecialchars($_SESSION["staff"]["number"] ?? '') ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Department</div>
                            <div class="info-value"><?= htmlspecialchars($_SESSION["staff"]["department_name"] ?? '') ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Position</div>
                            <div class="info-value"><?= htmlspecialchars($_SESSION["staff"]["designation"] ?? '') ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?= htmlspecialchars($_SESSION["staff"]["email"] ?? '') ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Phone</div>
                            <div class="info-value"><?= htmlspecialchars($_SESSION["staff"]["phone_number"] ?? '') ?></div>
                        </div>
                        <!-- <div class="info-item">
                            <div class="info-label">Office</div>
                            <div class="info-value">Engineering Block, Room 205</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Office Hours</div>
                            <div class="info-value">Mon, Wed: 10:00 AM - 12:00 PM</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Date of Joining</div>
                            <div class="info-value">September 15, 2018</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Status</div>
                            <div class="info-value">Full-time</div>
                        </div> -->
                    </div>

                    <!-- <h3 class="section-title">Areas of Specialization</h3>
                    <div class="specialization-list">
                        <span class="specialization-tag">Marine Propulsion</span>
                        <span class="specialization-tag">Ship Design</span>
                        <span class="specialization-tag">Naval Architecture</span>
                        <span class="specialization-tag">Maritime Engineering</span>
                        <span class="specialization-tag">Fluid Dynamics</span>
                    </div> -->

                    <!-- <h3 class="section-title">Education</h3>
                    <div class="education-list">
                        <div class="education-item">
                            <div class="education-degree">Ph.D. in Marine Engineering</div>
                            <div class="education-school">Massachusetts Institute of Technology</div>
                            <div class="education-date">2010 - 2014</div>
                            <div class="education-description">
                                Dissertation: "Advanced Propulsion Systems for Maritime Vessels: Efficiency and Environmental Impact"
                            </div>
                        </div>
                        <div class="education-item">
                            <div class="education-degree">M.Sc. in Naval Architecture</div>
                            <div class="education-school">University of Southampton</div>
                            <div class="education-date">2007 - 2009</div>
                            <div class="education-description">
                                Thesis: "Computational Fluid Dynamics in Ship Hull Design"
                            </div>
                        </div>
                        <div class="education-item">
                            <div class="education-degree">B.Eng. in Mechanical Engineering</div>
                            <div class="education-school">University of Ghana</div>
                            <div class="education-date">2003 - 2007</div>
                            <div class="education-description">
                                First Class Honors
                            </div>
                        </div>
                    </div> -->

                    <!-- <h3 class="section-title">Professional Experience</h3>
                    <div class="experience-list">
                        <div class="experience-item">
                            <div class="experience-position">Associate Professor</div>
                            <div class="experience-company">Regional Maritime University</div>
                            <div class="experience-date">2018 - Present</div>
                            <div class="experience-description">
                                Teaching advanced courses in marine engineering, supervising graduate students, and conducting research in maritime technology.
                            </div>
                        </div>
                        <div class="experience-item">
                            <div class="experience-position">Assistant Professor</div>
                            <div class="experience-company">Ghana Maritime Academy</div>
                            <div class="experience-date">2014 - 2018</div>
                            <div class="experience-description">
                                Taught undergraduate courses in marine engineering and conducted research on ship propulsion systems.
                            </div>
                        </div>
                        <div class="experience-item">
                            <div class="experience-position">Research Engineer</div>
                            <div class="experience-company">Maritime Research Institute</div>
                            <div class="experience-date">2009 - 2010</div>
                            <div class="experience-description">
                                Conducted research on fluid dynamics and ship design optimization.
                            </div>
                        </div>
                    </div> -->
                </div>

                <!-- Courses Tab -->
                <!-- <div class="tab-pane" id="courses">
                    <h3 class="section-title">Current Courses (2023/2024 First Semester)</h3>
                    <div class="course-grid">
                        <div class="course-card">
                            <div class="course-header">
                                <div class="course-title">Introduction to Marine Engineering</div>
                                <div class="course-code">ME101</div>
                            </div>
                            <div class="course-details">
                                <div class="course-detail">
                                    <span class="course-detail-label">Level:</span>
                                    <span class="course-detail-value">100</span>
                                </div>
                                <div class="course-detail">
                                    <span class="course-detail-label">Credits:</span>
                                    <span class="course-detail-value">3</span>
                                </div>
                                <div class="course-detail">
                                    <span class="course-detail-label">Students:</span>
                                    <span class="course-detail-value">45</span>
                                </div>
                                <div class="course-detail">
                                    <span class="course-detail-label">Schedule:</span>
                                    <span class="course-detail-value">Mon, Wed 9:00-10:30 AM</span>
                                </div>
                            </div>
                            <span class="course-status active">Active</span>
                        </div>

                        <div class="course-card">
                            <div class="course-header">
                                <div class="course-title">Marine Propulsion Systems</div>
                                <div class="course-code">ME302</div>
                            </div>
                            <div class="course-details">
                                <div class="course-detail">
                                    <span class="course-detail-label">Level:</span>
                                    <span class="course-detail-value">300</span>
                                </div>
                                <div class="course-detail">
                                    <span class="course-detail-label">Credits:</span>
                                    <span class="course-detail-value">4</span>
                                </div>
                                <div class="course-detail">
                                    <span class="course-detail-label">Students:</span>
                                    <span class="course-detail-value">32</span>
                                </div>
                                <div class="course-detail">
                                    <span class="course-detail-label">Schedule:</span>
                                    <span class="course-detail-value">Tue, Thu 1:00-3:00 PM</span>
                                </div>
                            </div>
                            <span class="course-status active">Active</span>
                        </div>

                        <div class="course-card">
                            <div class="course-header">
                                <div class="course-title">Ship Design and Construction</div>
                                <div class="course-code">ME405</div>
                            </div>
                            <div class="course-details">
                                <div class="course-detail">
                                    <span class="course-detail-label">Level:</span>
                                    <span class="course-detail-value">400</span>
                                </div>
                                <div class="course-detail">
                                    <span class="course-detail-label">Credits:</span>
                                    <span class="course-detail-value">3</span>
                                </div>
                                <div class="course-detail">
                                    <span class="course-detail-label">Students:</span>
                                    <span class="course-detail-value">28</span>
                                </div>
                                <div class="course-detail">
                                    <span class="course-detail-label">Schedule:</span>
                                    <span class="course-detail-value">Fri 9:00-12:00 PM</span>
                                </div>
                            </div>
                            <span class="course-status active">Active</span>
                        </div>
                    </div>

                    <h3 class="section-title">Previous Courses</h3>
                    <div class="course-grid">
                        <div class="course-card">
                            <div class="course-header">
                                <div class="course-title">Fluid Mechanics for Marine Engineers</div>
                                <div class="course-code">ME203</div>
                            </div>
                            <div class="course-details">
                                <div class="course-detail">
                                    <span class="course-detail-label">Level:</span>
                                    <span class="course-detail-value">200</span>
                                </div>
                                <div class="course-detail">
                                    <span class="course-detail-label">Credits:</span>
                                    <span class="course-detail-value">3</span>
                                </div>
                                <div class="course-detail">
                                    <span class="course-detail-label">Students:</span>
                                    <span class="course-detail-value">38</span>
                                </div>
                                <div class="course-detail">
                                    <span class="course-detail-label">Semester:</span>
                                    <span class="course-detail-value">2022/2023 Second</span>
                                </div>
                            </div>
                            <span class="course-status completed">Completed</span>
                        </div>

                        <div class="course-card">
                            <div class="course-header">
                                <div class="course-title">Marine Engineering Systems</div>
                                <div class="course-code">ME301</div>
                            </div>
                            <div class="course-details">
                                <div class="course-detail">
                                    <span class="course-detail-label">Level:</span>
                                    <span class="course-detail-value">300</span>
                                </div>
                                <div class="course-detail">
                                    <span class="course-detail-label">Credits:</span>
                                    <span class="course-detail-value">4</span>
                                </div>
                                <div class="course-detail">
                                    <span class="course-detail-label">Students:</span>
                                    <span class="course-detail-value">35</span>
                                </div>
                                <div class="course-detail">
                                    <span class="course-detail-label">Semester:</span>
                                    <span class="course-detail-value">2022/2023 First</span>
                                </div>
                            </div>
                            <span class="course-status completed">Completed</span>
                        </div>
                    </div>

                    <h3 class="section-title">Upcoming Courses</h3>
                    <div class="course-grid">
                        <div class="course-card">
                            <div class="course-header">
                                <div class="course-title">Advanced Marine Engineering</div>
                                <div class="course-code">ME501</div>
                            </div>
                            <div class="course-details">
                                <div class="course-detail">
                                    <span class="course-detail-label">Level:</span>
                                    <span class="course-detail-value">500</span>
                                </div>
                                <div class="course-detail">
                                    <span class="course-detail-label">Credits:</span>
                                    <span class="course-detail-value">4</span>
                                </div>
                                <div class="course-detail">
                                    <span class="course-detail-label">Students:</span>
                                    <span class="course-detail-value">TBD</span>
                                </div>
                                <div class="course-detail">
                                    <span class="course-detail-label">Semester:</span>
                                    <span class="course-detail-value">2023/2024 Second</span>
                                </div>
                            </div>
                            <span class="course-status upcoming">Upcoming</span>
                        </div>
                    </div>
                </div> -->

                <!-- Performance Tab -->
                <!-- <div class="tab-pane" id="performance">
                    <h3 class="section-title">Performance Overview</h3>
                    <div class="performance-stats">
                        <div class="stat-box">
                            <div class="stat-value">4.7</div>
                            <div class="stat-label">Average Rating</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value">98%</div>
                            <div class="stat-label">Attendance Rate</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value">105</div>
                            <div class="stat-label">Students Taught</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value">3</div>
                            <div class="stat-label">Current Courses</div>
                        </div>
                    </div>

                    <h3 class="section-title">Student Evaluations</h3>
                    <div class="chart-container">
                        <canvas id="evaluationChart"></canvas>
                    </div>

                    <h3 class="section-title">Recent Reviews</h3>
                    <div class="review-list">
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-course">ME101 - Introduction to Marine Engineering</div>
                                <div class="review-date">December 10, 2023</div>
                            </div>
                            <div class="review-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="review-text">
                                "Dr. Doe is an excellent instructor who makes complex concepts easy to understand. His practical examples from his industry experience really help connect theory to real-world applications."
                            </div>
                        </div>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-course">ME302 - Marine Propulsion Systems</div>
                                <div class="review-date">December 8, 2023</div>
                            </div>
                            <div class="review-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <div class="review-text">
                                "Very knowledgeable in the subject matter. The course was challenging but Dr. Doe was always available during office hours to provide additional help."
                            </div>
                        </div>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-course">ME405 - Ship Design and Construction</div>
                                <div class="review-date">December 5, 2023</div>
                            </div>
                            <div class="review-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <div class="review-text">
                                "The course material was well-organized and Dr. Doe's teaching style is engaging. Would have appreciated more hands-on projects, but overall a great learning experience."
                            </div>
                        </div>
                    </div>
                </div> -->

                <!-- Documents Tab -->
                <!-- <div class="tab-pane" id="documents">
                    <h3 class="section-title">Personal Documents</h3>
                    <div class="document-list">
                        <div class="document-item">
                            <div class="document-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="document-name">CV - John Doe</div>
                            <div class="document-info">PDF, 2.3 MB, Uploaded: Jan 15, 2023</div>
                            <div class="document-actions">
                                <button class="document-btn">
                                    <i class="fas fa-download"></i> Download
                                </button>
                                <button class="document-btn">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </div>
                        </div>
                        <div class="document-item">
                            <div class="document-icon">
                                <i class="fas fa-file-image"></i>
                            </div>
                            <div class="document-name">Profile Photo</div>
                            <div class="document-info">JPG, 1.1 MB, Uploaded: Jan 15, 2023</div>
                            <div class="document-actions">
                                <button class="document-btn">
                                    <i class="fas fa-download"></i> Download
                                </button>
                                <button class="document-btn">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </div>
                        </div>
                        <div class="document-item">
                            <div class="document-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="document-name">PhD Certificate</div>
                            <div class="document-info">PDF, 1.5 MB, Uploaded: Jan 15, 2023</div>
                            <div class="document-actions">
                                <button class="document-btn">
                                    <i class="fas fa-download"></i> Download
                                </button>
                                <button class="document-btn">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </div>
                        </div>
                        <div class="document-item">
                            <div class="document-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="document-name">Master's Certificate</div>
                            <div class="document-info">PDF, 1.2 MB, Uploaded: Jan 15, 2023</div>
                            <div class="document-actions">
                                <button class="document-btn">
                                    <i class="fas fa-download"></i> Download
                                </button>
                                <button class="document-btn">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </div>
                        </div>
                    </div>

                    <h3 class="section-title">Course Materials</h3>
                    <div class="document-list">
                        <div class="document-item">
                            <div class="document-icon">
                                <i class="fas fa-file-powerpoint"></i>
                            </div>
                            <div class="document-name">ME101 - Course Syllabus</div>
                            <div class="document-info">PPTX, 3.5 MB, Uploaded: Sep 5, 2023</div>
                            <div class="document-actions">
                                <button class="document-btn">
                                    <i class="fas fa-download"></i> Download
                                </button>
                                <button class="document-btn">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </div>
                        </div>
                        <div class="document-item">
                            <div class="document-icon">
                                <i class="fas fa-file-word"></i>
                            </div>
                            <div class="document-name">ME302 - Course Outline</div>
                            <div class="document-info">DOCX, 1.8 MB, Uploaded: Sep 7, 2023</div>
                            <div class="document-actions">
                                <button class="document-btn">
                                    <i class="fas fa-download"></i> Download
                                </button>
                                <button class="document-btn">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </div>
                        </div>
                        <div class="document-item">
                            <div class="document-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="document-name">ME405 - Reference Materials</div>
                            <div class="document-info">PDF, 5.2 MB, Uploaded: Sep 10, 2023</div>
                            <div class="document-actions">
                                <button class="document-btn">
                                    <i class="fas fa-download"></i> Download
                                </button>
                                <button class="document-btn">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </div>
                        </div>
                    </div>

                    <h3 class="section-title">Publications</h3>
                    <div class="document-list">
                        <div class="document-item">
                            <div class="document-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="document-name">Advances in Marine Propulsion</div>
                            <div class="document-info">Journal of Maritime Engineering, 2022</div>
                            <div class="document-actions">
                                <button class="document-btn">
                                    <i class="fas fa-external-link-alt"></i> View Publication
                                </button>
                            </div>
                        </div>
                        <div class="document-item">
                            <div class="document-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="document-name">Sustainable Ship Design</div>
                            <div class="document-info">Maritime Technology Journal, 2021</div>
                            <div class="document-actions">
                                <button class="document-btn">
                                    <i class="fas fa-external-link-alt"></i> View Publication
                                </button>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div class="toast-container" id="toastContainer" aria-live="polite"></div>

    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Tab functionality
            document.querySelectorAll(".profile-tab").forEach(function(tab) {
                tab.addEventListener("click", function() {
                    document.querySelectorAll(".profile-tab").forEach(function(t) {
                        t.classList.remove("active");
                    });
                    this.classList.add("active");

                    document.querySelectorAll(".tab-pane").forEach(function(pane) {
                        pane.classList.remove("active");
                    });

                    var tabId = this.getAttribute("data-tab");
                    var pane = document.getElementById(tabId);
                    if (pane) pane.classList.add("active");
                });
            });

            // Edit profile button
            var editBtn = document.querySelector(".profile-btn.secondary");
            if (editBtn) {
                editBtn.addEventListener("click", function() {
                    showToast("Edit profile functionality coming soon.", "info");
                });
            }
        });

        // Toast notification helper
        function showToast(message, type) {
            var container = document.getElementById("toastContainer");
            if (!container) return;
            var toast = document.createElement("div");
            toast.className = "toast toast-" + (type || "info");
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(function() { toast.classList.add("toast-show"); }, 10);
            setTimeout(function() {
                toast.classList.remove("toast-show");
                setTimeout(function() { toast.remove(); }, 300);
            }, 3000);
        }
    </script>
</body>

</html>