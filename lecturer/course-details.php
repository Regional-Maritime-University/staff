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

if (!isset($_GET["code"]) || empty($_GET["code"])) header('Location: courses.php');
$selectedCourse = $_GET["code"];

require_once('../bootstrap.php');

use Src\Controller\LecturerController;

require_once('../inc/admin-database-con.php');

$lecturer = new LecturerController($db, $user, $pass);

$departmentId = $_SESSION["staff"]["department_id"] ?? null;
$lecturerId = $_SESSION["staff"]["number"];
$semesterId = $_GET["semester"];
$archived = false;

$pageTitle = "Course Details";
$activePage = "courses";

$lecturerCourseDetails = $lecturer->getLecturerCourseDetails($lecturerId, $selectedCourse, $semesterId);
$courseDetails = $lecturerCourseDetails["data"]["details"] ?? [];
$courseStudents = $lecturerCourseDetails["data"]["students"] ?? [];
$courseOutline = $lecturerCourseDetails["data"]["outline"] ?? [];
$courseResourses = $lecturerCourseDetails["data"]["resources"] ?? [];
$courseResults = $lecturerCourseDetails["data"]["results"] ?? [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Lecturer Portal - Course Details</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="./css/course-details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php require_once '../components/datatables-head.php'; ?>
</head>

<body>

    <!-- Sidebar -->
    <?php require_once '../components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">

        <?php require_once '../components/header.php'; ?>

        <div class="course-details-content">
            <!-- Course Header Card -->
            <div class="course-header-card">
                <div class="course-header-info">
                    <div class="course-title-section">
                        <h2 class="course-title"><?= $courseDetails[0]["course_name"] ?></h2>
                        <div class="course-code"><?= $courseDetails[0]["course_code"] ?></div>
                    </div>
                    <div class="course-meta">
                        <div class="meta-item">
                            <i class="fas fa-layer-group"></i>
                            <span>Level <?= $courseDetails[0]["course_level"] ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-user-graduate"></i>
                            <span><?= $courseDetails[0]["total_students"] ?> Students</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <span>Mon, Wed 9:00-10:30 AM</span>
                        </div>
                    </div>
                    <span class="course-status <?= $courseDetails[0]["course_status"] ?>"> <?= $courseDetails[0]["course_status"] ?></span>
                </div>
                <!-- <div class="course-header-actions">
                    <button class="course-btn primary" id="uploadResourceBtn" style="height: 50px;">
                        <i class="fas fa-upload"></i> Upload Resource
                    </button>
                    <button class="course-btn secondary" id="editCourseBtn">
                        <i class="fas fa-edit"></i> Edit Course
                    </button>
                    <button class="course-btn primary" id="emailStudentsBtn">
                        <i class="fas fa-envelope"></i> Email Students
                    </button>
                </div> -->
            </div>

            <!-- Course Tabs -->
            <div class="course-tabs">
                <button class="course-tab active" data-tab="overview">Overview</button>
                <button class="course-tab" data-tab="students">Students</button>
                <button class="course-tab" data-tab="resources">Resources</button>
                <!-- <button class="course-tab" data-tab="schedule">Schedule</button> -->
                <button class="course-tab" data-tab="results">Results</button>
            </div>

            <!-- Course Content -->
            <div class="course-content">
                <!-- Overview Tab -->
                <div class="tab-pane active" id="overview">
                    <h3 class="section-title">Course Description</h3>
                    <div class="course-description">
                        <p>This course provides an introduction to the principles of marine engineering, covering the basic concepts of ship propulsion, power generation, and auxiliary systems. Students will learn about the different types of marine engines, their operation, and maintenance requirements. The course also covers the fundamentals of naval architecture and ship design.</p>
                        <p>Through a combination of lectures, practical demonstrations, and hands-on exercises, students will develop a solid foundation in marine engineering principles that will prepare them for more advanced courses in the field.</p>
                    </div>

                    <h3 class="section-title">Course Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Course Code</div>
                            <div class="info-value">ME101</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Credits</div>
                            <div class="info-value">3</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Level</div>
                            <div class="info-value">100</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Semester</div>
                            <div class="info-value">First Semester 2023/2024</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Schedule</div>
                            <div class="info-value">Mon, Wed 9:00-10:30 AM</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Location</div>
                            <div class="info-value">Engineering Block, Room 101</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Prerequisites</div>
                            <div class="info-value">None</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Students Enrolled</div>
                            <div class="info-value">45</div>
                        </div>
                    </div>

                    <h3 class="section-title">Course Objectives</h3>
                    <div class="course-objectives">
                        <ul class="objectives-list">
                            <li>Understand the basic principles of marine engineering and naval architecture</li>
                            <li>Identify and explain the function of major components in marine propulsion systems</li>
                            <li>Analyze the performance characteristics of different types of marine engines</li>
                            <li>Understand the fundamentals of ship stability and buoyancy</li>
                            <li>Develop basic skills in reading and interpreting marine engineering drawings</li>
                            <li>Recognize the environmental considerations in modern marine engineering</li>
                        </ul>
                    </div>
                </div>

                <!-- Students Tab -->
                <div class="tab-pane" id="students">
                    <div class="students-actions">
                        <div class="students-filters">
                            <select class="filter-select" id="programFilter">
                                <option value="all">All Classes</option>
                                <?php
                                // group students by class code
                                $classes = [];
                                foreach ($courseStudents as $student) {
                                    $classCode = $student["student_class_code"] ?: "Unassigned";
                                    if (!in_array($classCode, $classes)) {
                                        $classes[] = $classCode;
                                    }
                                }
                                foreach ($classes as $class) {
                                    echo "<option value='" . htmlspecialchars($class) . "'>" . htmlspecialchars($class) . "</option>";
                                }
                                ?>
                            </select>
                            <!-- <select class="filter-select" id="sortFilter">
                                <option value="name">Sort by Name</option>
                                <option value="id">Sort by ID</option>
                                <option value="program">Sort by Program</option>
                            </select> -->
                        </div>
                        <!-- <button class="course-btn primary" id="downloadStudentListBtn">
                            <i class="fas fa-download"></i> Download List
                        </button> -->
                    </div>
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Program</th>
                                <th>Email</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (empty($courseStudents)) {
                                echo '<tr><td colspan="6" style="text-align: center;">No students enrolled in this course.</td></tr>';
                            } else {
                                foreach ($courseStudents as $student) { ?>
                                    <tr>
                                        <td><?= htmlspecialchars($student["student_id"]) ?></td>
                                        <td class="student-name">
                                            <div class="student-avatar">
                                                <img src="<?= getenv("DOMAIN_APPLICANT") . "apply/photos/" . htmlspecialchars($student["photo"] ?: 'default-avatar.png') ?>" alt="Student">
                                            </div>
                                            <span><?= htmlspecialchars($student["student_name"]) ?></span>
                                        </td>
                                        <td><?= $student["student_class_code"] ?: "Unassigned" ?></td>
                                        <td><?= htmlspecialchars($student["student_email"]) ?></td>
                                        <td><?= $student["is_registered"] ? "Yes" : "No" ?></td>
                                        <td>
                                            <div class="student-actions">
                                                <!-- <button class="student-action view" title="View Profile">
                                                    <i class="fas fa-eye"></i>
                                                </button> -->
                                                <button class="student-action message" title="Send Message">
                                                    <i class="fas fa-envelope"></i>
                                                </button>
                                                <!-- <button class="student-action grade" title="Enter Grades">
                                                    <i class="fas fa-edit"></i>
                                                </button> -->
                                            </div>
                                        </td>
                                    </tr>
                            <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Resources Tab -->
                <div class="tab-pane" id="resources">
                    <div class="resources-actions">
                        <h3 class="section-title">Course Resources</h3>
                        <button class="course-btn primary" id="addResourceBtn">
                            <i class="fas fa-plus"></i> Add Resource
                        </button>
                    </div>
                    <div class="resources-grid">
                        <?php
                        if (empty($courseResourses)) {
                            echo '<p style="text-align: center; width: 100%;">No resources uploaded for this course.</p>';
                        } else foreach ($courseResourses as $resource) {
                            $iconClass = "fas fa-file";
                            $fileType = strtolower(pathinfo($resource["file_name"], PATHINFO_EXTENSION));
                            switch ($fileType) {
                                case 'pdf':
                                    $iconClass = "fas fa-file-pdf";
                                    break;
                                case 'doc':
                                case 'docx':
                                    $iconClass = "fas fa-file-word";
                                    break;
                                case 'ppt':
                                case 'pptx':
                                    $iconClass = "fas fa-file-powerpoint";
                                    break;
                                case 'xls':
                                case 'xlsx':
                                    $iconClass = "fas fa-file-excel";
                                    break;
                                case 'zip':
                                case 'rar':
                                    $iconClass = "fas fa-file-archive";
                                    break;
                                case 'jpg':
                                case 'jpeg':
                                case 'png':
                                case 'gif':
                                    $iconClass = "fas fa-file-image";
                                    break;
                                case 'mp4':
                                case 'avi':
                                case 'mov':
                                    $iconClass = "fas fa-file-video";
                                    break;
                                case 'mp3':
                                case 'wav':
                                    $iconClass = "fas fa-file-audio";
                                    break;
                                default:
                                    $iconClass = "fas fa-file";
                            }

                            $fileSize = number_format($resource["file_size"] / (1024 * 1024), 2) . " MB";
                            echo '<div class="resource-card">
                                    <div class="resource-icon">
                                        <i class="' . $iconClass . '"></i>
                                    </div>
                                    <div class="resource-title">' . htmlspecialchars($resource["title"]) . '</div>
                                    <div class="resource-info">' . strtoupper($fileType) . ', ' . $fileSize . ', Uploaded: ' . date("M d, Y", strtotime($resource["uploaded_at"])) . '</div>
                                    <div class="resource-actions">
                                        <button class="resource-btn">
                                            <i class="fas fa-download"></i> Download
                                        </button>
                                        <button class="resource-btn">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Schedule Tab -->
                <!-- <div class="tab-pane" id="schedule">
                    <h3 class="section-title">Course Schedule</h3>
                    <div class="schedule-grid">
                        <div class="day-header">Monday</div>
                        <div class="day-header">Tuesday</div>
                        <div class="day-header">Wednesday</div>
                        <div class="day-header">Thursday</div>
                        <div class="day-header">Friday</div>
                        <div class="day-header">Saturday</div>
                        <div class="day-header">Sunday</div>

                        <div class="schedule-cell">
                            <div class="schedule-item">
                                <div class="schedule-item-title">Lecture</div>
                                <div class="schedule-item-details">9:00-10:30 AM, Room 101</div>
                            </div>
                        </div>
                        <div class="schedule-cell"></div>
                        <div class="schedule-cell">
                            <div class="schedule-item">
                                <div class="schedule-item-title">Lecture</div>
                                <div class="schedule-item-details">9:00-10:30 AM, Room 101</div>
                            </div>
                        </div>
                        <div class="schedule-cell"></div>
                        <div class="schedule-cell">
                            <div class="schedule-item">
                                <div class="schedule-item-title">Lab Session</div>
                                <div class="schedule-item-details">2:00-4:00 PM, Lab 3</div>
                            </div>
                        </div>
                        <div class="schedule-cell"></div>
                        <div class="schedule-cell"></div>
                    </div>
                </div> -->

                <!-- Results Tab -->
                <div class="tab-pane" id="results">
                    <div class="results-actions">
                        <h3 class="section-title">Exam Results</h3>
                        <div class="course-button-actions">
                            <button class="course-btn primary" id="saveResultsBtn">
                                <i class="fas fa-save"></i> Upload
                            </button>
                            <button class="course-btn success" id="exportResultsBtn">
                                <i class="fas fa-file-export"></i> Export
                            </button>
                        </div>
                    </div>
                    <?php
                    if (empty($courseResults)) {
                        echo '<p style="text-align: center; width: 100%;">No results available for this course.</p>';
                    } else if ($courseResults["success"] === false) {
                        echo '<p style="text-align: center; width: 100%;">' . htmlspecialchars($courseResults["message"]) . '</p>';
                    } else if (empty($courseResults["data"])) {
                        echo '<p style="text-align: center; width: 100%;">No students\'s results uploaded for this course.</p>';
                    } else {
                        // Define key mapping between header text and weight keys
                        $headerWeightMap = [
                            "Exam Score" => "exam_score_weight",
                            "Project Score" => "project_score_weight",
                            "Ass. Score" => "assessment_score_weight"
                        ];

                        // Extract weights
                        $weights = $courseResults["data"]["values"][0] ?? []; // assuming it's always there
                        $projectBased = $courseResults["data"]["project_based"] ?? false;

                        echo '<table class="results-table">';
                        echo '<thead><tr>';
                        foreach ($courseResults["data"]["headers"] as $header) {
                            $baseHeader = explode(' (', $header)[0];

                            // Skip "Project Score" header if not project-based
                            if ($baseHeader === "Project Score" && !$projectBased) {
                                continue;
                            }

                            if ($baseHeader === "ACH Mark") {
                                echo '<th>' . htmlspecialchars($baseHeader) . ' (100%)</th>';
                            } else {
                                $weightKey = $headerWeightMap[$baseHeader] ?? null;
                                $weight = $weightKey && isset($weights[$weightKey]) ? ' (' . htmlspecialchars($weights[$weightKey]) . '%)' : '';
                                echo '<th>' . htmlspecialchars($baseHeader) . htmlspecialchars($weight) . '</th>';
                            }
                        }
                        echo '</tr></thead>';

                        // Populate results table
                        echo '<tbody>';
                        foreach ($courseResults["data"]["body"] as $row) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row["student_id"]) . '</td>';
                            echo '<td>' . htmlspecialchars($row["exam_score"]) . '</td>';
                            if ($projectBased && isset($row["project_score"])) {
                                echo '<td>' . htmlspecialchars($row["project_score"]) . '</td>';
                            }
                            echo '<td>' . htmlspecialchars($row["ass_score"]) . '</td>';
                            echo '<td>' . htmlspecialchars($row["final_score"]) . '</td>';
                            echo '<td>' . htmlspecialchars($row["grade"]) . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody>';
                        echo '</table>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Resource Modal -->
    <div class="modal" id="uploadResourceModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Upload Resource</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="resourceTitle">Resource Title</label>
                        <input type="text" id="resourceTitle" placeholder="Enter resource title" required>
                    </div>
                    <div class="form-group">
                        <label for="resourceType">Resource Type</label>
                        <select id="resourceType" required>
                            <option value="">Select resource type</option>
                            <option value="syllabus">Syllabus</option>
                            <option value="lecture">Lecture Notes</option>
                            <option value="assignment">Assignment</option>
                            <option value="textbook">Textbook/Reading</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="resourceFile">Upload File</label>
                        <input type="file" id="resourceFile" required>
                    </div>
                    <div class="form-group">
                        <label for="resourceDescription">Description (Optional)</label>
                        <textarea id="resourceDescription" placeholder="Enter resource description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" data-dismiss="modal">Cancel</button>
                    <button class="submit-btn" id="saveResourceBtn">Upload Resource</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <?php require_once '../components/datatables-scripts.php'; ?>
    <script src="../assets/js/main.js"></script>
    <script>
        // Toggle sidebar
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('expanded');
        });

        // Tab functionality
        document.querySelectorAll('.course-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.course-tab').forEach(t => {
                    t.classList.remove('active');
                });

                // Add active class to clicked tab
                this.classList.add('active');

                // Hide all tab panes
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('active');
                });

                // Show the corresponding tab pane
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Modal functionality
        const uploadResourceModal = document.getElementById('uploadResourceModal');

        // Open modal
        // document.getElementById('uploadResourceBtn').addEventListener('click', function() {
        //     uploadResourceModal.classList.add('active');
        // });

        document.getElementById('addResourceBtn').addEventListener('click', function() {
            uploadResourceModal.classList.add('active');
        });

        // Close modal
        document.querySelectorAll('.close-btn, .cancel-btn').forEach(button => {
            button.addEventListener('click', function() {
                uploadResourceModal.classList.remove('active');
            });
        });

        // Save resource
        document.getElementById('saveResourceBtn').addEventListener('click', function() {
            const title = document.getElementById('resourceTitle').value;
            const type = document.getElementById('resourceType').value;
            const file = document.getElementById('resourceFile').value;

            if (!title || !type || !file) {
                alert('Please fill all required fields.');
                return;
            }

            // In a real application, you would upload the file to the server
            alert('Resource uploaded successfully!');
            uploadResourceModal.classList.remove('active');
        });

        // Calculate grades
        document.querySelectorAll('.grade-input').forEach(input => {
            input.addEventListener('change', function() {
                const row = this.closest('tr');
                const inputs = row.querySelectorAll('.grade-input');
                let total = 0;

                inputs.forEach(input => {
                    total += parseInt(input.value) || 0;
                });

                row.cells[6].textContent = total;

                // Determine grade
                let grade = '';
                if (total >= 90) grade = 'A+';
                else if (total >= 80) grade = 'A';
                else if (total >= 75) grade = 'B+';
                else if (total >= 70) grade = 'B';
                else if (total >= 65) grade = 'C+';
                else if (total >= 60) grade = 'C';
                else if (total >= 55) grade = 'D+';
                else if (total >= 50) grade = 'D';
                else grade = 'F';

                row.cells[7].textContent = grade;
            });
        });

        // Save results
        document.getElementById('saveResultsBtn').addEventListener('click', function() {
            // In a real application, you would save the results to the database
            document.querySelectorAll('.grade-status').forEach(status => {
                status.textContent = 'Submitted';
                status.classList.remove('pending');
                status.classList.add('submitted');
            });

            alert('Results saved successfully!');
        });

        // Export results
        document.getElementById('exportResultsBtn').addEventListener('click', function() {
            // In a real application, you would generate a CSV or Excel file
            alert('Exporting results...');
        });

        // Download student list
        // document.getElementById('downloadStudentListBtn').addEventListener('click', function() {
        //     // In a real application, you would generate a CSV or Excel file
        //     alert('Downloading student list...');
        // });

        // Student actions
        document.querySelectorAll('.student-action').forEach(action => {
            action.addEventListener('click', function() {
                const actionType = this.classList.contains('view') ? 'View Profile' :
                    this.classList.contains('message') ? 'Send Message' : 'Enter Grades';

                const row = this.closest('tr');
                const studentId = row.cells[0].textContent;
                const studentName = row.cells[1].textContent.trim();

                alert(`${actionType} for ${studentName} (${studentId})`);
            });
        });

        // Resource actions
        document.querySelectorAll('.resource-btn').forEach(button => {
            button.addEventListener('click', function() {
                const action = this.textContent.trim();
                const resourceCard = this.closest('.resource-card');
                const resourceTitle = resourceCard.querySelector('.resource-title').textContent;

                alert(`${action} ${resourceTitle}`);
            });
        });

        // Edit course button
        // document.getElementById('editCourseBtn').addEventListener('click', function() {
        //     alert('Edit course functionality would open a form to edit course details.');
        // });

        // Email students button
        // document.getElementById('emailStudentsBtn').addEventListener('click', function() {
        //     alert('Email students functionality would open a form to send an email to all students.');
        // });
    </script>
</body>

</html>