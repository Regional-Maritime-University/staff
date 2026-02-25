<?php
require_once __DIR__ . '/../inc/auth-guard.php';

require_once('../bootstrap.php');

use Src\Controller\SecretaryController;
use Src\Core\Base;
use Src\Core\Course;
use Src\Core\CourseCategory;
use Src\Core\Student;

require_once('../inc/admin-database-con.php');

$secretary          = new SecretaryController($db, $user, $pass);
$course_category    = new CourseCategory($db, $user, $pass);
$course             = new Course($db, $user, $pass);
$base               = new Base($db, $user, $pass);
$student            = new Student($db, $user, $pass);

$pageTitle = "Students";
$activePage = "students";

$departmentId = $_SESSION["staff"]["department_id"] ?? null;
$activeSemesters = $secretary->fetchActiveSemesters();
$currentSemester = $activeSemesters ? $activeSemesters[0] : null;
$semesterId = $currentSemester ? $currentSemester['id'] : null;
$archived = false;

$lecturers = $secretary->fetchAllLecturers($departmentId, $archived);

$activePrograms = $secretary->fetchAllActivePrograms(departmentId: $departmentId);
$totalActivePrograms = is_array($activePrograms) && $activePrograms["success"]  ? count($activePrograms) : 0;

$activeStudents = $secretary->fetchAllActiveStudents(departmentId: $departmentId);
$totalActiveStudents = $activeStudents && is_array($activeStudents) ? count($activeStudents) : 0;
$activeStudentsExamAndAssessment = $secretary->fetchAllActiveStudentsExamAndAssessment(students: $activeStudents, semesterId: $semesterId);
$activeStudents = $activeStudentsExamAndAssessment && is_array($activeStudentsExamAndAssessment) ? $activeStudentsExamAndAssessment : $activeStudents;

