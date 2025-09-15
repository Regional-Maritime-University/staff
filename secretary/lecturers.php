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
use Src\Core\Base;
use Src\Core\Course;
use Src\Core\CourseCategory;
use Src\Core\Staff;

require_once('../inc/admin-database-con.php');

$secretary          = new SecretaryController($db, $user, $pass);
$course_category    = new CourseCategory($db, $user, $pass);
$course             = new Course($db, $user, $pass);
$base               = new Base($db, $user, $pass);
$staff           = new Staff($db, $user, $pass);


$pageTitle = "Lecturers";
$activePage = "lecturers";

$departmentId = $_SESSION["staff"]["department_id"] ?? null;
$semesterId = 2; //$_SESSION["semester"] ?? null;
$archived = false;

$activeSemesters = $secretary->fetchActiveSemesters();

$departmentStaffs = $staff->fetch("department", $departmentId, $archived, true, true);
$activeLectuers = $departmentStaffs ? array_filter($departmentStaffs["data"], function ($staff) {
    return in_array($staff['role'], ['lecturer', 'hod']) && !$staff['archived'];
}) : [];
$totalActiveLecturers = count($activeLectuers);

$activeSemesters = $secretary->fetchActiveSemesters();
$lecturers = $secretary->fetchAllLecturers($departmentId, $archived);

