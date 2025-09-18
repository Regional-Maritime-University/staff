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

$secretary = new SecretaryController($db, $user, $pass);
$course_category = new CourseCategory($db, $user, $pass);
$course = new Course($db, $user, $pass);
$base               = new Base($db, $user, $pass);

$pageTitle = "Results";
$activePage = "results";

$departmentId = $_SESSION["staff"]["department_id"] ?? null;
$semesterId = 2; //$_SESSION["semester"] ?? null;
$archived = false;

$activeSemesters = $secretary->fetchActiveSemesters();

$activeClasses = $secretary->fetchAllActiveClasses(departmentId: $departmentId);

$deadlines = $secretary->fetchPendingDeadlinesByClass($departmentId);
$totalPendingDeadlines = 0;
if ($deadlines && is_array($deadlines)) {
    $totalPendingDeadlines = count($deadlines);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Staff Portal - Courses</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="./css/results.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Sidebar -->
    <?php require_once '../components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">

        <?php require_once '../components/header.php'; ?>

        <!-- Main Content -->

        <div class="results-content">

            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <button class="action-btn" id="uploadResultsBtn">
                        <i class="fas fa-upload"></i>
                        <span>Upload Results</span>
                    </button>
                </div>
            </div>

            <!-- Results Filters -->
            <div class="results-filters">
                <div class="filter-group">
                    <select class="filter-select" id="semesterFilter">
                        <option value="current">First Semester 2023/2024</option>
                        <option value="previous">Second Semester 2022/2023</option>
                        <option value="all">All Semesters</option>
                    </select>
                    <select class="filter-select" id="courseFilter">
                        <option value="all">All Courses</option>
                        <option value="ME101">ME101 - Introduction to Marine Engineering</option>
                        <option value="ME302">ME302 - Marine Propulsion Systems</option>
                        <option value="ME405">ME405 - Ship Design and Construction</option>
                    </select>
                    <select class="filter-select" id="statusFilter">
                        <option value="all">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="submitted">Submitted</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="filter-btn primary" id="resetFiltersBtn">
                        <i class="fas fa-sync-alt"></i> Reset Filters
                    </button>
                </div>
            </div>

            <!-- Results Cards -->
            <div class="results-cards">

                <?php if ($totalPendingDeadlines > 0) : ?>
                    <!-- Display pending deadlines -->
                    <?php foreach ($deadlines as $deadline) : ?>
                        <?php
                        $status = $deadline['deadline_status'] === "pending" ? $deadline['deadline_status'] : ($deadline['result_status'] === "pending" && $deadline['deadline_status'] === "submitted" ? $deadline['deadline_status'] : ($deadline['result_status'] === "approved" && $deadline['deadline_status'] === "submitted" ? $deadline['result_status'] : $deadline['result_status']));
                        ?>
                        <div class="result-card">
                            <div class="result-header">
                                <h3 class="result-title"><?= "[" . $deadline['class_code'] . "] " . $deadline['course_name'] ?></h3>
                                <span class="result-status <?= $status ?>"><?= ucfirst($status) ?></span>
                            </div>
                            <div class="result-info">
                                <div class="info-item">
                                    <div class="info-label">Semester</div>
                                    <div class="info-value"><?= $deadline['semester_name'] ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Students</div>
                                    <div class="info-value"><?= $deadline['total_registered_students'] ?? 0 ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Due Date</div>
                                    <div class="info-value"><?= date('M d, Y', strtotime($deadline['due_date'])) ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Lecturer</div>
                                    <div class="info-value"><?= $deadline['lecturer_name'] ?></div>
                                </div>
                            </div>
                            <div class="result-actions">
                                <!-- <button class="result-btn secondary downloadResultsBtn"
                                    data-class="<?= $deadline['class_code'] ?>"
                                    data-course="<?= $deadline['course_code'] ?>"
                                    data-semester="<?= $deadline['semester_id'] ?>"
                                    title="Download <?= $deadline['class_code'] ?> results">
                                    <i class="fas fa-download"></i>
                                </button> -->
                                <?php if ($status == "submitted") : ?>
                                    <button class="result-btn success approveResultsBtn"
                                        data-class="<?= $deadline['class_code'] ?>"
                                        data-course="<?= $deadline['course_code'] ?>"
                                        data-semester="<?= $deadline['semester_id'] ?>"
                                        title="Approve <?= $deadline['class_code'] ?> results">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="result-btn danger declineResultsBtn"
                                        data-class="<?= $deadline['class_code'] ?>"
                                        data-course="<?= $deadline['course_code'] ?>"
                                        data-semester="<?= $deadline['semester_id'] ?>"
                                        title="Decline <?= $deadline['class_code'] ?> results">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php elseif ($status == "approved") : ?>
                                    <button class="result-btn primary viewResultsBtn"
                                        data-class="<?= $deadline['class_code'] ?>"
                                        data-course="<?= $deadline['course_code'] ?>"
                                        data-semester="<?= $deadline['semester_id'] ?>"
                                        title="View <?= $deadline['class_code'] ?> results">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                <?php endif ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="no-results">
                        <p>No pending deadlines for results upload.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upload Results Modal -->
        <div class="modal" id="uploadResultsModal">
            <div class="modal-dialog modal-lg modal-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Upload Exam Results</h2>
                        <button class="close-btn" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="uploadSemester">Semester</label>
                            <select id="uploadSemester" required>
                                <option value="">Select a semester</option>
                                <?php foreach ($activeSemesters as $semester) : ?>
                                    <option value="<?= $semester['id'] ?>" data-academicYear="<?= $semester["academic_year_name"] ?>"><?= $semester['name'] == 1 ? 'First Semester' : ($semester['name'] == 2 ? 'Second Semester' : 'Summer Semester') ?> <?= $semester['academic_year_start_year'] . '/' . $semester['academic_year_end_year'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="uploadClass">Select Class</label>
                            <select id="uploadClass" required>
                                <option value="">Select a class</option>
                                <?php
                                foreach ($activeClasses as $class) {
                                    echo '<option value="' . $class['code'] . '">' . $class['code'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="uploadCourse">Select Course</label>
                            <select id="uploadCourse" required>
                                <option value="">Select a course</option>
                                <?php
                                foreach ($deadlines as $deadline) {
                                    if ($deadline['deadline_status'] == 'pending') {
                                        echo '<option value="' . $deadline['course_code'] . '">' . $deadline['course_code'] . ' - ' . $deadline['course_name'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Is the course project based?</label>
                            <div class="radio-group">
                                <div class="radio-item">
                                    <input type="radio" id="projectBasedYes" name="uploadProjectBased" value="yes" checked>
                                    <label for="projectBasedYes">Yes</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" id="projectBasedNo" name="uploadProjectBased" value="no">
                                    <label for="projectBasedNo">No</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="score-weights-group">
                                <div class="score-weights-title">Score Weights</div>
                                <div class="score-weight-item">
                                    <label for="uploadExamScoreWeight">Exam Score Weight</label>
                                    <input type="number" title="Exam Score Weight" value="60" min="0" max="100" id="uploadExamScoreWeight" name="uploadExamScoreWeight">
                                </div>
                                <div class="score-weight-item">
                                    <label for="uploadProjectScoreWeight">Project Score Weight</label>
                                    <input type="number" title="Project Score Weight" value="0" min="0" max="100" id="uploadProjectScoreWeight" name="uploadProjectScoreWeight">
                                </div>
                                <div class="score-weight-item">
                                    <label for="uploadAssessmentScoreWeight">Assessment Score Weight</label>
                                    <input type="number" title="Assessment Score Weight" value="40" min="0" max="100" id="uploadAssessmentScoreWeight" name="uploadAssessmentScoreWeight">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Upload Results File</label>
                            <div class="file-upload">
                                <div class="file-input-wrapper">
                                    <input type="file" id="uploadResultsFile" accept=".xlsx, .csv">
                                    <div class="file-input-btn">
                                        <i class="fas fa-cloud-upload-alt"></i> Choose File or Drop File Here
                                    </div>
                                </div>
                                <div class="file-name" id="fileName">No file chosen</div>
                                <div class="file-format-info">Accepted formats: Excel (.xlsx) or CSV (.csv)</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="uploadNotes">Additional Notes (Optional)</label>
                            <textarea id="uploadNotes" rows="3" placeholder="Enter any additional notes or comments"></textarea>
                        </div>

                        <input type="hidden" id="staffId" value="<?= $_SESSION['staff']['number'] ?>">
                    </div>
                    <div class="modal-footer">
                        <button class="cancel-btn" data-dismiss="modal">Cancel</button>
                        <button class="normal-btn" id="submitUploadBtn">Upload Results</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Results Modal -->
        <div class="modal" id="viewResultsModal">
            <div class="modal-dialog  modal-lg modal-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>View Exam Results</h2>
                        <button class="close-btn" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">

                        <!-- Course and Semester Info -->
                        <div class="course-info">
                            <!-- Programme -->
                            <div class="program-info">
                                <span>Programme: </span>
                                <span>BSc Marine Engineering</span>
                            </div>
                            <!-- Course -->
                            <div class="course-info">
                                <span>Course: </span>
                                <span>ME101 - Introduction to Marine Engineering</span>
                            </div>
                            <!-- Semester -->
                            <div class="semester-info">
                                <span>Semester: </span>
                                <span>First Semester 2023/2024</span>
                            </div>
                        </div>

                        <!-- Results Table -->
                        <div class="results-table">
                            <table>
                                <thead></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="cancel-btn" data-dismiss="modal">Close</button>
                        <button class="normal-btn">Download Results</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const departmentId = <?= json_encode($departmentId) ?>;

            // Modal Functions
            function openModal(modalId) {
                document.getElementById(modalId).classList.add("active");
                document.body.style.overflow = "hidden";
            }

            function closeModal(modalId) {
                document.getElementById(modalId).classList.remove("active");
                document.body.style.overflow = "auto";
            }

            // Toggle sidebar
            document.querySelector('.toggle-sidebar').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('collapsed');
                document.querySelector('.main-content').classList.toggle('expanded');
            });

            // Modal functionality
            const uploadResultsModal = document.getElementById('uploadResultsModal');

            // Open modal
            document.getElementById('uploadResultsBtn').addEventListener('click', function() {
                uploadResultsModal.classList.add('active');
            });

            // fetch assigned semester courses
            const fetchCourseResults = async (classCode, courseCode, semesterId) => {
                try {
                    // fetch course results headers first
                    if (!classCode || !courseCode || !semesterId) {
                        alert('Class code, course code and semester ID are required to fetch results.');
                        return [];
                    }

                    const response = await fetch('../endpoint/fetch-semester-course-results', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            class: classCode,
                            course: courseCode,
                            semester: semesterId
                        })
                    });

                    return await response.json();
                } catch (error) {
                    console.error('Fetch error:', error);
                }
            };

            const approveCourseResults = async (classCode, courseCode, semesterId) => {
                try {
                    // fetch course results headers first
                    if (!classCode || !courseCode || !semesterId) {
                        alert('Class code, course code and semester ID are required to fetch results.');
                        return [];
                    }

                    const response = await fetch('../endpoint/approve-semester-course-results', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            class: classCode,
                            course: courseCode,
                            semester: semesterId
                        })
                    });

                    const results = await response.json();
                    return results;
                } catch (error) {
                    console.error('Fetch error:', error);
                }
            }

            // Open view results modal
            document.querySelectorAll('.result-btn.primary').forEach(button => {
                button.addEventListener('click', async function() {

                    const classCode = this.getAttribute('data-class');
                    const courseCode = this.getAttribute('data-course');
                    const semesterId = this.getAttribute('data-semester');

                    // fetch results data based on classCode, courseCode and semesterId
                    let result = await fetchCourseResults(classCode, courseCode, semesterId);

                    if (result.success) {
                        const modal = document.getElementById('viewResultsModal');
                        const courseInfo = modal.querySelector('.course-info');
                        const resultsTable = modal.querySelector('.results-table table tbody');

                        // Clear previous results
                        resultsTable.innerHTML = '';

                        // Set course and semester info
                        courseInfo.querySelector('.program-info span:nth-child(2)').textContent = 'BSc Marine Engineering';
                        courseInfo.querySelector('.course-info span:nth-child(2)').textContent = courseCode;
                        courseInfo.querySelector('.semester-info span:nth-child(2)').textContent = semesterId;

                        // Define key mapping between header text and weight keys
                        const headerWeightMap = {
                            "Exam Score": "exam_score_weight",
                            "Project Score": "project_score_weight",
                            "Ass. Score": "assessment_score_weight"
                        };

                        const thead = modal.querySelector('.results-table table thead');
                        thead.innerHTML = '';
                        const headerRow = document.createElement('tr');

                        // Extract weights
                        const weights = result.data.values[0]; // assuming it's always there
                        const projectBased = result.data.project_based;

                        result.data.headers.forEach(header => {
                            const baseHeader = header.split(' (')[0];

                            // Skip "Project Score" header if not project-based
                            if (baseHeader === "Project Score" && !projectBased) return;

                            const th = document.createElement('th');

                            if (baseHeader === "ACH Mark") {
                                th.textContent = `${baseHeader} (100%)`;
                            } else {
                                const weightKey = headerWeightMap[baseHeader];
                                const weight = weightKey && weights[weightKey] !== undefined ? ` (${weights[weightKey]}%)` : '';
                                th.textContent = `${baseHeader}${weight}`;
                            }

                            headerRow.appendChild(th);
                        });

                        thead.appendChild(headerRow);

                        // Populate results table
                        result.data.body.forEach(row => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${row.student_id}</td>
                                <td>${row.exam_score}</td>
                                ${projectBased && row.project_score ? `<td>${row.project_score}</td>` : ''}
                                <td>${row.ass_score}</td>
                                <td>${row.final_score}</td>
                                <td>${row.grade}</td>
                            `;
                            resultsTable.appendChild(tr);
                        });

                        modal.classList.add('active');
                    } else {
                        alert('Error fetching results: ' + result.message);
                    }
                });
            });

            // Download results
            document.querySelectorAll('.result-btn.secondary').forEach(button => {
                button.addEventListener('click', function() {
                    const classCode = this.getAttribute('data-class');
                    const courseCode = this.getAttribute('data-course');
                    const semesterId = this.getAttribute('data-semester');
                    // download results excel file from ../uploads/results/nameoffile.xlsx

                });
            });

            // Approve results
            document.querySelectorAll('.result-btn.success').forEach(button => {
                button.addEventListener('click', function() {
                    const classCode = this.getAttribute('data-class');
                    const courseCode = this.getAttribute('data-course');
                    const semesterId = this.getAttribute('data-semester');
                    // approve results
                    if (confirm("Are you sure you want to approve this exam results?")) {
                        approveCourseResults(classCode, courseCode, semesterId).then(result => {
                            alert(result.message);
                            if (result.success) {
                                window.location.reload();
                            }
                        });
                    }
                });
            });

            // Close modal
            document.querySelectorAll('.close-btn, .cancel-btn').forEach(button => {
                button.addEventListener('click', function() {
                    uploadResultsModal.classList.remove('active');
                });
            });

            // File upload
            const resultsFile = document.getElementById('uploadResultsFile');
            const fileName = document.getElementById('fileName');
            resultsFile.addEventListener('change', function() {
                if (this.files.length > 0) {
                    fileName.textContent = this.files[0].name;
                } else {
                    fileName.textContent = 'No file chosen';
                }
            });

            const uploadClassCode = document.getElementById("uploadClass");
            uploadClassCode.addEventListener('change', async function() {

                const response = await fetch('../endpoint/fetch-class-courses', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        code: this.value
                    })
                });

                const result = await response.json();

                // render select courses list
                const uploadCourseSelect = document.getElementById('uploadCourse');
                uploadCourseSelect.innerHTML = '<option value="">Select a course</option>';
                if (result.success && Array.isArray(result.data)) {
                    result.data.forEach(course => {
                        const option = document.createElement('option');
                        option.value = course.course_code;
                        option.textContent = `${course.course_code} - ${course.course_name}`;
                        uploadCourseSelect.appendChild(option);
                    });
                }
            });

            // When semester is changed, update the academic year
            document.getElementById('uploadSemester').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const academicYear = selectedOption.getAttribute('data-academicYear');
                if (academicYear) {
                    document.getElementById('uploadSemester').setAttribute('data-academicYear', academicYear);
                }
            });

            // Submit upload
            document.getElementById('submitUploadBtn').addEventListener('click', async function() {
                const classCode = document.getElementById('uploadClass').value;
                const courseCode = document.getElementById('uploadCourse').value;
                const semesterId = document.getElementById('uploadSemester').value;
                const resultsFile = document.getElementById('uploadResultsFile').value;
                const staffId = document.getElementById('staffId').value;
                const projectBased = document.querySelector('input[name="uploadProjectBased"]:checked').value;
                const academicYear = document.getElementById('uploadSemester').getAttribute('data-academicYear');
                const examScoreWeight = document.getElementById('uploadExamScoreWeight').value;
                const projectScoreWeight = document.getElementById('uploadProjectScoreWeight').value;
                const assessmentScoreWeight = document.getElementById('uploadAssessmentScoreWeight').value;


                if (!classCode || !courseCode || !semesterId || !resultsFile || !staffId) {
                    alert('Please fill all required fields and select a file.');
                    return;
                }

                // Send the upload request
                const formData = new FormData();
                formData.append('class', classCode);
                formData.append('course', courseCode);
                formData.append('semester', semesterId);
                formData.append('resultsFile', document.getElementById('uploadResultsFile').files[0]);
                formData.append('staffId', staffId);
                formData.append('projectBased', projectBased);
                formData.append('academicYear', academicYear);
                formData.append('examScoreWeight', examScoreWeight);
                formData.append('projectScoreWeight', projectScoreWeight);
                formData.append('assessmentScoreWeight', assessmentScoreWeight);
                formData.append('notes', document.getElementById('uploadNotes').value);

                response = await fetch('../endpoint/upload-results', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const result = await response.json();
                alert(result.message);
                if (result.success) window.location.reload();

                uploadResultsModal.classList.remove('active');
            });

            // Reset filters
            document.getElementById('resetFiltersBtn').addEventListener('click', function() {
                document.getElementById('semesterFilter').value = 'current';
                document.getElementById('courseFilter').value = 'all';
                document.getElementById('statusFilter').value = 'all';
            });

            // Filter functionality
            const filterResults = () => {
                const semester = document.getElementById('semesterFilter').value;
                const course = document.getElementById('courseFilter').value;
                const status = document.getElementById('statusFilter').value;

                // In a real application, you would filter the results based on the selected filters
            };

            document.getElementById('semesterFilter').addEventListener('change', filterResults);
            document.getElementById('courseFilter').addEventListener('change', filterResults);
            document.getElementById('statusFilter').addEventListener('change', filterResults);

            // Table actions
            document.querySelectorAll('.table-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const action = this.getAttribute('title');
                    const row = this.closest('tr');
                    const courseCode = row.cells[0].textContent;
                    const courseTitle = row.cells[1].textContent;

                    //alert(`${action} for ${courseCode} - ${courseTitle}`);
                });
            });

            // Result card buttons
            document.querySelectorAll('.result-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const action = this.textContent.trim();
                    const card = this.closest('.result-card');
                    const courseTitle = card.querySelector('.result-title').textContent;

                    //alert(`${action} for ${courseTitle}`);
                });
            });

            // Pagination
            document.querySelectorAll('.page-btn').forEach(button => {
                button.addEventListener('click', function() {
                    if (!this.classList.contains('active') && this.textContent) {
                        document.querySelector('.page-btn.active').classList.remove('active');
                        this.classList.add('active');

                        // In a real application, you would load the corresponding page
                    }
                });
            });
        });
    </script>
</body>

</html>