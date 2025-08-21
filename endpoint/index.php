<?php
session_start();

if (! isset($_SESSION["lastAccessed"])) {
    $_SESSION["lastAccessed"] = time();
}

$_SESSION["currentAccess"] = time();
$diff                      = $_SESSION["currentAccess"] - $_SESSION["lastAccessed"];
if ($diff > 1800) {
    die(json_encode(["success" => false, "message" => "logout"]));
}

/*
* Designed and programmed by
* @Author: Francis A. Anlimah
*/

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require "../bootstrap.php";

use Src\Controller\AdminController;
use Src\Controller\ExposeDataController;
use Src\Controller\SecretaryController;
use Src\Controller\UploadExcelDataController;
use Src\Core\Base;
use Src\Core\Classes;
use Src\Core\Course;
use Src\Core\Deadline;
use Src\Core\Program;
use Src\Core\Staff;
use Src\Core\Student;

require_once '../inc/admin-database-con.php';

$expose                 = new ExposeDataController($db, $user, $pass);
$admin                  = new AdminController($db, $user, $pass);
$program                = new Program($db, $user, $pass);
$course                 = new Course($db, $user, $pass);
$student                = new Student($db, $user, $pass);
$class                 = new Classes($db, $user, $pass);
$staff                  = new Staff($db, $user, $pass);
$secretary              = new SecretaryController($db, $user, $pass);
$base                   = new Base($db, $user, $pass);
$deadline                = new Deadline($db, $user, $pass);

$data   = [];
$errors = [];