$activeClasses = $secretary->fetchAllActiveClasses(departmentId: $departmentId);
$totalActiveClasses = $activeClasses && is_array($activeClasses) ? count($activeClasses) : 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Staff Portal - Lecturers</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="./css/lecturers.css">
    <link rel="stylesheet" href="./css/course-selection-modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Sidebar -->
    <?php require_once '../components/sidebar.php'; ?>

    <div class="main-content">

        <?php require_once '../components/header.php'; ?>

        <div class="dashboard-content">
            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <!-- <button class="action-btn" id="addLecturerBtn">
                        <i class="fas fa-plus"></i>
                        Add New Lecturer
                    </button> -->
                    <!-- <button class="action-btn" id="bulkUploadBtn">
                        <i class="fas fa-upload"></i>
                        Bulk Upload Lecturers
                    </button> -->
                    <button class="action-btn" id="assignCoursesBtn">
                        <i class="fas fa-tasks"></i>
                        Course & Class Assignments
                    </button>
                    <button class="action-btn" id="lecturerContactsBtn">
                        <i class="fas fa-address-book"></i>
                        Lecturer Contacts
                    </button>
                    <button class="action-btn danger" id="archivedLecturersBtn">
                        <i class="fas fa-list"></i>
                        Archived Lecturers
                    </button>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filter-group">
                    <label for="expertise">Area of Expertise</label>
                    <select id="expertise">
                        <option value="">All Areas</option>
                        <option value="1">Marine Engineering</option>
                        <option value="2">Maritime Law</option>
                        <option value="3">Navigation</option>
                        <option value="4">Supply Chain Management</option>
                        <option value="5">Computer Programming</option>
                        <option value="6">Database Systems</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="status">Availability Status</label>
                    <select id="status">
                        <option value="">All Statuses</option>
                        <option value="available">Available</option>
                        <option value="busy">Busy</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="courses">Course Load</label>
                    <select id="courses">
                        <option value="">Any</option>
                        <option value="low">Low (1-2 Courses)</option>
                        <option value="medium">Medium (3-4 Courses)</option>
                        <option value="high">High (5+ Courses)</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="filter-btn apply">Apply Filters</button>
                    <button class="filter-btn reset">Reset</button>
                </div>
            </div>

            <!-- Lecturer Grid -->
            <div class="lecturer-grid">
                <!-- Lecturer cards will be dynamically added here -->
            </div>

            <!-- Pagination -->
            <!-- <div class="pagination">
                <div class="page-btn disabled">
                    <i class="fas fa-chevron-left"></i>
                </div>
                <div class="page-btn active">1</div>
                <div class="page-btn">2</div>
                <div class="page-btn">3</div>
                <div class="page-btn">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div> -->
        </div>
    </div>

    <!-- Add Lecturer Modal -->
    <!-- <div class="modal" id="addLecturerModal">
        <div class="modal-dialog modal-lg modal-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Add New Lecturer</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="addLecturerForm">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="lecturerTitle">Title</label>
                                <select id="lecturerTitle" required>
                                    <option value="">Select Title</option>
                                    <option value="Dr.">Dr.</option>
                                    <option value="Prof.">Prof.</option>
                                    <option value="Mr.">Mr.</option>
                                    <option value="Mrs.">Mrs.</option>
                                    <option value="Ms.">Ms.</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="lecturerLastName">Last Name</label>
                                <input type="text" id="lecturerLastName" required>
                            </div>
                            <div class="form-group">
                                <label for="lecturerFirstName">First Name</label>
                                <input type="text" id="lecturerFirstName" required>
                            </div>
                            <div class="form-group">
                                <label for="lecturerMiddleName">Middle Name</label>
                                <input type="text" id="lecturerMiddleName" required>
                            </div>
                            <div class="form-group">
                                <label for="lecturerPosition">Position</label>
                                <select id="lecturerPosition" required>
                                    <option value="">Select Position</option>
                                    <option value="Professor">Professor</option>
                                    <option value="Associate Professor">Associate Professor</option>
                                    <option value="Senior Lecturer">Senior Lecturer</option>
                                    <option value="Lecturer">Lecturer</option>
                                    <option value="Assistant Lecturer">Assistant Lecturer</option>
                                    <option value="Visiting Lecturer">Visiting Lecturer</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="lecturerEmail">Email Address</label>
                                <input type="email" id="lecturerEmail" required>
                            </div>
                            <div class="form-group">
                                <label for="lecturerPhone">Phone Number</label>
                                <input type="tel" id="lecturerPhone" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="lecturerSpecialization">Areas of Specialization</label>
                            <div class="tags-input-container" id="specializationContainer">
                                <input type="text" class="tags-input" id="specializationInput" placeholder="Type and press Enter to add">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="lecturerPhoto">Profile Photo</label>
                            <input type="file" id="lecturerPhoto" class="file-input" accept="image/*">
                            <label for="lecturerPhoto" class="file-label">Choose Photo</label>
                            <div id="photoPreview" class="file-preview" style="display: none;">
                                <img src="../assets/img/default-avatar.jpg" alt="Profile Preview" class="preview-img" id="previewImg">
                                <div class="preview-details">
                                    <div class="preview-filename" id="previewFilename"></div>
                                    <div class="preview-filesize" id="previewFilesize"></div>
                                </div>
                                <div class="preview-remove" id="removePreview">
                                    <i class="fas fa-times"></i>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="departmentId" value="<?php echo $departmentId; ?>">
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" data-dismiss="modal">Cancel</button>
                    <button class="submit-btn" id="saveLecturerBtn">Save Lecturer</button>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Bulk Upload Modal -->
    <!-- <div class="modal" id="bulkUploadModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Bulk Upload Lecturers</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="bulkUploadLecturerForm">
                        <div class="upload-area">
                            <div class="upload-icon">
                                <i class="fas fa-file-upload fa-3x"></i>
                            </div>
                            <h3>Upload Lecturer List</h3>
                            <p>Drag and drop your CSV or Excel file here, or click the button below to select a file.</p>
                            <input type="file" id="lecturerFileInput" class="file-input" accept=".csv, .xlsx">
                            <label for="lecturerFileInput" class="file-label">Choose File</label>
                            <div class="selected-file-name" id="selectedFileName"></div>
                            <div class="template-download">
                                <a href="#" class="download-link">Download Template</a>
                            </div>
                            <input type="hidden" id="departmentId" value="<?php echo $departmentId; ?>">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" data-dismiss="modal">Cancel</button>
                    <button class="submit-btn" id="uploadLecturersBtn">Upload Lecturers</button>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Assign Courses Modal -->
    <div class="modal" id="assignCoursesModal">
        <div class="modal-dialog modal-lg modal-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Assign Lecturer</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="assign-course-tabs">
                        <button class="tab-btn active" data-tab="toCourse">To Course</button>
                        <button class="tab-btn" data-tab="toClass">To Class</button>
                    </div>
                    <div class="tab-content active" id="toCourse">
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
                    <div class="form-group">
                        <label for="assignmentNotes">Notes (Optional)</label>
                        <textarea id="assignmentNotes" rows="3" placeholder="Add any additional notes about this assignment"></textarea>
                    </div>
                    <input type="hidden" id="departmentSelect" name="department" value="<?= $departmentId ?>">
                    <input type="hidden" id="assignCourseActionSelect" name="action" value="toCourse">
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" data-dismiss="modal">Cancel</button>
                    <button class="submit-btn" id="saveAssignmentsBtn">Save Assignments</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Lecturer Contacts Modal -->
    <div class="modal" id="lecturerContactsModal">
        <div class="modal-dialog modal-lg  modal-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Lecturer Contact Directory</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <input type="text" placeholder="Search contacts..." id="contactSearch">
                    </div>
                    <div class="results-table-container">
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Dr. John Doe</td>
                                    <td>Marine Engineering</td>
                                    <td>john.doe@rmu.edu</td>
                                    <td>+233 55 123 4567</td>
                                    <td>
                                        <button class="action-icon" title="Email">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                        <button class="action-icon" title="Call">
                                            <i class="fas fa-phone"></i>
                                        </button>
                                        <button class="action-icon" title="Export vCard">
                                            <i class="fas fa-address-card"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Prof. Jane Smith</td>
                                    <td>Nautical Science</td>
                                    <td>jane.smith@rmu.edu</td>
                                    <td>+233 55 234 5678</td>
                                    <td>
                                        <button class="action-icon" title="Email">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                        <button class="action-icon" title="Call">
                                            <i class="fas fa-phone"></i>
                                        </button>
                                        <button class="action-icon" title="Export vCard">
                                            <i class="fas fa-address-card"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Dr. Robert Johnson</td>
                                    <td>Nautical Science</td>
                                    <td>robert.johnson@rmu.edu</td>
                                    <td>+233 55 345 6789</td>
                                    <td>
                                        <button class="action-icon" title="Email">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                        <button class="action-icon" title="Call">
                                            <i class="fas fa-phone"></i>
                                        </button>
                                        <button class="action-icon" title="Export vCard">
                                            <i class="fas fa-address-card"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Prof. Emily Brown</td>
                                    <td>Computer Science</td>
                                    <td>emily.brown@rmu.edu</td>
                                    <td>+233 55 456 7890</td>
                                    <td>
                                        <button class="action-icon" title="Email">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                        <button class="action-icon" title="Call">
                                            <i class="fas fa-phone"></i>
                                        </button>
                                        <button class="action-icon" title="Export vCard">
                                            <i class="fas fa-address-card"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Dr. Michael Wilson</td>
                                    <td>Logistics Management</td>
                                    <td>michael.wilson@rmu.edu</td>
                                    <td>+233 55 567 8901</td>
                                    <td>
                                        <button class="action-icon" title="Email">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                        <button class="action-icon" title="Call">
                                            <i class="fas fa-phone"></i>
                                        </button>
                                        <button class="action-icon" title="Export vCard">
                                            <i class="fas fa-address-card"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Prof. David Clark</td>
                                    <td>Marine Engineering</td>
                                    <td>david.clark@rmu.edu</td>
                                    <td>+233 55 678 9012</td>
                                    <td>
                                        <button class="action-icon" title="Email">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                        <button class="action-icon" title="Call">
                                            <i class="fas fa-phone"></i>
                                        </button>
                                        <button class="action-icon" title="Export vCard">
                                            <i class="fas fa-address-card"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="form-group" style="margin-top: 20px;">
                        <button class="submit-btn" id="exportContactsBtn" style="width: auto;">
                            <i class="fas fa-file-export"></i> Export All Contacts
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- View archived Lecturer Modal -->
    <div class="modal" id="archivedLecturersModal">
        <div class="modal-dialog modal-lg modal-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Archived Lecturers</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="archived-lecturers-grid">
                        <!-- Archived lecturers will be dynamically added here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" data-dismiss="modal">Close</button>
                    <button class="submit-btn" id="restoreArchivedLecturersBtn">Restore Selected Lecturers</button>
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

    <script>
        const staticLecturersData = <?= json_encode($activeLectuers); ?>;
        const totalLecturers = <?= $totalActiveLecturers; ?>;
        const departmentId = <?= json_encode($departmentId); ?>;
        const semesterId = <?= json_encode($semesterId); ?>;
        const activeSemesters = <?= json_encode($activeSemesters); ?>;
        const baseUrl = '../endpoint/';
        let departmentCourses = null;

        const lecturersData = Object.values(staticLecturersData).map(lecturer => ({
            number: lecturer.number,
            first_name: lecturer.first_name,
            middle_name: lecturer.middle_name || '',
            last_name: lecturer.last_name,
            full_name: lecturer.full_name,
            title: lecturer.prefix || '',
            position: lecturer.designation,
            email: lecturer.email,
            phone: lecturer.phone_number || '',
            photo: lecturer.avatar || '../assets/img/default-avatar.jpg',
            availability: (lecturer.availability === 'available' || lecturer.availability === 'busy') ? lecturer.availability : 'unavailable',
            gender: lecturer.gender,
            department_id: lecturer.department_id,
            department_name: lecturer.department_name,
            specializations: lecturer.specializations || [],
            courses: lecturer.courses || [],
        }));

        console.log('Lecturers Data:', lecturersData);

        // Function to render lecturers in the grid
        let lecturerGrid = document.querySelector('.lecturer-grid');
        lecturerGrid.innerHTML = '';
        lecturersData.forEach(lecturer => {
            const card = document.createElement('div');
            card.className = 'lecturer-card';
            card.innerHTML = `
                <div class="lecturer-header">
                    <div class="lecturer-info">
                        <div class="lecturer-avatar">
                            <img src="../uploads/profiles/${lecturer.photo || '../assets/img/default-avatar.jpg'}" alt="${lecturer.full_name}">
                        </div>
                        <div class="lecturer-info-text">
                            <h3>${lecturer.full_name}</h3>
                            <div class="lecturer-title">${lecturer.position}</div>
                            <!--<div class="lecturer-department">${lecturer.department_name}</div>-->
                        </div>
                    </div>
                    <div class="lecturer-actions">
                        <button class="action-icon edit-lecturer" title="Edit Lecturer" data-id="${lecturer.number}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-icon archive-lecturer" title="Delete Lecturer" data-id="${lecturer.number}">
                            <i class="fas fa-archive" style="color: var(--danger-color);"></i>
                        </button>
                    </div>
                </div>
                <div class="lecturer-details">
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value">${lecturer.email}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value">${lecturer.phone}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Courses Teaching</div>
                        <div class="detail-value"><span class="course-count">${(lecturer.courses || []).length}</span></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Availability</div>
                        <div class="detail-value">
                            <div class="${'availability-status ' + lecturer.availability}">
                                <span class="${'status-dot ' + lecturer.availability}"></span>
                                ${lecturer.availability.charAt(0).toUpperCase() + lecturer.availability.slice(1)} 
                            </div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Specializations</div>
                        <div class="lecturer-specializations">
                            ${(lecturer.specializations || []).map(spec => `<span class="specialization-tag">${spec}</span>`).join('')}
                        </div>
                    </div>
                    <div class="course-list">
                        <h4>Assigned Courses:</h4>
                        <div class="courses">
                            ${(lecturer.courses || []).map(course => `<span class="course-tag">${course.code} - ${course.name}</span>`).join('')}
                        </div>
                    </div>
                </div>
            `;
            lecturerGrid.appendChild(card);
        });

        // Toggle sidebar
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('expanded');
        });

        // Modal functionality
        const modals = {
            addLecturerModal: document.getElementById('addLecturerModal'),
            // bulkUploadModal: document.getElementById('bulkUploadModal'),
            assignCoursesModal: document.getElementById('assignCoursesModal'),
            lecturerContactsModal: document.getElementById('lecturerContactsModal'),
            archivedLecturersModal: document.getElementById('archivedLecturersModal'),
        };

        // Open modals
        // document.getElementById('addLecturerBtn').addEventListener('click', () => openModal('addLecturerModal'));
        // document.getElementById('bulkUploadBtn').addEventListener('click', () => openModal('bulkUploadModal'));
        document.getElementById('assignCoursesBtn').addEventListener('click', () => openModal('assignCoursesModal'));
        document.getElementById('lecturerContactsBtn').addEventListener('click', () => openModal('lecturerContactsModal'));
        document.getElementById('archivedLecturersBtn').addEventListener('click', () => openModal('archivedLecturersModal'));

        // Close modals
        document.querySelectorAll('.close-btn, .cancel-btn').forEach(button => {
            button.addEventListener('click', function() {
                const modal = this.closest('.modal');
                if (modal) {
                    modal.classList.remove('active');
                }
            });
        });

        // File input handling for bulk upload
        // const fileInput = document.getElementById('lecturerFileInput');
        // const fileNameDisplay = document.getElementById('selectedFileName');

        // fileInput.addEventListener('change', function() {
        //     if (this.files.length > 0) {
        //         fileNameDisplay.textContent = this.files[0].name;
        //     } else {
        //         fileNameDisplay.textContent = '';
        //     }
        // });

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

        // Tab functionality for Upload Courses Modal
        const assignLecturerTabs = document.querySelectorAll(".assign-course-tabs .tab-btn")

        assignLecturerTabs.forEach((btn) => {
            btn.addEventListener("click", function() {
                const tabId = this.getAttribute("data-tab");

                // Remove active class from all tabs and contents
                assignLecturerTabs.forEach((btn) => btn.classList.remove("active"));
                document.querySelectorAll(".tab-content").forEach((content) => content.classList.remove("active"));

                // Add active class to clicked tab and corresponding content
                this.classList.add("active");
                document.getElementById(tabId).classList.add("active");

                // add to actionSelect
                document.getElementById("assignCourseActionSelect").value = tabId;
                console.log(document.getElementById("assignCourseActionSelect").value);
            });
        });

        // Photo preview handling
        const photoInput = document.getElementById('lecturerPhoto');
        const photoPreview = document.getElementById('photoPreview');
        const previewImg = document.getElementById('previewImg');
        const previewFilename = document.getElementById('previewFilename');
        const previewFilesize = document.getElementById('previewFilesize');
        const removePreview = document.getElementById('removePreview');

        photoInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                const file = this.files[0];
                const reader = new FileReader();

                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewFilename.textContent = file.name;

                    // Format file size
                    let size = file.size;
                    let sizeDisplay = '';
                    if (size < 1024) {
                        sizeDisplay = size + ' B';
                    } else if (size < 1024 * 1024) {
                        sizeDisplay = Math.round(size / 1024) + ' KB';
                    } else {
                        sizeDisplay = Math.round(size / (1024 * 1024) * 10) / 10 + ' MB';
                    }

                    previewFilesize.textContent = sizeDisplay;
                    photoPreview.style.display = 'flex';
                };

                reader.readAsDataURL(file);
            }
        });

        removePreview.addEventListener('click', function() {
            photoInput.value = '';
            photoPreview.style.display = 'none';
            previewImg.src = '';
        });

        // Tags input for specializations
        const specializationInput = document.getElementById('specializationInput');
        const specializationContainer = document.getElementById('specializationContainer');
        const specializations = [];

        specializationInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                const value = this.value.trim();

                if (value && !specializations.includes(value)) {
                    specializations.push(value);
                    const tag = document.createElement('div');
                    tag.className = 'tag';
                    tag.innerHTML = `
                    <span>${value}</span>
                    <i class="fas fa-times"></i>
                `;

                    specializationContainer.insertBefore(tag, this);
                    this.value = '';

                    // Add event listener for removing tag
                    tag.querySelector('i').addEventListener('click', function() {
                        const idx = specializations.indexOf(value);
                        if (idx !== -1) {
                            specializations.splice(idx, 1);
                        }
                        specializationContainer.removeChild(tag);
                    });
                }
            }
        });

        // Form submissions
        document.getElementById('saveLecturerBtn').addEventListener('click', function() {
            const form = document.getElementById('addLecturerForm');
            if (form.checkValidity()) {
                // Simulate form submission
                alert('Lecturer added successfully!');
                modals.addLecturerModal.classList.remove('active');
                form.reset();

                // Clear specializations
                const tags = specializationContainer.querySelectorAll('.tag');
                tags.forEach(tag => specializationContainer.removeChild(tag));
                specializations.length = 0;

                // Clear photo preview
                photoPreview.style.display = 'none';
            } else {
                alert('Please fill all required fields.');
            }
        });

        // document.getElementById('uploadLecturersBtn').addEventListener('click', function() {
        //     if (fileInput.files.length > 0) {
        //         // Simulate file upload
        //         alert('Lecturers uploaded successfully!');
        //         modals.bulkUploadModal.classList.remove('active');
        //         fileInput.value = '';
        //         fileNameDisplay.textContent = '';
        //     } else {
        //         alert('Please select a file to upload.');
        //     }
        // });

        document.getElementById('saveAssignmentsBtn').addEventListener('click', function() {
            const lecturer = document.getElementById('assignLecturer').value;
            const selectedCourses = document.querySelectorAll('#assignCoursesModal .checkbox-list input[type="checkbox"]:checked');

            if (lecturer && selectedCourses.length > 0) {
                // Simulate assignment
                alert('Courses assigned successfully!');
                modals.assignCoursesModal.classList.remove('active');
            } else {
                alert('Please select a lecturer and at least one course.');
            }
        });

        // Edit and archive lecturer buttons
        document.querySelectorAll('.edit-lecturer').forEach(button => {
            button.addEventListener('click', function() {
                const lecturerCard = this.closest('.lecturer-card');
                const lecturerName = lecturerCard.querySelector('h3').textContent;
                alert(`Edit lecturer: ${lecturerName}`);
                // In a real application, you would populate the edit form with lecturer data
                openModal('addLecturerModal');
            });
        });

        document.querySelectorAll('.archive-lecturer').forEach(button => {
            button.addEventListener('click', function() {
                const lecturerCard = this.closest('.lecturer-card');
                const lecturerName = lecturerCard.querySelector('h3').textContent;
                if (confirm(`Are you sure you want to archive the lecturer: ${lecturerName}?`)) {
                    // Simulate archiving lecturer
                    const lecturerNumber = this.getAttribute('data-id');
                    // Call the async function to archive the lecturer
                    archiveLecturer(lecturerNumber).then(response => {
                        if (response.success) {
                            alert(`Lecturer ${lecturerName} archived successfully!`);
                            lecturerCard.remove(); // Remove the card from the grid
                        } else {
                            alert(`Failed to archive lecturer: ${response.message}`);
                        }
                    }).catch(error => {
                        console.error("Error archiving lecturer:", error);
                        alert("An error occurred while archiving the lecturer.");
                    });
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
            alert('Filters reset!');
        });

        // Pagination
        document.querySelectorAll('.pagination .page-item:not(.disabled)').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.pagination .page-item').forEach(p => {
                    p.classList.remove('active');
                });
                this.classList.add('active');
                // In a real application, you would load the corresponding page of lecturers
            });
        });

        // Contact search functionality
        document.getElementById('contactSearch').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#lecturerContactsModal tbody tr');

            rows.forEach(row => {
                const name = row.cells[0].textContent.toLowerCase();
                const department = row.cells[1].textContent.toLowerCase();
                const email = row.cells[2].textContent.toLowerCase();

                if (name.includes(searchTerm) || department.includes(searchTerm) || email.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Export contacts button
        document.getElementById('exportContactsBtn').addEventListener('click', function() {
            alert('Contacts exported successfully!');
        });

        // Async function to fetch archived lecturers
        async function fetchArchivedLecturers() {
            try {
                const response = await fetch('../endpoint/fetch-staff?department=' + departmentId + '&archived=true');
                const result = await response.json();

                if (!result.success) {
                    if (result.message && result.message == "logout") {
                        window.location.href = "../index.php";
                        return [];
                    }
                    throw new Error(result.message || "Failed to load archived lecturers");
                }

                return result.data || [];
            } catch (error) {
                console.error("Error fetching archived lecturers:", error);
                return [];
            }
        }

        // Async function to restore and delete lecturers
        async function restoreLecturer(lecturerNumber) {
            try {
                const response = await fetch('../endpoint/unarchive-staff', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        'number[]': lecturerNumber
                    })
                });
                const data = await response.json();
                if (data.success) {
                    alert("Lecturer restored successfully!");
                    document.getElementById('archivedLecturersBtn').click();
                } else {
                    alert("Failed to restore lecturer: " + data.message);
                }
            } catch (error) {
                console.error("Error restoring lecturer:", error);
                alert("An error occurred while restoring the lecturer.");
            }
        }

        async function deleteLecturer(lecturerNumber) {
            if (confirm("Are you sure you want to delete this lecturer permanently? This action cannot be undone.")) {
                try {
                    const response = await fetch('../endpoint/delete-staff', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            'number[]': lecturerNumber
                        })
                    });
                    const data = await response.json();
                    if (data.success) {
                        alert("Lecturer deleted successfully!");
                        document.getElementById('archivedLecturersBtn').click();
                    } else {
                        alert("Failed to delete lecturer: " + data.message);
                    }
                } catch (error) {
                    console.error("Error deleting lecturer:", error);
                    alert("An error occurred while deleting the lecturer.");
                }
            }
        }

        async function archiveLecturer(lecturerNumber) {
            try {
                const response = await fetch('../endpoint/archive-staff', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        'number': lecturerNumber
                    })
                });
                const data = await response.json();
                if (data.success) {
                    return data;
                } else {
                    throw new Error(data.message || "Failed to archive lecturer");
                }
            } catch (error) {
                console.error("Error archiving lecturer:", error);
                throw error;
            }

        }

        // Fetch all archived lecturers for this department and display them in the modal when archivedLecturersBtn is clicked
        document.getElementById('archivedLecturersBtn').addEventListener('click', () => {
            const archivedLecturersModal = document.getElementById('archivedLecturersModal');
            const archivedLecturersGrid = archivedLecturersModal.querySelector('.archived-lecturers-grid');
            archivedLecturersGrid.innerHTML = ''; // Clear previous content

            if (!departmentId) {
                archivedLecturersGrid.innerHTML = `
                    <div class="no-lecturers">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Error: Department ID is not set. Please select a department.</p>
                    </div>
                    `;
                return;
            }

            // Show loading message
            archivedLecturersGrid.innerHTML = `
                    <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading archived lecturers...</p>
                    </div>
                `;

            // Fetch archived lecturers from the server
            fetchArchivedLecturers()
                .then(lecturers => {
                    lecturers = lecturers.data || [];

                    if (!Array.isArray(lecturers)) {
                        throw new Error("Invalid data format received from server.");
                    }

                    archivedLecturersGrid.innerHTML = ''; // Clear loading message
                    if (!lecturers || lecturers.length === 0) {
                        archivedLecturersGrid.innerHTML = `
                            <div class="no-lecturers">
                                <i class="fas fa-exclamation-triangle"></i>
                                <p>No archived lecturers found for this department.</p>
                            </div>
                            `;
                        return;
                    }

                    // Populate the archived lecturers grid with fetched data
                    lecturers.forEach(lecturer => {
                        const lecturerCard = document.createElement('div');
                        lecturerCard.className = 'lecturer-card';
                        lecturerCard.innerHTML = `
                            <div class="lecturer-header">
                                <div class="lecturer-info">
                                <div class="lecturer-avatar">
                                    <img src="../uploads/profiles/${lecturer.avatar || '../assets/img/default-avatar.jpg'}" alt="${lecturer.full_name}">
                                </div>
                                <div class="lecturer-info-text">
                                    <h3>${lecturer.full_name}</h3>
                                    <div class="lecturer-title">${lecturer.designation}</div>
                                    <div class="lecturer-department">${lecturer.department_name || ''}</div>
                                </div>
                                </div>
                                <div class="lecturer-actions">
                                <button class="action-icon restore-lecturer" title="Restore Lecturer" data-number="${lecturer.number}">
                                    <i class="fas fa-undo"></i>
                                </button>
                                <button class="action-icon delete-lecturer" title="Delete Lecturer" data-number="${lecturer.number}">
                                    <i class="fas fa-trash-alt" style="color: var(--danger-color);"></i>
                                </button>
                                </div>
                            </div>
                            <div class="lecturer-details">
                                <div class="detail-item">
                                <div class="detail-label">Email</div>
                                <div class="detail-value">${lecturer.email}</div>
                                </div>
                                <div class="detail-item">
                                <div class="detail-label">Phone</div>
                                <div class="detail-value">${lecturer.phone_number || ''}</div>
                                </div>
                                <div class="detail-item">
                                <div class="detail-label">Specializations</div>
                                <div class="lecturer-specializations">
                                    ${(lecturer.specializations || []).map(spec => `<span class="specialization-tag">${spec}</span>`).join('')}
                                </div>
                                </div>
                            </div>
                            `;
                        archivedLecturersGrid.appendChild(lecturerCard);
                        // Add event listeners for restore and delete buttons
                        lecturerCard.querySelector('.restore-lecturer').addEventListener('click', function() {
                            const lecturerNumber = this.getAttribute('data-number');
                            restoreLecturer(lecturerNumber);
                        });
                        lecturerCard.querySelector('.delete-lecturer').addEventListener('click', function() {
                            const lecturerNumber = this.getAttribute('data-number');
                            deleteLecturer(lecturerNumber);
                        });
                    });
                })
                .catch(error => {
                    console.error("Error fetching archived lecturers:", error);
                    archivedLecturersGrid.innerHTML = `
                            <div class="no-lecturers">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Error loading archived lecturers: ${error.message}</p>
                            </div>
                        `;
                });
        });
    </script>
</body>

</html>