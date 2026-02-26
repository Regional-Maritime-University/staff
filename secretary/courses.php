<?php
require_once __DIR__ . '/../inc/auth-guard.php';

require_once('../bootstrap.php');

use Src\Controller\SecretaryController;
use Src\Core\Base;
use Src\Core\Course;
use Src\Core\CourseCategory;

require_once('../inc/admin-database-con.php');

$secretary = new SecretaryController($db, $user, $pass);
$course_category = new CourseCategory($db, $user, $pass);
$course = new Course($db, $user, $pass);
$base               = new Base($db, $user, $pass);

$pageTitle = "Courses";
$activePage = "courses";

$departmentId = $_SESSION["staff"]["department_id"] ?? null;
$activeSemesters = $secretary->fetchActiveSemesters();
$currentSemester = $activeSemesters ? $activeSemesters[0] : null;
$semesterId = $currentSemester ? $currentSemester['id'] : null;
$archived = false;
$lecturers = $secretary->fetchAllLecturers($departmentId, $archived);

$activeCourses = $secretary->fetchActiveCourses($departmentId, null, $archived);
$totalActiveCourses = count($activeCourses);

$assignedCourses = [];
foreach ($activeSemesters as $semester) {
    $semesterId = $semester['id'];
    $departmentAssigndCourses = $secretary->fetchSemesterCourseAssignmentsByDepartment($departmentId, $semesterId);
    if ($departmentAssigndCourses) $assignedCourses = array_merge($assignedCourses, $departmentAssigndCourses);
}
$totalAssignedCourses = $assignedCourses && is_array($assignedCourses) ? count($assignedCourses) : 0;

$assignedLecturers = [];
foreach ($activeSemesters as $semester) {
    $semesterId = $semester['id'];
    $departmentAssignedLecturers = $secretary->fetchSemesterCourseAssignmentsGroupByLecturer($departmentId, $semesterId);
    if ($departmentAssignedLecturers) $assignedLecturers = array_merge($assignedLecturers, $departmentAssignedLecturers);
}
$totalAssignedLecturers = $assignedLecturers && is_array($assignedLecturers) ? count($assignedLecturers) : 0;

$deadlines = $secretary->fetchPendingDeadlinesByCourse($departmentId);
$totalPendingDeadlines = 0;
if ($deadlines && is_array($deadlines)) {
    foreach ($deadlines as $d) {
        if ($d['deadline_status'] == 'pending') $totalPendingDeadlines++;
    }
}

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
    <title>RMU Staff Portal - Courses</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="./css/courses.css">
    <link rel="stylesheet" href="./css/course-selection-modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/toast.css">
    <?php require_once '../components/datatables-head.php'; ?>
</head>