// All GET request will be sent here
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if ($_GET["url"] == "fetch-fee-structure-category") {
        die(json_encode(["success" => true, "data" => $fee_structure_category->fetch()]));
    } elseif ($_GET["url"] == "fetch-fee-structure-type") {
        die(json_encode(["success" => true, "data" => $fee_structure_type->fetch()]));
    } elseif ($_GET["url"] == "fetch-fee-item") {
        die(json_encode(["success" => true, "data" => $fee_item->fetch()]));
    } elseif ($_GET["url"] == "active-semesters") {
        die(json_encode(["success" => true, "data" => $base->getActiveSemesters()]));
    } else if ($_GET["url"] == "fetch-classes") {
        // must have a department, class code, program, or year 
        // archived is optional
        if (isset($_GET["department"]) && ! empty($_GET["department"])) {
            $data["key"]   = "department";
            $data["value"] = $_GET["department"];
        } elseif (isset($_GET["code"]) && ! empty($_GET["code"])) {
            $data["key"]   = "code";
            $data["value"] = $_GET["code"];
        } elseif (isset($_GET["program"]) && ! empty($_GET["program"])) {
            $data["key"]   = "program";
            $data["value"] = $_GET["program"];
        } elseif (isset($_GET["year"]) && ! empty($_GET["year"])) {
            $data["key"]   = "year";
            $data["value"] = $_GET["year"];
        } else {
            die(json_encode(["success" => false, "message" => "Invalid request!"]));
        }
        if (isset($_GET["archived"]) && $_GET["archived"] === "true") {
            $data["archived"] = true;
        } else {
            $data["archived"] = false;
        }
        $result = $class->fetch($data["key"], $data["value"], $data["archived"]);
        if ($result) {
            die(json_encode(["success" => true, "data" => $result]));
        } else {
            die(json_encode(["success" => false, "message" => "Failed to fetch classes!"]));
        }
    } elseif ($_GET["url"] == "fetch-staff") {
        if (isset($_GET["staff"]) && ! empty($_GET["staff"])) {
            $data["key"]   = "number";
            $data["value"] = $_GET["staff"];
        } else if (isset($_GET["email"]) && ! empty($_GET["email"])) {
            $data["key"]   = "email";
            $data["value"] = $_GET["email"];
        } else if (isset($_GET["gender"]) && ! empty($_GET["gender"])) {
            $data["key"]   = "gender";
            $data["value"] = $_GET["gender"];
        } else if (isset($_GET["role"]) && ! empty($_GET["role"])) {
            $data["key"]   = "role";
            $data["value"] = $_GET["role"];
        } else if (isset($_GET["department"]) && ! empty($_GET["department"])) {
            $data["key"]   = "department";
            $data["value"] = $_GET["department"];
        } else {
            die(json_encode(["success" => false, "message" => "Invalid request!"]));
        }

        if (isset($_GET["archived"]) && $_GET["archived"] === "true") {
            $data["archived"] = true;
        } else {
            $data["archived"] = false;
        }

        $result = $staff->fetch($data["key"], $data["value"], $data["archived"]);

        if ($result) {
            die(json_encode(["success" => true, "data" => $result]));
        } else {
            die(json_encode(["success" => false, "message" => "Failed to fetch lecturers data!"]));
        }
    }

    // All POST request will be sent here
} elseif ($_SERVER['REQUEST_METHOD'] == "POST") {

    if ($_GET["url"] == "login") {
        if (! isset($_SESSION["_staffLogToken"]) || empty($_SESSION["_staffLogToken"])) {
            die(json_encode(["success" => false, "message" => "Invalid request: 1!"]));
        }

        if (! isset($_POST["_vALToken"]) || empty($_POST["_vALToken"])) {
            die(json_encode(["success" => false, "message" => "Invalid request: 2!"]));
        }

        if ($_POST["_vALToken"] !== $_SESSION["_staffLogToken"]) {
            die(json_encode(["success" => false, "message" => "Invalid request: 3!"]));
        }

        $email = $expose->validateText($_POST["email"]);
        $password = $expose->validatePassword($_POST["password"]);

        $result = $admin->verifyStaffLogin($email, $password);

        if (!$result["success"]) {
            $_SESSION['staffLoginSuccess'] = false;
            die(json_encode($result));
        }

        $secretary->setInitialSessions($result["data"]);

        die(json_encode(["success" => true, "message" => $result["data"]["role"]]));
    }

    // backup database
    elseif ($_GET["url"] == "backup-data") {
        $dbs  = ["rmu_admissions"];
        $user = "root";
        $pass = "";
        $host = "localhost";

        if (! file_exists("../Backups")) {
            mkdir("../Backups");
        }

        foreach ($dbs as $db) {
            if (! file_exists("../Backups/$db")) {
                mkdir("../Backups/$db");
            }

            $file_name = $db . "_" . date("F_d_Y") . "@" . date("g_ia") . uniqid("_", false);
            $folder    = "../Backups/$db/$file_name" . ".sql";
            $d         = exec("mysqldump --user={$user} --password={$pass} --host={$host} {$db} --result-file={$folder}", $output);
            die(json_encode(["success" => true, "message" => $output]));
        }
    }

    // reset password
    elseif ($_GET["url"] == "reset-password") {
        if (! isset($_POST["currentPassword"]) || empty($_POST["currentPassword"])) {
            die(json_encode(["success" => false, "message" => "Current password field is required!"]));
        }

        if (! isset($_POST["newPassword"]) || empty($_POST["newPassword"])) {
            die(json_encode(["success" => false, "message" => "New password field is required!"]));
        }

        if (! isset($_POST["renewPassword"]) || empty($_POST["renewPassword"])) {
            die(json_encode(["success" => false, "message" => "Retype new password field is required!"]));
        }

        $currentPass = $expose->validatePassword($_POST["currentPassword"]);
        $newPass     = $expose->validatePassword($_POST["newPassword"]);
        $renewPass   = $expose->validatePassword($_POST["renewPassword"]);

        if ($newPass !== $renewPass) {
            die(json_encode(["success" => false, "message" => "New password entry mismatched!"]));
        }

        $userDetails = $admin->verifySysUserExistsByID($_SESSION["user"]);
        if (empty($userDetails)) {
            die(json_encode(["success" => false, "message" => "Failed to verify user account!"]));
        }

        $result = $admin->verifyStaffLogin($userDetails[0]["user_name"], $currentPass);
        if (! $result) {
            die(json_encode(["success" => false, "message" => "Incorrect current password!"]));
        }

        $changePassword = $admin->resetUserPassword($_SESSION["user"], $newPass);
        die(json_encode($changePassword));
    }

    //
    elseif ($_GET["url"] == "program-info") {
        if (! isset($_POST["prog"]) || empty($_POST["prog"])) {
            die(json_encode(["success" => false, "message" => "Missing input field"]));
        }
        $rslt = $admin->fetchAllFromProgramByName($_POST["prog"]);
        if (! $rslt) {
            die(json_encode(["success" => false, "message" => "Failed to fetch program's details for this applicant"]));
        }

        die(json_encode(["success" => true, "message" => $rslt]));
    }

    //Departments
    elseif ($_GET["url"] == "fetch-department") {
        if (! isset($_POST["department"]) || empty($_POST["department"])) {
            die(json_encode(["success" => false, "message" => "Department id is required!"]));
        }
        die(json_encode($program->fetch('id', $_POST["department"])));
    } elseif ($_GET["url"] == "add-department") {
        if (! isset($_POST["name"]) || empty($_POST["name"])) {
            die(json_encode(["success" => false, "message" => "Department name is required!"]));
        }
        if (! isset($_POST["hod"]) || empty($_POST["hod"])) {
            die(json_encode(["success" => false, "message" => "Department hod is required!"]));
        }
        die(json_encode($department->add($_POST)));
    } elseif ($_GET["url"] == "update-department") {
        if (! isset($_POST["department"]) || empty($_POST["department"])) {
            die(json_encode(["success" => false, "message" => "Department id is required!"]));
        }
        if (! isset($_POST["name"]) || empty($_POST["name"])) {
            die(json_encode(["success" => false, "message" => "Department name is required!"]));
        }
        if (! isset($_POST["hod"]) || empty($_POST["hod"])) {
            die(json_encode(["success" => false, "message" => "Department hod is required!"]));
        }
        die(json_encode($department->update($_POST)));
    } elseif ($_GET["url"] == "archive-department") {
        if (! isset($_POST["department"]) || empty($_POST["department"])) {
            die(json_encode(["success" => false, "message" => "Department id is required!"]));
        }
        die(json_encode($department->archive($_POST["department"])));
    } elseif ($_GET["url"] == "delete-department") {
        if (! isset($_POST["department"]) || empty($_POST["department"])) {
            die(json_encode(["success" => false, "message" => "Department id is required!"]));
        }
        die(json_encode($department->delete($_POST["department"])));
    } elseif ($_GET["url"] == "total-department") {
        die(json_encode($department->total($_POST["archived"])));
    }

    //staffs
    elseif ($_GET["url"] == "fetch-staff") {
        if (isset($_POST["staff"]) && ! empty($_POST["staff"])) {
            $_POST["key"]   = "number";
            $_POST["value"] = $_POST["staff"];
        } else if (isset($_POST["email"]) && ! empty($_POST["email"])) {
            $_POST["key"]   = "email";
            $_POST["value"] = $_POST["email"];
        } else if (isset($_POST["gender"]) && ! empty($_POST["gender"])) {
            $_POST["key"]   = "gender";
            $_POST["value"] = $_POST["gender"];
        } else if (isset($_POST["role"]) && ! empty($_POST["role"])) {
            $_POST["key"]   = "role";
            $_POST["value"] = $_POST["role"];
        } else if (isset($_POST["department"]) && ! empty($_POST["department"])) {
            $_POST["key"]   = "department";
            $_POST["value"] = $_POST["department"];
        } else {
            $_POST["key"]   = "";
            $_POST["value"] = "";
        }
        die(json_encode($staff->fetch($_POST["key"], $_POST["value"])));
    } elseif ($_GET["url"] == "add-staff") {
        if (! isset($_POST["name"]) || empty($_POST["name"])) {
            die(json_encode(["success" => false, "message" => "Staff name is required!"]));
        }
        if (! isset($_POST["hod"]) || empty($_POST["hod"])) {
            die(json_encode(["success" => false, "message" => "Staff hod is required!"]));
        }
        die(json_encode($staff->add($_POST)));
    } elseif ($_GET["url"] == "update-staff") {
        if (! isset($_POST["number"]) || empty($_POST["number"])) {
            die(json_encode(["success" => false, "message" => "Staff id is required!"]));
        }
        if (! isset($_POST["name"]) || empty($_POST["name"])) {
            die(json_encode(["success" => false, "message" => "Staff name is required!"]));
        }
        if (! isset($_POST["hod"]) || empty($_POST["hod"])) {
            die(json_encode(["success" => false, "message" => "Staff hod is required!"]));
        }
        die(json_encode($staff->update($_POST, $_POST["number"])));
    } elseif ($_GET["url"] == "archive-staff") {
        if (! isset($_POST["number"]) || empty($_POST["number"])) {
            die(json_encode(["success" => false, "message" => "Staff id is required!"]));
        }
        die(json_encode($staff->archive($_POST["number"])));
    } elseif ($_GET["url"] == "unarchive-staff") {
        if (! isset($_POST["number"]) || empty($_POST["number"])) {
            die(json_encode(["success" => false, "message" => "Staff id is required!"]));
        }
        die(json_encode($staff->unarchive($_POST["number"])));
    } elseif ($_GET["url"] == "delete-staff") {
        if (! isset($_POST["number"]) || empty($_POST["number"])) {
            die(json_encode(["success" => false, "message" => "Staff number(s) required!"]));
        }
        die(json_encode($staff->delete($_POST["number"])));
    } elseif ($_GET["url"] == "total-staff") {
        die(json_encode($staff->fetch($_POST["key"], $_POST["value"], $_POST["archived"])));
    }

    //programs
    elseif ($_GET["url"] == "fetch-program") {
        die(json_encode($program->fetch($_POST["key"], $_POST["value"], $_POST["archived"])));
    } elseif ($_GET["url"] == "add-program") {
        die(json_encode($program->add($_POST)));
    } elseif ($_GET["url"] == "update-program") {
        die(json_encode($program->update($_POST, $_POST["program"])));
    } elseif ($_GET["url"] == "archive-program") {
        if (! isset($_POST["program"]) || empty($_POST["program"])) {
            die(json_encode(["success" => false, "message" => "Program id is required!"]));
        }
        die(json_encode($program->archive($_POST["program"])));
    } elseif ($_GET["url"] == "delete-program") {
        die(json_encode($program->delete($_POST["program"])));
    } elseif ($_GET["url"] == "total-program") {
        die(json_encode($program->fetch($_POST["key"], $_POST["value"], $_POST["archived"])));
    }

    //courses
    elseif ($_GET["url"] == "fetch-program-curriculum") {
        if (! isset($_POST["program"]) || empty($_POST["program"])) {
            die(json_encode(["success" => false, "message" => "Program is required!"]));
        }
        die(json_encode(["success" => true, "data" => $secretary->fetchProgramCurriculum($programId = $_POST["program"], $departmentId = $_SESSION["staff"]["department_id"])]));
    } elseif ($_GET["url"] == "fetch-program-classes") {
        if (! isset($_POST["program"]) || empty($_POST["program"])) {
            die(json_encode(["success" => false, "message" => "Program is required!"]));
        }
        die(json_encode(["success" => true, "data" => $secretary->fetchProgramClasses($programId = $_POST["program"])]));
    } elseif ($_GET["url"] == "fetch-program-students") {
        if (! isset($_POST["program"]) || empty($_POST["program"])) {
            die(json_encode(["success" => false, "message" => "Program is required!"]));
        }
        die(json_encode(["success" => true, "data" => $secretary->fetchProgramStudents($programId = $_POST["program"])]));
    } elseif ($_GET["url"] == "fetch-program-courses") {
        if (! isset($_POST["program"]) || empty($_POST["program"])) {
            die(json_encode(["success" => false, "message" => "Program is required!"]));
        }
        die(json_encode(["success" => true, "data" => $secretary->fetchProgramCourses($programId = $_POST["program"], $departmentId = $_SESSION["staff"]["department_id"])]));
    } elseif ($_GET["url"] == "fetch-course") {
        if (isset($_POST["course"]) && ! empty($_POST["course"])) {
            $_POST["key"]   = "code";
            $_POST["value"] = $_POST["course"];
        } else if (isset($_POST["name"]) && ! empty($_POST["name"])) {
            $_POST["key"]   = "name";
            $_POST["value"] = $_POST["name"];
        } else if (isset($_POST["category"]) && ! empty($_POST["category"])) {
            $_POST["key"]   = "category";
            $_POST["value"] = $_POST["category"];
        } else if (isset($_POST["department"]) && ! empty($_POST["department"])) {
            $_POST["key"]   = "department";
            $_POST["value"] = $_POST["department"];
        } else {
            $_POST["key"]   = "";
            $_POST["value"] = "";
        }
        die(json_encode(["success" => true, "data" => $course->fetch($_POST["key"], $_POST["value"])]));
    } elseif ($_GET["url"] == "fetch-assigned-courses-no-deadlines") {
        if (! isset($_POST["department"]) || empty($_POST["department"])) {
            die(json_encode(["success" => false, "message" => "Department is required!"]));
        }
        die(json_encode(["success" => true, "data" => $secretary->fetchAssignedSemesterCoursesWithNoDeadlinesByDepartment($_POST["department"])]));
    } elseif ($_GET["url"] == "fetch-assigned-courses") {
        if (! isset($_POST["department"]) || empty($_POST["department"])) {
            die(json_encode(["success" => false, "message" => "Department is required!"]));
        }
        die(json_encode(["success" => true, "data" => $secretary->fetchAssignedSemesterCoursesByDepartment($_POST["department"])]));
    } elseif ($_GET["url"] == "fetch-semester-courses") {
        if (! isset($_POST["semester"]) || empty($_POST["semester"])) {
            die(json_encode(["success" => false, "message" => "Semester is required!"]));
        }
        die(json_encode(["success" => true, "data" => $secretary->fetchSemesterCourses($_POST["semester"])]));
    } elseif ($_GET["url"] == "add-course") {
        if (! isset($_POST["courseCode"]) || empty($_POST["courseCode"])) {
            die(json_encode(["success" => false, "message" => "Course code is required!"]));
        }
        if (! isset($_POST["courseName"]) || empty($_POST["courseName"])) {
            die(json_encode(["success" => false, "message" => "Course name is required!"]));
        }
        if (! isset($_POST["creditHours"]) || empty($_POST["creditHours"])) {
            die(json_encode(["success" => false, "message" => "Course credit hours is required!"]));
        }
        if (! isset($_POST["contactHours"]) || empty($_POST["contactHours"])) {
            die(json_encode(["success" => false, "message" => "Course contact hours is required!"]));
        }
        if (! isset($_POST["semester"]) || empty($_POST["semester"])) {
            die(json_encode(["success" => false, "message" => "Course semester is required!"]));
        }
        if (! isset($_POST["level"]) || empty($_POST["level"])) {
            die(json_encode(["success" => false, "message" => "Course level is required!"]));
        }
        if (! isset($_POST["category"]) || empty($_POST["category"])) {
            die(json_encode(["success" => false, "message" => "Course category is required!"]));
        }
        if (! isset($_POST["departmentId"]) || empty($_POST["departmentId"])) {
            die(json_encode(["success" => false, "message" => "Course department is required!"]));
        }

        die(json_encode($course->add($_POST)));
    } elseif ($_GET["url"] == "edit-course") {
        if (! isset($_POST["courseCode"]) || empty($_POST["courseCode"])) {
            die(json_encode(["success" => false, "message" => "Course code is required!"]));
        }
        if (! isset($_POST["courseName"]) || empty($_POST["courseName"])) {
            die(json_encode(["success" => false, "message" => "Course name is required!"]));
        }
        if (! isset($_POST["creditHours"]) || empty($_POST["creditHours"])) {
            die(json_encode(["success" => false, "message" => "Course credit hours is required!"]));
        }
        if (! isset($_POST["contactHours"]) || empty($_POST["contactHours"])) {
            die(json_encode(["success" => false, "message" => "Course contact hours is required!"]));
        }
        if (! isset($_POST["semester"]) || empty($_POST["semester"])) {
            die(json_encode(["success" => false, "message" => "Course semester is required!"]));
        }
        if (! isset($_POST["level"]) || empty($_POST["level"])) {
            die(json_encode(["success" => false, "message" => "Course level is required!"]));
        }
        if (! isset($_POST["category"]) || empty($_POST["category"])) {
            die(json_encode(["success" => false, "message" => "Course category is required!"]));
        }
        if (! isset($_POST["departmentId"]) || empty($_POST["departmentId"])) {
            die(json_encode(["success" => false, "message" => "Course department is required!"]));
        }
        die(json_encode($course->update($_POST)));
    } elseif ($_GET["url"] == "archive-course") {
        if (! isset($_POST["courseCode"]) || empty($_POST["courseCode"])) {
            die(json_encode(["success" => false, "message" => "Course code is required!"]));
        }
        die(json_encode($course->archive($_POST["courseCode"])));
    } elseif ($_GET["url"] == "delete-course") {
        if (! isset($_POST["code"]) || empty($_POST["code"])) {
            die(json_encode(["success" => false, "message" => "Course code is required!"]));
        }
        die(json_encode($course->archive($_POST["code"])));
    } elseif ($_GET["url"] == "total-course") {
        die(json_encode($program->fetch($_POST["key"], $_POST["value"], $_POST["archived"])));
    } elseif ($_GET["url"] == "upload-courses") {
        if (! isset($_FILES["courseFile"]) || empty($_FILES["courseFile"])) {
            die(json_encode(["success" => false, "message" => "Invalid request. An Excel file is required!"]));
        }

        if ($_FILES["courseFile"]['error']) {
            die(json_encode(["success" => false, "message" => "Failed to upload file!"]));
        }

        if ($_FILES["courseFile"]['size'] > 2048000) {
            die(json_encode(["success" => false, "message" => "File size is too large!"]));
        }

        if ($_FILES["courseFile"]['type'] != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
            die(json_encode(["success" => false, "message" => "Invalid file type!"]));
        }

        $excelData = new UploadExcelDataController($_FILES["courseFile"], 2, 0, "course");
        $result    = $excelData->run($_SESSION["staff"]["department_id"]);
        die(json_encode($result));
    } elseif ($_GET["url"] == "assign-course") {
        if (! isset($_POST["action"]) || empty($_POST["action"])) {
            die(json_encode(["success" => false, "message" => "Action is required!"]));
        }
        switch ($_POST["action"]) {
            case 'lecturer':
                if (! isset($_POST["lecturer"]) || empty($_POST["lecturer"])) {
                    die(json_encode(["success" => false, "message" => "Lecturer is required!"]));
                }
                break;

            case 'student':
                if (! isset($_POST["student"]) || empty($_POST["student"])) {
                    die(json_encode(["success" => false, "message" => "Student is required!"]));
                }
                break;

            case 'class':
                if (! isset($_POST["class"]) || empty($_POST["class"])) {
                    die(json_encode(["success" => false, "message" => "Class is required!"]));
                }
                break;

            case 'program':
                if (! isset($_POST["program"]) || empty($_POST["program"])) {
                    die(json_encode(["success" => false, "message" => "Program is required!"]));
                }
                break;

            default:
                die(json_encode(["success" => false, "message" => "Invalid action requested!"]));
                break;
        }

        if (! isset($_POST["courses"]) || empty($_POST["courses"])) {
            die(json_encode(["success" => false, "message" => "Select at least one course!"]));
        }

        if ($_POST["action"] !== "program") {
            if (! isset($_POST["semester"]) || empty($_POST["semester"])) {
                die(json_encode(["success" => false, "message" => "Semester is required!"]));
            }
            if (! isset($_POST["department"]) || empty($_POST["department"])) {
                die(json_encode(["success" => false, "message" => "Department is required!"]));
            }
        }

        $notes = isset($_POST["notes"]) ? $_POST["notes"] : null;

        die(json_encode($secretary->assignCourses($_POST)));
    } elseif ($_GET["url"] == "archive-curriculum-course") {
        if (! isset($_POST["program"]) || empty($_POST["program"])) {
            die(json_encode(["success" => false, "message" => "Program is required!"]));
        }

        if (! isset($_POST["course"]) || empty($_POST["course"])) {
            die(json_encode(["success" => false, "message" => "Course is required!"]));
        }
        $result = $program->archiveCurriculumCourse($_POST["program"], $_POST["course"]);
        die(json_encode($result));
    }

    // Class
    elseif ($_GET["url"] == "fetch-class") {
        if (isset($_POST["code"]) && ! empty($_POST["code"])) {
            $_POST["key"]   = "code";
            $_POST["value"] = $_POST["code"];
        } else if (isset($_POST["department"]) && ! empty($_POST["department"])) {
            $_POST["key"]   = "department";
            $_POST["value"] = $_POST["department"];
        } else if (isset($_POST["program"]) && ! empty($_POST["program"])) {
            $_POST["key"]   = "program";
            $_POST["value"] = $_POST["program"];
        } else {
            $_POST["key"]   = "";
            $_POST["value"] = "";
        }
        die(json_encode($class->fetch($_POST["key"], $_POST["value"], $_POST["archived"] ?? false)));
    } elseif ($_GET["url"] == "assign-class") {

        if (! isset($_POST["action"]) || empty($_POST["action"])) {
            die(json_encode(["success" => false, "message" => "Action is required!"]));
        }

        switch ($_POST["action"]) {
            case 'lecturer':
                if (! isset($_POST["lecturer"]) || empty($_POST["lecturer"])) {
                    die(json_encode(["success" => false, "message" => "Lecturer is required!"]));
                }
                break;

            case 'student':
                if (! isset($_POST["students"]) || empty($_POST["students"])) {
                    die(json_encode(["success" => false, "message" => "Student is required!"]));
                }
                break;

            default:
                die(json_encode(["success" => false, "message" => "Invalid action requested!"]));
                break;
        }

        if (! isset($_POST["code"]) || empty($_POST["code"])) {
            die(json_encode(["success" => false, "message" => "Select at least one class!"]));
        }

        $notes = isset($_POST["notes"]) ? $_POST["notes"] : null;

        die(json_encode($class->assign($_POST)));
    } elseif ($_GET["url"] == "add-class") {
        if (! isset($_POST["code"]) || empty($_POST["code"])) {
            die(json_encode(["success" => false, "message" => "Class code is required!"]));
        }
        if (! isset($_POST["program"]) || empty($_POST["program"])) {
            die(json_encode(["success" => false, "message" => "Program is required!"]));
        }
        if (! isset($_POST["category"]) || empty($_POST["category"])) {
            die(json_encode(["success" => false, "message" => "Category is required!"]));
        }
        if (! isset($_POST["year"]) || empty($_POST["year"])) {
            die(json_encode(["success" => false, "message" => "Year is required!"]));
        }
        die(json_encode($class->add($_POST)));
    } elseif ($_GET["url"] == "update-class") {
        if (! isset($_POST["oldCode"]) || empty($_POST["oldCode"])) {
            die(json_encode(["success" => false, "message" => "Old class code is required!"]));
        }
        if (! isset($_POST["code"]) || empty($_POST["code"])) {
            die(json_encode(["success" => false, "message" => "Class code is required!"]));
        }
        if (! isset($_POST["program"]) || empty($_POST["program"])) {
            die(json_encode(["success" => false, "message" => "Program is required!"]));
        }
        if (! isset($_POST["category"]) || empty($_POST["category"])) {
            die(json_encode(["success" => false, "message" => "Category is required!"]));
        }
        if (! isset($_POST["year"]) || empty($_POST["year"])) {
            die(json_encode(["success" => false, "message" => "Year is required!"]));
        }
        die(json_encode($class->update($_POST)));
    } elseif ($_GET["url"] == "archive-class") {
        if (! isset($_POST["code"]) || empty($_POST["code"])) {
            die(json_encode(["success" => false, "message" => "Class code is required!"]));
        }
        die(json_encode($class->archive($_POST["code"])));
    } elseif ($_GET["url"] == "unarchive-class") {
        if (! isset($_POST["code"]) || empty($_POST["code"])) {
            die(json_encode(["success" => false, "message" => "Class code is required!"]));
        }
        die(json_encode($class->unarchive($_POST["code"])));
    } elseif ($_GET["url"] == "delete-class") {
        if (! isset($_POST["code"]) || empty($_POST["code"])) {
            die(json_encode(["success" => false, "message" => "Class code is required!"]));
        }
        die(json_encode($class->delete($_POST["code"])));
    } elseif ($_GET["url"] == "total-class") {
        die(json_encode($class->fetch($_POST["key"], $_POST["value"], $_POST["archived"] ?? false)));
    }

    //students
    elseif ($_GET["url"] == "fetch-student") {
        if (isset($_POST["indexNumber"]) && ! empty($_POST["indexNumber"])) {
            $_POST["key"]   = "index_number";
            $_POST["value"] = $_POST["indexNumber"];
        } else if (isset($_POST["class"]) && ! empty($_POST["class"])) {
            $_POST["key"]   = "class";
            $_POST["value"] = $_POST["class"];
        } else if (isset($_POST["department"]) && ! empty($_POST["department"])) {
            $_POST["key"]   = "department";
            $_POST["value"] = $_POST["department"];
        } else if (isset($_POST["program"]) && ! empty($_POST["program"])) {
            $_POST["key"]   = "program";
            $_POST["value"] = $_POST["program"];
        } else {
            $_POST["key"]   = "";
            $_POST["value"] = "";
        }
        die(json_encode($student->fetch($_POST["key"], $_POST["value"], $_POST["archived"] ?? false)));
    } elseif ($_GET["url"] == "update-student") {
        die(json_encode($student->update($_POST)));
    } elseif ($_GET["url"] == "archive-student") {
        if (! isset($_POST["indexNumber"]) || empty($_POST["indexNumber"])) {
            die(json_encode(["success" => false, "message" => "Student's index number is required!"]));
        }
        die(json_encode($student->archive($_POST["indexNumber"])));
    } elseif ($_GET["url"] == "unarchive-student") {
        if (! isset($_POST["indexNumber"]) || empty($_POST["indexNumber"])) {
            die(json_encode(["success" => false, "message" => "Student's index number is required!"]));
        }
        die(json_encode($student->unarchive($_POST["indexNumber"])));
    } elseif ($_GET["url"] == "delete-student") {
        if (! isset($_POST["indexNumber"]) || empty($_POST["indexNumber"])) {
            die(json_encode(["success" => false, "message" => "Student's index number is required!"]));
        }
        die(json_encode($student->delete($_POST["indexNumber"])));
    } elseif ($_GET["url"] == "total-student") {
        //die(json_encode($student->total($_POST)));
    } elseif ($_GET["url"] == "fetch-student-grades") {
        if (! isset($_POST["indexNumber"]) || empty($_POST["indexNumber"])) {
            die(json_encode(["success" => false, "message" => "Student's index number is required!"]));
        }
        if (! isset($_POST["semester"]) || empty($_POST["semester"])) {
            die(json_encode(["success" => false, "message" => "Semester is required!"]));
        }
        die(json_encode($student->fetchStudentGrades($_POST["indexNumber"], $_POST["semester"])));
    }

    // Deadline
    elseif ($_GET["url"] == "add-deadline") {
        if (! isset($_POST["department"]) || empty($_POST["department"])) {
            die(json_encode(["success" => false, "message" => "Department required!"]));
        }
        if (! isset($_POST["semester"]) || empty($_POST["semester"])) {
            die(json_encode(["success" => false, "message" => "Semester required!"]));
        }
        if (! isset($_POST["courses"]) || empty($_POST["courses"])) {
            die(json_encode(["success" => false, "message" => "Course(s) required!"]));
        }
        if (! isset($_POST["lecturer"]) || empty($_POST["lecturer"])) {
            die(json_encode(["success" => false, "message" => "Lecturer required!"]));
        }
        if (! isset($_POST["date"]) || empty($_POST["date"])) {
            die(json_encode(["success" => false, "message" => "Date required!"]));
        }
        if (! isset($_POST["note"]) || empty($_POST["note"])) {
            $note = null;
        } else {
            $note = $_POST["note"];
        }
        die(json_encode($secretary->assignCourseSubmissionDeadline($_POST)));
    } elseif ($_GET["url"] == "edit-deadline") {
        if (! isset($_POST["deadline"]) || empty($_POST["deadline"])) {
            die(json_encode(["success" => false, "message" => "Deadline id required!"]));
        }
        if (! isset($_POST["courses"]) || empty($_POST["courses"])) {
            die(json_encode(["success" => false, "message" => "Course(s) required!"]));
        }
        if (! isset($_POST["date"]) || empty($_POST["date"])) {
            die(json_encode(["success" => false, "message" => "Date of deadline required!"]));
        }
        if (! isset($_POST["note"]) || empty($_POST["note"])) {
            $note = null;
        } else {
            $note = $_POST["note"];
        }
        die(json_encode($deadline->add($_POST["deadline"], $_POST["semester"], $_POST["department"])));
    }

    // Upload Results
    elseif ($_GET["url"] == "upload-results") {

        if (! isset($_FILES["resultsFile"]) || empty($_FILES["resultsFile"])) {
            die(json_encode(["success" => false, "message" => "Invalid request. An Excel file is required!"]));
        }

        if ($_FILES["resultsFile"]['error']) {
            die(json_encode(["success" => false, "message" => "Failed to upload file!"]));
        }

        if ($_FILES["resultsFile"]['size'] > 5242880) {
            die(json_encode(["success" => false, "message" => "File size is too large!"]));
        }

        if ($_FILES["resultsFile"]['type'] != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
            die(json_encode(["success" => false, "message" => "Invalid file type!"]));
        }

        if (! isset($_POST["semester"]) || empty($_POST["semester"])) {
            die(json_encode(["success" => false, "message" => "Semester is required!"]));
        }

        if (! isset($_POST["class"]) || empty($_POST["class"])) {
            die(json_encode(["success" => false, "message" => "Class is required!"]));
        }

        if (! isset($_POST["course"]) || empty($_POST["course"])) {
            die(json_encode(["success" => false, "message" => "Course is required!"]));
        }

        if (! isset($_POST["staffId"]) || empty($_POST["staffId"])) {
            die(json_encode(["success" => false, "message" => "Staff number is required!"]));
        }

        if (! isset($_POST["projectBased"]) || empty($_POST["projectBased"])) {
            die(json_encode(["success" => false, "message" => "Course upload type is required!"]));
        }

        if (! isset($_POST["academicYear"]) || empty($_POST["academicYear"])) {
            die(json_encode(["success" => false, "message" => "Academic year is required!"]));
        }

        if (! isset($_POST["examScoreWeight"]) || empty($_POST["examScoreWeight"])) {
            die(json_encode(["success" => false, "message" => "Exam score weight is required!"]));
        }

        if (! isset($_POST["projectScoreWeight"])) {
            die(json_encode(["success" => false, "message" => "Project score weight is required!"]));
        }

        if (! isset($_POST["assessmentScoreWeight"]) || empty($_POST["assessmentScoreWeight"])) {
            die(json_encode(["success" => false, "message" => "Assessment score weight is required!"]));
        }

        $notes = isset($_POST["notes"]) ? $_POST["notes"] : null;

        $data = [
            "semester"              => $_POST["semester"],
            "class"                 => $_POST["class"],
            "course"                => $_POST["course"],
            "staffId"               => $_POST["staffId"],
            "projectBased"          => $_POST["projectBased"],
            "academicYear"          => $_POST["academicYear"],
            "examScoreWeight"       => $_POST["examScoreWeight"],
            "projectScoreWeight"    => $_POST["projectScoreWeight"],
            "assessmentScoreWeight" => $_POST["assessmentScoreWeight"],
            "notes"                 => $notes
        ];

        $excelData = new UploadExcelDataController($_FILES["resultsFile"], 13, 0, "result");
        $result    = $excelData->run($_SESSION["staff"]["department_id"], $data);
        die(json_encode($result));
    }

    // fetch semester course results headers
    elseif ($_GET["url"] == "fetch-semester-course-results-headers") {
        if (! isset($_POST["semester"]) || empty($_POST["semester"])) {
            die(json_encode(["success" => false, "message" => "Semester is required!"]));
        }

        if (! isset($_POST["course"]) || empty($_POST["course"])) {
            die(json_encode(["success" => false, "message" => "Course is required!"]));
        }

        if (! isset($_POST["class"]) || empty($_POST["class"])) {
            die(json_encode(["success" => false, "message" => "Class is required!"]));
        }

        die(json_encode($secretary->fetchSemesterCourseResultsHeaders($_POST["semester"], $_POST["course"], $_POST["class"])));
    }


    // All PUT request will be sent here
} else if ($_SERVER['REQUEST_METHOD'] == "PUT") {
    parse_str(file_get_contents("php://input"), $_PUT);

    // All DELETE request will be sent here
} else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
    parse_str(file_get_contents("php://input"), $_DELETE);
} else {
    http_response_code(405);
}
