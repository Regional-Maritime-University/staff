<?php
session_start();

if (!isset($_SESSION["adminLogSuccess"]) || $_SESSION["adminLogSuccess"] == false || !isset($_SESSION["user"]) || empty($_SESSION["user"])) {
    header("Location: ../index.php");
}

$isUser = false;
if (strtolower($_SESSION["role"]) == "admin" || strtolower($_SESSION["role"]) == "developers" || strtolower($_SESSION["role"]) == "secretary") $isUser = true;

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

    header('Location: ../login.php');
}

$_SESSION["lastAccessed"] = time();

require_once('../bootstrap.php');

use Src\Controller\SecretaryController;

require_once('../inc/admin-database-con.php');

$admin = new SecretaryController($db, $user, $pass);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Staff Portal - Secretary Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Sidebar -->
    <?php require_once '../components/sidebar.php'; ?>

    <div class="main-content">
        
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Secretary Dashboard</h1>
            </div>
            <div class="header-right">
                <div class="search-bar">
                    <input type="text" placeholder="Search...">
                    <button class="search-btn"><i class="fas fa-search"></i></button>
                </div>
                <div class="header-actions">
                    <button class="action-btn notifications">
                        <i class="fas fa-bell"></i>
                        <span class="badge">5</span>
                    </button>
                    <button class="action-btn messages">
                        <i class="fas fa-envelope"></i>
                        <span class="badge">3</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: var(--primary-color);">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3>42</h3>
                        <p>Active Courses</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: var(--accent-color);">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-info">
                        <h3>18</h3>
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
                        <h3>5</h3>
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
                        <div class="deadline-item">
                            <div class="deadline-icon" style="background-color: var(--danger-color);">
                                <i class="fas fa-calendar-times"></i>
                            </div>
                            <div class="deadline-details">
                                <h4>Maritime Law (ML201)</h4>
                                <p>Results submission deadline</p>
                                <div class="deadline-meta">
                                    <span class="deadline-date"><i class="fas fa-clock"></i> 2 days left</span>
                                    <span class="deadline-lecturer"><i class="fas fa-user"></i> Dr. James Wilson</span>
                                </div>
                            </div>
                            <div class="deadline-status urgent">Urgent</div>
                        </div>
                        <div class="deadline-item">
                            <div class="deadline-icon" style="background-color: var(--warning-color);">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div class="deadline-details">
                                <h4>Navigation Systems (NS302)</h4>
                                <p>Results submission deadline</p>
                                <div class="deadline-meta">
                                    <span class="deadline-date"><i class="fas fa-clock"></i> 5 days left</span>
                                    <span class="deadline-lecturer"><i class="fas fa-user"></i> Prof. Sarah Johnson</span>
                                </div>
                            </div>
                            <div class="deadline-status pending">Pending</div>
                        </div>
                        <div class="deadline-item">
                            <div class="deadline-icon" style="background-color: var(--success-color);">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="deadline-details">
                                <h4>Marine Engineering (ME101)</h4>
                                <p>Results submission deadline</p>
                                <div class="deadline-meta">
                                    <span class="deadline-date"><i class="fas fa-clock"></i> 10 days left</span>
                                    <span class="deadline-lecturer"><i class="fas fa-user"></i> Dr. Michael Brown</span>
                                </div>
                            </div>
                            <div class="deadline-status normal">Normal</div>
                        </div>
                    </div>
                </div>

                <div class="recent-activity">
                    <div class="section-header">
                        <h2>Recent Activity</h2>
                        <button class="view-all-btn">View All</button>
                    </div>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon" style="background-color: var(--success-color);">
                                <i class="fas fa-file-upload"></i>
                            </div>
                            <div class="activity-details">
                                <h4>Results Submitted</h4>
                                <p>Dr. James Wilson submitted results for Maritime Law (ML201)</p>
                                <span class="activity-time">2 hours ago</span>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon" style="background-color: var(--primary-color);">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="activity-details">
                                <h4>Course Assignment</h4>
                                <p>You assigned Prof. Sarah Johnson to Navigation Systems (NS302)</p>
                                <span class="activity-time">Yesterday</span>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon" style="background-color: var(--accent-color);">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <div class="activity-details">
                                <h4>Deadline Set</h4>
                                <p>You set a deadline for Marine Engineering (ME101) results</p>
                                <span class="activity-time">2 days ago</span>
                            </div>
                        </div>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Assign Course to Lecturer</h2>
                    <button class="close-btn" id="closeAssignCourseModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="assignCourseForm">
                        <div class="form-group">
                            <label for="courseSelect">Select Course</label>
                            <select id="courseSelect" required>
                                <option value="">-- Select Course --</option>
                                <option value="ML201">Maritime Law (ML201)</option>
                                <option value="NS302">Navigation Systems (NS302)</option>
                                <option value="ME101">Marine Engineering (ME101)</option>
                                <option value="OC205">Oceanography (OC205)</option>
                                <option value="SM401">Ship Management (SM401)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="lecturerSelect">Select Lecturer</label>
                            <select id="lecturerSelect" required>
                                <option value="">-- Select Lecturer --</option>
                                <option value="1">Dr. James Wilson</option>
                                <option value="2">Prof. Sarah Johnson</option>
                                <option value="3">Dr. Michael Brown</option>
                                <option value="4">Dr. Emily Davis</option>
                                <option value="5">Prof. Robert Taylor</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="semesterSelect">Semester</label>
                            <select id="semesterSelect" required>
                                <option value="">-- Select Semester --</option>
                                <option value="1">First Semester 2023/2024</option>
                                <option value="2">Second Semester 2023/2024</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="assignmentNotes">Notes (Optional)</label>
                            <textarea id="assignmentNotes" rows="3" placeholder="Add any additional notes about this assignment"></textarea>
                        </div>
                    </form>
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
        <div class="modal-dialog modal-lg">
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
                        <div class="upload-area">
                            <div class="upload-icon">
                                <i class="fas fa-file-excel fa-3x"></i>
                            </div>
                            <h3>Upload Course List</h3>
                            <p>Upload an Excel or CSV file with course details</p>
                            <input type="file" id="courseFileUpload" class="file-input" accept=".xlsx, .xls, .csv">
                            <label for="courseFileUpload" class="file-label">Choose File</label>
                            <p class="selected-file-name" id="selectedFileName">No file selected</p>
                        </div>
                        <div class="template-download">
                            <p>Don't have the template? <a href="#" class="download-link">Download Template</a></p>
                        </div>
                    </div>

                    <div class="tab-content" id="singleCourse">
                        <form id="addCourseForm">
                            <div class="form-group">
                                <label for="courseCode">Course Code</label>
                                <input type="text" id="courseCode" placeholder="e.g. ML201" required>
                            </div>
                            <div class="form-group">
                                <label for="courseTitle">Course Title</label>
                                <input type="text" id="courseTitle" placeholder="e.g. Maritime Law" required>
                            </div>
                            <div class="form-group">
                                <label for="creditHours">Credit Hours</label>
                                <input type="number" id="creditHours" min="1" max="6" required>
                            </div>
                            <div class="form-group">
                                <label for="department">Department</label>
                                <select id="department" required>
                                    <option value="">-- Select Department --</option>
                                    <option value="1">Maritime Studies</option>
                                    <option value="2">Marine Engineering</option>
                                    <option value="3">Nautical Science</option>
                                    <option value="4">Logistics and Transport</option>
                                </select>
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
                                <label for="courseSemester">Semester</label>
                                <select id="courseSemester" required>
                                    <option value="">-- Select Semester --</option>
                                    <option value="1">First Semester</option>
                                    <option value="2">Second Semester</option>
                                </select>
                            </div>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Set Results Submission Deadline</h2>
                    <button class="close-btn" id="closeSetDeadlineModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="tabs">
                        <button class="tab-btn active" data-tab="singleDeadline">Single Course</button>
                        <button class="tab-btn" data-tab="bulkDeadline">Multiple Courses</button>
                    </div>

                    <div class="tab-content active" id="singleDeadline">
                        <form id="singleDeadlineForm">
                            <div class="form-group">
                                <label for="deadlineCourse">Select Course</label>
                                <select id="deadlineCourse" required>
                                    <option value="">-- Select Course --</option>
                                    <option value="ML201">Maritime Law (ML201)</option>
                                    <option value="NS302">Navigation Systems (NS302)</option>
                                    <option value="ME101">Marine Engineering (ME101)</option>
                                    <option value="OC205">Oceanography (OC205)</option>
                                    <option value="SM401">Ship Management (SM401)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="deadlineDate">Deadline Date</label>
                                <input type="date" id="deadlineDate" required>
                            </div>
                            <div class="form-group">
                                <label for="deadlineNotes">Notes (Optional)</label>
                                <textarea id="deadlineNotes" rows="3" placeholder="Add any additional notes about this deadline"></textarea>
                            </div>
                        </form>
                    </div>

                    <div class="tab-content" id="bulkDeadline">
                        <form id="bulkDeadlineForm">
                            <div class="form-group">
                                <label>Select Courses</label>
                                <div class="checkbox-list">
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="course1" value="ML201">
                                        <label for="course1">Maritime Law (ML201)</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="course2" value="NS302">
                                        <label for="course2">Navigation Systems (NS302)</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="course3" value="ME101">
                                        <label for="course3">Marine Engineering (ME101)</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="course4" value="OC205">
                                        <label for="course4">Oceanography (OC205)</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="course5" value="SM401">
                                        <label for="course5">Ship Management (SM401)</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="bulkDeadlineDate">Deadline Date</label>
                                <input type="date" id="bulkDeadlineDate" required>
                            </div>
                            <div class="form-group">
                                <label for="bulkDeadlineNotes">Notes (Optional)</label>
                                <textarea id="bulkDeadlineNotes" rows="3" placeholder="Add any additional notes about these deadlines"></textarea>
                            </div>
                        </form>
                    </div>
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

    <script src="../assets/js/main.js"></script>
</body>

</html>