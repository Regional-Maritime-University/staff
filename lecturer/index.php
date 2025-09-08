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

$pageTitle = "Dashboard";
$activePage = "dashboard";

$departmentId = $_SESSION["staff"]["department_id"] ?? null;
$lecturerId = $_SESSION["staff"]["number"];
$semesterId = 2; //$_SESSION["semester"] ?? null;
$archived = false;

$activeSemesters = $lecturer->fetchActiveSemesters();
$activeClasses = $lecturer->fetchAllActiveClasses(departmentId: $departmentId);
$deadlines = $lecturer->fetchPendingDeadlines($departmentId);

$totalPendingDeadlines = 0;
if ($deadlines && is_array($deadlines)) {
    foreach ($deadlines as $d) {
        if ($d['deadline_status'] == 'pending') $totalPendingDeadlines++;
    }
}

$totalActiveCourses = $lecturer->getTotalActiveCourses($lecturerId);
$totalStudents = $lecturer->getTotalStudents($lecturerId);
$totalPendingResults = $lecturer->getTotalPendingResults($lecturerId);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Lecturer Portal - Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="./css/dashboard.css">
    <link rel="stylesheet" href="./css/results.css">
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
                    <h2>Welcome back, <?= $_SESSION["staff"]["prefix"] . " " .  $_SESSION["staff"]["first_name"] . " " . $_SESSION["staff"]["last_name"] ?>!</h2>
                    <p>Here's what's happening with your courses today.</p>
                    <div class="welcome-actions">
                        <button class="welcome-btn primary">
                            <i class="fas fa-book"></i> View My Courses
                        </button>
                        <button class="welcome-btn secondary" id="uploadResultsBtn">
                            <i class="fas fa-upload"></i> Upload Results
                        </button>
                    </div>
                </div>
                <!-- <img src="welcome.svg" alt="Welcome" class="welcome-image"> -->
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon courses">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $totalActiveCourses ?></h3>
                        <p>Active Courses</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon students">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $totalStudents ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon results">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $totalPendingResults ?></h3>
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
                <!-- <div class="courses-section">
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
                </div> -->

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

    <!-- Upload Results Modal -->
    <div class="modal" id="uploadResultsModal">
        <div class="modal-dialog modal-lg modal-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Upload Exam Results</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="uploadSemester">Semester</label>
                        <select id="uploadSemester" required>
                            <option value="">Select a semester</option>
                            <?php foreach ($activeSemesters as $semester) : ?>
                                <option value="<?= $semester['id'] ?>" data-academicYear="<?= $semester["academic_year_name"] ?>"><?= $semester['name'] == 1 ? 'First Semester' : ($semester['name'] == 2 ? 'Second Semester' : 'Summer Semester') ?> <?= $semester['academic_year_start_year'] . '/' . $semester['academic_year_end_year'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="uploadClass">Select Class</label>
                        <select id="uploadClass" required>
                            <option value="">Select a class</option>
                            <?php
                            foreach ($activeClasses as $class) {
                                echo '<option value="' . $class['code'] . '">' . $class['code'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="uploadCourse">Select Course</label>
                        <select id="uploadCourse" required>
                            <option value="">Select a course</option>
                            <?php
                            foreach ($deadlines as $deadline) {
                                if ($deadline['deadline_status'] == 'pending') {
                                    echo '<option value="' . $deadline['course_code'] . '">' . $deadline['course_code'] . ' - ' . $deadline['course_name'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Is the course project based?</label>
                        <div class="radio-group">
                            <div class="radio-item">
                                <input type="radio" id="projectBasedYes" name="uploadProjectBased" value="yes" checked>
                                <label for="projectBasedYes">Yes</label>
                            </div>
                            <div class="radio-item">
                                <input type="radio" id="projectBasedNo" name="uploadProjectBased" value="no">
                                <label for="projectBasedNo">No</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="score-weights-group">
                            <div class="score-weights-title">Score Weights</div>
                            <div class="score-weight-item">
                                <label for="uploadExamScoreWeight">Exam Score Weight</label>
                                <input type="number" title="Exam Score Weight" value="60" min="0" max="100" id="uploadExamScoreWeight" name="uploadExamScoreWeight">
                            </div>
                            <div class="score-weight-item">
                                <label for="uploadProjectScoreWeight">Project Score Weight</label>
                                <input type="number" title="Project Score Weight" value="0" min="0" max="100" id="uploadProjectScoreWeight" name="uploadProjectScoreWeight">
                            </div>
                            <div class="score-weight-item">
                                <label for="uploadAssessmentScoreWeight">Assessment Score Weight</label>
                                <input type="number" title="Assessment Score Weight" value="40" min="0" max="100" id="uploadAssessmentScoreWeight" name="uploadAssessmentScoreWeight">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Upload Results File</label>
                        <div class="file-upload">
                            <div class="file-input-wrapper">
                                <input type="file" id="uploadResultsFile" accept=".xlsx, .csv">
                                <div class="file-input-btn">
                                    <i class="fas fa-cloud-upload-alt"></i> Choose File or Drop File Here
                                </div>
                            </div>
                            <div class="file-name" id="fileName">No file chosen</div>
                            <div class="file-format-info">Accepted formats: Excel (.xlsx) or CSV (.csv)</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="uploadNotes">Additional Notes (Optional)</label>
                        <textarea id="uploadNotes" rows="3" placeholder="Enter any additional notes or comments"></textarea>
                    </div>

                    <input type="hidden" id="staffId" value="<?= $_SESSION['staff']['number'] ?>">
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" data-dismiss="modal">Cancel</button>
                    <button class="normal-btn" id="submitUploadBtn">Upload Results</button>
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

        // Modal functionality
        const uploadResultsModal = document.getElementById('uploadResultsModal');

        // Open modal
        document.getElementById('uploadResultsBtn').addEventListener('click', function() {
            uploadResultsModal.classList.add('active');
        });

        // Close modal
        document.querySelectorAll('.close-btn, .cancel-btn').forEach(button => {
            button.addEventListener('click', function() {
                uploadResultsModal.classList.remove('active');
            });
        });

        // Welcome card buttons
        document.querySelector('.welcome-btn.primary').addEventListener('click', function() {
            window.location.href = 'courses.php';
        });

        // document.querySelector('.welcome-btn.secondary').addEventListener('click', function() {
        //     window.location.href = 'results.php';
        // });

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

        // Fetch class courses

        const uploadClassCode = document.getElementById("uploadClass");
        uploadClassCode.addEventListener('change', async function() {

            const response = await fetch('../endpoint/fetch-class-courses', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    code: this.value
                })
            });

            const result = await response.json();

            // render select courses list
            const uploadCourseSelect = document.getElementById('uploadCourse');
            uploadCourseSelect.innerHTML = '<option value="">Select a course</option>';
            if (result.success && Array.isArray(result.data)) {
                result.data.forEach(course => {
                    const option = document.createElement('option');
                    option.value = course.course_code;
                    option.textContent = `${course.course_code} - ${course.course_name}`;
                    uploadCourseSelect.appendChild(option);
                });
            }
        });

        // Submit upload
        document.getElementById('submitUploadBtn').addEventListener('click', async function() {
            const classCode = document.getElementById('uploadClass').value;
            const courseCode = document.getElementById('uploadCourse').value;
            const semesterId = document.getElementById('uploadSemester').value;
            const resultsFile = document.getElementById('uploadResultsFile').value;
            const staffId = document.getElementById('staffId').value;
            const projectBased = document.querySelector('input[name="uploadProjectBased"]:checked').value;
            const academicYear = document.getElementById('uploadSemester').getAttribute('data-academicYear');
            const examScoreWeight = document.getElementById('uploadExamScoreWeight').value;
            const projectScoreWeight = document.getElementById('uploadProjectScoreWeight').value;
            const assessmentScoreWeight = document.getElementById('uploadAssessmentScoreWeight').value;


            if (!classCode || !courseCode || !semesterId || !resultsFile || !staffId) {
                alert('Please fill all required fields and select a file.');
                return;
            }

            // Send the upload request
            const formData = new FormData();
            formData.append('class', classCode);
            formData.append('course', courseCode);
            formData.append('semester', semesterId);
            formData.append('resultsFile', document.getElementById('uploadResultsFile').files[0]);
            formData.append('staffId', staffId);
            formData.append('projectBased', projectBased);
            formData.append('academicYear', academicYear);
            formData.append('examScoreWeight', examScoreWeight);
            formData.append('projectScoreWeight', projectScoreWeight);
            formData.append('assessmentScoreWeight', assessmentScoreWeight);
            formData.append('notes', document.getElementById('uploadNotes').value);

            response = await fetch('../endpoint/upload-results', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const result = await response.json();

            console.log('Upload result:', result);
            uploadResultsModal.classList.remove('active');
        });
    </script>
</body>

</html>