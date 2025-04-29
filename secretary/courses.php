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

$_SESSION["lastAccessed"] = time();

require_once('../bootstrap.php');

use Src\Controller\SecretaryController;

require_once('../inc/admin-database-con.php');

$admin = new SecretaryController($db, $user, $pass);

$pageTitle = "Courses";
$activePage = "courses";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Staff Portal - Courses</title>
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

        <div class="dashboard-content">
            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <button class="action-btn" id="addCourseBtn">
                        <i class="fas fa-plus"></i>
                        Add New Course
                    </button>
                    <button class="action-btn" id="bulkUploadBtn">
                        <i class="fas fa-upload"></i>
                        Bulk Upload Courses
                    </button>
                    <button class="action-btn" id="assignCoursesBtn">
                        <i class="fas fa-user-plus"></i>
                        Assign Courses
                    </button>
                    <button class="action-btn" id="setDeadlinesBtn">
                        <i class="fas fa-clock"></i>
                        Set Deadlines
                    </button>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filter-group">
                    <label for="semester">Semester</label>
                    <select id="semester">
                        <option value="">All Semesters</option>
                        <option value="1" selected>First Semester 2023/2024</option>
                        <option value="2">Second Semester 2023/2024</option>
                        <option value="3">Summer Semester 2023/2024</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="department">Department</label>
                    <select id="department">
                        <option value="">All Departments</option>
                        <option value="1">Marine Engineering</option>
                        <option value="2">Nautical Science</option>
                        <option value="3">Logistics Management</option>
                        <option value="4">Computer Science</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="lecturer">Lecturer</label>
                    <select id="lecturer">
                        <option value="">All Lecturers</option>
                        <option value="1">Dr. John Doe</option>
                        <option value="2">Prof. Jane Smith</option>
                        <option value="3">Dr. Robert Johnson</option>
                        <option value="4">Prof. Emily Brown</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select id="status">
                        <option value="">All Statuses</option>
                        <option value="assigned">Assigned</option>
                        <option value="unassigned">Unassigned</option>
                        <option value="deadline">With Deadline</option>
                        <option value="nodeadline">No Deadline</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="filter-btn apply">Apply Filters</button>
                    <button class="filter-btn reset">Reset</button>
                </div>
            </div>

            <!-- Course Grid -->
            <div class="course-grid">
                <!-- Course Card 1 -->
                <div class="course-card">
                    <div class="course-header">
                        <div>
                            <div class="course-title">Introduction to Marine Engineering</div>
                            <div class="course-code">ME101</div>
                        </div>
                        <div class="course-actions">
                            <button class="action-icon edit-course" title="Edit Course">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-icon delete-course" title="Delete Course">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="course-details">
                        <div class="detail-item">
                            <div class="detail-label">Department</div>
                            <div class="detail-value">Marine Engineering</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Credit Hours</div>
                            <div class="detail-value">3</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Level</div>
                            <div class="detail-value">100</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Semester</div>
                            <div class="detail-value">First Semester 2023/2024</div>
                        </div>
                    </div>
                    <div class="lecturer-info">
                        <div class="lecturer-avatar">
                            <img src="lecturer1.jpg" alt="Lecturer Avatar">
                        </div>
                        <div class="lecturer-details">
                            <div class="lecturer-name">Dr. John Doe</div>
                            <div class="lecturer-email">john.doe@rmu.edu</div>
                        </div>
                    </div>
                    <div class="deadline-info">
                        <i class="fas fa-clock"></i>
                        <div class="deadline-text">Deadline: December 15, 2023</div>
                    </div>
                </div>

                <!-- Course Card 2 -->
                <div class="course-card">
                    <div class="course-header">
                        <div>
                            <div class="course-title">Maritime Law and Regulations</div>
                            <div class="course-code">ML202</div>
                        </div>
                        <div class="course-actions">
                            <button class="action-icon edit-course" title="Edit Course">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-icon delete-course" title="Delete Course">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="course-details">
                        <div class="detail-item">
                            <div class="detail-label">Department</div>
                            <div class="detail-value">Nautical Science</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Credit Hours</div>
                            <div class="detail-value">4</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Level</div>
                            <div class="detail-value">200</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Semester</div>
                            <div class="detail-value">First Semester 2023/2024</div>
                        </div>
                    </div>
                    <div class="lecturer-info">
                        <div class="lecturer-avatar">
                            <img src="lecturer2.jpg" alt="Lecturer Avatar">
                        </div>
                        <div class="lecturer-details">
                            <div class="lecturer-name">Prof. Jane Smith</div>
                            <div class="lecturer-email">jane.smith@rmu.edu</div>
                        </div>
                    </div>
                    <div class="deadline-info">
                        <i class="fas fa-clock"></i>
                        <div class="deadline-text">Deadline: December 20, 2023</div>
                    </div>
                </div>

                <!-- Course Card 3 -->
                <div class="course-card">
                    <div class="course-header">
                        <div>
                            <div class="course-title">Ship Navigation Systems</div>
                            <div class="course-code">NS305</div>
                        </div>
                        <div class="course-actions">
                            <button class="action-icon edit-course" title="Edit Course">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-icon delete-course" title="Delete Course">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="course-details">
                        <div class="detail-item">
                            <div class="detail-label">Department</div>
                            <div class="detail-value">Nautical Science</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Credit Hours</div>
                            <div class="detail-value">3</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Level</div>
                            <div class="detail-value">300</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Semester</div>
                            <div class="detail-value">First Semester 2023/2024</div>
                        </div>
                    </div>
                    <div class="lecturer-info">
                        <div class="lecturer-avatar">
                            <img src="lecturer3.jpg" alt="Lecturer Avatar">
                        </div>
                        <div class="lecturer-details">
                            <div class="lecturer-name">Dr. Robert Johnson</div>
                            <div class="lecturer-email">robert.johnson@rmu.edu</div>
                        </div>
                    </div>
                    <div class="deadline-info">
                        <i class="fas fa-clock"></i>
                        <div class="deadline-text">Deadline: December 18, 2023</div>
                    </div>
                </div>

                <!-- Course Card 4 -->
                <div class="course-card">
                    <div class="course-header">
                        <div>
                            <div class="course-title">Database Management Systems</div>
                            <div class="course-code">CS304</div>
                        </div>
                        <div class="course-actions">
                            <button class="action-icon edit-course" title="Edit Course">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-icon delete-course" title="Delete Course">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="course-details">
                        <div class="detail-item">
                            <div class="detail-label">Department</div>
                            <div class="detail-value">Computer Science</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Credit Hours</div>
                            <div class="detail-value">3</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Level</div>
                            <div class="detail-value">300</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Semester</div>
                            <div class="detail-value">First Semester 2023/2024</div>
                        </div>
                    </div>
                    <div class="lecturer-info">
                        <div class="lecturer-avatar">
                            <img src="lecturer4.jpg" alt="Lecturer Avatar">
                        </div>
                        <div class="lecturer-details">
                            <div class="lecturer-name">Prof. Emily Brown</div>
                            <div class="lecturer-email">emily.brown@rmu.edu</div>
                        </div>
                    </div>
                    <div class="deadline-info">
                        <i class="fas fa-clock"></i>
                        <div class="deadline-text">Deadline: December 22, 2023</div>
                    </div>
                </div>

                <!-- Course Card 5 -->
                <div class="course-card">
                    <div class="course-header">
                        <div>
                            <div class="course-title">Supply Chain Management</div>
                            <div class="course-code">LM201</div>
                        </div>
                        <div class="course-actions">
                            <button class="action-icon edit-course" title="Edit Course">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-icon delete-course" title="Delete Course">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="course-details">
                        <div class="detail-item">
                            <div class="detail-label">Department</div>
                            <div class="detail-value">Logistics Management</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Credit Hours</div>
                            <div class="detail-value">3</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Level</div>
                            <div class="detail-value">200</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Semester</div>
                            <div class="detail-value">First Semester 2023/2024</div>
                        </div>
                    </div>
                    <div class="lecturer-info">
                        <div class="lecturer-avatar">
                            <img src="lecturer5.jpg" alt="Lecturer Avatar">
                        </div>
                        <div class="lecturer-details">
                            <div class="lecturer-name">Dr. Michael Wilson</div>
                            <div class="lecturer-email">michael.wilson@rmu.edu</div>
                        </div>
                    </div>
                    <div class="deadline-info">
                        <i class="fas fa-clock"></i>
                        <div class="deadline-text">Deadline: December 17, 2023</div>
                    </div>
                </div>

                <!-- Course Card 6 -->
                <div class="course-card">
                    <div class="course-header">
                        <div>
                            <div class="course-title">Marine Propulsion Systems</div>
                            <div class="course-code">ME302</div>
                        </div>
                        <div class="course-actions">
                            <button class="action-icon edit-course" title="Edit Course">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-icon delete-course" title="Delete Course">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="course-details">
                        <div class="detail-item">
                            <div class="detail-label">Department</div>
                            <div class="detail-value">Marine Engineering</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Credit Hours</div>
                            <div class="detail-value">4</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Level</div>
                            <div class="detail-value">300</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Semester</div>
                            <div class="detail-value">First Semester 2023/2024</div>
                        </div>
                    </div>
                    <div class="lecturer-info">
                        <div class="lecturer-avatar">
                            <img src="lecturer6.jpg" alt="Lecturer Avatar">
                        </div>
                        <div class="lecturer-details">
                            <div class="lecturer-name">Prof. David Clark</div>
                            <div class="lecturer-email">david.clark@rmu.edu</div>
                        </div>
                    </div>
                    <div class="deadline-info">
                        <i class="fas fa-clock"></i>
                        <div class="deadline-text">Deadline: December 19, 2023</div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <div class="page-item disabled">
                    <i class="fas fa-chevron-left"></i>
                </div>
                <div class="page-item active">1</div>
                <div class="page-item">2</div>
                <div class="page-item">3</div>
                <div class="page-item">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Course Modal -->
    <div class="modal" id="addCourseModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Add New Course</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="addCourseForm">
                        <div class="form-group">
                            <label for="courseTitle">Course Title</label>
                            <input type="text" id="courseTitle" required>
                        </div>
                        <div class="form-group">
                            <label for="courseCode">Course Code</label>
                            <input type="text" id="courseCode" required>
                        </div>
                        <div class="form-group">
                            <label for="courseDepartment">Department</label>
                            <select id="courseDepartment" required>
                                <option value="">Select Department</option>
                                <option value="1">Marine Engineering</option>
                                <option value="2">Nautical Science</option>
                                <option value="3">Logistics Management</option>
                                <option value="4">Computer Science</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="courseCredits">Credit Hours</label>
                            <input type="number" id="courseCredits" min="1" max="6" required>
                        </div>
                        <div class="form-group">
                            <label for="courseLevel">Level</label>
                            <select id="courseLevel" required>
                                <option value="">Select Level</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                                <option value="300">300</option>
                                <option value="400">400</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="courseSemester">Semester</label>
                            <select id="courseSemester" required>
                                <option value="">Select Semester</option>
                                <option value="1">First Semester 2023/2024</option>
                                <option value="2">Second Semester 2023/2024</option>
                                <option value="3">Summer Semester 2023/2024</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" data-dismiss="modal">Cancel</button>
                    <button class="submit-btn" id="saveCourseBtn">Save Course</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Upload Modal -->
    <div class="modal" id="bulkUploadModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Bulk Upload Courses</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="upload-area">
                        <div class="upload-icon">
                            <i class="fas fa-file-upload fa-3x"></i>
                        </div>
                        <h3>Upload Course List</h3>
                        <p>Drag and drop your CSV or Excel file here, or click the button below to select a file.</p>
                        <input type="file" id="courseFileInput" class="file-input" accept=".csv, .xlsx">
                        <label for="courseFileInput" class="file-label">Choose File</label>
                        <div class="selected-file-name" id="selectedFileName"></div>
                        <div class="template-download">
                            <a href="#" class="download-link">Download Template</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="uploadSemester">Semester</label>
                        <select id="uploadSemester" required>
                            <option value="">Select Semester</option>
                            <option value="1" selected>First Semester 2023/2024</option>
                            <option value="2">Second Semester 2023/2024</option>
                            <option value="3">Summer Semester 2023/2024</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" data-dismiss="modal">Cancel</button>
                    <button class="submit-btn" id="uploadCoursesBtn">Upload Courses</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Courses Modal -->
    <div class="modal" id="assignCoursesModal">
        <div class="modal-dialog modal-lg modal-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Assign Courses to Lecturers</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="assignLecturer">Select Lecturer</label>
                        <select id="assignLecturer" required>
                            <option value="">Select Lecturer</option>
                            <option value="1">Dr. John Doe</option>
                            <option value="2">Prof. Jane Smith</option>
                            <option value="3">Dr. Robert Johnson</option>
                            <option value="4">Prof. Emily Brown</option>
                            <option value="5">Dr. Michael Wilson</option>
                            <option value="6">Prof. David Clark</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Select Courses to Assign</label>
                        <div class="checkbox-list">
                            <div class="checkbox-item">
                                <input type="checkbox" id="course1" value="ME101">
                                <label for="course1">ME101 - Introduction to Marine Engineering</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="course2" value="ML202">
                                <label for="course2">ML202 - Maritime Law and Regulations</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="course3" value="NS305">
                                <label for="course3">NS305 - Ship Navigation Systems</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="course4" value="CS304">
                                <label for="course4">CS304 - Database Management Systems</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="course5" value="LM201">
                                <label for="course5">LM201 - Supply Chain Management</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="course6" value="ME302">
                                <label for="course6">ME302 - Marine Propulsion Systems</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" data-dismiss="modal">Cancel</button>
                    <button class="submit-btn" id="saveAssignmentsBtn">Save Assignments</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Set Deadlines Modal -->
    <div class="modal" id="setDeadlinesModal">
        <div class="modal-dialog modal-lg modal-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Set Submission Deadlines</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Set Deadline for Multiple Courses</label>
                        <input type="date" id="bulkDeadlineDate" min="2023-11-01" required>
                    </div>
                    <div class="form-group">
                        <label>Select Courses</label>
                        <div class="checkbox-list">
                            <div class="checkbox-item">
                                <input type="checkbox" id="deadline1" value="ME101">
                                <label for="deadline1">ME101 - Introduction to Marine Engineering (Dr. John Doe)</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="deadline2" value="ML202">
                                <label for="deadline2">ML202 - Maritime Law and Regulations (Prof. Jane Smith)</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="deadline3" value="NS305">
                                <label for="deadline3">NS305 - Ship Navigation Systems (Dr. Robert Johnson)</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="deadline4" value="CS304">
                                <label for="deadline4">CS304 - Database Management Systems (Prof. Emily Brown)</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="deadline5" value="LM201">
                                <label for="deadline5">LM201 - Supply Chain Management (Dr. Michael Wilson)</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="deadline6" value="ME302">
                                <label for="deadline6">ME302 - Marine Propulsion Systems (Prof. David Clark)</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="notifyLecturers">
                            <input type="checkbox" id="notifyLecturers" checked>
                            Notify lecturers about the deadline
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" data-dismiss="modal">Cancel</button>
                    <button class="submit-btn" id="saveDeadlinesBtn">Save Deadlines</button>
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
        const modals = {
            addCourseModal: document.getElementById('addCourseModal'),
            bulkUploadModal: document.getElementById('bulkUploadModal'),
            assignCoursesModal: document.getElementById('assignCoursesModal'),
            setDeadlinesModal: document.getElementById('setDeadlinesModal')
        };

        // Open modals
        document.getElementById('addCourseBtn').addEventListener('click', () => openModal('addCourseModal'));
        document.getElementById('bulkUploadBtn').addEventListener('click', () => openModal('bulkUploadModal'));
        document.getElementById('assignCoursesBtn').addEventListener('click', () => openModal('assignCoursesModal'));
        document.getElementById('setDeadlinesBtn').addEventListener('click', () => openModal('setDeadlinesModal'));

        // Close modals
        document.querySelectorAll('.close-btn, .cancel-btn').forEach(button => {
            button.addEventListener('click', function() {
                const modal = this.closest('.modal');
                if (modal) {
                    modal.classList.remove('active');
                }
            });
        });

        function openModal(modalId) {
            // Close all modals first
            Object.values(modals).forEach(modal => {
                modal.classList.remove('active');
            });

            // Open the requested modal
            modals[modalId].classList.add('active');
        }

        // File input handling
        const fileInput = document.getElementById('courseFileInput');
        const fileNameDisplay = document.getElementById('selectedFileName');

        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileNameDisplay.textContent = this.files[0].name;
            } else {
                fileNameDisplay.textContent = '';
            }
        });

        // Form submissions
        document.getElementById('saveCourseBtn').addEventListener('click', function() {
            const form = document.getElementById('addCourseForm');
            if (form.checkValidity()) {
                // Simulate form submission
                alert('Course added successfully!');
                modals.addCourseModal.classList.remove('active');
                form.reset();
            } else {
                alert('Please fill all required fields.');
            }
        });

        document.getElementById('uploadCoursesBtn').addEventListener('click', function() {
            if (fileInput.files.length > 0) {
                // Simulate file upload
                alert('Courses uploaded successfully!');
                modals.bulkUploadModal.classList.remove('active');
                fileInput.value = '';
                fileNameDisplay.textContent = '';
            } else {
                alert('Please select a file to upload.');
            }
        });

        document.getElementById('saveAssignmentsBtn').addEventListener('click', function() {
            const lecturer = document.getElementById('assignLecturer').value;
            const selectedCourses = document.querySelectorAll('.checkbox-list input[type="checkbox"]:checked');

            if (lecturer && selectedCourses.length > 0) {
                // Simulate assignment
                alert('Courses assigned successfully!');
                modals.assignCoursesModal.classList.remove('active');
            } else {
                alert('Please select a lecturer and at least one course.');
            }
        });

        document.getElementById('saveDeadlinesBtn').addEventListener('click', function() {
            const deadlineDate = document.getElementById('bulkDeadlineDate').value;
            const selectedCourses = document.querySelectorAll('#setDeadlinesModal .checkbox-list input[type="checkbox"]:checked');

            if (deadlineDate && selectedCourses.length > 0) {
                // Simulate setting deadlines
                alert('Deadlines set successfully!');
                modals.setDeadlinesModal.classList.remove('active');
            } else {
                alert('Please select a date and at least one course.');
            }
        });

        // Edit and delete course buttons
        document.querySelectorAll('.edit-course').forEach(button => {
            button.addEventListener('click', function() {
                const courseCard = this.closest('.course-card');
                const courseTitle = courseCard.querySelector('.course-title').textContent;
                alert(`Edit course: ${courseTitle}`);
                // In a real application, you would populate the edit form with course data
                openModal('addCourseModal');
            });
        });

        document.querySelectorAll('.delete-course').forEach(button => {
            button.addEventListener('click', function() {
                const courseCard = this.closest('.course-card');
                const courseTitle = courseCard.querySelector('.course-title').textContent;
                if (confirm(`Are you sure you want to delete the course: ${courseTitle}?`)) {
                    // Simulate deletion
                    courseCard.remove();
                    alert('Course deleted successfully!');
                }
            });
        });

        // Filter functionality
        document.querySelector('.filter-btn.apply').addEventListener('click', function() {
            // Simulate filtering
            alert('Filters applied!');
        });

        document.querySelector('.filter-btn.reset').addEventListener('click', function() {
            // Reset all filter inputs
            document.querySelectorAll('.filter-group select, .filter-group input').forEach(input => {
                input.value = '';
            });
            document.getElementById('semester').value = '1'; // Reset to default semester
            alert('Filters reset!');
        });

        // Pagination
        document.querySelectorAll('.pagination .page-item:not(.disabled)').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.pagination .page-item').forEach(p => {
                    p.classList.remove('active');
                });
                this.classList.add('active');
                // In a real application, you would load the corresponding page of courses
            });
        });
    </script>
</body>

</html>