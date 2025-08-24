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


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Staff Portal - Lecturers</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="./css/lecturers.css">
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
                    <button class="action-btn" id="addLecturerBtn">
                        <i class="fas fa-plus"></i>
                        Add New Lecturer
                    </button>
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
            <div class="pagination">
                <div class="page-btn disabled">
                    <i class="fas fa-chevron-left"></i>
                </div>
                <div class="page-btn active">1</div>
                <div class="page-btn">2</div>
                <div class="page-btn">3</div>
                <div class="page-btn">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Lecturer Modal -->
    <div class="modal" id="addLecturerModal">
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
    </div>

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
                    <h2>Manage Course Assignments</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="assignLecturerForm">
                        <div class="form-group">
                            <label for="assignLecturer">Select Lecturer</label>
                            <select id="assignLecturer" required>
                                <option value="">Select Lecturer</option>
                                <option value="1">Dr. John Doe - Marine Engineering</option>
                                <option value="2">Prof. Jane Smith - Nautical Science</option>
                                <option value="3">Dr. Robert Johnson - Nautical Science</option>
                                <option value="4">Prof. Emily Brown - Computer Science</option>
                                <option value="5">Dr. Michael Wilson - Logistics Management</option>
                                <option value="6">Prof. David Clark - Marine Engineering</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Select Courses to Assign</label>
                            <div class="checkbox-list">
                                <div class="checkbox-item">
                                    <input type="checkbox" id="assignCourse1" value="ME101">
                                    <label for="assignCourse1">ME101 - Introduction to Marine Engineering</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="assignCourse2" value="ML202">
                                    <label for="assignCourse2">ML202 - Maritime Law and Regulations</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="assignCourse3" value="NS305">
                                    <label for="assignCourse3">NS305 - Ship Navigation Systems</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="assignCourse4" value="CS304">
                                    <label for="assignCourse4">CS304 - Database Management Systems</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="assignCourse5" value="LM201">
                                    <label for="assignCourse5">LM201 - Supply Chain Management</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="assignCourse6" value="ME302">
                                    <label for="assignCourse6">ME302 - Marine Propulsion Systems</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="notifyLecturer">
                                <input type="checkbox" id="notifyLecturer" checked>
                                Notify lecturer about course assignment
                            </label>
                        </div>
                        <input type="hidden" id="departmentId" value="<?php echo $departmentId; ?>">
                    </form>
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

    <script>
        const staticLecturersData = <?= json_encode($activeLectuers); ?>;
        const totalLecturers = <?= $totalActiveLecturers; ?>;
        const departmentId = <?= json_encode($departmentId); ?>;
        const semesterId = <?= json_encode($semesterId); ?>;
        const activeSemesters = <?= json_encode($activeSemesters); ?>;
        const baseUrl = '../endpoint/';

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
                            <div class="lecturer-department">${lecturer.department_name}</div>
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
        document.getElementById('addLecturerBtn').addEventListener('click', () => openModal('addLecturerModal'));
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

        function openModal(modalId) {
            // Close all modals first
            Object.values(modals).forEach(modal => {
                modal.classList.remove('active');
            });

            // Open the requested modal
            modals[modalId].classList.add('active');
        }

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