<body>
    <!-- Sidebar -->
    <?php require_once '../components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">

        <?php require_once '../components/header.php'; ?>

        <div class="dashboard-content">
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: var(--success-color);">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $totalActiveCourses ?></h3>
                        <p>Semester Courses</p>
                    </div>
                </div>
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
                    <div class="stat-icon" style="background-color: var(--danger-color);">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $totalPendingDeadlines ?></h3>
                        <p>Pending Deadlines</p>
                    </div>
                </div>
            </div>

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
                    <button class="action-btn danger" id="archivedCoursesBtn">
                        <i class="fas fa-list"></i>
                        Archived Courses
                    </button>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filter-group">
                    <label for="semester">Semester</label>
                    <select id="semester">
                        <option value="all">All Semesters</option>
                        <option value="1">First Semester</option>
                        <option value="2">Second Semester</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="level">Level</label>
                    <select id="level">
                        <option value="all">All Levels</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                        <option value="300">300</option>
                        <option value="400">400</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="lecturer">Lecturer</label>
                    <select id="lecturer">
                        <option value="all">All Lecturers</option>
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
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select id="status">
                        <option value="all">All Statuses</option>
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

                <?php
                if ($totalActiveCourses == 0) {
                    echo "<div class='no-courses'>No courses available.</div>";
                } else {
                    foreach ($activeCourses as $course) {
                ?>
                        <div class="course-card"
                            data-semester="<?= htmlspecialchars($course["semester"]) ?>"
                            data-level="<?= htmlspecialchars($course["level"]) ?>"
                            data-lecturer-number="<?= htmlspecialchars($course["lecturer_number"]) ?>"
                            data-has-deadline="<?= $course["deadline_date"] ? 'true' : 'false' ?>">
                            <div class="course-header">
                                <div>
                                    <div class="course-title"><?= htmlspecialchars($course["name"]) ?></div>
                                    <div class="course-code"><?= htmlspecialchars($course["code"]) ?></div>
                                </div>
                                <div class="course-actions">
                                    <button class="action-icon edit-course" title="Edit Course" id="<?= htmlspecialchars($course["code"]) ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-icon archive-course" title="Archive Course" id="<?= htmlspecialchars($course["code"]) ?>">
                                        <i class="fas fa-archive" style="color: var(--danger-color);"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="course-details">
                                <div class="detail-item">
                                    <div class="detail-label">Department</div>
                                    <div class="detail-value"><?= htmlspecialchars($course["department_name"]) ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Credit Hours</div>
                                    <div class="detail-value"><?= htmlspecialchars($course["credit_hours"]) ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Level</div>
                                    <div class="detail-value"><?= htmlspecialchars($course["level"]) ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Semester</div>
                                    <div class="detail-value"><?= $course["semester"] == 1 ? "First Semester" : ($course["semester"] == 2 ? "Second Semester" : htmlspecialchars($course["semester"])) ?></div>
                                </div>
                            </div>
                            <div class="lecturer-info">
                                <?php
                                if ($course["lecturer_number"]) {
                                ?>
                                    <div class="lecturer-avatar">
                                        <img src="../uploads/profiles/<?= htmlspecialchars($course["lecturer_avatar"]) ?>" alt="Lecturer Avatar">
                                    </div>
                                    <div class="lecturer-details">
                                        <div class="lecturer-name"><?= htmlspecialchars($course["lecturer_prefix"] . " " . $course["lecturer_first_name"] . " " . $course["lecturer_last_name"]) ?></div>
                                        <div class="lecturer-email"><?= htmlspecialchars($course["lecturer_email"]) ?></div>
                                        <input type="hidden" class="lecturer-number" value="<?= htmlspecialchars($course["lecturer_number"]) ?>">
                                    </div>
                                <?php
                                } else {
                                    echo "Not assigned";
                                }
                                ?>
                            </div>
                            <div class="deadline-info">
                                <?php
                                if ($course["deadline_date"]) {
                                ?>
                                    <i class="fas fa-clock"></i>
                                    <div class="deadline-text">Deadline: <?= date('F j, Y', strtotime($course["deadline_date"])) ?></div>
                                <?php
                                } else {
                                    echo "No deadline set";
                                }
                                ?>
                            </div>
                        </div>
                <?php
                    }
                }
                ?>
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
        <div class="modal-dialog modal-md modal-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Add New Course</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
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
                        <input type="hidden" name="action" id="courseAction" value="add">
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
                    <input type="hidden" name="department" id="departmentId" value="<?= $departmentId ?>">
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
                    <button class="cancel-btn" data-dismiss="modal">Cancel</button>
                    <button class="submit-btn" id="submitSetDeadline">Save Deadlines</button>
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

    <script src="../assets/js/toast.js"></script>
    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <?php require_once '../components/datatables-scripts.php'; ?>
    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get filter elements
            let lecturers = <?= json_encode($lecturers) ?>;
            const semesterFilter = document.getElementById('semester');
            const levelFilter = document.getElementById('level');
            const lecturerFilter = document.getElementById('lecturer');
            const statusFilter = document.getElementById('status');
            const applyButton = document.querySelector('.filter-btn.apply');
            const resetButton = document.querySelector('.filter-btn.reset');
            const courseCards = document.querySelectorAll('.course-card');

            // Add event listeners
            applyButton.addEventListener('click', applyFilters);
            resetButton.addEventListener('click', resetFilters);

            /**
             * Apply filters to the course grid
             */
            function applyFilters() {
                const levelValue = levelFilter.value;
                const semesterValue = semesterFilter.value;
                const lecturerValue = lecturerFilter.value;
                const statusValue = statusFilter.value;

                // Loop through each course card and check if it matches the filters
                courseCards.forEach(card => {
                    // Initially assume the card matches all filters
                    let matchesSemester = true;
                    let matchesLevel = true;
                    let matchesLecturer = true;
                    let matchesStatus = true;

                    // Check semester filter
                    if (semesterValue !== 'all') {
                        // Get semester value from the card
                        const cardSemester = card.dataset.semester;
                        matchesSemester = cardSemester === semesterValue;
                    }

                    // Check level filter
                    if (levelValue !== 'all') {
                        const cardLevel = card.dataset.level;
                        matchesLevel = cardLevel === levelValue;
                    }

                    // Check lecturer filter
                    if (lecturerValue !== 'all') {
                        const lecturerNumber = card.dataset.lecturerNumber || '';

                        if (lecturerValue === 'unassigned') {
                            // If filtering for unassigned courses
                            matchesLecturer = lecturerNumber === '' || lecturerNumber === 'null';
                        } else {
                            // If filtering for a specific lecturer
                            matchesLecturer = lecturerNumber === lecturerValue;
                        }
                    }

                    // Check status filter
                    if (statusValue !== 'all') {
                        const hasLecturer = card.dataset.lecturerNumber && card.dataset.lecturerNumber !== 'null';
                        const hasDeadline = card.dataset.hasDeadline === 'true';

                        switch (statusValue) {
                            case 'assigned':
                                matchesStatus = hasLecturer;
                                break;
                            case 'unassigned':
                                matchesStatus = !hasLecturer;
                                break;
                            case 'deadline':
                                matchesStatus = hasDeadline;
                                break;
                            case 'nodeadline':
                                matchesStatus = !hasDeadline;
                                break;
                        }
                    }

                    // Show or hide the card based on combined filter results
                    if (matchesSemester && matchesLevel && matchesLecturer && matchesStatus) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Display message if no courses match the filters
                updateNoCoursesMessage();
            }

            /**
             * Reset all filters and show all courses
             */
            function resetFilters() {
                // Reset filter dropdowns to default values
                semesterFilter.value = 'all';
                levelFilter.value = 'all';
                lecturerFilter.value = 'all';
                statusFilter.value = 'all';

                // Show all course cards
                courseCards.forEach(card => {
                    card.style.display = '';
                });

                // Hide "no courses" message if it exists
                const noCoursesMessage = document.querySelector('.no-courses');
                if (noCoursesMessage) {
                    noCoursesMessage.style.display = 'none';
                }
            }

            /**
             * Show or hide "No courses available" message based on filter results
             */
            function updateNoCoursesMessage() {
                // Check if any courses are visible
                const visibleCourses = Array.from(courseCards).filter(card =>
                    card.style.display !== 'none'
                );

                // Get or create the "no courses" message element
                let noCoursesMessage = document.querySelector('.no-courses');
                if (!noCoursesMessage) {
                    noCoursesMessage = document.createElement('div');
                    noCoursesMessage.className = 'no-courses';
                    noCoursesMessage.textContent = 'No courses match the selected filters.';
                    document.querySelector('.course-grid').appendChild(noCoursesMessage);
                }

                // Show or hide the message
                if (visibleCourses.length === 0) {
                    noCoursesMessage.style.display = 'block';
                } else {
                    noCoursesMessage.style.display = 'none';
                }
            }

            // Add data attributes to course cards for easier filtering
            function initializeDataAttributes() {
                courseCards.forEach(card => {
                    // Extract course data from the card elements
                    const courseTitle = card.querySelector('.course-title').textContent;
                    const courseCode = card.querySelector('.course-code').textContent;

                    // Extract semester information
                    const semesterText = card.querySelector('.course-details .detail-item:nth-child(4) .detail-value').textContent;
                    const semesterValue = semesterText.includes('First') ? '1' :
                        semesterText.includes('Second') ? '2' : '';
                    card.dataset.semester = semesterValue;

                    // Extract level information
                    const levelElement = card.querySelector('.course-details .detail-item:nth-child(3) .detail-value');
                    const levelValue = levelElement ? levelElement.textContent : '';
                    card.dataset.level = levelValue;

                    // Extract lecturer information
                    const lecturerInfo = card.querySelector('.lecturer-info');
                    const lecturerNumberInfo = lecturerInfo.querySelector('.lecturer-number');

                    if (lecturerNumberInfo) {
                        const lecturerNumber = lecturerNumberInfo.value.trim();
                        card.dataset.lecturerNumber = lecturerNumber;
                    } else {
                        card.dataset.lecturerNumber = 'null';
                    }

                    // Check if the course has a deadline
                    const deadlineInfo = card.querySelector('.deadline-info');
                    const hasDeadline = !deadlineInfo.textContent.includes('No deadline set');
                    card.dataset.hasDeadline = hasDeadline;
                });
            }

            // Initialize data attributes
            initializeDataAttributes();
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar
            document.querySelector('.toggle-sidebar').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('collapsed');
                document.querySelector('.main-content').classList.toggle('expanded');
            });

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

            // Close modals
            document.querySelectorAll('.close-btn, .cancel-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    if (modal) {
                        const form = modal.querySelector('form');
                        if (form) form.reset();
                        modal.classList.remove('active');
                    }
                });
            });

            let semesterCourses = assignedCourses = null;
            let activeCourses = <?= json_encode($activeCourses) ?>;
            const user = <?= json_encode($staffData); ?>;
            const departmentId = user ? user.department_id : null;
            const userId = user ? user.number : null;

            // Open modals
            document.getElementById('addCourseBtn').addEventListener('click', () => openModal('addCourseModal'));
            document.getElementById('bulkUploadBtn').addEventListener('click', () => openModal('bulkUploadModal'));
            document.getElementById('assignCoursesBtn').addEventListener('click', () => openModal('assignCoursesModal'));
            document.getElementById('setDeadlinesBtn').addEventListener('click', () => openModal('setDeadlinesModal'));
            document.getElementById('departmentSelectCoursesBtn').addEventListener('click', () => openModal('departmentCourseSelectionModal'));
            document.getElementById('selectCoursesBtn').addEventListener('click', () => openModal('courseSelectionModal'));

            // File input handling
            const fileInput = document.getElementById('courseFileInput');
            const fileNameDisplay = document.getElementById('selectedFileName');

            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    fileNameDisplay.textContent = this.files[0].name;
                } else {
                    fileNameDisplay.textContent = 'No file selected';
                }
            });

            // Add a single course Form submissions
            document.getElementById('saveCourseBtn').addEventListener('click', function() {
                const form = document.getElementById('addCourseForm');
                if (!form.checkValidity()) {
                    alert("Please fill in all required fields");
                    return;
                }

                // Single course form validation
                const courseCode = document.getElementById("courseCode");
                const courseName = document.getElementById("courseName");
                const creditHours = document.getElementById("creditHours");
                const contactHours = document.getElementById("contactHours");
                const courseLevel = document.getElementById("courseLevel");
                const courseCategory = document.getElementById("courseCategory");
                const courseSemester = document.getElementById("courseSemester");
                const department = document.getElementById("courseDepartment");
                const courseAction = document.getElementById("courseAction");

                if (!courseCode.value || !courseName.value || !creditHours.value || !contactHours.value || !department.value || !courseLevel.value || !courseSemester.value) {
                    alert("Please fill in all required fields");
                    return;
                }

                let url = null;

                switch (courseAction.value) {
                    case "add":
                        url = "../endpoint/add-course";
                        break;
                    case "edit":
                        url = "../endpoint/edit-course";
                        break;
                    default:
                        alert("Invalid action");
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
                    url: url,
                    data: formData,
                    success: function(result) {
                        console.log(result);
                        if (result.success) {
                            alert(result.message);
                            form.reset();
                            closeModal("addCourseModal");
                        } else {
                            alert(result['message']);
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            // Upload bulk courses
            document.getElementById('uploadCoursesBtn').addEventListener('click', function() {
                if (!fileInput.files.length) {
                    alert("Please select a file to upload");
                    return;
                }

                // Simulate file upload
                const courseFile = fileInput.files[0];
                const department = document.getElementById("departmentId");

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
                            closeModal("bulkUploadModal");
                            fileInput.value = '';
                            fileNameDisplay.textContent = '';
                            //closeModal("bulkUploadModal');
                            //document.getElementById("bulkUploadForm").reset();
                        } else {
                            alert(result.message);
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            document.getElementById("semesterSelect").addEventListener("change", function() {
                if (semesterCourses !== null) {
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
                            if (data.success) semesterCourses = data.data;
                            else alert("Failed to fetch courses for selected semester: ", data.message);
                        })
                        .catch(error => console.error("Error fetching courses for selected semester:", error));
                }
            });

            // Course Selection Modal
            const confirmDepartmentCourseSelectionBtn = document.getElementById("confirmDepartmentCourseSelectionBtn");
            const departmentCourseSearchInput = document.getElementById("departmentCourseSearchInput");

            confirmDepartmentCourseSelectionBtn.addEventListener("click", () => {
                closeModal("departmentCourseSelectionModal");
            });

            departmentCourseSearchInput.addEventListener("input", () => {
                departmentSearchCourses();
            });

            // Initialize course list on modal open
            departmentCourseSearchInput.addEventListener("focus", () => {
                if (departmentCourseSearchInput.value === "") {
                    departmentSearchCourses();
                }
            });

            // Initialize course list when modal opens
            departmentSelectCoursesBtn.addEventListener("click", () => {
                setTimeout(() => {
                    departmentSearchCourses();
                }, 100);
            });

            function departmentSearchCourses() {
                const searchTerm = document.getElementById("departmentCourseSearchInput").value.toLowerCase();
                const courseList = document.getElementById("departmentCourseList");
                courseList.innerHTML = "";

                // Check if semester courses has data
                if (!semesterCourses || semesterCourses.length === 0) {
                    courseList.innerHTML = `
                        <div class="department-no-courses-message">
                            <i class="fas fa-info-circle"></i>
                            <p>No courses are available.</p>
                        </div>
                    `;
                    return;
                }

                semesterCourses.forEach((course) => {
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

            /**
             * Reset the Assign Courses Modal to its initial state
             */
            function resetAssignCoursesModal() {
                // Reset selects and textarea
                document.getElementById("semesterSelect").value = "";
                document.getElementById("lecturerSelect").value = "";
                document.getElementById("studentSelect").value = "";
                document.getElementById("classSelect").value = "";
                document.getElementById("assignmentNotes").value = "";

                // Reset tab to default (toLecturer)
                document.querySelectorAll(".assign-course-tabs .tab-btn").forEach((btn, idx) => {
                    if (btn.getAttribute("data-tab") === "toLecturer") {
                        btn.classList.add("active");
                    } else {
                        btn.classList.remove("active");
                    }
                });

                document.querySelectorAll(".tab-content").forEach(content => {
                    if (content.id === "toLecturer") {
                        content.classList.add("active");
                    } else {
                        content.classList.remove("active");
                    }
                });

                document.getElementById("assignCourseActionSelect").value = "toLecturer";

                // Remove all selected courses
                document.getElementById("departmentSelectedCoursesList").innerHTML = "";
                // Show the empty message
                const noCoursesMsg = document.getElementById("departmentNoCoursesMessage");
                if (noCoursesMsg) noCoursesMsg.style.display = "block";
            }

            /**
             * Attach resetAssignCoursesModal to modal close events
             */
            document.querySelectorAll('#assignCoursesModal .close-btn, #assignCoursesModal .cancel-btn').forEach(btn => {
                btn.addEventListener('click', resetAssignCoursesModal);
            });

            const saveAssignmentsBtn = document.getElementById("saveAssignmentsBtn");

            saveAssignmentsBtn.addEventListener("click", function() {
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
                            closeModal("assignCoursesModal");
                        } else {
                            alert(result['message']);
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            // Set Deadline Modal
            const submitSetDeadline = document.getElementById("submitSetDeadline");

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
            const confirmCourseSelectionBtn = document.getElementById("confirmCourseSelectionBtn");
            const courseSearchInput = document.getElementById("courseSearchInput");

            confirmCourseSelectionBtn.addEventListener("click", () => {
                closeModal("courseSelectionModal");
            });

            courseSearchInput.addEventListener("input", () => {
                searchCourses();
            });

            // Initialize course list on modal open
            courseSearchInput.addEventListener("focus", () => {
                if (courseSearchInput.value === "") {
                    searchCourses();
                }
            });

            // Initialize course list when modal opens
            selectCoursesBtn.addEventListener("click", () => {
                if (assignedCourses == null) {
                    fetch(`../endpoint/fetch-assigned-courses`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams({
                                department: departmentId,
                            }).toString()
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                assignedCourses = data.data;
                                console.log("Assigned Courses", assignedCourses);
                                setTimeout(() => {
                                    searchCourses();
                                }, 100);
                            } else alert("Failed to fetch courses for selected semester: ", data.message);
                        })
                        .catch(error => console.error("Error fetching courses for selected semester:", error));
                }
            });

            document.getElementById('submitSetDeadline').addEventListener('click', function() {

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
                            document.getElementById("deadlineForm").reset();

                            // Clear the selected courses list
                            document.getElementById("selectedCoursesList").innerHTML = "";

                            // Show the "no courses" message
                            const noCoursesMessage = document.getElementById("noCoursesMessage");
                            if (noCoursesMessage) {
                                noCoursesMessage.style.display = "block";
                            }
                            closeModal("setDeadlinesModal");
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

            // Edit and archive course buttons
            document.querySelectorAll('.edit-course').forEach(button => {
                button.addEventListener('click', function() {
                    const courseCard = this.closest('.course-card');
                    const courseTitle = courseCard.querySelector('.course-title').textContent;
                    // In a real application, you would populate the edit form with course data
                    const courseCode = this.id;
                    const course = activeCourses.find(course => course.code === courseCode);
                    if (course) {
                        // Populate the edit form with course data
                        document.getElementById("courseAction").value = "edit";
                        document.getElementById("courseCode").value = course.code;
                        document.getElementById("courseName").value = course.name;
                        document.getElementById("creditHours").value = course.credit_hours;
                        document.getElementById("contactHours").value = course.contact_hours;
                        document.getElementById("courseLevel").value = course.level;
                        document.getElementById("courseCategory").value = course.category_id;
                        document.getElementById("courseSemester").value = course.semester;
                        document.getElementById("courseDepartment").value = course.department_id;
                        // Open the course modal
                        openModal('addCourseModal');
                    } else {
                        alert("Course not found");
                    }
                    openModal('addCourseModal');
                });
            });

            document.querySelectorAll('.archive-course').forEach(button => {
                button.addEventListener('click', function() {
                    const courseCard = this.closest('.course-card');
                    const courseTitle = courseCard.querySelector('.course-title').textContent;
                    if (confirm(`Are you sure you want to archive the course: ${courseTitle}?`)) {
                        const courseCode = this.id;

                        if (!courseCode) {
                            alert("There was an error archiving the course. Please try again.");
                            return;
                        }

                        // Simulate API call
                        const formData = {
                            courseCode: courseCode
                        };

                        $.ajax({
                            type: "POST",
                            url: "../endpoint/archive-course",
                            data: formData,
                            success: function(result) {
                                console.log(result);
                                if (result.success) {
                                    alert(result.message);
                                    courseCard.remove();
                                } else {
                                    alert(result.message);
                                }
                            },
                            error: function(error) {
                                console.log(error);
                            }
                        });
                    }
                });
            });

            // Filter functionality
            document.querySelector('.filter-btn.apply').addEventListener('click', function() {});

            document.querySelector('.filter-btn.reset').addEventListener('click', function() {
                // Reset all filter inputs
                document.querySelectorAll('.filter-group select, .filter-group input').forEach(input => {
                    input.value = 'all';
                });
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
        });
    </script>
</body>

</html>