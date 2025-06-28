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
use Src\Core\Program;

require_once('../inc/admin-database-con.php');

$secretary          = new SecretaryController($db, $user, $pass);
$course_category    = new CourseCategory($db, $user, $pass);
$course             = new Course($db, $user, $pass);
$base               = new Base($db, $user, $pass);
$program            = new Program($db, $user, $pass);

$pageTitle = "Classes";
$activePage = "classes";

$departmentId = $_SESSION["staff"]["department_id"] ?? null;
$semesterId = 2; //$_SESSION["semester"] ?? null;
$archived = false;

$activeSemesters = $secretary->fetchActiveSemesters();
$lecturers = $secretary->fetchAllLecturers($departmentId, $archived);

$activeClass = $secretary->fetchAllActiveClasses($departmentId, $archived);
$totalActiveClass = count($activeClass);
// /dd($activeClass);

$activeStudents = $secretary->fetchAllActiveStudents(departmentId: $departmentId);
$totalActiveStudents = $activeStudents && is_array($activeStudents) ? count($activeStudents) : 0;

$activeClasses = $secretary->fetchAllActiveClasses(departmentId: $departmentId);
$totalActiveClasses = $activeClasses && is_array($activeClasses) ? count($activeClasses) : 0;
$current_year = date("Y");
$years = range($current_year, ($current_year + 5));

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Staff Portal - Class</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="./css/courses.css">
    <link rel="stylesheet" href="./css/course-selection-modal.css">
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
                    <button class="action-btn" id="addClassBtn">
                        <i class="fas fa-plus"></i>
                        Add New Class
                    </button>
                    <!-- <button class="action-btn" id="assignClassBtn">
                        <i class="fas fa-user-plus"></i>
                        Assign Class
                    </button> -->
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
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
                        <option value="assigned">Assigned</option>
                        <option value="unassigned">Unassigned</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="filter-btn apply">Apply Filters</button>
                    <button class="filter-btn reset">Reset</button>
                </div>
            </div>

            <!-- Class Grid -->
            <div class="course-grid">
                <?php
                if ($totalActiveClass == 0) {
                    echo "<div class='no-classes'>No classes available.</div>";
                } else {
                    foreach ($activeClass as $class) {
                ?>
                        <div class="course-card">
                            <div class="course-header">
                                <div>
                                    <div class="course-title"><?= $class["code"] ?></div>
                                    <div class="course-code"><?= $class["program_name"] ?></div>
                                </div>
                                <div class="course-actions">
                                    <button class="action-icon edit-course" title="Edit Class" id="<?= $class["code"] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-icon archive-course" title="Archive Class" id="<?= $class["code"] ?>">
                                        <i class="fas fa-archive" style="color: var(--danger-color);"></i>
                                    </button>
                                </div>
                            </div>
                            <button class="filter-btn assign" title="Assign a lecturer or a student to this class" id="<?= $class["code"] ?>">
                                <i class="fas fa-user-plus"></i>
                                Assign
                            </button>
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

    <!-- Add Class Modal -->
    <div class="modal" id="addClassModal">
        <div class="modal-dialog modal-md modal-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Add New Class</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="addClassForm">
                        <div class="form-group">
                            <label for="classProgram">Program</label>
                            <select id="classProgram" name="classProgram" required>
                                <option value="">-- Select Program --</option>
                                <?php
                                $programs = $program->fetch("department", $departmentId);
                                foreach ($programs as $pg) {
                                ?>
                                    <option value="<?= $pg["id"] ?>"><?= $pg["name"] ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div style="display: flex; justify-content: space-between; gap: 10px;">
                            <div class="form-group">
                                <label for="classYear">Year of Graduation</label>
                                <select id="classYear" required>
                                    <option value="">-- Select Year --</option>
                                    <?php
                                    foreach ($years as $year) {
                                        echo "<option value='$year'>$year</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="classType">Type</label>
                                <select id="classType" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="regular">Regular</option>
                                    <option value="weekend">Weekend</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="classCode">Class Code</label>
                            <input type="text" id="classCode" placeholder="eg. BCS28" required readonly>
                        </div>
                        <input type="hidden" name="department" id="classDepartment" value="<?= $departmentId ?>">
                        <input type="hidden" name="action" id="classAction" value="add">
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" data-dismiss="modal">Cancel</button>
                    <button class="submit-btn" id="saveClassBtn">Save Class</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Class Modal -->
    <div class="modal" id="assignClassModal">
        <div class="modal-dialog modal-lg modal-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Assign Class</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="assign-class-tabs">
                        <button class="tab-btn active" data-tab="classToLecturer">To Lecturer</button>
                        <button class="tab-btn" data-tab="classToStudent">To Student</button>
                    </div>
                    <div class="form-group">
                        <div class="course-selection-header">
                            <label>Selected Class</label>
                        </div>
                        <div class="department-selected-classes-container">
                            <input type="text" id="singleClassCode" readonly placeholder="Class Code" required>
                        </div>
                    </div>
                    <div class="tab-content active" id="classToLecturer">
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
                    <div class="tab-content" id="classToStudent">
                        <div class="form-group">
                            <div class="course-selection-header">
                                <label>Selected Student</label>
                                <button type="button" id="selectStudentBtn">
                                    <i class="fas fa-search"></i> Find Student
                                </button>
                            </div>
                            <div class="department-selected-classes-container">
                                <div id="selectedStudentList">
                                    <!-- Selected classes will be added here dynamically -->
                                </div>
                                <div class="department-selected-classes-empty" id="departmentNoStudentMessage">
                                    No classes selected. Click "Find Student" to add classes.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="assignmentNotes">Notes (Optional)</label>
                        <textarea id="assignmentNotes" rows="3" placeholder="Add any additional notes about this assignment"></textarea>
                    </div>
                    <input type="hidden" id="departmentSelect" name="department" value="<?= $departmentId ?>">
                    <input type="hidden" id="assignClassActionSelect" name="action" value="toLecturer">
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" data-dismiss="modal">Cancel</button>
                    <button class="submit-btn" id="saveAssignmentsBtn">Save Assignments</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Selection Modal -->
    <div class="modal" id="classSelectionModal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Select Student</h2>
                    <button class="close-btn" id="closeStudentSelectionModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="class-search">
                        <input type="text" id="studentSearchInput" placeholder="Search by class code or name">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="class-list" id="classList">
                        <!-- Student items will be added here dynamically -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" id="closeStudentSelectionModal">Cancel</button>
                    <button class="submit-btn" id="confirmStudentSelectionBtn">Confirm Selection</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery-3.6.0.min.js"></script>
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
                        // Get level value from the card
                        const cardSemester = card.dataset.level;
                        matchesSemester = cardSemester === levelValue;
                    }

                    // Check lecturer filter
                    if (lecturerValue !== 'all') {
                        const lecturerNumber = card.dataset.lecturerNumber || '';

                        if (lecturerValue === 'unassigned') {
                            // If filtering for unassigned classes
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
                        }
                    }

                    // Show or hide the card based on combined filter results
                    if (matchesSemester && matchesLevel && matchesLecturer && matchesStatus) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Display message if no classes match the filters
                updateNoStudentMessage();
            }

            /**
             * Reset all filters and show all classes
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

                // Hide "no classes" message if it exists
                const noStudentMessage = document.querySelector('.no-classes');
                if (noStudentMessage) {
                    noStudentMessage.style.display = 'none';
                }
            }

            /**
             * Show or hide "No classes available" message based on filter results
             */
            function updateNoStudentMessage() {
                // Check if any classes are visible
                const visibleStudent = Array.from(courseCards).filter(card =>
                    card.style.display !== 'none'
                );

                // Get or create the "no classes" message element
                let noStudentMessage = document.querySelector('.no-classes');
                if (!noStudentMessage) {
                    noStudentMessage = document.createElement('div');
                    noStudentMessage.className = 'no-classes';
                    noStudentMessage.textContent = 'No classes match the selected filters.';
                    document.querySelector('.course-grid').appendChild(noStudentMessage);
                }

                // Show or hide the message
                if (visibleStudent.length === 0) {
                    noStudentMessage.style.display = 'block';
                } else {
                    noStudentMessage.style.display = 'none';
                }
            }

            // Add data attributes to course cards for easier filtering
            // function initializeDataAttributes() {
            //     courseCards.forEach(card => {
            //         // Extract course data from the card elements
            //         const courseTitle = card.querySelector('.course-title').textContent;
            //         const courseCode = card.querySelector('.course-code').textContent;

            //         // Extract semester information
            //         const semesterText = card.querySelector('.course-details .detail-item:nth-child(4) .detail-value').textContent;
            //         const semesterValue = semesterText.includes('First') ? '1' :
            //             semesterText.includes('Second') ? '2' : '';
            //         card.dataset.semester = semesterValue;

            //         // Extract level information
            //         const levelElement = card.querySelector('.course-details .detail-item:nth-child(3) .detail-value');
            //         const levelValue = levelElement ? levelElement.textContent : '';
            //         card.dataset.level = levelValue;

            //         // Extract lecturer information
            //         const lecturerInfo = card.querySelector('.lecturer-info');
            //         const lecturerNumberInfo = lecturerInfo.querySelector('.lecturer-number');

            //         if (lecturerNumberInfo) {
            //             const lecturerNumber = lecturerNumberInfo.value.trim();
            //             card.dataset.lecturerNumber = lecturerNumber;
            //         } else {
            //             card.dataset.lecturerNumber = 'null';
            //         }
            //     });
            // }

            // Initialize data attributes
            //initializeDataAttributes();
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

            let semesterClass = assignedStudent = null;
            let activeClass = <?= json_encode($activeClass) ?>;
            const user = <?= json_encode($staffData); ?>;
            const departmentId = user ? user.department_id : null;
            const userId = user ? user.number : null;


            // Open modals
            document.getElementById('addClassBtn').addEventListener('click', () => openModal('addClassModal'));
            // document.getElementById('assignClassBtn').addEventListener('click', () => openModal('assignClassModal'));

            // Set the class code in the
            //document.getElementById('departmentSelectClassBtn').addEventListener('click', () => openModal('departmentClassSelectionModal'));
            document.getElementById('selectStudentBtn').addEventListener('click', () => openModal('courseSelectionModal'));

            $(document).on("click", ".assign", function() {
                $("#singleClassCode").val($(this).attr("id"));
                openModal('assignClassModal')
            });

            // File input handling
            // const fileInput = document.getElementById('courseFileInput');
            // const fileNameDisplay = document.getElementById('selectedFileName');

            // fileInput.addEventListener('change', function() {
            //     if (this.files.length > 0) {
            //         fileNameDisplay.textContent = this.files[0].name;
            //     } else {
            //         fileNameDisplay.textContent = 'No file selected';
            //     }
            // });

            // Add a single course Form submissions
            document.getElementById('saveClassBtn').addEventListener('click', function() {
                const form = document.getElementById('addClassForm');
                if (!form.checkValidity()) {
                    alert("Please fill in all required fields");
                    return;
                }

                // Single course form validation
                const programId = document.getElementById("programId");
                const graduationYear = document.getElementById("graduationYear");
                const classType = document.getElementById("classType");
                const classCode = document.getElementById("classCode");
                const department = document.getElementById("classDepartment");
                const classAction = document.getElementById("classAction");

                if (!programId.value || !graduationYear.value || !classType.value || !classCode.value || !department.value || !classAction.value) {
                    alert("Please fill in all required fields");
                    return;
                }

                let url = null;

                switch (classAction.value) {
                    case "add":
                        url = "../endpoint/add-class";
                        break;
                    case "edit":
                        url = "../endpoint/edit-class";
                        break;
                    default:
                        alert("Invalid action");
                        return;
                }

                // Simulate API call
                const formData = {
                    programId: programId.value,
                    graduationYear: graduationYear.value,
                    classType: classType.value,
                    classCode: classCode.value,
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
                            closeModal("addClassModal");
                        } else {
                            alert(result['message']);
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            // Tab functionality for Upload Class Modal
            const assignClassTabs = document.querySelectorAll(".assign-class-tabs .tab-btn");

            assignClassTabs.forEach((btn) => {
                btn.addEventListener("click", function() {
                    const tabId = this.getAttribute("data-tab");

                    // Remove active class from all tabs and contents
                    assignClassTabs.forEach((btn) => btn.classList.remove("active"));
                    document.querySelectorAll(".tab-content").forEach((content) => content.classList.remove("active"));

                    // Add active class to clicked tab and corresponding content
                    this.classList.add("active");
                    document.getElementById(tabId).classList.add("active");

                    // add to actionSelect
                    document.getElementById("assignClassActionSelect").value = tabId;
                    console.log(document.getElementById("assignClassActionSelect").value);
                });
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

                const selectedStudentElements = document.querySelectorAll('#selectedStudentList .selected-student');
                if (selectedStudentElements.length === 0) {
                    alert("Please select at least one course");
                    return;
                }

                const selectedStudents = [];
                selectedStudentElements.forEach((element) => {
                    selectedStudents.push(element.getAttribute("data-code"));
                });

                const form = document.getElementById("assignClassForm");
                const action = document.getElementById("assignClassActionSelect").value;

                // Simulate API call
                let formData = {
                    semester: semesterSelect.value,
                    classes: selectedStudents,
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
                        alert("Class(s) can only be asigned to lecturer(s), student(s) and class(es)!");
                        return;
                }

                console.log(formData);

                $.ajax({
                    type: "POST",
                    url: "../endpoint/assign-class",
                    data: formData,
                    success: function(result) {
                        console.log(result);
                        if (result.success) {
                            alert(result.message);
                            closeModal("assignClassModal");
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

            // Set Deadline Modal
            const submitSetDeadline = document.getElementById("submitSetDeadline");

            function searchClass() {
                const searchTerm = document.getElementById("studentSearchInput").value.toLowerCase();
                const courseList = document.getElementById("courseList");
                courseList.innerHTML = "";

                // Check if assignedStudent has data
                if (!assignedStudent || assignedStudent.length === 0) {
                    courseList.innerHTML = `
                        <div class="no-classes-message">
                            <i class="fas fa-info-circle"></i>
                            <p>No classes are available.</p>
                        </div>
                    `;
                    return;
                }

                assignedStudent.forEach((course) => {
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
                        addStudentToSelection(code, name);

                        // Update the button to show it's selected
                        this.classList.add("selected");
                        this.disabled = true;
                        this.querySelector("i").classList.remove("fa-plus");
                        this.querySelector("i").classList.add("fa-check");
                        this.closest(".course-item").classList.add("course-selected");
                    });
                });
            }

            function addStudentToSelection(code, name) {
                const selectedStudentsList = document.getElementById("selectedStudentsList");

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
                    <input type="hidden" name="selectedStudents[]" value="${code}">
                `;
                selectedStudentsList.appendChild(courseItem);

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
                            addStudentToSelection(code, name)

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

            // Student Selection Modal
            const confirmStudentSelectionBtn = document.getElementById("confirmStudentSelectionBtn");
            const studentSearchInput = document.getElementById("studentSearchInput");

            confirmStudentSelectionBtn.addEventListener("click", () => {
                closeModal("courseSelectionModal");
            });

            studentSearchInput.addEventListener("input", () => {
                searchStudent();
            });

            // Initialize course list on modal open
            studentSearchInput.addEventListener("focus", () => {
                if (studentSearchInput.value === "") {
                    searchStudent();
                }
            });

            // Initialize course list when modal opens
            selectStudentBtn.addEventListener("click", () => {
                if (assignedStudent == null) {
                    fetch(`../endpoint/fetch-assigned-students`, {
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
                                assignedStudent = data.data;
                                console.log("Assigned Class", assignedStudent);
                                setTimeout(() => {
                                    searchStudent();
                                }, 100);
                            } else alert("Failed to fetch students for selected semester: ", data.message);
                        })
                        .catch(error => console.error("Error fetching students for selected semester:", error));
                }
            });

            // Edit and archive course buttons
            document.querySelectorAll('.edit-course').forEach(button => {
                button.addEventListener('click', function() {
                    const courseCard = this.closest('.course-card');
                    const courseTitle = courseCard.querySelector('.course-title').textContent;
                    // In a real application, you would populate the edit form with course data
                    const courseCode = this.id;
                    const course = activeClass.find(course => course.code === courseCode);
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
                        openModal('addClassModal');
                    } else {
                        alert("Class not found");
                    }
                    openModal('addClassModal');
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
                    // In a real application, you would load the corresponding page of classes
                });
            });
        });
    </script>
</body>

</html>