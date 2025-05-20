<?php
session_start();

if (!isset($_SESSION["staffLoginSuccess"]) || $_SESSION["staffLoginSuccess"] == false || !isset($_SESSION["staff"]["number"]) || empty($_SESSION["staff"]["number"])) {
    header("Location: ../index.php");
}

$isUser = false;
if (strtolower($_SESSION["staff"]["role"]) == "admin" || strtolower($_SESSION["staff"]["role"]) == "developers" || strtolower($_SESSION["staff"]["role"]) == "secretary") $isUser = true;

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

$staffData = $_SESSION["staff"] ?? null;
$_SESSION["lastAccessed"] = time();

require_once('../bootstrap.php');

use Src\Controller\SecretaryController;
use Src\Core\Base;
use Src\Core\Course;
use Src\Core\CourseCategory;

require_once('../inc/admin-database-con.php');

$secretary          = new SecretaryController($db, $user, $pass);
$course_category    = new CourseCategory($db, $user, $pass);
$course             = new Course($db, $user, $pass);
$base               = new Base($db, $user, $pass);

$pageTitle = "Secretary Dashboard";
$activePage = "dashboard";

$departmentId = $_SESSION["staff"]["department_id"] ?? null;
$semesterId = 2; //$_SESSION["semester"] ?? null;
$archived = false;

$activeSemesters = $secretary->fetchActiveSemesters();
$lecturers = $secretary->fetchAllLecturers($departmentId, $archived);

$activeCourses = $secretary->fetchActiveCourses($departmentId, null, $archived);
$totalActiveCourses = count($activeCourses);

$assignedCourses = [];
foreach ($activeSemesters as $semester) {
    $semesterId = $semester['id'];
    $assignedCourses = array_merge($assignedCourses, $secretary->fetchSemesterCourseAssignmentsByDepartment($departmentId, $semesterId));
}
$totalAssignedCourses = $assignedCourses && is_array($assignedCourses) ? count($assignedCourses) : 0;

$assignedLecturers = [];
foreach ($activeSemesters as $semester) {
    $semesterId = $semester['id'];
    $assignedLecturers = array_merge($assignedLecturers, $secretary->fetchSemesterCourseAssignmentsGroupByLecturer($departmentId, $semesterId));
}
$totalAssignedLecturers = $assignedLecturers && is_array($assignedLecturers) ? count($assignedLecturers) : 0;

$deadlines = $secretary->fetchPendingDeadlines($departmentId);
$totalPendingDeadlines = 0;
if ($deadlines && is_array($deadlines)) {
    foreach ($deadlines as $d) {
        if ($d['status'] == 'pending') $totalPendingDeadlines++;
    }
}

$courseWithNoDeadlines = $secretary->fetchAssignedSemesterCoursesWithNoDeadlinesByDepartment($departmentId);

$recentActivities = $secretary->fetchRecentActivities($departmentId, false);

$activeStudents = $secretary->fetchAllActiveStudents(departmentId: $departmentId);
$totalActiveStudents = $activeStudents && is_array($activeStudents) ? count($activeStudents) : 0;

