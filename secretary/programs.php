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

$pageTitle = "Programs";
$activePage = "programs";

$departmentId = $_SESSION["staff"]["department_id"] ?? null;
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
                        <option value="all">All Years</option>
                        <option value="1">Year 1</option>
                        <option value="2">Year 2</option>
                        <option value="3">Year 3</option>
                        <option value="4">Year 4</option>
                    </select>
                    <select class="modal-filter-select" id="curriculumSemesterFilter">
                        <option value="all">All Semesters</option>
                        <option value="1">Semester 1</option>
                        <option value="2">Semester 2</option>
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

    <!-- Courses Modal -->
    <div class="modal" id="coursesModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><i class="fas fa-book"></i> <span id="coursesModalTitle">Program Courses</span></h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-filters">
                    <div class="modal-search">
                        <input type="text" placeholder="Search courses..." id="coursesSearch">
                        <button class="modal-search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <select class="modal-filter-select" id="coursesTypeFilter">
                        <option value="all">All Types</option>
                        <option value="core">Core</option>
                        <option value="elective">Elective</option>
                        <option value="practical">Practical</option>
                    </select>
                    <select class="modal-filter-select" id="coursesStatusFilter">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="modal-body">
                    <div class="modal-list" id="coursesList">
                        <!-- Courses items will be dynamically generated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function capitalizeWords(str) {
            return str.toLowerCase().split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        }

        // Sample data
        const staticProgramData = <?= json_encode($activeCummulativePrograms) ?>;
        const programsData = staticProgramData.map(program => ({
            id: program.program_id,
            title: capitalizeWords(program.program_name),
            code: program.program_code,
            level: program.program_type.toLowerCase(),
            department: program.department_name,
            description: program.description ?? '',
            duration: program.duration + " " + program.dur_format,
            credits: program.total_credits,
            students: program.total_students,
            courses: program.total_courses,
            classes: program.total_classes,
            status: program.status
        }));

        const curriculumData = {
            1: [ // BME curriculum
                {
                    id: 1,
                    code: "ME101",
                    title: "Introduction to Marine Engineering",
                    year: 1,
                    semester: 1,
                    credits: 3,
                    type: "core"
                },
                {
                    id: 2,
                    code: "MA101",
                    title: "Engineering Mathematics I",
                    year: 1,
                    semester: 1,
                    credits: 4,
                    type: "core"
                },
                {
                    id: 3,
                    code: "PH101",
                    title: "Engineering Physics",
                    year: 1,
                    semester: 1,
                    credits: 3,
                    type: "core"
                },
                {
                    id: 4,
                    code: "ME102",
                    title: "Marine Thermodynamics",
                    year: 1,
                    semester: 2,
                    credits: 3,
                    type: "core"
                },
                {
                    id: 5,
                    code: "ME201",
                    title: "Marine Propulsion Systems",
                    year: 2,
                    semester: 1,
                    credits: 4,
                    type: "core"
                },
                {
                    id: 6,
                    code: "ME202",
                    title: "Ship Design Principles",
                    year: 2,
                    semester: 2,
                    credits: 4,
                    type: "core"
                },
                {
                    id: 7,
                    code: "ME301",
                    title: "Advanced Marine Engineering",
                    year: 3,
                    semester: 1,
                    credits: 4,
                    type: "core"
                },
                {
                    id: 8,
                    code: "ME302",
                    title: "Marine Electrical Systems",
                    year: 3,
                    semester: 2,
                    credits: 3,
                    type: "elective"
                }
            ],
            2: [ // MNS curriculum
                {
                    id: 9,
                    code: "NS201",
                    title: "Advanced Navigation",
                    year: 1,
                    semester: 1,
                    credits: 4,
                    type: "core"
                },
                {
                    id: 10,
                    code: "NS202",
                    title: "Ship Handling",
                    year: 1,
                    semester: 1,
                    credits: 3,
                    type: "core"
                },
                {
                    id: 11,
                    code: "NS203",
                    title: "Maritime Operations",
                    year: 1,
                    semester: 2,
                    credits: 4,
                    type: "core"
                },
                {
                    id: 12,
                    code: "NS301",
                    title: "Port Management",
                    year: 2,
                    semester: 1,
                    credits: 3,
                    type: "elective"
                },
                {
                    id: 13,
                    code: "NS302",
                    title: "Maritime Law",
                    year: 2,
                    semester: 2,
                    credits: 3,
                    type: "core"
                }
            ]
        };

        const classesData = {
            1: [ // BME classes
                {
                    id: 1,
                    name: "BME Class A - Year 1",
                    year: 1,
                    students: 35,
                    status: "active",
                    lecturer: "Dr. Smith"
                },
                {
                    id: 2,
                    name: "BME Class B - Year 1",
                    year: 1,
                    students: 32,
                    status: "active",
                    lecturer: "Prof. Johnson"
                },
                {
                    id: 3,
                    name: "BME Class A - Year 2",
                    year: 2,
                    students: 28,
                    status: "active",
                    lecturer: "Dr. Brown"
                },
                {
                    id: 4,
                    name: "BME Class A - Year 3",
                    year: 3,
                    students: 25,
                    status: "active",
                    lecturer: "Dr. Wilson"
                },
                {
                    id: 5,
                    name: "BME Class A - Year 4",
                    year: 4,
                    students: 25,
                    status: "active",
                    lecturer: "Prof. Davis"
                }
            ],
            2: [ // MNS classes
                {
                    id: 6,
                    name: "MNS Class A - Year 1",
                    year: 1,
                    students: 40,
                    status: "active",
                    lecturer: "Capt. Anderson"
                },
                {
                    id: 7,
                    name: "MNS Class A - Year 2",
                    year: 2,
                    students: 38,
                    status: "active",
                    lecturer: "Capt. Thompson"
                }
            ]
        };

        const studentsData = {
            1: [ // BME students
                {
                    id: 1,
                    name: "John Smith",
                    studentId: "BME2021001",
                    year: 3,
                    status: "active",
                    gpa: 3.45
                },
                {
                    id: 2,
                    name: "Sarah Johnson",
                    studentId: "BME2021002",
                    year: 3,
                    status: "active",
                    gpa: 3.78
                },
                {
                    id: 3,
                    name: "Michael Brown",
                    studentId: "BME2020001",
                    year: 4,
                    status: "active",
                    gpa: 3.23
                },
                {
                    id: 4,
                    name: "Emily Davis",
                    studentId: "BME2022001",
                    year: 2,
                    status: "active",
                    gpa: 3.89
                },
                {
                    id: 5,
                    name: "Robert Wilson",
                    studentId: "BME2019001",
                    year: 4,
                    status: "graduated",
                    gpa: 3.56
                }
            ],
            2: [ // MNS students
                {
                    id: 6,
                    name: "James Anderson",
                    studentId: "MNS2022001",
                    year: 2,
                    status: "active",
                    gpa: 3.67
                },
                {
                    id: 7,
                    name: "Lisa Thompson",
                    studentId: "MNS2023001",
                    year: 1,
                    status: "active",
                    gpa: 3.45
                },
                {
                    id: 8,
                    name: "David Miller",
                    studentId: "MNS2022002",
                    year: 2,
                    status: "suspended",
                    gpa: 2.34
                }
            ]
        };

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
                console.error("Failed to fetch courses:", error);
            }
        }

        const coursesData = {
            1: [ // BME courses
                {
                    id: 1,
                    code: "ME101",
                    title: "Introduction to Marine Engineering",
                    credits: 3,
                    type: "core",
                    status: "active"
                },
                {
                    id: 2,
                    code: "ME201",
                    title: "Marine Propulsion Systems",
                    credits: 4,
                    type: "core",
                    status: "active"
                },
                {
                    id: 3,
                    code: "ME301",
                    title: "Advanced Marine Engineering",
                    credits: 4,
                    type: "core",
                    status: "active"
                },
                {
                    id: 4,
                    code: "ME302",
                    title: "Marine Electrical Systems",
                    credits: 3,
                    type: "elective",
                    status: "active"
                },
                {
                    id: 5,
                    code: "ME401",
                    title: "Ship Design Project",
                    credits: 6,
                    type: "practical",
                    status: "active"
                }
            ],
            2: [ // MNS courses
                {
                    id: 6,
                    code: "NS201",
                    title: "Advanced Navigation",
                    credits: 4,
                    type: "core",
                    status: "active"
                },
                {
                    id: 7,
                    code: "NS202",
                    title: "Ship Handling",
                    credits: 3,
                    type: "core",
                    status: "active"
                },
                {
                    id: 8,
                    code: "NS301",
                    title: "Port Management",
                    credits: 3,
                    type: "elective",
                    status: "active"
                }
            ]
        };

        let filteredPrograms = [...programsData];

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            renderPrograms();
            initializeEventListeners();
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
                            <div class="program-code">${program.code}</div>
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
                        <div class="info-item">
                            <i class="fas fa-building"></i>
                            <span>${program.department.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-circle ${program.status === 'active' ? 'text-success' : 'text-danger'}"></i>
                            <span>${program.status.charAt(0).toUpperCase() + program.status.slice(1)}</span>
                        </div>
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
                        <button class="program-btn secondary" onclick="openCoursesModal(${program.id})">
                            <i class="fas fa-book"></i> Courses
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
                button.addEventListener('click', function() {
                    closeAllModals();
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
            document.getElementById('curriculumModalTitle').textContent = `${program.title} - Curriculum`;
            renderCurriculumList(programId);
            document.getElementById('curriculumModal').classList.add('active');
        }

        function openClassesModal(programId) {
            const program = programsData.find(p => p.id === programId);
            document.getElementById('classesModalTitle').textContent = `${program.title} - Classes`;
            renderClassesList(programId);
            document.getElementById('classesModal').classList.add('active');
        }

        function openStudentsModal(programId) {
            const program = programsData.find(p => p.id === programId);
            document.getElementById('studentsModalTitle').textContent = `${program.title} - Students`;
            renderStudentsList(programId);
            document.getElementById('studentsModal').classList.add('active');
        }

        function openCoursesModal(programId) {
            const program = programsData.find(p => p.id === programId);
            document.getElementById('coursesModalTitle').textContent = `${program.title} - Courses`;
            renderCoursesList(programId);
            document.getElementById('coursesModal').classList.add('active');
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
                <div class="list-item" data-year="${course.year}" data-semester="${course.semester}">
                    <div class="list-item-info">
                        <div class="list-item-title">${course.code} - ${course.title}</div>
                        <div class="list-item-subtitle">Year ${course.year}, Semester ${course.semester} • ${course.credits} Credits • ${course.type}</div>
                    </div>
                    <div class="list-item-actions">
                        <button class="list-item-btn view" onclick="viewCourseDetails(${course.id})">
                            <i class="fas fa-eye"></i> View
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

            // Classes filters
            document.getElementById('classesSearch').addEventListener('input', filterClasses);
            document.getElementById('classesYearFilter').addEventListener('change', filterClasses);
            document.getElementById('classesStatusFilter').addEventListener('change', filterClasses);

            // Students filters
            document.getElementById('studentsSearch').addEventListener('input', filterStudents);
            document.getElementById('studentsYearFilter').addEventListener('change', filterStudents);
            document.getElementById('studentsStatusFilter').addEventListener('change', filterStudents);

            // Courses filters
            document.getElementById('coursesSearch').addEventListener('input', filterCourses);
            document.getElementById('coursesTypeFilter').addEventListener('change', filterCourses);
            document.getElementById('coursesStatusFilter').addEventListener('change', filterCourses);
        }

        // Modal filter functions
        function filterCurriculum() {
            const searchTerm = document.getElementById('curriculumSearch').value.toLowerCase();
            const yearFilter = document.getElementById('curriculumYearFilter').value;
            const semesterFilter = document.getElementById('curriculumSemesterFilter').value;

            const items = document.querySelectorAll('#curriculumList .list-item');
            items.forEach(item => {
                const title = item.querySelector('.list-item-title').textContent.toLowerCase();
                const year = item.dataset.year;
                const semester = item.dataset.semester;

                const matchesSearch = title.includes(searchTerm);
                const matchesYear = yearFilter === 'all' || year === yearFilter;
                const matchesSemester = semesterFilter === 'all' || semester === semesterFilter;

                if (matchesSearch && matchesYear && matchesSemester) {
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

        function filterCourses() {
            const searchTerm = document.getElementById('coursesSearch').value.toLowerCase();
            const typeFilter = document.getElementById('coursesTypeFilter').value;
            const statusFilter = document.getElementById('coursesStatusFilter').value;

            const items = document.querySelectorAll('#coursesList .list-item');
            items.forEach(item => {
                const title = item.querySelector('.list-item-title').textContent.toLowerCase();
                const type = item.dataset.type;
                const status = item.dataset.status;

                const matchesSearch = title.includes(searchTerm);
                const matchesType = typeFilter === 'all' || type === typeFilter;
                const matchesStatus = statusFilter === 'all' || status === statusFilter;

                if (matchesSearch && matchesType && matchesStatus) {
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

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                closeAllModals();
            }
        });

        // Keyboard navigation for modals
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeAllModals();
            }
        });
    </script>
</body>

</html>