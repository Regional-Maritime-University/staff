<?php
session_name("rmu_staff_portal");
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

$pageTitle = "Programs";
$activePage = "programs";

$departmentId = $_SESSION["staff"]["department_id"] ?? null;
$facultyId = $_SESSION["staff"]["faculty_id"] ?? null;
$semesterId = 2; //$_SESSION["semester"] ?? null;
$archived = false;

$activePrograms = $secretary->fetchAllActivePrograms(departmentId: $departmentId);
// dd($activePrograms);
$totalActivePrograms = $activePrograms && is_array($activePrograms) ? count($activePrograms) : 0;

$activeCummulativePrograms = $secretary->fetchAllCummulativeProgramsDetails(departmentId: $departmentId);
// /dd($activeCummulativePrograms);

$activeStudents = $secretary->fetchAllActiveStudents(departmentId: $departmentId);
$totalActiveStudents = $activeStudents && is_array($activeStudents) ? count($activeStudents) : 0;

$activeCoursesData = $course->fetch(key: "department", value: $departmentId, archived: $archived);
$totalActiveCourses = $activeCoursesData && is_array($activeCoursesData) ? count($activeCoursesData) : 0;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Staff Portal - Programs</title>
    <link rel="stylesheet" href="./css/program.css">
    <link rel="stylesheet" href="./css/course-selection-modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Sidebar -->
    <?php require_once '../components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">

        <?php require_once '../components/header.php'; ?>

        <div class="programs-content">
            <!-- Programs Filters -->
            <div class="programs-filters">
                <div class="filter-group">
                    <select class="filter-select" id="levelFilter">
                        <option value="all">All Levels</option>
                        <option value="degree">Degree</option>
                        <option value="masters">Masters</option>
                        <option value="diploma">Diploma</option>
                    </select>
                    <select class="filter-select" id="statusFilter">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="filter-btn secondary" id="resetFiltersBtn">
                        <i class="fas fa-sync-alt"></i> Reset Filters
                    </button>
                    <button class="filter-btn primary" id="addProgramBtn">
                        <i class="fas fa-plus"></i> Add Program
                    </button>
                    <button class="filter-btn danger" id="archivedProgramBtn">
                        <i class="fas fa-list"></i> Archived Programs
                    </button>
                </div>
            </div>

            <!-- Programs Grid -->
            <div class="programs-grid" id="programsGrid">
                <!-- Program cards will be dynamically generated here -->
            </div>
        </div>
    </div>

    <!-- Curriculum Modal -->
    <div class="modal" id="curriculumModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><i class="fas fa-list-alt"></i> <span id="curriculumModalTitle">Program Curriculum</span></h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-filters">
                    <div class="modal-search">
                        <input type="text" placeholder="Search courses..." id="curriculumSearch">
                        <button class="modal-search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <select class="modal-filter-select" id="curriculumYearFilter">
                        <option value="all">All Levels</option>
                        <option value="100">Level 100</option>
                        <option value="200">Level 200</option>
                        <option value="300">Level 300</option>
                        <option value="400">Level 400</option>
                    </select>
                    <select class="modal-filter-select" id="curriculumSemesterFilter">
                        <option value="all">All Semesters</option>
                        <option value="1">Semester 1</option>
                        <option value="2">Semester 2</option>
                    </select>
                    <select class="modal-filter-select" id="curriculumTypeFilter">
                        <option value="all">All Types</option>
                        <option value="compulsory">Compulsory</option>
                        <option value="elective">Elective</option>
                        <option value="optional">Optional</option>
                    </select>
                </div>
                <div class="modal-body">
                    <div class="modal-list" id="curriculumList">
                        <!-- Curriculum items will be dynamically generated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Classes Modal -->
    <div class="modal" id="classesModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><i class="fas fa-users"></i> <span id="classesModalTitle">Program Classes</span></h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-filters">
                    <div class="modal-search">
                        <input type="text" placeholder="Search classes..." id="classesSearch">
                        <button class="modal-search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <select class="modal-filter-select" id="classesYearFilter">
                        <option value="all">All Years</option>
                        <option value="1">Year 1</option>
                        <option value="2">Year 2</option>
                        <option value="3">Year 3</option>
                        <option value="4">Year 4</option>
                    </select>
                    <select class="modal-filter-select" id="classesStatusFilter">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="modal-body">
                    <div class="modal-list" id="classesList">
                        <!-- Classes items will be dynamically generated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Modal -->
    <div class="modal" id="studentsModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><i class="fas fa-user-graduate"></i> <span id="studentsModalTitle">Program Students</span></h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-filters">
                    <div class="modal-search">
                        <input type="text" placeholder="Search students..." id="studentsSearch">
                        <button class="modal-search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <select class="modal-filter-select" id="studentsYearFilter">
                        <option value="all">All Years</option>
                        <option value="1">Year 1</option>
                        <option value="2">Year 2</option>
                        <option value="3">Year 3</option>
                        <option value="4">Year 4</option>
                    </select>
                    <select class="modal-filter-select" id="studentsStatusFilter">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="graduated">Graduated</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                <div class="modal-body">
                    <div class="modal-list" id="studentsList">
                        <!-- Students items will be dynamically generated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Programs Modal -->
    <div class="modal" id="addProgramModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><i class="fas fa-book"></i> <span id="addProgramModalTitle">Add Program</span></h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="saveProgramForm" method="POST">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem;">
                            <div class="form-group">
                                <label for="category">Category</label>
                                <select id="category" name="category" required>
                                    <option value="" hidden>Select</option>
                                    <option value="DEGREE">Degree</option>
                                    <option value="DIPLOMA">Diploma</option>
                                    <option value="MASTERS">Masters</option>
                                    <option value="SHORT">Vocational/Professional</option>
                                    <option value="UPGRADE">Upgrade</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="code">Code</label>
                                <select id="code" name="code" required>
                                    <option value="" hidden>Select</option>
                                    <option value="BSC">BSc</option>
                                    <option value="DIPLOMA">Diploma</option>
                                    <option value="MSC">MSc</option>
                                    <option value="MA">MA</option>
                                    <option value="SHORT">Short</option>
                                    <option value="UPGRADE">Upgrade</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="group">Group</label>
                                <select id="group" name="group" required>
                                    <option value="" hidden>Select</option>
                                    <option value="M">Masters based</option>
                                    <option value="A">Science based</option>
                                    <option value="B">None Science based</option>
                                    <option value="N">Nothing Applicable</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="index_code">Index Code</label>
                                <input type="text" id="index_code" name="index_code" minlength="3" maxlength="3" class="form-control" required>
                            </div>
                        </div>
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            <div class="form-group">
                                <label for="duration">Duration</label>
                                <input type="number" id="duration" name="duration" min="1" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="dur_format">Format</label>
                                <select id="dur_format" name="dur_format" required>
                                    <option value="" hidden>Select</option>
                                    <option value="semester">semester</option>
                                    <option value="year">year</option>
                                    <option value="month">month</option>
                                    <option value="week">week</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="num_of_semesters">No. of Semesters</label>
                                <input type="number" id="num_of_semesters" min="0" name="num_of_semesters" value="0" required>
                            </div>
                            <div class="form-group">
                                <label for="regulation">Regulation</label>
                                <input type="text" id="regulation" name="regulation" class="form-control">
                            </div>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" name="regular" type="checkbox" id="regular_available">
                            <label class="form-check-label" for="regular_available">
                                Is this program available for regular?
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" name="weekend" type="checkbox" id="weekend_available">
                            <label class="form-check-label" for="weekend_available">
                                Is this program available for weekend?
                            </label>
                        </div>
                        <input type="hidden" name="department" id="programDepartment" value="<?= $departmentId ?>">
                        <input type="hidden" name="faculty" id="programFaculty" value="<?= $facultyId ?>">
                        <input type="hidden" name="action" id="programAction" value="add">
                        <div class="modal-footer" style="width: 100%; display: flex; justify-content: flex-end; gap: 1rem;">
                            <button type="submit" class="filter-btn primary" id="saveProgramBtn">
                                <i class="fas fa-save"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add course to -->
    <div class="modal" id="assignCourseModal">
        <div class="modal-dialog modal-lg modal-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><i class="fas fa-user-graduate"></i> <span id="assignCourseModalTitle">Assign Course To Program</span></h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <div class="course-selection-header">
                            <label>Selected Courses</label>
                            <button type="button" id="departmentSelectCoursesBtn" data-program="">
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
                    <input type="hidden" id="programSelect" name="program">
                    <div class="modal-footer" style="display: flex; justify-content:right">
                        <button class="cancel-btn" data-dismiss="modal">Cancel</button>
                        <button class="submit-btn" id="saveAssignmentsBtn">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Course Selection Modal -->
    <div class="modal" id="departmentCourseSelectionModal">
        <div class="modal-dialog">
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
                    <input type="hidden" name="program" id="programId">
                    <div class="modal-footer">
                        <button class="cancel-btn" id="closeDepartmentCourseSelectionModal">Cancel</button>
                        <button class="submit-btn" id="confirmDepartmentCourseSelectionBtn">Confirm Selection</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <script>
        // Modal Functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.add("active");
            document.body.style.overflow = "hidden";
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove("active");
            document.body.style.overflow = "auto";
        }

        document.getElementById('addProgramBtn').addEventListener('click', () => openModal('addProgramModal'));

        function capitalizeWords(str) {
            return str.toLowerCase().split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        }

        // Sample data
        const staticProgramData = <?= json_encode($activeCummulativePrograms) ?>;
        const programsData = staticProgramData.map(program => ({
            id: program.id,
            title: capitalizeWords(program.name),
            code: program.code,
            index_code: program.index_code,
            code: program.category,
            level: program.type.toLowerCase(),
            group: program.group,
            regular_available: program.regular ? true : false,
            weekend_available: program.weekend ? true : false,
            department: program.department_id,
            department: program.department_name,
            description: program.description ?? '',
            duration: program.duration + " " + program.dur_format,
            credits: program.total_credits,
            students: program.total_students,
            courses: program.total_courses,
            classes: program.total_classes,
            status: program.status
        }));

        let curriculumData = {};

        let notCurriculumCourses = [];

        let curriculumCourses = null;

        let classesData = {};

        let studentsData = {};

        let coursesData = {};

        const user = <?= json_encode($staffData); ?>;
        const departmentId = user ? user.department_id : null;

        async function fetchCoursesNotInCurriculum(programId) {
            if (!programId) {
                alert("Program ID is required to fetch curriculum.");
                return;
            }

            try {
                const response = await fetch('../endpoint/fetch-courses-not-curriculum', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        'program': programId
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const data = await response.json();
                return data;

            } catch (error) {
                console.error("Failed to fetch courses:", error);
            }
        }

        async function fetchProgramCurriculum(programId) {
            if (!programId) {
                alert("Program ID is required to fetch curriculum.");
                return;
            }

            try {
                const response = await fetch('../endpoint/fetch-program-curriculum', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        'program': programId
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const data = await response.json();
                return data;

            } catch (error) {
                console.error("Failed to fetch courses:", error);
            }
        }

        async function fetchProgramClasses(programId) {
            if (!programId) {
                alert("Program ID is required to fetch classes.");
                return;
            }

            try {
                const response = await fetch('../endpoint/fetch-program-classes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        'program': programId
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const data = await response.json();
                return data;

            } catch (error) {
                console.error("Failed to fetch program classes:", error);
            }
        }

        async function fetchProgramStudents(programId) {
            if (!programId) {
                alert("Program ID is required to fetch students.");
                return;
            }

            try {
                const response = await fetch('../endpoint/fetch-program-students', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        'program': programId
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const data = await response.json();
                return data;

            } catch (error) {
                console.error("Failed to fetch program students:", error);
            }
        }

        async function fetchProgramCourses(programId) {
            if (!programId) {
                alert("Program ID is required to fetch courses.");
                return;
            }

            try {
                const response = await fetch('../endpoint/fetch-program-courses', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        'program': programId
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const data = await response.json();
                return data;

            } catch (error) {
                console.error("Failed to fetch program courses:", error);
            }
        }

        let filteredPrograms = [...programsData];

        // Initialize the page
        document.addEventListener('DOMContentLoaded', async function() {
            renderPrograms();
            initializeEventListeners();
        });

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
        departmentSelectCoursesBtn.addEventListener("click", async () => {
            const programId = document.getElementById('departmentSelectCoursesBtn').getAttribute("data-program");
            const result = await fetchCoursesNotInCurriculum(programId); // <-- await here

            console.log(result); // now you get { success: true, data: [...] }
            if (result && result.success) {
                console.log(result.data);
                notCurriculumCourses = result.data;

                setTimeout(() => {
                    departmentSearchCourses();
                }, 100);
            }
            openModal('departmentCourseSelectionModal');
        });

        function departmentSearchCourses() {
            const searchTerm = document.getElementById("departmentCourseSearchInput").value.toLowerCase();
            const courseList = document.getElementById("departmentCourseList");
            courseList.innerHTML = "";

            // Check if semester courses has data
            if (!notCurriculumCourses || notCurriculumCourses.length === 0) {
                courseList.innerHTML = `
                        <div class="department-no-courses-message">
                            <i class="fas fa-info-circle"></i>
                            <p>No courses are available.</p>
                        </div>
                    `;
                return;
            }

            notCurriculumCourses.forEach((course) => {
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

        /**
         * Reset the Assign Courses Modal to its initial state
         */
        function resetAssignCoursesModal() {
            // Reset selects and textarea
            document.getElementById("programSelect").value = "";

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
            const programSelect = document.getElementById("programSelect");

            if (!programSelect.value) {
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

            const action = "program";

            // Simulate API call
            let formData = {
                action: action,
                courses: selectedCourses,
                program: programSelect.value
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
                        resetAssignCoursesModal();
                        window.location.reload();
                    } else {
                        alert(result['message']);
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });
        });

        // Render programs
        function renderPrograms() {
            const programsGrid = document.getElementById('programsGrid');

            if (filteredPrograms.length === 0) {
                programsGrid.innerHTML = `
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <i class="fas fa-graduation-cap"></i>
                        <h3>No Programs Found</h3>
                        <p>No programs match your current filters. Try adjusting your search criteria.</p>
                    </div>
                `;
                return;
            }

            programsGrid.innerHTML = filteredPrograms.map(program => `
                <div class="program-card" data-program-id="${program.id}">
                    <div class="program-header">
                        <div>
                            <div class="program-title">${program.title}</div>
                            <div class="program-code">
                                <span>${program.code}</span>
                                <span class="btn program-edit-btn" title="Edit Program" style="margin-left: 0.5rem; margin-right: 0.5rem;">
                                    <i class="fas fa-edit" style="color: #007bff; cursor: pointer;" onclick="editProgram(${program.id})"></i>
                                </span>
                                <span class="program-archive-btn" title="Archive Program">
                                    <i class="fas fa-archive" style="color: #dc3545; cursor: pointer;" onclick="archiveProgram(${program.id})"></i>
                                </span>
                            </div>
                        </div>
                        <span class="program-level ${program.level}">${program.level}</span>
                    </div>
                    <div class="program-description">${program.description}</div>
                    <div class="program-stats">
                        <div class="stat-item">
                            <div class="stat-value">${program.students}</div>
                            <div class="stat-label">Students</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">${program.courses}</div>
                            <div class="stat-label">Courses</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">${program.classes}</div>
                            <div class="stat-label">Classes</div>
                        </div>
                    </div>
                    <div class="program-info">
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <span>${program.duration}</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-certificate"></i>
                            <span>${program.credits} Credits</span>
                        </div>
                        <!--<div class="info-item">
                            <i class="fas fa-building"></i>
                            <span>${program.department.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-circle ${program.status ? 'text-success' : 'text-danger'}"></i>
                            <span>${program.status.charAt(0).toUpperCase() + program.status.slice(1)}</span>
                        </div>-->
                    </div>
                    <div class="program-actions">
                        <button class="program-btn primary" onclick="openCurriculumModal(${program.id})">
                            <i class="fas fa-list-alt"></i> Curriculum
                        </button>
                        <button class="program-btn secondary" onclick="openClassesModal(${program.id})">
                            <i class="fas fa-users"></i> Classes
                        </button>
                        <button class="program-btn primary" onclick="openStudentsModal(${program.id})">
                            <i class="fas fa-user-graduate"></i> Students
                        </button>
                        <button class="program-btn secondary" onclick="openAssignCourseModal(${program.id})">
                            <i class="fas fa-plus"></i> Assign Courses
                        </button>
                    </div>
                </div>
            `).join('');
        }

        // Initialize event listeners
        function initializeEventListeners() {
            // Toggle sidebar
            document.querySelector('.toggle-sidebar').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('collapsed');
            });

            // Global search
            document.getElementById('globalSearch').addEventListener('input', function() {
                filterPrograms();
            });

            // Filter dropdowns
            document.getElementById('levelFilter').addEventListener('change', filterPrograms);
            document.getElementById('statusFilter').addEventListener('change', filterPrograms);

            // Reset filters
            document.getElementById('resetFiltersBtn').addEventListener('click', function() {
                document.getElementById('globalSearch').value = '';
                document.getElementById('levelFilter').value = 'all';
                document.getElementById('statusFilter').value = 'all';
                filterPrograms();
            });

            // Modal close buttons
            document.querySelectorAll('.close-btn, [data-dismiss="modal"]').forEach(button => {
                button.addEventListener('click', function(event) {
                    // If the close button is inside departmentCourseSelectionModal, only close that modal
                    if (this.closest('#departmentCourseSelectionModal')) {
                        closeModal('departmentCourseSelectionModal');
                    } else {
                        closeAllModals();
                    }
                });
            });

            // Modal search and filter event listeners
            setupModalFilters();
        }

        // Filter programs
        function filterPrograms() {
            const searchTerm = document.getElementById('globalSearch').value.toLowerCase();
            const levelFilter = document.getElementById('levelFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;

            filteredPrograms = programsData.filter(program => {
                const matchesSearch = program.title.toLowerCase().includes(searchTerm) ||
                    program.code.toLowerCase().includes(searchTerm) ||
                    program.description.toLowerCase().includes(searchTerm);

                const matchesLevel = levelFilter === 'all' || program.level === levelFilter;
                const matchesStatus = statusFilter === 'all' || program.status === statusFilter;

                return matchesSearch && matchesLevel && matchesStatus;
            });

            renderPrograms();
        }

        // Modal functions
        function openCurriculumModal(programId) {
            const program = programsData.find(p => p.id === programId);
            fetchProgramCurriculum(programId)
                .then(courses => {
                    if (courses.data && Array.isArray(courses.data)) {
                        curriculumData[programId] = courses.data.map(course => ({
                            id: course.id,
                            code: course.code,
                            title: course.name,
                            year: course.level,
                            semester: course.semester,
                            credits: course.credit_hours,
                            type: course.category,
                            status: !course.archived ? 'Active' : 'Inactive',
                        }));
                    }
                    document.getElementById('curriculumModalTitle').textContent = `${program.title} - Curriculum`;
                    renderCurriculumList(programId);
                    document.getElementById('curriculumModal').classList.add('active');
                })
                .catch(error => {
                    console.error("Error fetching program curriculum: ", error);
                });
        }

        function openClassesModal(programId) {
            const program = programsData.find(p => p.id === programId);
            fetchProgramClasses(programId)
                .then(classes => {
                    console.log("Classes Data:", classes.data);
                    if (classes.data && Array.isArray(classes.data)) {
                        classesData[programId] = classes.data.map(classItem => ({
                            id: classItem.id,
                            name: classItem.name || classItem.code || `Class ${classItem.id}`,
                            year: classItem.year || classItem.level || 1,
                            students: classItem.total_students || classItem.students || 0,
                            status: classItem.status || "N/A",
                            lecturer: classItem.lecturer || "N/A"
                        }));
                    }
                    document.getElementById('classesModalTitle').textContent = `${program.title} - Classes`;
                    renderClassesList(programId);
                    document.getElementById('classesModal').classList.add('active');
                })
                .catch(error => {
                    console.error("Error fetching program curriculum: ", error);
                });
        }

        function openStudentsModal(programId) {
            const program = programsData.find(p => p.id === programId);
            fetchProgramStudents(programId)
                .then(students => {
                    console.log("Students Data:", students.data);
                    if (students.data && Array.isArray(students.data)) {
                        studentsData[programId] = students.data.map(student => ({
                            id: student.id,
                            name: student.name || student.full_name || `Student ${student.id}`,
                            studentId: student.id || 'N/A',
                            year: student.year || student.level || 1,
                            status: student.status || "N/A",
                            gpa: student.gpa || 0.0
                        }));
                    }
                    document.getElementById('studentsModalTitle').textContent = `${program.title} - Students`;
                    renderStudentsList(programId);
                    document.getElementById('studentsModal').classList.add('active');
                })
                .catch(error => {
                    console.error("Error fetching program curriculum: ", error);
                });
        }

        function openAssignCourseModal(programId) {
            const program = programsData.find(p => p.id === programId);
            document.getElementById('assignCourseModalTitle').textContent = `${program.title} - Assign Courses`;
            document.getElementById("programSelect").value = programId;
            document.getElementById('departmentSelectCoursesBtn').setAttribute('data-program', programId);
            document.getElementById('assignCourseModal').classList.add('active');
        }

        function closeAllModals() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.classList.remove('active');
            });
        }

        // Render modal lists
        function renderCurriculumList(programId) {
            const curriculum = curriculumData[programId] || [];
            const listContainer = document.getElementById('curriculumList');

            if (curriculum.length === 0) {
                listContainer.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-list-alt"></i>
                        <h3>No Curriculum Found</h3>
                        <p>No curriculum data available for this program.</p>
                    </div>
                `;
                return;
            }

            listContainer.innerHTML = curriculum.map(course => `
                <div class="list-item" data-year="${course.year}" data-semester="${course.semester}" data-type="${course.type}">
                    <div class="list-item-info">
                        <div class="list-item-title">${course.code} - ${course.title}</div>
                        <div class="list-item-subtitle">Level ${course.year}, Semester ${course.semester} • ${course.credits} Credits • ${course.type}</div>
                        <div class="list-item-meta">
                            <span>Status: ${course.status}</span>
                        </div>
                    </div>
                    <div class="list-item-actions">
                        <button class="list-item-btn view" onclick="viewCourseDetails(${course.code})" title="View course details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="list-item-btn remove" onclick="archiveCurriculumCourse(${programId}, '${course.code}')" title="Archive this course from this curriculum">
                            <i class="fas fa-archive"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function renderClassesList(programId) {
            const classes = classesData[programId] || [];
            const listContainer = document.getElementById('classesList');

            if (classes.length === 0) {
                listContainer.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No Classes Found</h3>
                        <p>No classes data available for this program.</p>
                    </div>
                `;
                return;
            }

            listContainer.innerHTML = classes.map(classItem => `
                <div class="list-item" data-year="${classItem.year}" data-status="${classItem.status}">
                    <div class="list-item-info">
                        <div class="list-item-title">${classItem.name}</div>
                        <div class="list-item-subtitle">${classItem.students} Students • Lecturer: ${classItem.lecturer}</div>
                        <div class="list-item-meta">
                            <span>Status: ${classItem.status}</span>
                        </div>
                    </div>
                    <div class="list-item-actions">
                        <button class="list-item-btn view" onclick="viewClassDetails(${classItem.id})">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="list-item-btn edit" onclick="editClass(${classItem.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function renderStudentsList(programId) {
            const students = studentsData[programId] || [];
            const listContainer = document.getElementById('studentsList');

            if (students.length === 0) {
                listContainer.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-user-graduate"></i>
                        <h3>No Students Found</h3>
                        <p>No students data available for this program.</p>
                    </div>
                `;
                return;
            }

            listContainer.innerHTML = students.map(student => `
                <div class="list-item" data-year="${student.year}" data-status="${student.status}">
                    <div class="list-item-info">
                        <div class="list-item-title">${student.name}</div>
                        <div class="list-item-subtitle">ID: ${student.studentId} • Year ${student.year}</div>
                        <div class="list-item-meta">
                            <span>GPA: ${student.gpa}</span>
                            <span>Status: ${student.status}</span>
                        </div>
                    </div>
                    <div class="list-item-actions">
                        <button class="list-item-btn view" onclick="viewStudentDetails(${student.id})">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="list-item-btn edit" onclick="editStudent(${student.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function renderCoursesList(programId) {
            const courses = coursesData[programId] || [];
            const listContainer = document.getElementById('coursesList');

            if (courses.length === 0) {
                listContainer.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-book"></i>
                        <h3>No Courses Found</h3>
                        <p>No courses data available for this program.</p>
                    </div>
                `;
                return;
            }

            listContainer.innerHTML = courses.map(course => `
                <div class="list-item" data-type="${course.type}" data-status="${course.status}">
                    <div class="list-item-info">
                        <div class="list-item-title">${course.code} - ${course.title}</div>
                        <div class="list-item-subtitle">${course.credits} Credits • ${course.type}</div>
                        <div class="list-item-meta">
                            <span>Status: ${course.status}</span>
                        </div>
                    </div>
                    <div class="list-item-actions">
                        <button class="list-item-btn view" onclick="viewCourseDetails(${course.id})">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="list-item-btn edit" onclick="editCourse(${course.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>
                </div>
            `).join('');
        }

        // Setup modal filters
        function setupModalFilters() {
            // Curriculum filters
            document.getElementById('curriculumSearch').addEventListener('input', filterCurriculum);
            document.getElementById('curriculumYearFilter').addEventListener('change', filterCurriculum);
            document.getElementById('curriculumSemesterFilter').addEventListener('change', filterCurriculum);
            document.getElementById('curriculumTypeFilter').addEventListener('change', filterCurriculum);

            // Classes filters
            document.getElementById('classesSearch').addEventListener('input', filterClasses);
            document.getElementById('classesYearFilter').addEventListener('change', filterClasses);
            document.getElementById('classesStatusFilter').addEventListener('change', filterClasses);

            // Students filters
            document.getElementById('studentsSearch').addEventListener('input', filterStudents);
            document.getElementById('studentsYearFilter').addEventListener('change', filterStudents);
            document.getElementById('studentsStatusFilter').addEventListener('change', filterStudents);
        }

        // Modal filter functions
        function filterCurriculum() {
            const searchTerm = document.getElementById('curriculumSearch').value.toLowerCase();
            const yearFilter = document.getElementById('curriculumYearFilter').value;
            const semesterFilter = document.getElementById('curriculumSemesterFilter').value;
            const typeFilter = document.getElementById('curriculumTypeFilter').value;

            const items = document.querySelectorAll('#curriculumList .list-item');
            items.forEach(item => {
                const title = item.querySelector('.list-item-title').textContent.toLowerCase();
                const year = item.dataset.year;
                const semester = item.dataset.semester;
                const type = item.dataset.type;

                const matchesSearch = title.includes(searchTerm);
                const matchesYear = yearFilter === 'all' || year === yearFilter;
                const matchesSemester = semesterFilter === 'all' || semester === semesterFilter;
                const matchesType = typeFilter === 'all' || type === typeFilter;

                if (matchesSearch && matchesYear && matchesSemester && matchesType) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function filterClasses() {
            const searchTerm = document.getElementById('classesSearch').value.toLowerCase();
            const yearFilter = document.getElementById('classesYearFilter').value;
            const statusFilter = document.getElementById('classesStatusFilter').value;

            const items = document.querySelectorAll('#classesList .list-item');
            items.forEach(item => {
                const title = item.querySelector('.list-item-title').textContent.toLowerCase();
                const year = item.dataset.year;
                const status = item.dataset.status;

                const matchesSearch = title.includes(searchTerm);
                const matchesYear = yearFilter === 'all' || year === yearFilter;
                const matchesStatus = statusFilter === 'all' || status === statusFilter;

                if (matchesSearch && matchesYear && matchesStatus) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function filterStudents() {
            const searchTerm = document.getElementById('studentsSearch').value.toLowerCase();
            const yearFilter = document.getElementById('studentsYearFilter').value;
            const statusFilter = document.getElementById('studentsStatusFilter').value;

            const items = document.querySelectorAll('#studentsList .list-item');
            items.forEach(item => {
                const title = item.querySelector('.list-item-title').textContent.toLowerCase();
                const year = item.dataset.year;
                const status = item.dataset.status;

                const matchesSearch = title.includes(searchTerm);
                const matchesYear = yearFilter === 'all' || year === yearFilter;
                const matchesStatus = statusFilter === 'all' || status === statusFilter;

                if (matchesSearch && matchesYear && matchesStatus) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Action functions (these would typically navigate to detail pages or open edit modals)
        function viewCourseDetails(courseId) {
            alert(`Viewing course details for course ID: ${courseId}`);
            // In a real application, this would navigate to a course details page
            // window.location.href = `course-details.html?id=${courseId}`;
        }

        function viewClassDetails(classId) {
            alert(`Viewing class details for class ID: ${classId}`);
            // In a real application, this would navigate to a class details page
            // window.location.href = `class-details.html?id=${classId}`;
        }

        function viewStudentDetails(studentId) {
            alert(`Viewing student details for student ID: ${studentId}`);
            // In a real application, this would navigate to a student details page
            // window.location.href = `student-details.html?id=${studentId}`;
        }

        function editClass(classId) {
            alert(`Editing class with ID: ${classId}`);
            // In a real application, this would open an edit modal or navigate to an edit page
        }

        function editStudent(studentId) {
            alert(`Editing student with ID: ${studentId}`);
            // In a real application, this would open an edit modal or navigate to an edit page
        }

        function editCourse(courseId) {
            alert(`Editing course with ID: ${courseId}`);
            // In a real application, this would open an edit modal or navigate to an edit page
        }

        function editProgram(programId) {
            const program = programsData.find(p => p.id === programId);
            console.log("Editing program:", program);
            if (program) {
                openModal('addProgramModal');
                document.getElementById('programAction').value = 'edit';
                document.getElementById('name').value = program.title;
                document.getElementById('category').value = program.category;
                document.getElementById('code').value = program.code;
                document.getElementById('index_code').value = program.index_code;
                document.getElementById("group").value = program.group;
                document.getElementById('duration').value = program.duration.split(" ")[0];
                document.getElementById('dur_format').value = program.duration.split(" ")[1];
                document.getElementById('programDepartment').value = program.department_name;
                document.getElementById('programFaculty').value = program.faculty;
                document.getElementById('regular_available').checked = program.regular_available;
                document.getElementById('weekend_available').checked = program.weekend_available;
            } else {
                alert("Program not found.");
            }
        }

        function archiveProgram(programId) {
            if (confirm("Are you sure you want to archive this program?")) {
                // Perform AJAX request to archive program
                fetch('../endpoint/archive-program', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            'program': programId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Program archived successfully!");
                            // Optionally, refresh the programs list
                            //renderPrograms();
                            // hard reload the page to reflect the changes
                            window.location.reload();
                        } else {
                            alert("Error archiving program: " + data.message);
                        }
                    })
                    .catch(error => {
                        console.error("Error archiving program:", error);
                        alert("An error occurred while archiving the program.");
                    });
            }
        }

        function archiveCurriculumCourse(programId, courseCode) {
            if (confirm("Are you sure you want to archive this course for this program?")) {
                // Perform AJAX request to archive curriculum course
                fetch('../endpoint/archive-curriculum-course', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            'program': programId,
                            'course': courseCode,
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Course archived successfully for this program!");
                            // Remove course from curriculum list for program
                            if (curriculumData[programId]) {
                                curriculumData[programId] = curriculumData[programId].filter(course => course.code !== courseCode);
                            }
                            // Optionally, refresh the course list for program
                            renderCurriculumList(programId);
                            // hard reload the page to reflect the changes
                            // window.location.reload();
                        } else {
                            alert("Error archiving course for the program: " + data.message);
                        }
                    })
                    .catch(error => {
                        console.error("Error archiving program:", error);
                        alert("An error occurred while archiving the program.");
                    });
            }
        }

        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                // Only close the topmost modal if departmentCourseSelectionModal is open
                if (event.target.id === 'departmentCourseSelectionModal') {
                    closeModal('departmentCourseSelectionModal');
                } else {
                    closeAllModals();
                }
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                // If departmentCourseSelectionModal is open, close only it
                if (document.getElementById('departmentCourseSelectionModal').classList.contains('active')) {
                    closeModal('departmentCourseSelectionModal');
                } else {
                    closeAllModals();
                }
            }
        });

        document.getElementById('saveProgramForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            const action = formData.get('action');

            if (action === 'add') {
                // Perform AJAX request to add program
                fetch('../endpoint/add-program', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Program added successfully!");
                            closeAllModals();
                            // Optionally, refresh the programs list
                            renderPrograms();
                        } else {
                            alert("Error adding program: " + data.message);
                        }
                    })
                    .catch(error => {
                        console.error("Error adding program:", error);
                        alert("An error occurred while adding the program.");
                    });
            } else if (action === 'update') {
                // Perform AJAX request to update program
                fetch('../endpoint/update-program', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Program updated successfully!");
                            closeAllModals();
                            // Optionally, refresh the programs list
                            renderPrograms();
                        } else {
                            alert("Error updating program: " + data.message);
                        }
                    })
                    .catch(error => {
                        console.error("Error updating program:", error);
                        alert("An error occurred while updating the program.");
                    });
            }
        });
    </script>
</body>

</html>