$totalFinalYear = 0;
$totalDeferred = 0;
$totalInactive = 0;
if ($activeStudents && is_array($activeStudents)) {
    foreach ($activeStudents as $s) {
        if (isset($s['level']) && $s['level'] == '400') $totalFinalYear++;
        if (isset($s['status']) && strtolower($s['status']) == 'deferred') $totalDeferred++;
        if (isset($s['status']) && strtolower($s['status']) == 'inactive') $totalInactive++;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Staff Portal - Students</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="./css/students.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/toast.css">
</head>

<body>
    <!-- Sidebar -->
    <?php require_once '../components/sidebar.php'; ?>

    <div class="main-content">
        <?php require_once '../components/header.php'; ?>

        <div class="dashboard-content">
            <!-- Student Stats -->
            <div class="student-stats">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-value"><?= $totalActiveStudents ?></div>
                    <div class="stat-label">Active</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-value"><?= $totalFinalYear ?></div>
                    <div class="stat-label">Final Year</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="stat-value"><?= $totalDeferred ?></div>
                    <div class="stat-label">Deferred</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <div class="stat-value"><?= $totalInactive ?></div>
                    <div class="stat-label">Inactive</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <!-- <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <button class="action-btn" id="registerCoursesBtn">
                        <i class="fas fa-clipboard-list"></i>
                        Course Registration
                    </button>
                    <button class="action-btn" id="exportDataBtn">
                        <i class="fas fa-file-export"></i>
                        Export Student Data
                    </button>
                </div>
            </div> -->

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filter-group">
                    <label for="program">Program</label>
                    <select id="program">
                        <option value="all">All Programs</option>
                        <?php
                        if ($activePrograms && is_array($activePrograms)) {
                            foreach ($activePrograms as $program) {
                                echo "<option value='" . htmlspecialchars($program['id']) . "'>" . htmlspecialchars($program['name']) . "</option>";
                            }
                        } else {
                            echo "<option value=''>No active programs available</option>";
                        }
                        ?>
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
                    <label for="status">Status</label>
                    <select id="status">
                        <option value="all">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="deferred">Deferred</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="filter-btn apply">Apply Filters</button>
                    <button class="filter-btn reset">Reset</button>
                </div>
            </div>

            <!-- Student Grid -->
            <div class="student-grid">
                <?php
                if ($totalActiveStudents == 0) {
                    echo "<div class='no-students'>No students available.</div>";
                } else {
                    foreach ($activeStudents as $student) {
                ?>
                        <div class="student-card" data-program="<?= htmlspecialchars($student["program_id"] ?? '') ?>" data-level="<?= htmlspecialchars($student["level"] ?? '') ?>" data-status="<?= htmlspecialchars(strtolower($student["status"] ?? 'active')) ?>">
                            <div class="student-header">
                                <div class="student-photo">
                                    <img src="../uploads/profiles/me.jpg" alt="Student Photo">
                                </div>
                                <div class="student-info">
                                    <h3 class="student-name"><?= htmlspecialchars($student["first_name"] . " " . $student["middle_name"] . " " . $student["last_name"]) ?></h3>
                                    <p class="student-id"><?= htmlspecialchars($student["index_number"]) ?></p>
                                    <span class="student-program"><?= htmlspecialchars($student["program_name"]) ?></span>
                                </div>
                                <span class="student-status active">Active</span>
                                <div class="student-actions">
                                    <!-- <button class="student-action edit-student" title="Edit Student">
                                        <i class="fas fa-edit"></i>
                                    </button> -->
                                    <button class="student-action archive-student" title="Archive Student" id="<?= htmlspecialchars($student["index_number"]) ?>">
                                        <i class="fas fa-archive" style="color: var(--danger-color);"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="student-content">
                                <div class="contact-info">
                                    <div class="contact-item">
                                        <i class="fas fa-envelope"></i>
                                        <a href="mailto:<?= htmlspecialchars($student["email"]) ?>"><?= htmlspecialchars($student["email"]) ?></a>
                                    </div>
                                    <div class="contact-item">
                                        <i class="fas fa-phone"></i>
                                        <a href="tel:<?= htmlspecialchars($student["phone_number"]) ?>"><?= htmlspecialchars($student["phone_number"]) ?></a>
                                    </div>
                                </div>
                                <div class="academic-info">
                                    <div class="academic-item">
                                        <div class="academic-label">Level</div>
                                        <div class="academic-value"><?= htmlspecialchars($student["level"] ?? '') ?></div>
                                    </div>
                                    <div class="academic-item">
                                        <div class="academic-label">Credits</div>
                                        <div class="academic-value"><?= htmlspecialchars($student["total_credit_hours"]) ?></div>
                                    </div>
                                    <div class="academic-item">
                                        <div class="academic-label">Courses</div>
                                        <div class="academic-value"><?= htmlspecialchars($student["total_courses"]) ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="student-footer">
                                <div class="gpa excellent">CGPA: <?= htmlspecialchars($student["cgpa"]) ?></div>
                                <button class="view-profile-btn view-grades-btn" data-student="<?= htmlspecialchars($student["first_name"] . " " . $student["last_name"]) ?>" id="<?= htmlspecialchars($student["index_number"]) ?>">View Grades</button>
                            </div>
                        </div>
                <?php
                    }
                }
                ?>
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

    <!-- Course Registration Modal -->
    <!-- <div class="modal" id="courseRegistrationModal">
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
    </div> -->

    <!-- View Grades Modal -->
    <div class="modal" id="viewGradesModal">
        <div class="modal-dialog modal-lg modal-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Grades for <span id="studentNameGrades">Student</span></h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">

                    <div class="form-group">
                        <label for="gradeSemester">Select Semester</label>
                        <select id="gradeSemester" required>
                            <option value="">-- Select Semester --</option>
                            <?php
                            if ($activeSemesters) {
                                foreach ($activeSemesters as $semester) {
                                    echo "<option value='" . htmlspecialchars($semester['id']) . "'>" . htmlspecialchars($semester['academic_year_name']) . " Semester " . htmlspecialchars($semester['name']) . " </option>";
                                }
                            } else {
                                echo "<option value=''>No active semester</option>";
                            }
                            ?>
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
                            <tr style="color: var(--accent-color);">
                                <td colspan="5">Choose a semester to see student's grades</td>
                            </tr>
                        </tbody>
                        <tfoot></tfoot>
                    </table>

                    <input type="hidden" name="viewGradesStudent" id="viewGradesStudent" value="">

                    <div style="display: flex; margin-top: 20px; text-align: right;">
                        <button class="action-btn" id="exportGradesToPDF" style="width: auto; height: auto; padding: 8px 15px; background-color: var(--primary-color); margin-right: 10px;">
                            <i class="fas fa-file-pdf"></i> Export as PDF
                        </button>
                        <button class="action-btn" id="exportGradesToExcel" style="width: auto; height: auto; padding: 8px 15px; background-color: var(--success-color);">
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

    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {


            const applyButton = document.querySelector('.filter-btn.apply');
            const resetButton = document.querySelector('.filter-btn.reset');
            const studentCards = document.querySelectorAll('.student-card');

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

                // Loop through each student cards and check if it matches the filters
                studentCards.forEach(card => {
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
                        // Get level value from the card
                        const cardSemester = card.dataset.level;
                        matchesSemester = cardSemester === levelValue;
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
                            case 'active':
                                matchesStatus = hasLecturer;
                                break;
                            case 'deferred':
                                matchesStatus = !hasLecturer;
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

                // Show all student cardss
                studentCards.forEach(card => {
                    card.style.display = '';
                });

                // Hide "no courses" message if it exists
                const noCoursesMessage = document.querySelector('.no-courses');
                if (noCoursesMessage) {
                    noCoursesMessage.style.display = 'none';
                }
            }

            // Add data attributes to student cards for easier filtering
            function initializeDataAttributes() {
                studentCards.forEach(card => {
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
                    const lecturerName = lecturerInfo.querySelector('.lecturer-name');

                    if (lecturerName) {
                        // Set a data attribute for the lecturer number if we can extract it
                        // This is a placeholder - you'll need to adapt based on your actual HTML structure
                        // In a real scenario, this would be added server-side directly to the HTML
                        const lecturerNameText = lecturerName.textContent;

                        // Since we don't have direct access to lecturer numbers in the HTML,
                        // we'll use a lookup based on the dataset you provided
                        const lecturerMapping = {
                            'Mr. Francis Anlimah': 'ICT0002'
                            // Add more mappings as needed
                        };

                        const lecturerNumber = lecturerMapping[lecturerNameText.trim()] || '';
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

            // Modal functionality
            const modals = {
                // courseRegistrationModal: document.getElementById('courseRegistrationModal'),
                viewGradesModal: document.getElementById('viewGradesModal')
            };

            // Open modals
            // document.getElementById('registerCoursesBtn').addEventListener('click', () => openModal('courseRegistrationModal'));

            // Close modals
            document.querySelectorAll('.close-btn, .cancel-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    if (modal) {
                        modal.classList.remove('active');
                        if (modal.id === "viewGradesModal") {
                            // Reset semester select to default option
                            const semesterSelect = modal.querySelector('#gradeSemester');
                            if (semesterSelect) semesterSelect.value = '';

                            // Reset grades table body to default message row
                            const tbody = modal.querySelector('.grades-table tbody');
                            if (tbody) {
                                tbody.innerHTML = `
                                    <tr style="color: var(--accent-color);">
                                        <td colspan="5">Choose a semester to see student's grades</td>
                                    </tr>
                                `;
                            }

                            const tfoot = modal.querySelector('.grades-table tfoot');
                            if (tfoot) {
                                tfoot.innerHTML = `<tfoot></tfoot>`;
                            }
                        }

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
            // const fileInput = document.getElementById('studentFileInput');
            // const fileNameDisplay = document.getElementById('selectedFileName');

            // fileInput.addEventListener('change', function() {
            //     if (this.files.length > 0) {
            //         fileNameDisplay.textContent = this.files[0].name;
            //     } else {
            //         fileNameDisplay.textContent = '';
            //     }
            // });

            // // Photo preview
            // const photoInput = document.getElementById('studentPhoto');
            // const photoPreview = document.getElementById('photoPreview');

            // photoInput.addEventListener('change', function() {
            //     if (this.files && this.files[0]) {
            //         const reader = new FileReader();
            //         reader.onload = function(e) {
            //             photoPreview.src = e.target.result;
            //         };
            //         reader.readAsDataURL(this.files[0]);
            //     }
            // });

            // // Import options toggle
            // document.querySelectorAll('.import-option').forEach(option => {
            //     option.addEventListener('click', function() {
            //         document.querySelectorAll('.import-option').forEach(opt => opt.classList.remove('active'));
            //         this.classList.add('active');

            //         const importType = this.getAttribute('data-option');
            //         if (importType === 'file') {
            //             document.getElementById('fileImportSection').style.display = 'block';
            //             document.getElementById('apiImportSection').style.display = 'none';
            //         } else {
            //             document.getElementById('fileImportSection').style.display = 'none';
            //             document.getElementById('apiImportSection').style.display = 'block';
            //         }
            //     });
            // });

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
            // document.getElementById('saveStudentBtn').addEventListener('click', function() {
            //     const form = document.getElementById('addStudentForm');
            //     if (form.checkValidity()) {
            //         // Simulate form submission
            //         alert('Student added successfully!');
            //         modals.addStudentModal.classList.remove('active');
            //         // In a real application, you would reset the form and update the UI
            //     } else {
            //         alert('Please fill all required fields.');
            //     }
            // });

            // document.getElementById('importBtn').addEventListener('click', function() {
            //     const activeOption = document.querySelector('.import-option.active').getAttribute('data-option');

            //     if (activeOption === 'file') {
            //         if (fileInput.files.length > 0) {
            //             // Simulate file upload
            //             alert('Students imported successfully!');
            //             modals.importStudentsModal.classList.remove('active');
            //             fileInput.value = '';
            //             fileNameDisplay.textContent = '';
            //         } else {
            //             alert('Please select a file to upload.');
            //         }
            //     } else {
            //         const endpoint = document.getElementById('apiEndpoint').value;
            //         const apiKey = document.getElementById('apiKey').value;

            //         if (endpoint && apiKey) {
            //             // Simulate API import
            //             alert('Students imported successfully from API!');
            //             modals.importStudentsModal.classList.remove('active');
            //         } else {
            //             alert('Please provide API endpoint and key.');
            //         }
            //     }
            // });

            // document.getElementById('registerCoursesSubmitBtn').addEventListener('click', function() {
            //     const student = document.getElementById('registrationStudent').value;
            //     const totalCredits = parseInt(document.getElementById('totalCredits').textContent);

            //     if (student && totalCredits > 0) {
            //         if (totalCredits > 18) {
            //             alert('Warning: Credit limit exceeded. Maximum allowed is 18 credits.');
            //         } else {
            //             // Simulate course registration
            //             alert('Courses registered successfully!');
            //             modals.courseRegistrationModal.classList.remove('active');
            //         }
            //     } else {
            //         alert('Please select a student and at least one course.');
            //     }
            // });

            // Edit and archive student buttons
            document.querySelectorAll('.edit-student').forEach(button => {
                button.addEventListener('click', function() {
                    const studentCard = this.closest('.student-card');
                    const studentName = studentCard.querySelector('.student-name').textContent;
                    alert(`Edit student: ${studentName}`);
                    // In a real application, you would populate the edit form with student data
                    openModal('addStudentModal');
                });
            });

            document.querySelectorAll('.archive-student').forEach(button => {
                button.addEventListener('click', function() {
                    const studentCard = this.closest('.student-card');
                    const studentName = studentCard.querySelector('.student-name').textContent;
                    if (confirm(`Are you sure you want to archive ${studentName}?`)) {

                        const indexNumber = this.id;

                        if (!indexNumber) {
                            alert("There was an error archiving the student. Please try again.");
                            return;
                        }

                        // Simulate API call
                        const formData = {
                            indexNumber: indexNumber
                        };

                        console.log(formData);

                        $.ajax({
                            type: "POST",
                            url: "../endpoint/archive-student",
                            data: formData,
                            success: function(result) {
                                console.log(result);
                                if (result.success) {
                                    alert(result.message);
                                    studentCard.remove();
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

            // View grades buttons
            document.querySelectorAll('.view-grades-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const studentName = this.getAttribute('data-student');
                    const indexNumber = this.id;
                    document.getElementById('studentNameGrades').textContent = `${studentName} (${indexNumber})`;
                    document.getElementById('viewGradesStudent').value = indexNumber;
                    openModal('viewGradesModal');
                });
            });

            document.getElementById('gradeSemester').addEventListener('change', function() {
                const selectedSemester = this.value;
                if (!selectedSemester) {
                    const gradesTableBody = document.querySelector('.grades-table tbody');
                    const gradesTableFoot = document.querySelector('.grades-table tfoot');
                    gradesTableBody.innerHTML = '<tr style="color: var(--accent-color)"><td colspan="5">Choose a semester to see student\'s grades</td></tr>';
                    gradesTableFoot.innerHTML = '';
                    return;
                }

                const indexNumber = document.getElementById('viewGradesStudent').value;
                if (!indexNumber) {
                    alert("There was an error retrieving the student's grades. Please try again.");
                    return;
                }
                // Simulate API call
                const formData = {
                    indexNumber: indexNumber,
                    semester: selectedSemester
                };

                $.ajax({
                    type: "POST",
                    url: "../endpoint/fetch-student-grades",
                    data: formData,
                    success: function(result) {
                        console.log(result);
                        if (result.success) {
                            console.log("result", result.data);
                            // Populate the grades table with the result data
                            // For example, you can use result.data to fill the table
                            const gradesTableBody = document.querySelector('.grades-table tbody');
                            const gradesTableFoot = document.querySelector('.grades-table tfoot');
                            let totalCredits = 0;
                            let totalGpa = 0;
                            gradesTableBody.innerHTML = ''; // Clear existing rows
                            result.data.forEach(grade => {
                                const row = document.createElement('tr');
                                gradeColor = '';
                                switch (grade.grade) {
                                    case 'A':
                                        gradeColor = 'A'.toLowerCase();
                                        break;
                                    case 'A-':
                                        gradeColor = 'A-minus'.toLowerCase();
                                        break;
                                    case 'B+':
                                        gradeColor = 'B-plus'.toLowerCase();
                                        break;
                                    case 'B':
                                        gradeColor = 'B'.toLowerCase();
                                        break;
                                    case 'C+':
                                        gradeColor = 'C-plus'.toLowerCase();
                                        break;
                                    case 'C':
                                        gradeColor = 'C'.toLowerCase();
                                        break;
                                    case 'D':
                                        gradeColor = 'D'.toLowerCase();
                                        break;
                                    case 'E':
                                        gradeColor = 'E'.toLowerCase();
                                        break;
                                    case 'F':
                                        gradeColor = 'F'.toLowerCase();
                                        break;
                                    default:
                                        gradeColor = 'N/A';
                                }
                                row.innerHTML = `
                                    <td>${grade.course_code}</td>
                                    <td>${grade.course_name}</td>
                                    <td>${grade.course_credit_hours}</td>
                                    <td><span class="grade-value ${gradeColor}">${grade.grade}</span></td>
                                    <td>${grade.gpa}</td>
                                `;
                                gradesTableBody.appendChild(row);
                                totalCredits += parseInt(grade.course_credit_hours);
                                totalGpa += parseFloat(grade.gpa);
                            });
                            const averageGpa = (totalGpa / result.data.length).toFixed(2);
                            const footerRow = document.createElement('tr');
                            gradesTableFoot.innerHTML = ''; // Clear existing rows
                            footerRow.innerHTML = `
                                <td colspan="2"><strong>Semester Total</strong></td>
                                <td><strong>${totalCredits}</strong></td>
                                <td></td>
                                <td><strong>${averageGpa}</strong></td>
                            `;
                            gradesTableFoot.appendChild(footerRow);
                        } else {
                            alert(result.message);
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            // Filter functionality
            document.querySelector('.filter-btn.apply').addEventListener('click', function() {
                // Simulate filtering
            });

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
                    // In a real application, you would load the corresponding page of students
                });
            });

            // Export Data
            // document.getElementById('exportDataBtn').addEventListener('click', function() {
            //     // Simulate export
            //     alert('Student data exported successfully!');
            // });
        });
    </script>
</body>

</html>