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
    <title>RMU Staff Portal - Students</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/students.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Sidebar -->
    <?php require_once '../components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <button class="toggle-sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Student Management</h1>
            </div>
            <div class="header-right">
                <div class="search-bar">
                    <input type="text" placeholder="Search students...">
                    <button class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="header-actions">
                    <button class="action-btn">
                        <i class="fas fa-bell"></i>
                        <span class="badge">7</span>
                    </button>
                    <button class="action-btn">
                        <i class="fas fa-envelope"></i>
                        <span class="badge">3</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="dashboard-content">
            <!-- Student Stats -->
            <div class="student-stats">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-value">1,245</div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-value">1,180</div>
                    <div class="stat-label">Active Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="stat-value">45</div>
                    <div class="stat-label">On Leave</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <div class="stat-value">20</div>
                    <div class="stat-label">Inactive</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <button class="action-btn" id="addStudentBtn">
                        <i class="fas fa-user-plus"></i>
                        Add New Student
                    </button>
                    <button class="action-btn" id="importStudentsBtn">
                        <i class="fas fa-file-import"></i>
                        Import Students
                    </button>
                    <button class="action-btn" id="registerCoursesBtn">
                        <i class="fas fa-clipboard-list"></i>
                        Course Registration
                    </button>
                    <button class="action-btn" id="exportDataBtn">
                        <i class="fas fa-file-export"></i>
                        Export Student Data
                    </button>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filter-group">
                    <label for="program">Program</label>
                    <select id="program">
                        <option value="">All Programs</option>
                        <option value="1">BSc. Marine Engineering</option>
                        <option value="2">BSc. Nautical Science</option>
                        <option value="3">BSc. Logistics Management</option>
                        <option value="4">BSc. Computer Science</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="level">Level</label>
                    <select id="level">
                        <option value="">All Levels</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                        <option value="300">300</option>
                        <option value="400">400</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select id="status">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="on-leave">On Leave</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="semester">Semester</label>
                    <select id="semester">
                        <option value="">All Semesters</option>
                        <option value="1" selected>First Semester 2023/2024</option>
                        <option value="2">Second Semester 2023/2024</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="filter-btn apply">Apply Filters</button>
                    <button class="filter-btn reset">Reset</button>
                </div>
            </div>

            <!-- Student Grid -->
            <div class="student-grid">
                <!-- Student Card 1 -->
                <div class="student-card">
                    <div class="student-header">
                        <div class="student-photo">
                            <img src="student1.jpg" alt="Student Photo">
                        </div>
                        <div class="student-info">
                            <h3 class="student-name">Samuel Mensah</h3>
                            <p class="student-id">RMU/CS/2020/001</p>
                            <span class="student-program">BSc. Computer Science</span>
                        </div>
                        <span class="student-status active">Active</span>
                        <div class="student-actions">
                            <button class="student-action edit-student" title="Edit Student">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="student-action delete-student" title="Delete Student">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="student-content">
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:samuel.mensah@rmu.edu">samuel.mensah@rmu.edu</a>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <a href="tel:+233501234567">+233 50 123 4567</a>
                            </div>
                        </div>
                        <div class="academic-info">
                            <div class="academic-item">
                                <div class="academic-label">Level</div>
                                <div class="academic-value">300</div>
                            </div>
                            <div class="academic-item">
                                <div class="academic-label">Credits</div>
                                <div class="academic-value">72/120</div>
                            </div>
                            <div class="academic-item">
                                <div class="academic-label">Courses</div>
                                <div class="academic-value">5 Current</div>
                            </div>
                        </div>
                    </div>
                    <div class="student-footer">
                        <div class="gpa excellent">GPA: 3.8</div>
                        <button class="view-profile-btn view-grades-btn" data-student="Samuel Mensah">View Grades</button>
                    </div>
                </div>

                <!-- Student Card 2 -->
                <div class="student-card">
                    <div class="student-header">
                        <div class="student-photo">
                            <img src="student2.jpg" alt="Student Photo">
                        </div>
                        <div class="student-info">
                            <h3 class="student-name">Abena Osei</h3>
                            <p class="student-id">RMU/ME/2021/042</p>
                            <span class="student-program">BSc. Marine Engineering</span>
                        </div>
                        <span class="student-status active">Active</span>
                        <div class="student-actions">
                            <button class="student-action edit-student" title="Edit Student">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="student-action delete-student" title="Delete Student">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="student-content">
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:abena.osei@rmu.edu">abena.osei@rmu.edu</a>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <a href="tel:+233551234568">+233 55 123 4568</a>
                            </div>
                        </div>
                        <div class="academic-info">
                            <div class="academic-item">
                                <div class="academic-label">Level</div>
                                <div class="academic-value">200</div>
                            </div>
                            <div class="academic-item">
                                <div class="academic-label">Credits</div>
                                <div class="academic-value">36/120</div>
                            </div>
                            <div class="academic-item">
                                <div class="academic-label">Courses</div>
                                <div class="academic-value">6 Current</div>
                            </div>
                        </div>
                    </div>
                    <div class="student-footer">
                        <div class="gpa good">GPA: 3.5</div>
                        <button class="view-profile-btn view-grades-btn" data-student="Abena Osei">View Grades</button>
                    </div>
                </div>

                <!-- Student Card 3 -->
                <div class="student-card">
                    <div class="student-header">
                        <div class="student-photo">
                            <img src="student3.jpg" alt="Student Photo">
                        </div>
                        <div class="student-info">
                            <h3 class="student-name">Kwame Addo</h3>
                            <p class="student-id">RMU/NS/2019/015</p>
                            <span class="student-program">BSc. Nautical Science</span>
                        </div>
                        <span class="student-status on-leave">On Leave</span>
                        <div class="student-actions">
                            <button class="student-action edit-student" title="Edit Student">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="student-action delete-student" title="Delete Student">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="student-content">
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:kwame.addo@rmu.edu">kwame.addo@rmu.edu</a>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <a href="tel:+233241234569">+233 24 123 4569</a>
                            </div>
                        </div>
                        <div class="academic-info">
                            <div class="academic-item">
                                <div class="academic-label">Level</div>
                                <div class="academic-value">400</div>
                            </div>
                            <div class="academic-item">
                                <div class="academic-label">Credits</div>
                                <div class="academic-value">105/120</div>
                            </div>
                            <div class="academic-item">
                                <div class="academic-label">Courses</div>
                                <div class="academic-value">0 Current</div>
                            </div>
                        </div>
                    </div>
                    <div class="student-footer">
                        <div class="gpa good">GPA: 3.2</div>
                        <button class="view-profile-btn view-grades-btn" data-student="Kwame Addo">View Grades</button>
                    </div>
                </div>

                <!-- Student Card 4 -->
                <div class="student-card">
                    <div class="student-header">
                        <div class="student-photo">
                            <img src="student4.jpg" alt="Student Photo">
                        </div>
                        <div class="student-info">
                            <h3 class="student-name">Fatima Ibrahim</h3>
                            <p class="student-id">RMU/LM/2022/078</p>
                            <span class="student-program">BSc. Logistics Management</span>
                        </div>
                        <span class="student-status active">Active</span>
                        <div class="student-actions">
                            <button class="student-action edit-student" title="Edit Student">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="student-action delete-student" title="Delete Student">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="student-content">
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:fatima.ibrahim@rmu.edu">fatima.ibrahim@rmu.edu</a>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <a href="tel:+233201234570">+233 20 123 4570</a>
                            </div>
                        </div>
                        <div class="academic-info">
                            <div class="academic-item">
                                <div class="academic-label">Level</div>
                                <div class="academic-value">100</div>
                            </div>
                            <div class="academic-item">
                                <div class="academic-label">Credits</div>
                                <div class="academic-value">18/120</div>
                            </div>
                            <div class="academic-item">
                                <div class="academic-label">Courses</div>
                                <div class="academic-value">6 Current</div>
                            </div>
                        </div>
                    </div>
                    <div class="student-footer">
                        <div class="gpa average">GPA: 2.8</div>
                        <button class="view-profile-btn view-grades-btn" data-student="Fatima Ibrahim">View Grades</button>
                    </div>
                </div>

                <!-- Student Card 5 -->
                <div class="student-card">
                    <div class="student-header">
                        <div class="student-photo">
                            <img src="student5.jpg" alt="Student Photo">
                        </div>
                        <div class="student-info">
                            <h3 class="student-name">Daniel Agyei</h3>
                            <p class="student-id">RMU/CS/2020/045</p>
                            <span class="student-program">BSc. Computer Science</span>
                        </div>
                        <span class="student-status inactive">Inactive</span>
                        <div class="student-actions">
                            <button class="student-action edit-student" title="Edit Student">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="student-action delete-student" title="Delete Student">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="student-content">
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:daniel.agyei@rmu.edu">daniel.agyei@rmu.edu</a>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <a href="tel:+233271234571">+233 27 123 4571</a>
                            </div>
                        </div>
                        <div class="academic-info">
                            <div class="academic-item">
                                <div class="academic-label">Level</div>
                                <div class="academic-value">300</div>
                            </div>
                            <div class="academic-item">
                                <div class="academic-label">Credits</div>
                                <div class="academic-value">54/120</div>
                            </div>
                            <div class="academic-item">
                                <div class="academic-label">Courses</div>
                                <div class="academic-value">0 Current</div>
                            </div>
                        </div>
                    </div>
                    <div class="student-footer">
                        <div class="gpa poor">GPA: 1.9</div>
                        <button class="view-profile-btn view-grades-btn" data-student="Daniel Agyei">View Grades</button>
                    </div>
                </div>

                <!-- Student Card 6 -->
                <div class="student-card">
                    <div class="student-header">
                        <div class="student-photo">
                            <img src="student6.jpg" alt="Student Photo">
                        </div>
                        <div class="student-info">
                            <h3 class="student-name">Grace Owusu</h3>
                            <p class="student-id">RMU/ME/2021/033</p>
                            <span class="student-program">BSc. Marine Engineering</span>
                        </div>
                        <span class="student-status active">Active</span>
                        <div class="student-actions">
                            <button class="student-action edit-student" title="Edit Student">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="student-action delete-student" title="Delete Student">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="student-content">
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:grace.owusu@rmu.edu">grace.owusu@rmu.edu</a>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <a href="tel:+233551234572">+233 55 123 4572</a>
                            </div>
                        </div>
                        <div class="academic-info">
                            <div class="academic-item">
                                <div class="academic-label">Level</div>
                                <div class="academic-value">200</div>
                            </div>
                            <div class="academic-item">
                                <div class="academic-label">Credits</div>
                                <div class="academic-value">42/120</div>
                            </div>
                            <div class="academic-item">
                                <div class="academic-label">Courses</div>
                                <div class="academic-value">5 Current</div>
                            </div>
                        </div>
                    </div>
                    <div class="student-footer">
                        <div class="gpa excellent">GPA: 3.9</div>
                        <button class="view-profile-btn view-grades-btn" data-student="Grace Owusu">View Grades</button>
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

    <!-- Add Student Modal -->
    <div class="modal" id="addStudentModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Add New Student</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="addStudentForm">
                        <div class="photo-upload">
                            <div class="photo-preview">
                                <img src="profile-placeholder.jpg" alt="Profile Photo" id="photoPreview">
                            </div>
                            <input type="file" id="studentPhoto" accept="image/*" style="display: none;">
                            <label for="studentPhoto">Choose Photo</label>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">First Name</label>
                                <input type="text" id="firstName" required>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name</label>
                                <input type="text" id="lastName" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="dateOfBirth">Date of Birth</label>
                                <input type="date" id="dateOfBirth" required>
                            </div>
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select id="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="program">Program</label>
                                <select id="studentProgram" required>
                                    <option value="">Select Program</option>
                                    <option value="1">BSc. Marine Engineering</option>
                                    <option value="2">BSc. Nautical Science</option>
                                    <option value="3">BSc. Logistics Management</option>
                                    <option value="4">BSc. Computer Science</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="level">Level</label>
                                <select id="studentLevel" required>
                                    <option value="">Select Level</option>
                                    <option value="100">100</option>
                                    <option value="200">200</option>
                                    <option value="300">300</option>
                                    <option value="400">400</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="entryYear">Entry Year</label>
                                <select id="entryYear" required>
                                    <option value="">Select Year</option>
                                    <option value="2023">2023</option>
                                    <option value="2022">2022</option>
                                    <option value="2021">2021</option>
                                    <option value="2020">2020</option>
                                    <option value="2019">2019</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="studentStatus" required>
                                    <option value="">Select Status</option>
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="on-leave">On Leave</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="guardianInfo">Guardian Information</label>
                            <textarea id="guardianInfo" rows="3" placeholder="Name, Relationship, Contact"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" data-dismiss="modal">Cancel</button>
                    <button class="submit-btn" id="saveStudentBtn">Save Student</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Students Modal -->
    <div class="modal" id="importStudentsModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Import Students</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="import-options">
                        <div class="import-option active" data-option="file">
                            <div class="import-icon">
                                <i class="fas fa-file-excel"></i>
                            </div>
                            <div class="import-title">Excel/CSV File</div>
                            <div class="import-desc">Import students from a spreadsheet</div>
                        </div>
                        <div class="import-option" data-option="api">
                            <div class="import-icon">
                                <i class="fas fa-cloud-download-alt"></i>
                            </div>
                            <div class="import-title">API Integration</div>
                            <div class="import-desc">Import from university system</div>
                        </div>
                    </div>

                    <div id="fileImportSection">
                        <div class="upload-area">
                            <div class="upload-icon">
                                <i class="fas fa-file-upload fa-3x"></i>
                            </div>
                            <h3>Upload Student List</h3>
                            <p>Drag and drop your CSV or Excel file here, or click the button below to select a file.</p>
                            <input type="file" id="studentFileInput" class="file-input" accept=".csv, .xlsx">
                            <label for="studentFileInput" class="file-label">Choose File</label>
                            <div class="selected-file-name" id="selectedFileName"></div>
                            <div class="template-download">
                                <a href="#" class="download-link">Download Template</a>
                            </div>
                        </div>
                    </div>

                    <div id="apiImportSection" style="display: none;">
                        <div class="form-group">
                            <label for="apiEndpoint">API Endpoint</label>
                            <input type="text" id="apiEndpoint" placeholder="https://api.university.edu/students">
                        </div>
                        <div class="form-group">
                            <label for="apiKey">API Key</label>
                            <input type="password" id="apiKey">
                        </div>
                        <div class="form-group">
                            <label for="importFilters">Import Filters</label>
                            <select id="importFilters" multiple>
                                <option value="new">New Students Only</option>
                                <option value="active">Active Students Only</option>
                                <option value="program1">Marine Engineering</option>
                                <option value="program2">Nautical Science</option>
                                <option value="program3">Logistics Management</option>
                                <option value="program4">Computer Science</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" data-dismiss="modal">Cancel</button>
                    <button class="submit-btn" id="importBtn">Import Students</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Registration Modal -->
    <div class="modal" id="courseRegistrationModal">
        <div class="modal-dialog modal-lg modal-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Course Registration</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="registrationStudent">Select Student</label>
                        <select id="registrationStudent" required>
                            <option value="">Select Student</option>
                            <option value="1">Samuel Mensah - RMU/CS/2020/001</option>
                            <option value="2">Abena Osei - RMU/ME/2021/042</option>
                            <option value="3">Fatima Ibrahim - RMU/LM/2022/078</option>
                            <option value="4">Grace Owusu - RMU/ME/2021/033</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="registrationSemester">Semester</label>
                        <select id="registrationSemester" required>
                            <option value="1" selected>First Semester 2023/2024</option>
                            <option value="2">Second Semester 2023/2024</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Select Courses to Register</label>
                        <div class="course-selection">
                            <div class="course-checkbox">
                                <input type="checkbox" id="course1" value="CS301">
                                <label for="course1">
                                    <div class="course-code">CS301</div>
                                    <div class="course-name">Database Management Systems</div>
                                </label>
                            </div>
                            <div class="course-checkbox">
                                <input type="checkbox" id="course2" value="CS302">
                                <label for="course2">
                                    <div class="course-code">CS302</div>
                                    <div class="course-name">Software Engineering</div>
                                </label>
                            </div>
                            <div class="course-checkbox">
                                <input type="checkbox" id="course3" value="CS303">
                                <label for="course3">
                                    <div class="course-code">CS303</div>
                                    <div class="course-name">Computer Networks</div>
                                </label>
                            </div>
                            <div class="course-checkbox">
                                <input type="checkbox" id="course4" value="CS304">
                                <label for="course4">
                                    <div class="course-code">CS304</div>
                                    <div class="course-name">Operating Systems</div>
                                </label>
                            </div>
                            <div class="course-checkbox">
                                <input type="checkbox" id="course5" value="CS305">
                                <label for="course5">
                                    <div class="course-code">CS305</div>
                                    <div class="course-name">Web Development</div>
                                </label>
                            </div>
                            <div class="course-checkbox">
                                <input type="checkbox" id="course6" value="CS306">
                                <label for="course6">
                                    <div class="course-code">CS306</div>
                                    <div class="course-name">Mobile Application Development</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Total Credits: <span id="totalCredits">0</span>/18</label>
                        <div class="progress" style="height: 10px; background-color: #e9ecef; border-radius: 5px; margin-top: 5px;">
                            <div id="creditProgress" style="height: 100%; width: 0%; background-color: var(--accent-color); border-radius: 5px; transition: width 0.3s;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" data-dismiss="modal">Cancel</button>
                    <button class="submit-btn" id="registerCoursesSubmitBtn">Register Courses</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Grades Modal -->
    <div class="modal" id="viewGradesModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Grades for <span id="studentNameGrades">Student</span></h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="gradeSemester">Select Semester</label>
                        <select id="gradeSemester">
                            <option value="1" selected>First Semester 2023/2024</option>
                            <option value="2">Second Semester 2022/2023</option>
                            <option value="3">First Semester 2022/2023</option>
                        </select>
                    </div>

                    <table class="grades-table">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Credits</th>
                                <th>Grade</th>
                                <th>GPA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>CS301</td>
                                <td>Database Management Systems</td>
                                <td>3</td>
                                <td><span class="grade-value a">A</span></td>
                                <td>4.0</td>
                            </tr>
                            <tr>
                                <td>CS302</td>
                                <td>Software Engineering</td>
                                <td>3</td>
                                <td><span class="grade-value a">A-</span></td>
                                <td>3.7</td>
                            </tr>
                            <tr>
                                <td>CS303</td>
                                <td>Computer Networks</td>
                                <td>3</td>
                                <td><span class="grade-value b">B+</span></td>
                                <td>3.3</td>
                            </tr>
                            <tr>
                                <td>CS304</td>
                                <td>Operating Systems</td>
                                <td>3</td>
                                <td><span class="grade-value b">B</span></td>
                                <td>3.0</td>
                            </tr>
                            <tr>
                                <td>CS305</td>
                                <td>Web Development</td>
                                <td>3</td>
                                <td><span class="grade-value a">A</span></td>
                                <td>4.0</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2"><strong>Semester Total</strong></td>
                                <td><strong>15</strong></td>
                                <td></td>
                                <td><strong>3.6</strong></td>
                            </tr>
                        </tfoot>
                    </table>

                    <div style="margin-top: 20px; text-align: right;">
                        <button class="action-btn" style="width: auto; height: auto; padding: 8px 15px; background-color: var(--primary-color);">
                            <i class="fas fa-file-pdf"></i> Export as PDF
                        </button>
                        <button class="action-btn" style="width: auto; height: auto; padding: 8px 15px; background-color: var(--success-color);">
                            <i class="fas fa-file-excel"></i> Export as Excel
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" data-dismiss="modal">Close</button>
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
            addStudentModal: document.getElementById('addStudentModal'),
            importStudentsModal: document.getElementById('importStudentsModal'),
            courseRegistrationModal: document.getElementById('courseRegistrationModal'),
            viewGradesModal: document.getElementById('viewGradesModal')
        };

        // Open modals
        document.getElementById('addStudentBtn').addEventListener('click', () => openModal('addStudentModal'));
        document.getElementById('importStudentsBtn').addEventListener('click', () => openModal('importStudentsModal'));
        document.getElementById('registerCoursesBtn').addEventListener('click', () => openModal('courseRegistrationModal'));

        // View grades buttons
        document.querySelectorAll('.view-grades-btn').forEach(button => {
            button.addEventListener('click', function() {
                const studentName = this.getAttribute('data-student');
                document.getElementById('studentNameGrades').textContent = studentName;
                openModal('viewGradesModal');
            });
        });

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
        const fileInput = document.getElementById('studentFileInput');
        const fileNameDisplay = document.getElementById('selectedFileName');

        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileNameDisplay.textContent = this.files[0].name;
            } else {
                fileNameDisplay.textContent = '';
            }
        });

        // Photo preview
        const photoInput = document.getElementById('studentPhoto');
        const photoPreview = document.getElementById('photoPreview');

        photoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    photoPreview.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Import options toggle
        document.querySelectorAll('.import-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.import-option').forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');

                const importType = this.getAttribute('data-option');
                if (importType === 'file') {
                    document.getElementById('fileImportSection').style.display = 'block';
                    document.getElementById('apiImportSection').style.display = 'none';
                } else {
                    document.getElementById('fileImportSection').style.display = 'none';
                    document.getElementById('apiImportSection').style.display = 'block';
                }
            });
        });

        // Course registration credit calculation
        const courseCheckboxes = document.querySelectorAll('.course-checkbox input');
        const totalCreditsDisplay = document.getElementById('totalCredits');
        const creditProgress = document.getElementById('creditProgress');

        courseCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateCredits);
        });

        function updateCredits() {
            let totalCredits = 0;
            courseCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    totalCredits += 3; // Assuming each course is 3 credits
                }
            });

            totalCreditsDisplay.textContent = totalCredits;
            const percentage = Math.min((totalCredits / 18) * 100, 100);
            creditProgress.style.width = percentage + '%';

            if (totalCredits > 18) {
                creditProgress.style.backgroundColor = 'var(--danger-color)';
                totalCreditsDisplay.style.color = 'var(--danger-color)';
            } else {
                creditProgress.style.backgroundColor = 'var(--accent-color)';
                totalCreditsDisplay.style.color = 'var(--primary-color)';
            }
        }

        // Form submissions
        document.getElementById('saveStudentBtn').addEventListener('click', function() {
            const form = document.getElementById('addStudentForm');
            if (form.checkValidity()) {
                // Simulate form submission
                alert('Student added successfully!');
                modals.addStudentModal.classList.remove('active');
                // In a real application, you would reset the form and update the UI
            } else {
                alert('Please fill all required fields.');
            }
        });

        document.getElementById('importBtn').addEventListener('click', function() {
            const activeOption = document.querySelector('.import-option.active').getAttribute('data-option');

            if (activeOption === 'file') {
                if (fileInput.files.length > 0) {
                    // Simulate file upload
                    alert('Students imported successfully!');
                    modals.importStudentsModal.classList.remove('active');
                    fileInput.value = '';
                    fileNameDisplay.textContent = '';
                } else {
                    alert('Please select a file to upload.');
                }
            } else {
                const endpoint = document.getElementById('apiEndpoint').value;
                const apiKey = document.getElementById('apiKey').value;

                if (endpoint && apiKey) {
                    // Simulate API import
                    alert('Students imported successfully from API!');
                    modals.importStudentsModal.classList.remove('active');
                } else {
                    alert('Please provide API endpoint and key.');
                }
            }
        });

        document.getElementById('registerCoursesSubmitBtn').addEventListener('click', function() {
            const student = document.getElementById('registrationStudent').value;
            const totalCredits = parseInt(document.getElementById('totalCredits').textContent);

            if (student && totalCredits > 0) {
                if (totalCredits > 18) {
                    alert('Warning: Credit limit exceeded. Maximum allowed is 18 credits.');
                } else {
                    // Simulate course registration
                    alert('Courses registered successfully!');
                    modals.courseRegistrationModal.classList.remove('active');
                }
            } else {
                alert('Please select a student and at least one course.');
            }
        });

        // Edit and delete student buttons
        document.querySelectorAll('.edit-student').forEach(button => {
            button.addEventListener('click', function() {
                const studentCard = this.closest('.student-card');
                const studentName = studentCard.querySelector('.student-name').textContent;
                alert(`Edit student: ${studentName}`);
                // In a real application, you would populate the edit form with student data
                openModal('addStudentModal');
            });
        });

        document.querySelectorAll('.delete-student').forEach(button => {
            button.addEventListener('click', function() {
                const studentCard = this.closest('.student-card');
                const studentName = studentCard.querySelector('.student-name').textContent;
                if (confirm(`Are you sure you want to delete ${studentName}?`)) {
                    // Simulate deletion
                    studentCard.remove();
                    alert('Student deleted successfully!');
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
                // In a real application, you would load the corresponding page of students
            });
        });

        // Export Data
        document.getElementById('exportDataBtn').addEventListener('click', function() {
            // Simulate export
            alert('Student data exported successfully!');
        });
    </script>
</body>

</html>