$activeClasses = $secretary->fetchAllActiveClasses(departmentId: $departmentId);
$totalActiveClasses = $activeClasses && is_array($activeClasses) ? count($activeClasses) : 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Staff Portal - Secretary Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="./css/course-selection-modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Sidebar -->
    <?php require_once '../components/sidebar.php'; ?>

    <div class="main-content">

        <?php require_once '../components/header.php'; ?>

        <div class="dashboard-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: var(--primary-color);">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $totalAssignedCourses ?></h3>
                        <p>Assigned Courses</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: var(--accent-color);">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $totalAssignedLecturers ?></h3>
                        <p>Assigned Lecturers</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: var(--success-color);">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3>24</h3>
                        <p>Submitted Results</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: var(--danger-color);">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $totalPendingDeadlines ?></h3>
                        <p>Pending Deadlines</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="upcoming-deadlines">
                    <div class="section-header">
                        <h2>Upcoming Deadlines</h2>
                        <button class="view-all-btn">View All</button>
                    </div>
                    <div class="deadline-list">
                        <?php
                        $counter = 0;
                        if ($deadlines && is_array($deadlines)) {
                            foreach ($deadlines as $d) {

                                if ($counter >= 3) break;

                                $deadline_overdue = false;
                                $deadline_remaining_days = 0;
                                $deadline_course_name = $d["course_name"];
                                $deadline_period = "";
                                $deadline_lectuer_name = $d["lecturer_name"];
                                $deadline_status = $deadline_status_icon = $deadline_status_color = "";

                                if ($d["date"]) {
                                    $deadlineDate = new DateTime($d["date"]);
                                    $currentDate = new DateTime();
                                    $interval = $currentDate->diff($deadlineDate);
                                    $daysLeft = $interval->days;
                                    $deadline_remaining_days = $daysLeft;

                                    if ($currentDate > $deadlineDate) {
                                        $deadline_overdue = true;
                                        $deadline_period = $daysLeft == 0 ? "Overdue today" : "{$daysLeft}  days overdue";
                                    } else {
                                        $deadline_period = "{$daysLeft} days left";
                                    }

                                    if ($deadline_overdue || $daysLeft <= 2) {
                                        $deadline_status = "Urgent";
                                        $deadline_status_icon = "fa-calendar-times";
                                        $deadline_status_color = "var(--danger-color);";
                                    } elseif ($daysLeft <= 5) {
                                        $deadline_status = "Pending";
                                        $deadline_status_icon = "fa-calendar-day";
                                        $deadline_status_color = "var(--warning-color);";
                                    } else {
                                        $deadline_status = "Normal";
                                        $deadline_status_icon = "fa-calendar-check";
                                        $deadline_status_color = "var(--success-color);";
                                    }
                                }
                        ?>
                                <div class="deadline-item">
                                    <div class="deadline-icon" style="background-color: <?= $deadline_status_color ?>">
                                        <i class="fas <?= $deadline_status_icon ?>"></i>
                                    </div>
                                    <div class="deadline-details">
                                        <h4><?= $deadline_course_name ?></h4>
                                        <p>Results submission deadline</p>
                                        <div class="deadline-meta">
                                            <span class="deadline-date"> <i class="fas fa-clock"></i><?= $deadline_period ?></span>
                                            <span class="deadline-lecturer"><i class="fas fa-user"></i><?= $deadline_lectuer_name  ?></span>
                                        </div>
                                    </div>
                                    <div class="deadline-status <?= strtolower($deadline_status) ?>"><?= $deadline_status ?></div>
                                </div>
                            <?php
                                $counter++;
                            }
                        } else {
                            ?>
                            <div>No available deadline</div>
                        <?php
                        }
                        ?>
                    </div>
                </div>

                <div class="recent-activity">
                    <div class="section-header">
                        <h2>Recent Activity</h2>
                        <button class="view-all-btn">View All</button>
                    </div>
                    <div class="activity-list">
                        <?php
                        $counter = 0;
                        if ($recentActivities && is_array($recentActivities)) {
                            foreach ($recentActivities as $a) {

                                if ($counter >= 3) break;

                                $activity_action = $a["action"];
                                $activity_description = $a["description"];
                                $activity_period = $a["timestamp"];

                                $icon = match (strtolower($activity_action)) {
                                    'course assignment' => ['icon' => 'fa-user-plus', 'color' => 'var(--primary-color);'],
                                    'results submission deadline' => ['icon' => 'fa-calendar-plus', 'color' => 'var(--accent-color);'],
                                    'results submissted' => ['icon' => 'fa-file-upload', 'color' => 'var(--success-color);'],
                                    'login' => ['icon' => 'fa-sign-in', 'color' => 'var(--danger-color);'],
                                    default => ['icon' => 'fa-info-circle', 'color' => 'var(--accent-color);']
                                };

                                $timeAgo = $secretary->getTimeStamp($activity_period);
                        ?>
                                <div class="activity-item">
                                    <div class="activity-icon" style="background-color: <?= $icon["color"] ?>;">
                                        <i class="fas <?= $icon["icon"] ?>"></i>
                                    </div>
                                    <div class="activity-details">
                                        <h4><?= $activity_action ?></h4>
                                        <p><?= $activity_description ?></p>
                                        <span class="activity-time"><?= $timeAgo ?></span>
                                    </div>
                                </div>
                            <?php
                                $counter++;
                            }
                        } else {
                            ?>
                            <div>No recent activities</div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <button class="action-btn" id="assignCourseBtn">
                        <i class="fas fa-user-plus"></i>
                        <span>Assign Course</span>
                    </button>
                    <button class="action-btn" id="uploadCoursesBtn">
                        <i class="fas fa-file-upload"></i>
                        <span>Upload Courses</span>
                    </button>
                    <button class="action-btn" id="setDeadlineBtn">
                        <i class="fas fa-calendar-plus"></i>
                        <span>Set Deadline</span>
                    </button>
                    <button class="action-btn" id="viewResultsBtn">
                        <i class="fas fa-clipboard-list"></i>
                        <span>View Results</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Course Modal -->
    <div class="modal" id="assignCourseModal">
        <div class="modal-dialog modal-lg modal-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Assign Course to Lecturer</h2>
                    <button class="close-btn" id="closeAssignCourseModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="assign-course-tabs">
                        <button class="tab-btn active" data-tab="toLecturer">To Lecturer</button>
                        <button class="tab-btn" data-tab="toStudent">To Student</button>
                        <button class="tab-btn" data-tab="toClass">To Class</button>
                    </div>
                    <div class="form-group">
                        <label for="semesterSelect">Semester</label>
                        <select id="semesterSelect" required>
                            <option value="">-- Select Semester --</option>
                            <?php
                            if ($activeSemesters) {
                                foreach ($activeSemesters as $semester) {
                                    echo "<option value='{$semester['id']}'>{$semester['academic_year_name']} Semester {$semester['name']} </option>";
                                }
                            } else {
                                echo "<option value=''>No active semester</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="course-selection-header">
                            <label>Selected Courses</label>
                            <button type="button" id="departmentSelectCoursesBtn">
                                <i class="fas fa-search"></i> Find Courses
                            </button>
                        </div>
                        <div class="department-selected-courses-container">
                            <div id="departmentSelectedCoursesList">
                                <!-- Selected courses will be added here dynamically -->
                            </div>
                            <div class="department-selected-courses-empty" id="departmentNoCoursesMessage">
                                No courses selected. Click "Find Courses" to add courses.
                            </div>
                        </div>
                    </div>
                    <div class="tab-content active" id="toLecturer">
                        <div class="form-group">
                            <label for="lecturerSelect">Lecturer</label>
                            <select id="lecturerSelect" required>
                                <option value="">-- Select Lecturer --</option>
                                <?php
                                if (! $lecturers) {
                                    echo "<option value=''>No lecturers available</option>";
                                } else {
                                    foreach ($lecturers as $lecturer) {
                                        echo "<option value='{$lecturer['number']}'>{$lecturer['prefix']} {$lecturer['first_name']} {$lecturer['last_name']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="tab-content" id="toStudent">
                        <div class="form-group">
                            <label for="studentSelect">Student</label>
                            <select id="studentSelect" required>
                                <option value="">-- Select Student --</option>
                                <option value="all">All</option>
                                <?php
                                if (! $totalActiveStudents) {
                                    echo "<option value=''>No students available</option>";
                                } else {
                                    foreach ($activeStudents as $student) {
                                        echo "<option value='{$student['index_number']}'>{$student["prefix"]} {$student["first_name"]} {$student["last_name"]} {$student["suffix"]}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="tab-content" id="toClass">
                        <div class="form-group">
                            <label for="classSelect">Class</label>
                            <select id="classSelect" required>
                                <option value="">-- Select Class --</option>
                                <option value="all">All</option>
                                <?php
                                if (! $totalActiveClasses) {
                                    echo "<option value=''>No students available</option>";
                                } else {
                                    foreach ($activeClasses as $class) {
                                        echo "<option value='{$class['code']}'>{$class["code"]} ({$class["program_name"]})</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="assignmentNotes">Notes (Optional)</label>
                        <textarea id="assignmentNotes" rows="3" placeholder="Add any additional notes about this assignment"></textarea>
                    </div>
                    <input type="hidden" id="departmentSelect" name="department" value="<?= $departmentId ?>">
                    <input type="hidden" id="assignCourseActionSelect" name="action" value="toLecturer">
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" id="cancelAssignCourse">Cancel</button>
                    <button class="submit-btn" id="submitAssignCourse">Assign Course</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Courses Modal -->
    <div class="modal" id="uploadCoursesModal">
        <div class="modal-dialog modal-md modal-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Upload Courses</h2>
                    <button class="close-btn" id="closeUploadCoursesModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="tabs">
                        <button class="tab-btn active" data-tab="bulkUpload">Bulk Upload</button>
                        <button class="tab-btn" data-tab="singleCourse">Add Single Course</button>
                    </div>

                    <div class="tab-content active" id="bulkUpload">
                        <form id="bulkUploadForm">
                            <div class="upload-area">
                                <div class="upload-icon">
                                    <i class="fas fa-file-excel fa-3x"></i>
                                </div>
                                <h3>Upload Course List</h3>
                                <p>Upload an Excel file with course details</p>
                                <input type="file" id="courseFileUpload" class="file-input" accept=".xlsx, .xls">
                                <label for="courseFileUpload" class="file-label">Choose File</label>
                                <p class="selected-file-name" id="selectedFileName">No file selected</p>
                            </div>
                            <div class="template-download">
                                <p>Don't have the template? <a href="#" class="download-link">Download Template</a></p>
                            </div>
                        </form>
                    </div>

                    <div class="tab-content" id="singleCourse">
                        <form id="addCourseForm">
                            <div class="form-group">
                                <label for="courseCode">Course Code</label>
                                <input type="text" id="courseCode" placeholder="e.g. ML201" required>
                            </div>
                            <div class="form-group">
                                <label for="courseName">Course Name</label>
                                <input type="text" id="courseName" placeholder="e.g. Maritime Law" required>
                            </div>
                            <div class="form-group">
                                <label for="creditHours">Credit Hours</label>
                                <input type="number" id="creditHours" min="1" max="6" required>
                            </div>
                            <div class="form-group">
                                <label for="contactHours">Contact Hours</label>
                                <input type="number" id="contactHours" min="1" max="6" required>
                            </div>
                            <div class="form-group">
                                <label for="courseLevel">Level</label>
                                <select id="courseLevel" required>
                                    <option value="">-- Select Level --</option>
                                    <option value="100">100</option>
                                    <option value="200">200</option>
                                    <option value="300">300</option>
                                    <option value="400">400</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="courseCategory">Category</label>
                                <select id="courseCategory" name="courseCategory" required>
                                    <option value="">-- Select Category --</option>
                                    <?php
                                    $course_categories = $course_category->fetch();
                                    foreach ($course_categories as $cc) {
                                    ?>
                                        <option value="<?= $cc["id"] ?>"><?= $cc["name"] ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="courseSemester">Semester</label>
                                <select id="courseSemester" required>
                                    <option value="">-- Select Semester --</option>
                                    <option value="1">First Semester</option>
                                    <option value="2">Second Semester</option>
                                </select>
                            </div>
                            <input type="hidden" name="department" id="courseDepartment" value="<?= $departmentId ?>">
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" id="cancelUploadCourses">Cancel</button>
                    <button class="submit-btn" id="submitUploadCourses">Upload Courses</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Set Deadline Modal -->
    <div class="modal" id="setDeadlineModal">
        <div class="modal-dialog modal-md modal-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Set Results Submission Deadline</h2>
                    <button class="close-btn" id="closeSetDeadlineModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="deadlineForm">
                        <!-- Inside the deadlineForm, replace the deadlineCourse select with this: -->
                        <div class="course-selection-container">
                            <div class="form-group">
                                <label for="deadlineLecturerSelect">Lecturer</label>
                                <select id="deadlineLecturerSelect" required>
                                    <option value="">-- Select Lecturer --</option>
                                    <?php
                                    if (! $lecturers) {
                                        echo "<option value=''>No lecturers available</option>";
                                    } else {
                                        foreach ($lecturers as $lecturer) {
                                            echo "<option value='{$lecturer['number']}'>{$lecturer['prefix']} {$lecturer['first_name']} {$lecturer['last_name']}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="course-selection-header">
                                    <label>Selected Courses</label>
                                    <button type="button" id="selectCoursesBtn" class="selectCoursesBtn">
                                        <i class="fas fa-search"></i> Find Courses
                                    </button>
                                </div>
                                <div class="selected-courses-container">
                                    <div id="selectedCoursesList">
                                        <!-- Selected courses will be added here dynamically -->
                                    </div>
                                    <div class="selected-courses-empty" id="noCoursesMessage">
                                        No courses selected. Click "Find Courses" to add courses.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="deadlineDate">Deadline Date</label>
                            <input type="date" id="deadlineDate" required>
                        </div>
                        <div class="form-group">
                            <label for="deadlineNotes">Notes (Optional)</label>
                            <textarea id="deadlineNotes" rows="3" placeholder="Add any additional notes about this deadline"></textarea>
                        </div>
                        <input type="hidden" id="deadlineSelectDepartment" name="department" value="<?= $departmentId ?>">
                        <input type="hidden" id="deadlineSelectSemester" name="semester" value="<?= $semesterId ?>">
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" id="cancelSetDeadline">Cancel</button>
                    <button class="submit-btn" id="submitSetDeadline">Set Deadline</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Results Modal -->
    <div class="modal" id="viewResultsModal">
        <div class="modal-dialog modal-xl modal-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Submitted Exam Results</h2>
                    <button class="close-btn" id="closeViewResultsModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="results-filter">
                        <div class="form-group">
                            <label for="filterCourse">Filter by Course</label>
                            <select id="filterCourse">
                                <option value="">All Courses</option>
                                <option value="ML201">Maritime Law (ML201)</option>
                                <option value="NS302">Navigation Systems (NS302)</option>
                                <option value="ME101">Marine Engineering (ME101)</option>
                                <option value="OC205">Oceanography (OC205)</option>
                                <option value="SM401">Ship Management (SM401)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="filterLecturer">Filter by Lecturer</label>
                            <select id="filterLecturer">
                                <option value="">All Lecturers</option>
                                <option value="1">Dr. James Wilson</option>
                                <option value="2">Prof. Sarah Johnson</option>
                                <option value="3">Dr. Michael Brown</option>
                                <option value="4">Dr. Emily Davis</option>
                                <option value="5">Prof. Robert Taylor</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="filterStatus">Filter by Status</label>
                            <select id="filterStatus">
                                <option value="">All Status</option>
                                <option value="submitted">Submitted</option>
                                <option value="pending">Pending</option>
                                <option value="overdue">Overdue</option>
                            </select>
                        </div>
                    </div>

                    <div class="results-table-container">
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th>Course Code</th>
                                    <th>Course Title</th>
                                    <th>Lecturer</th>
                                    <th>Submission Date</th>
                                    <th>Deadline</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>ML201</td>
                                    <td>Maritime Law</td>
                                    <td>Dr. James Wilson</td>
                                    <td>15/04/2023</td>
                                    <td>20/04/2023</td>
                                    <td><span class="status-badge submitted">Submitted</span></td>
                                    <td>
                                        <button class="action-icon view-results" data-course="ML201">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="action-icon download-results" data-course="ML201">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>NS302</td>
                                    <td>Navigation Systems</td>
                                    <td>Prof. Sarah Johnson</td>
                                    <td>-</td>
                                    <td>25/04/2023</td>
                                    <td><span class="status-badge pending">Pending</span></td>
                                    <td>
                                        <button class="action-icon remind-lecturer" data-course="NS302">
                                            <i class="fas fa-bell"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>ME101</td>
                                    <td>Marine Engineering</td>
                                    <td>Dr. Michael Brown</td>
                                    <td>10/04/2023</td>
                                    <td>12/04/2023</td>
                                    <td><span class="status-badge submitted">Submitted</span></td>
                                    <td>
                                        <button class="action-icon view-results" data-course="ME101">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="action-icon download-results" data-course="ME101">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>OC205</td>
                                    <td>Oceanography</td>
                                    <td>Dr. Emily Davis</td>
                                    <td>-</td>
                                    <td>05/04/2023</td>
                                    <td><span class="status-badge overdue">Overdue</span></td>
                                    <td>
                                        <button class="action-icon remind-lecturer" data-course="OC205">
                                            <i class="fas fa-exclamation-circle"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>SM401</td>
                                    <td>Ship Management</td>
                                    <td>Prof. Robert Taylor</td>
                                    <td>08/04/2023</td>
                                    <td>10/04/2023</td>
                                    <td><span class="status-badge submitted">Submitted</span></td>
                                    <td>
                                        <button class="action-icon view-results" data-course="SM401">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="action-icon download-results" data-course="SM401">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" id="closeResultsBtn">Close</button>
                    <button class="submit-btn" id="exportResultsBtn">Export Report</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Results Detail Modal -->
    <div class="modal" id="courseResultsDetailModal">
        <div class="modal-dialog modal-xl modal-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Maritime Law (ML201) - Results Details</h2>
                    <button class="close-btn" id="closeCourseResultsDetailModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="results-summary">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon" style="background-color: var(--primary-color);">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-info">
                                    <h3>45</h3>
                                    <p>Registered Students</p>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon" style="background-color: var(--accent-color);">
                                    <i class="fas fa-pen"></i>
                                </div>
                                <div class="stat-info">
                                    <h3>42</h3>
                                    <p>Students Who Took Exam</p>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon" style="background-color: var(--success-color);">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stat-info">
                                    <h3>38</h3>
                                    <p>Passed Students</p>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon" style="background-color: var(--danger-color);">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div class="stat-info">
                                    <h3>4</h3>
                                    <p>Failed Students</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="results-tabs">
                        <button class="tab-btn active" data-tab="allStudents">All Students</button>
                        <button class="tab-btn" data-tab="passedStudents">Passed</button>
                        <button class="tab-btn" data-tab="failedStudents">Failed</button>
                        <button class="tab-btn" data-tab="absentStudents">Absent</button>
                    </div>

                    <div class="tab-content active" id="allStudents">
                        <div class="results-table-container">
                            <table class="results-table">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Class</th>
                                        <th>Continuous Assessment (40%)</th>
                                        <th>Exam Score (60%)</th>
                                        <th>Total (100%)</th>
                                        <th>Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>RMU001234</td>
                                        <td>John Smith</td>
                                        <td>Maritime Law 2A</td>
                                        <td>32</td>
                                        <td>45</td>
                                        <td>77</td>
                                        <td><span class="grade a">A</span></td>
                                    </tr>
                                    <tr>
                                        <td>RMU001235</td>
                                        <td>Mary Johnson</td>
                                        <td>Maritime Law 2A</td>
                                        <td>28</td>
                                        <td>42</td>
                                        <td>70</td>
                                        <td><span class="grade b">B</span></td>
                                    </tr>
                                    <tr>
                                        <td>RMU001236</td>
                                        <td>Robert Davis</td>
                                        <td>Maritime Law 2B</td>
                                        <td>35</td>
                                        <td>50</td>
                                        <td>85</td>
                                        <td><span class="grade a">A</span></td>
                                    </tr>
                                    <tr>
                                        <td>RMU001237</td>
                                        <td>Sarah Wilson</td>
                                        <td>Maritime Law 2B</td>
                                        <td>18</td>
                                        <td>30</td>
                                        <td>48</td>
                                        <td><span class="grade f">F</span></td>
                                    </tr>
                                    <tr>
                                        <td>RMU001238</td>
                                        <td>Michael Brown</td>
                                        <td>Maritime Law 2A</td>
                                        <td>25</td>
                                        <td>38</td>
                                        <td>63</td>
                                        <td><span class="grade c">C</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-content" id="passedStudents">
                        <!-- Similar table structure for passed students -->
                    </div>

                    <div class="tab-content" id="failedStudents">
                        <!-- Similar table structure for failed students -->
                    </div>

                    <div class="tab-content" id="absentStudents">
                        <!-- Similar table structure for absent students -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" id="backToResultsBtn">Back to Results</button>
                    <button class="submit-btn" id="downloadDetailedResultsBtn">Download Detailed Report</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Selection Modal -->
    <div class="modal" id="departmentCourseSelectionModal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Select Courses</h2>
                    <button class="close-btn" id="closeDepartmentCourseSelectionModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="department-course-search">
                        <input type="text" id="departmentCourseSearchInput" placeholder="Search by course code or name">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="department-course-list" id="departmentCourseList">
                        <!-- Course items will be added here dynamically -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" id="closeDepartmentCourseSelectionModal">Cancel</button>
                    <button class="submit-btn" id="confirmDepartmentCourseSelectionBtn">Confirm Selection</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Selection Modal -->
    <div class="modal" id="courseSelectionModal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Select Courses</h2>
                    <button class="close-btn" id="closeCourseSelectionModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="course-search">
                        <input type="text" id="courseSearchInput" placeholder="Search by course code or name">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="course-list" id="courseList">
                        <!-- Course items will be added here dynamically -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" id="closeCourseSelectionModal">Cancel</button>
                    <button class="submit-btn" id="confirmCourseSelectionBtn">Confirm Selection</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Make PHP session data available to JS

        document.addEventListener("DOMContentLoaded", function() {

            let activeSemesters = null;
            let courses = null;
            let assignedCourses = null;
            let semesterCourses = null;
            let lecturersAndHods = null;
            let deadlines = null;
            let results = null;
            let students = null;
            let messages = null;
            let notifications = null;

            const user = <?= json_encode($staffData); ?>;
            let departmentCourses = null;

            const departmentId = user ? user.department_id : null;
            const userId = user ? user.number : null;

            if (departmentId === null || departmentId === undefined) {
                console.error("Department ID is not available. Cannot fetch data.");
                return;
            }

            if (userId === null || userId === undefined) {
                console.error("User ID is not available. Cannot fetch data.");
                return;
            }

            document.getElementById("semesterSelect").addEventListener("change", function() {
                if (departmentCourses !== null) {
                    return;
                }
                const selectedSemester = this.value;
                if (selectedSemester) {
                    fetch(`../endpoint/fetch-semester-courses`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams({
                                semester: selectedSemester,
                            }).toString()
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                semesterCourses = departmentCourses = data.data;
                            } else {
                                alert("Failed to fetch courses for selected semester: ", data.message);
                            }
                        })
                        .catch(error => console.error("Error fetching courses for selected semester:", error));
                }
            });

            async function fetchData() {
                try {
                    const [
                        activeSemestersRes,
                        coursesRes,
                        assignedCoursesRes,
                        staffRes,
                        // deadlinesRes,
                        // resultsRes,
                        // studentsRes,
                        // messagesRes,
                        // notificationsRes
                    ] = await Promise.all([
                        fetch(`../endpoint/active-semesters`, {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            }
                        }),
                        fetch(`../endpoint/fetch-course`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: JSON.stringify({
                                departmentId
                            })
                        }),
                        fetch(`../endpoint/fetch-assigned-courses`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams({
                                department: departmentId,
                            }).toString()
                        }),
                        fetch(`../endpoint/fetch-staff`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            }
                        }),
                        // fetch(`../endpoint/fetch-deadline`, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: JSON.stringify({ departmentId }) }),
                        // fetch(`../endpoint/fetch-result`, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: JSON.stringify({ departmentId }) }),
                        // fetch(`../endpoint/fetch-student`, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: JSON.stringify({ departmentId }) }),
                        // fetch(`../endpoint/fetch-message`, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: JSON.stringify({ userId }) }),
                        // fetch(`../endpoint/fetch-notification`, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: JSON.stringify({ userId }) })
                    ]);

                    const activeSemestersData = await activeSemestersRes.json();
                    const coursesData = await coursesRes.json();
                    const assignedCoursesData = await assignedCoursesRes.json();
                    const staffData = await staffRes.json();
                    // const deadlinesData = await deadlinesRes.json();
                    // const resultsData = await resultsRes.json();
                    // const studentsData = await studentsRes.json();
                    // const messagesData = await messagesRes.json();
                    // const notificationsData = await notificationsRes.json();

                    // Populate global object
                    if (activeSemestersData.success) activeSemesters = activeSemestersData.data;
                    if (coursesData.success) courses = coursesData.data;
                    if (assignedCoursesData.success) assignedCourses = assignedCoursesData.data;
                    if (staffData.success) lecturersAndHods = staffData.data.filter(s => s.role === 'lecturer' || s.role === 'hod');
                    // appData.DEADLINES = deadlinesData.data;
                    // appData.RESULTS = resultsData.data;
                    // appData.STUDENTS = studentsData.data;
                    // appData.MESSAGES = messagesData.data;
                    // appData.NOTIFICATIONS = notificationsData.data;

                    console.log("Courses ready:", courses);
                    console.log("Assigned Courses ready:", assignedCourses);
                    console.log("Lecturers and HODs ready:", lecturersAndHods);
                    console.log("Deadlines ready:", deadlines);
                    console.log("Results ready:", results);
                    console.log("Students ready:", students);
                    console.log("Messages ready:", messages);
                    console.log("Notifications ready:", notifications);

                } catch (error) {
                    console.error("Error fetching data:", error);
                }
            }
            fetchData();

            // Modal Functions
            function openModal(modalId) {
                document.getElementById(modalId).classList.add("active");
                document.body.style.overflow = "hidden";
            }

            function closeModal(modalId) {
                document.getElementById(modalId).classList.remove("active");
                document.body.style.overflow = "auto";
            }

            // Close modal when clicking outside
            document.querySelectorAll(".modal").forEach((modal) => {
                modal.addEventListener("click", function(e) {
                    if (e.target === this) {
                        this.classList.remove("active");
                        document.body.style.overflow = "auto";
                    }
                });
            });

            // Course Selection Modal
            const departmentSelectCoursesBtn = document.getElementById("departmentSelectCoursesBtn");
            const closeDepartmentCourseSelectionModal = document.getElementById("closeDepartmentCourseSelectionModal");
            const confirmDepartmentCourseSelectionBtn = document.getElementById("confirmDepartmentCourseSelectionBtn");
            const departmentCourseSearchInput = document.getElementById("departmentCourseSearchInput");

            if (departmentSelectCoursesBtn) {
                departmentSelectCoursesBtn.addEventListener("click", () => {
                    openModal("departmentCourseSelectionModal");
                });
            }

            if (closeDepartmentCourseSelectionModal) {
                closeDepartmentCourseSelectionModal.addEventListener("click", () => {
                    closeModal("departmentCourseSelectionModal");
                });
            }

            if (confirmDepartmentCourseSelectionBtn) {
                confirmDepartmentCourseSelectionBtn.addEventListener("click", () => {
                    closeModal("departmentCourseSelectionModal");
                });
            }

            if (departmentCourseSearchInput) {
                departmentCourseSearchInput.addEventListener("input", () => {
                    departmentSearchCourses();
                });

                // Initialize course list on modal open
                departmentCourseSearchInput.addEventListener("focus", () => {
                    if (departmentCourseSearchInput.value === "") {
                        departmentSearchCourses();
                    }
                });
            }

            // Initialize course list when modal opens
            if (departmentSelectCoursesBtn) {
                departmentSelectCoursesBtn.addEventListener("click", () => {
                    setTimeout(() => {
                        departmentSearchCourses();
                    }, 100);
                });
            }

            function departmentSearchCourses() {
                const searchTerm = document.getElementById("departmentCourseSearchInput").value.toLowerCase();
                const courseList = document.getElementById("departmentCourseList");
                courseList.innerHTML = "";

                // Check if assignedCourses has data
                if (!departmentCourses || departmentCourses.length === 0) {
                    courseList.innerHTML = `
                        <div class="department-no-courses-message">
                            <i class="fas fa-info-circle"></i>
                            <p>No courses are available.</p>
                        </div>
                    `;
                    return;
                }

                departmentCourses.forEach((course) => {
                    if (course.code.toLowerCase().includes(searchTerm) || course.name.toLowerCase().includes(searchTerm)) {
                        // Check if course is already selected
                        const isSelected = document.querySelector(`.department-selected-course[data-code="${course.code}"]`) !== null;
                        const courseItem = document.createElement("div");
                        courseItem.className = "department-course-item";

                        // Add a class if the course is selected
                        if (isSelected) {
                            courseItem.classList.add("department-course-selected");
                        }

                        courseItem.innerHTML = `
                                <div class="department-course-info">
                                    <strong>${course.code}</strong> - ${course.name}
                                </div>
                                <button class="department-add-course-btn ${isSelected ? "selected" : ""}" data-code="${course.code}" data-name="${course.name}" ${isSelected ? "disabled" : ""}>
                                    <i class="fas ${isSelected ? "fa-check" : "fa-plus"}"></i>
                                </button>
                            `;
                        courseList.appendChild(courseItem);
                    }
                });

                // Add event listeners to the add buttons
                document.querySelectorAll(".department-add-course-btn:not(.selected)").forEach((btn) => {
                    btn.addEventListener("click", function() {
                        const code = this.getAttribute("data-code");
                        const name = this.getAttribute("data-name");
                        departmentAddCourseToSelection(code, name);

                        // Update the button to show it's selected
                        this.classList.add("selected");
                        this.disabled = true;
                        this.querySelector("i").classList.remove("fa-plus");
                        this.querySelector("i").classList.add("fa-check");
                        this.closest(".department-course-item").classList.add("course-selected");
                    });
                });
            }

            function departmentAddCourseToSelection(code, name) {
                const selectedCoursesList = document.getElementById("departmentSelectedCoursesList");

                // Check if course is already added
                if (document.querySelector(`.department-selected-course[data-code="${code}"]`)) {
                    return;
                }

                const courseItem = document.createElement("div");
                courseItem.className = "department-selected-course";
                courseItem.setAttribute("data-code", code);
                courseItem.innerHTML = `
                    <div class="department-course-info">
                        <strong>${code}</strong> - ${name}
                    </div>
                    <button class="department-remove-course-btn" data-code="${code}"">
                        <i class="fas fa-times"></i>
                    </button>
                    <input type="hidden" name="departmentSelectedCourses[]" value="${code}">
                `;
                selectedCoursesList.appendChild(courseItem);

                // Add event listener to the remove button
                courseItem.querySelector(".department-remove-course-btn").addEventListener("click", function() {
                    const code = this.getAttribute("data-code");
                    departmentRemoveFromSelection(code);
                });
            }

            function departmentRemoveFromSelection(code) {
                const courseItem = document.querySelector(`.department-selected-course[data-code="${code}"]`)
                if (courseItem) {
                    courseItem.remove()

                    // Update the course in the search list if it's visible
                    const courseInList = document.querySelector(`.department-course-item .department-add-course-btn[data-code="${code}"]`)
                    if (courseInList) {
                        courseInList.classList.remove("selected")
                        courseInList.disabled = false
                        courseInList.querySelector("i").classList.remove("fa-check")
                        courseInList.querySelector("i").classList.add("fa-plus")
                        courseInList.closest(".department-course-item").classList.remove("department-course-selected")

                        // Re-add the click event listener
                        courseInList.addEventListener("click", function() {
                            const name = this.getAttribute("data-name")
                            departmentAddCourseToSelection(code, name)

                            // Update the button to show it's selected
                            this.classList.add("selected")
                            this.disabled = true
                            this.querySelector("i").classList.remove("fa-plus")
                            this.querySelector("i").classList.add("fa-check")
                            this.closest(".department-course-item").classList.add("department-course-selected")
                        });
                    }
                }
            }

            // Assign Course Modal
            const assignCourseBtn = document.getElementById("assignCourseBtn");
            const closeAssignCourseModal = document.getElementById("closeAssignCourseModal");
            const cancelAssignCourse = document.getElementById("cancelAssignCourse");
            const submitAssignCourse = document.getElementById("submitAssignCourse");

            assignCourseBtn.addEventListener("click", function() {
                openModal("assignCourseModal");
            });

            closeAssignCourseModal.addEventListener("click", function() {
                closeModal("assignCourseModal");
            });

            cancelAssignCourse.addEventListener("click", function() {
                closeModal("assignCourseModal");
            });

            // Tab functionality for Upload Courses Modal
            const assignCourseTabs = document.querySelectorAll(".assign-course-tabs .tab-btn")

            assignCourseTabs.forEach((btn) => {
                btn.addEventListener("click", function() {
                    const tabId = this.getAttribute("data-tab");

                    // Remove active class from all tabs and contents
                    assignCourseTabs.forEach((btn) => btn.classList.remove("active"));
                    document.querySelectorAll(".tab-content").forEach((content) => content.classList.remove("active"));

                    // Add active class to clicked tab and corresponding content
                    this.classList.add("active");
                    document.getElementById(tabId).classList.add("active");

                    // add to actionSelect
                    document.getElementById("assignCourseActionSelect").value = tabId;
                    console.log(document.getElementById("assignCourseActionSelect").value);
                });
            });

            submitAssignCourse.addEventListener("click", function() {
                // Validate form
                const semesterSelect = document.getElementById("semesterSelect");
                const assignmentNotes = document.getElementById("assignmentNotes");
                const departmentSelect = document.getElementById("departmentSelect");

                if (!semesterSelect.value) {
                    alert("Please fill in all required fields");
                    return;
                }

                const selectedCourseElements = document.querySelectorAll('#departmentSelectedCoursesList .department-selected-course');
                if (selectedCourseElements.length === 0) {
                    alert("Please select at least one course");
                    return;
                }

                const selectedCourses = [];
                selectedCourseElements.forEach((element) => {
                    selectedCourses.push(element.getAttribute("data-code"));
                });

                const form = document.getElementById("assignCourseForm");
                const action = document.getElementById("assignCourseActionSelect").value;

                // Simulate API call
                let formData = {
                    semester: semesterSelect.value,
                    courses: selectedCourses,
                    notes: assignmentNotes.value,
                    department: departmentSelect.value,
                }

                switch (action) {
                    case 'toLecturer':
                        const lecturerSelect = document.getElementById("lecturerSelect");

                        if (!lecturerSelect.value) {
                            alert("Please fill in all required fields");
                            return;
                        }

                        formData.action = "lecturer";
                        formData.lecturer = lecturerSelect.value;
                        break;

                    case 'toClass':
                        const classSelect = document.getElementById("classSelect");

                        if (!classSelect.value) {
                            alert("Please fill in all required fields");
                            return;
                        }

                        formData.action = "class";
                        formData.class = classSelect.value;
                        break;

                    case 'toStudent':
                        const studentSelect = document.getElementById("studentSelect");

                        if (!studentSelect.value) {
                            alert("Please fill in all required fields");
                            return;
                        }

                        formData.action = "student";
                        formData.student = studentSelect.value;
                        break;

                    default:
                        alert("Course(s) can only be asigned to lecturer(s), student(s) and class(es)!");
                        return;
                }

                console.log(formData);

                $.ajax({
                    type: "POST",
                    url: "../endpoint/assign-course",
                    data: formData,
                    success: function(result) {
                        console.log(result);
                        if (result.success) {
                            alert(result.message);
                            closeModal('assignCourseModal');
                            form.reset();
                        } else {
                            alert(result['message']);
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            // Upload Courses Modal
            const uploadCoursesBtn = document.getElementById("uploadCoursesBtn");
            const closeUploadCoursesModal = document.getElementById("closeUploadCoursesModal");
            const cancelUploadCourses = document.getElementById("cancelUploadCourses");
            const submitUploadCourses = document.getElementById("submitUploadCourses");

            uploadCoursesBtn.addEventListener("click", function() {
                openModal("uploadCoursesModal");
            })

            closeUploadCoursesModal.addEventListener("click", function() {
                closeModal("uploadCoursesModal");
            })

            cancelUploadCourses.addEventListener("click", function() {
                closeModal("uploadCoursesModal");
            });

            // Tab functionality for Upload Courses Modal
            const tabBtns = document.querySelectorAll(".tab-btn")

            tabBtns.forEach((btn) => {
                btn.addEventListener("click", function() {
                    const tabId = this.getAttribute("data-tab");

                    // Remove active class from all tabs and contents
                    tabBtns.forEach((btn) => btn.classList.remove("active"));
                    document.querySelectorAll(".tab-content").forEach((content) => content.classList.remove("active"));

                    // Add active class to clicked tab and corresponding content
                    this.classList.add("active");
                    document.getElementById(tabId).classList.add("active");
                });
            });

            // File upload handling
            const courseFileUpload = document.getElementById("courseFileUpload");
            const selectedFileName = document.getElementById("selectedFileName");

            courseFileUpload.addEventListener("change", function() {
                if (this.files.length > 0) {
                    selectedFileName.textContent = this.files[0].name;
                } else {
                    selectedFileName.textContent = "No file selected";
                }
            });

            submitUploadCourses.addEventListener("click", async function() {
                const activeTab = document.querySelector(".tab-content.active").id

                if (activeTab === "bulkUpload") {
                    if (!courseFileUpload.files.length) {
                        alert("Please select a file to upload");
                        return;
                    }

                    // Simulate file upload
                    const courseFile = courseFileUpload.files[0];
                    const department = document.getElementById("courseDepartment");

                    if (!department.value) {
                        alert("Please fill in all required fields");
                        return;
                    }

                    const formData = new FormData();
                    formData.append("courseFile", courseFile);
                    formData.append("departmentId", department.value);

                    $.ajax({
                        type: "POST",
                        url: "../endpoint/upload-courses",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(result) {
                            console.log(result);
                            if (result.success) {
                                alert(result.message);
                                closeModal('uploadCoursesModal');
                                document.getElementById("bulkUploadForm").reset();
                            } else {
                                alert(result.message);
                            }
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });


                } else {
                    // Single course form validation
                    const courseCode = document.getElementById("courseCode");
                    const courseName = document.getElementById("courseName");
                    const creditHours = document.getElementById("creditHours");
                    const contactHours = document.getElementById("contactHours");
                    const courseLevel = document.getElementById("courseLevel");
                    const courseCategory = document.getElementById("courseCategory");
                    const courseSemester = document.getElementById("courseSemester");
                    const department = document.getElementById("courseDepartment");

                    if (!courseCode.value || !courseName.value || !creditHours.value || !contactHours.value || !department.value || !courseLevel.value || !courseSemester.value) {
                        alert("Please fill in all required fields");
                        return;
                    }

                    // Simulate API call
                    const formData = {
                        courseCode: courseCode.value,
                        courseName: courseName.value,
                        creditHours: creditHours.value,
                        contactHours: contactHours.value,
                        semester: courseSemester.value,
                        level: courseLevel.value,
                        category: courseCategory.value,
                        departmentId: department.value,
                    };

                    $.ajax({
                        type: "POST",
                        url: "../endpoint/add-course",
                        data: formData,
                        success: function(result) {
                            console.log(result);
                            if (result.success) {
                                alert(result.message);
                                closeModal('uploadCoursesModal');
                                document.getElementById("addCourseForm").reset();
                            } else {
                                alert(result['message']);
                            }
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                }
            });

            // Set Deadline Modal
            const setDeadlineBtn = document.getElementById("setDeadlineBtn");
            const closeSetDeadlineModal = document.getElementById("closeSetDeadlineModal");
            const cancelSetDeadline = document.getElementById("cancelSetDeadline");
            const submitSetDeadline = document.getElementById("submitSetDeadline");

            setDeadlineBtn.addEventListener("click", function() {
                openModal("setDeadlineModal");
            });

            closeSetDeadlineModal.addEventListener("click", function() {
                closeModal("setDeadlineModal");
            });

            cancelSetDeadline.addEventListener("click", function() {
                closeModal("setDeadlineModal");
            });

            function searchCourses() {
                const searchTerm = document.getElementById("courseSearchInput").value.toLowerCase();
                const courseList = document.getElementById("courseList");
                courseList.innerHTML = "";

                // Check if assignedCourses has data
                if (!assignedCourses || assignedCourses.length === 0) {
                    courseList.innerHTML = `
                        <div class="no-courses-message">
                            <i class="fas fa-info-circle"></i>
                            <p>No courses are available.</p>
                        </div>
                    `;
                    return;
                }

                assignedCourses.forEach((course) => {
                    if (course.course_code.toLowerCase().includes(searchTerm) || course.course_name.toLowerCase().includes(searchTerm)) {
                        // Check if course is already selected
                        const isSelected = document.querySelector(`.selected-course[data-code="${course.course_code}"]`) !== null;
                        const courseItem = document.createElement("div");
                        courseItem.className = "course-item";

                        // Add a class if the course is selected
                        if (isSelected) {
                            courseItem.classList.add("course-selected");
                        }

                        courseItem.innerHTML = `
                            <div class="course-info">
                                <strong>${course.course_code}</strong> - ${course.course_name}
                            </div>
                            <button class="add-course-btn ${isSelected ? "selected" : ""}" data-code="${course.course_code}" data-name="${course.course_name}" ${isSelected ? "disabled" : ""}>
                                <i class="fas ${isSelected ? "fa-check" : "fa-plus"}"></i>
                            </button>
                        `;
                        courseList.appendChild(courseItem);
                    }
                });

                // Add event listeners to the add buttons
                document.querySelectorAll(".add-course-btn:not(.selected)").forEach((btn) => {
                    btn.addEventListener("click", function() {
                        const code = this.getAttribute("data-code");
                        const name = this.getAttribute("data-name");
                        addCourseToSelection(code, name);

                        // Update the button to show it's selected
                        this.classList.add("selected");
                        this.disabled = true;
                        this.querySelector("i").classList.remove("fa-plus");
                        this.querySelector("i").classList.add("fa-check");
                        this.closest(".course-item").classList.add("course-selected");
                    });
                });
            }

            function addCourseToSelection(code, name) {
                const selectedCoursesList = document.getElementById("selectedCoursesList");

                // Check if course is already added
                if (document.querySelector(`.selected-course[data-code="${code}"]`)) {
                    return;
                }

                const courseItem = document.createElement("div");
                courseItem.className = "selected-course";
                courseItem.setAttribute("data-code", code);
                courseItem.innerHTML = `
                    <div class="course-info">
                    <strong>${code}</strong> - ${name}
                    </div>
                    <button class="remove-course-btn" data-code="${code}">
                    <i class="fas fa-times"></i>
                    </button>
                    <input type="hidden" name="selectedCourses[]" value="${code}">
                `;
                selectedCoursesList.appendChild(courseItem);

                // Add event listener to the remove button
                courseItem.querySelector(".remove-course-btn").addEventListener("click", function() {
                    const code = this.getAttribute("data-code");
                    removeFromSelection(code);
                });
            }

            function removeFromSelection(code) {
                const courseItem = document.querySelector(`.selected-course[data-code="${code}"]`)
                if (courseItem) {
                    courseItem.remove()

                    // Update the course in the search list if it's visible
                    const courseInList = document.querySelector(`.course-item .add-course-btn[data-code="${code}"]`)
                    if (courseInList) {
                        courseInList.classList.remove("selected")
                        courseInList.disabled = false
                        courseInList.querySelector("i").classList.remove("fa-check")
                        courseInList.querySelector("i").classList.add("fa-plus")
                        courseInList.closest(".course-item").classList.remove("course-selected")

                        // Re-add the click event listener
                        courseInList.addEventListener("click", function() {
                            const name = this.getAttribute("data-name")
                            addCourseToSelection(code, name)

                            // Update the button to show it's selected
                            this.classList.add("selected")
                            this.disabled = true
                            this.querySelector("i").classList.remove("fa-plus")
                            this.querySelector("i").classList.add("fa-check")
                            this.closest(".course-item").classList.add("course-selected")
                        });
                    }
                }
            }

            // Course Selection Modal
            const selectCoursesBtn = document.getElementById("selectCoursesBtn");
            const closeCourseSelectionModal = document.getElementById("closeCourseSelectionModal");
            const confirmCourseSelectionBtn = document.getElementById("confirmCourseSelectionBtn");
            const courseSearchInput = document.getElementById("courseSearchInput");

            if (selectCoursesBtn) {
                selectCoursesBtn.addEventListener("click", () => {
                    openModal("courseSelectionModal");
                });
            }

            if (closeCourseSelectionModal) {
                closeCourseSelectionModal.addEventListener("click", () => {
                    closeModal("courseSelectionModal");
                });
            }

            if (confirmCourseSelectionBtn) {
                confirmCourseSelectionBtn.addEventListener("click", () => {
                    closeModal("courseSelectionModal");
                });
            }

            if (courseSearchInput) {
                courseSearchInput.addEventListener("input", () => {
                    searchCourses();
                });

                // Initialize course list on modal open
                courseSearchInput.addEventListener("focus", () => {
                    if (courseSearchInput.value === "") {
                        searchCourses();
                    }
                });
            }

            // Initialize course list when modal opens
            if (selectCoursesBtn) {
                selectCoursesBtn.addEventListener("click", () => {
                    setTimeout(() => {
                        searchCourses();
                    }, 100);
                });
            }

            if (submitSetDeadline) {
                submitSetDeadline.addEventListener("click", function() {

                    const selectedLecturer = document.getElementById("deadlineLecturerSelect").value;
                    if (!selectedLecturer) {
                        alert("Please select a semester");
                        return;
                    }

                    const selectedCourseElements = document.querySelectorAll('#selectedCoursesList .selected-course');
                    if (selectedCourseElements.length === 0) {
                        alert("Please select at least one course");
                        return;
                    }

                    const deadlineDate = document.getElementById("deadlineDate");
                    if (!deadlineDate.value) {
                        alert("Please select a deadline date");
                        return;
                    }

                    const deadlineSelectDepartment = document.getElementById("deadlineSelectDepartment");
                    if (!deadlineSelectDepartment.value) {
                        alert("Please no department has been set");
                        return;
                    }

                    const deadlineSelectSemester = document.getElementById("deadlineSelectSemester");
                    if (!deadlineSelectSemester.value) {
                        alert("Please select a deadline date");
                        return;
                    }

                    const selectedCourses = [];
                    selectedCourseElements.forEach((element) => {
                        selectedCourses.push(element.getAttribute("data-code"));
                    });

                    // Create the form data
                    const formData = {
                        courses: selectedCourses,
                        lecturer: selectedLecturer,
                        department: deadlineSelectDepartment.value,
                        semester: deadlineSelectSemester.value,
                        date: deadlineDate.value,
                        note: document.getElementById("deadlineNotes").value || "",
                    };

                    console.log(formData);

                    // Show loading state
                    submitSetDeadline.disabled = true;
                    submitSetDeadline.textContent = "Submitting...";

                    $.ajax({
                        type: "POST",
                        url: "../endpoint/add-deadline",
                        data: formData,
                        success: function(result) {
                            console.log(result);

                            if (result.success) {
                                alert(result.message || 'Deadlines set successfully!');
                                closeModal("setDeadlineModal");
                                document.getElementById("deadlineForm").reset();

                                // Clear the selected courses list
                                document.getElementById("selectedCoursesList").innerHTML = "";

                                // Show the "no courses" message
                                const noCoursesMessage = document.getElementById("noCoursesMessage");
                                if (noCoursesMessage) {
                                    noCoursesMessage.style.display = "block";
                                }
                            } else {
                                alert(result.message);
                            }
                        },
                        error: function(error) {
                            console.error(error);
                            alert("There was an error setting the deadlines. Please try again.");
                        },
                        complete: function() {
                            // Reset button state
                            submitSetDeadline.disabled = false;
                            submitSetDeadline.textContent = "Set Deadline";
                        }
                    });
                });
            }


            // View Results Modal
            const viewResultsBtn = document.getElementById("viewResultsBtn");
            const closeViewResultsModal = document.getElementById("closeViewResultsModal");
            const closeResultsBtn = document.getElementById("closeResultsBtn");

            viewResultsBtn.addEventListener("click", function() {
                openModal("viewResultsModal");
            });

            closeViewResultsModal.addEventListener("click", function() {
                closeModal("viewResultsModal");
            });

            closeResultsBtn.addEventListener("click", function() {
                closeModal("viewResultsModal");
            });

            // Results filtering
            const filterCourse = document.getElementById("filterCourse");
            const filterLecturer = document.getElementById("filterLecturer");
            const filterStatus = document.getElementById("filterStatus");

            [filterCourse, filterLecturer, filterStatus].forEach((filter) => {
                filter.addEventListener("change", function() {
                    // In a real application, you would filter the results table based on the selected filters
                    console.log("Filtering results:", {
                        course: filterCourse.value,
                        lecturer: filterLecturer.value,
                        status: filterStatus.value,
                    })
                })
            })

            // View detailed results
            const viewResultsBtns = document.querySelectorAll(".view-results");
            const closeCourseResultsDetailModal = document.getElementById("closeCourseResultsDetailModal");
            const backToResultsBtn = document.getElementById("backToResultsBtn");

            viewResultsBtns.forEach((btn) => {
                btn.addEventListener("click", function() {
                    const courseId = this.getAttribute("data-course");
                    console.log("Viewing results for course:", courseId);

                    // In a real application, you would fetch the course results data
                    // and populate the detailed results modal

                    closeModal("viewResultsModal");
                    openModal("courseResultsDetailModal");
                })
            })

            closeCourseResultsDetailModal.addEventListener("click", function() {
                closeModal("courseResultsDetailModal");
            })

            backToResultsBtn.addEventListener("click", function() {
                closeModal("courseResultsDetailModal");
                openModal("viewResultsModal");
            })

            // Results tabs in detailed view
            const resultsTabs = document.querySelectorAll(".results-tabs .tab-btn")

            resultsTabs.forEach((tab) => {
                tab.addEventListener("click", function() {
                    const tabId = this.getAttribute("data-tab");

                    resultsTabs.forEach((t) => t.classList.remove("active"));
                    document.querySelectorAll("#courseResultsDetailModal .tab-content").forEach((content) => {
                        content.classList.remove("active");
                    });

                    this.classList.add("active");
                    document.getElementById(tabId).classList.add("active");
                });
            });

            // Download results
            const downloadResultsBtns = document.querySelectorAll(".download-results");

            downloadResultsBtns.forEach((btn) => {
                btn.addEventListener("click", function() {
                    const courseId = this.getAttribute("data-course")
                    console.log("Downloading results for course:", courseId);

                    // In a real application, you would trigger a download of the results file
                    alert(`Downloading results for course ${courseId}`);
                });
            })

            // Remind lecturer
            const remindLecturerBtns = document.querySelectorAll(".remind-lecturer");

            remindLecturerBtns.forEach((btn) => {
                btn.addEventListener("click", function() {
                    const courseId = this.getAttribute("data-course");
                    console.log("Sending reminder for course:", courseId);

                    // In a real application, you would send a reminder to the lecturer
                    alert(`Reminder sent to lecturer for course ${courseId}`);
                })
            })

            // Export results report
            const exportResultsBtn = document.getElementById("exportResultsBtn")

            exportResultsBtn.addEventListener("click", function() {
                console.log("Exporting results report");
                // In a real application, you would generate and download a report
                alert("Exporting results report");
            });

            // Download detailed results
            const downloadDetailedResultsBtn = document.getElementById("downloadDetailedResultsBtn");

            downloadDetailedResultsBtn.addEventListener("click", function() {
                console.log("Downloading detailed results report");

                // In a real application, you would generate and download a detailed report
                alert("Downloading detailed results report");
            });

        });
    </script>
</body>

</html>