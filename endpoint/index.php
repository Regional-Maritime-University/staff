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

        $_SESSION['staffLoginSuccess']  = true;
        $_SESSION['staff']              = $result["data"];

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

    //programs

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
        die(json_encode(["success" => true, "data" => $staff->fetch($_POST["key"], $_POST["value"])]));
    } elseif ($_GET["url"] == "add-staff") {
        if (! isset($_POST["name"]) || empty($_POST["name"])) {
            die(json_encode(["success" => false, "message" => "Staff name is required!"]));
        }
        if (! isset($_POST["hod"]) || empty($_POST["hod"])) {
            die(json_encode(["success" => false, "message" => "Staff hod is required!"]));
        }
        die(json_encode($staff->add($_POST)));
    } elseif ($_GET["url"] == "update-staff") {
        if (! isset($_POST["staff"]) || empty($_POST["staff"])) {
            die(json_encode(["success" => false, "message" => "Staff id is required!"]));
        }
        if (! isset($_POST["name"]) || empty($_POST["name"])) {
            die(json_encode(["success" => false, "message" => "Staff name is required!"]));
        }
        if (! isset($_POST["hod"]) || empty($_POST["hod"])) {
            die(json_encode(["success" => false, "message" => "Staff hod is required!"]));
        }
        die(json_encode($staff->update($_POST, $_POST["staff"])));
    } elseif ($_GET["url"] == "archive-staff") {
        if (! isset($_POST["staff"]) || empty($_POST["staff"])) {
            die(json_encode(["success" => false, "message" => "Staff id is required!"]));
        }
        die(json_encode($staff->archive($_POST["staff"])));
    } elseif ($_GET["url"] == "delete-staff") {
        if (! isset($_POST["staff"]) || empty($_POST["staff"])) {
            die(json_encode(["success" => false, "message" => "Staff id is required!"]));
        }
        die(json_encode($staff->delete($_POST["staff"])));
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
        die(json_encode($program->archive($_POST["program"])));
    } elseif ($_GET["url"] == "delete-program") {
        die(json_encode($program->delete($_POST["program"])));
    } elseif ($_GET["url"] == "total-program") {
        die(json_encode($program->fetch($_POST["key"], $_POST["value"], $_POST["archived"])));
    }

    //courses

    elseif ($_GET["url"] == "fetch-course") {
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
    } elseif ($_GET["url"] == "fetch-assigned-courses") {
        if (! isset($_POST["department"]) || empty($_POST["department"])) {
            die(json_encode(["success" => false, "message" => "Department is required!"]));
        }
        die(json_encode(["success" => true, "data" => $secretary->fetchAssignedSemesterCoursesWithNoDeadlinesByDepartment($_POST["department"])]));
    }
    //add
    elseif ($_GET["url"] == "add-course") {
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
    }
    //edit
    elseif ($_GET["url"] == "edit-course") {
        if (! isset($_POST["code"]) || empty($_POST["code"])) {
            die(json_encode(["success" => false, "message" => "Course code is required!"]));
        }
        if (! isset($_POST["name"]) || empty($_POST["name"])) {
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
        if (! isset($_POST["department"]) || empty($_POST["department"])) {
            die(json_encode(["success" => false, "message" => "Course department is required!"]));
        }
        die(json_encode($course->update($_POST)));
    }
    //archive
    elseif ($_GET["url"] == "archive-course") {
        if (! isset($_POST["code"]) || empty($_POST["code"])) {
            die(json_encode(["success" => false, "message" => "Course code is required!"]));
        }
        die(json_encode($course->archive($_POST["code"])));
    }
    //delete
    elseif ($_GET["url"] == "delete-course") {
        if (! isset($_POST["code"]) || empty($_POST["code"])) {
            die(json_encode(["success" => false, "message" => "Course code is required!"]));
        }
        die(json_encode($course->archive($_POST["code"])));
    }
    //total
    elseif ($_GET["url"] == "total-course") {
        die(json_encode($program->fetch($_POST["key"], $_POST["value"], $_POST["archived"])));
    }
    //upload
    elseif ($_GET["url"] == "upload-courses") {
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

        $excelData = new UploadExcelDataController($_FILES["courseFile"], 2, 0);
        $result    = $excelData->run($_SESSION["staff"]["department_id"]);
        die(json_encode($result));
    } elseif ($_GET["url"] == "assign-course") {
        if (! isset($_POST["course"]) || empty($_POST["course"])) {
            die(json_encode(["success" => false, "message" => "Course is required!"]));
        }
        if (! isset($_POST["lecturer"]) || empty($_POST["lecturer"])) {
            die(json_encode(["success" => false, "message" => "Lecturer is required!"]));
        }
        if (! isset($_POST["semester"]) || empty($_POST["semester"])) {
            die(json_encode(["success" => false, "message" => "Semester is required!"]));
        }
        if (! isset($_POST["department"]) || empty($_POST["department"])) {
            die(json_encode(["success" => false, "message" => "Department is required!"]));
        }
        $notes = isset($_POST["notes"]) ? $_POST["notes"] : null;
        die(json_encode($secretary->assignCourseToLecturer($_POST["course"], $_POST["lecturer"], $_POST["semester"], $_POST["department"], $notes)));
    }

    //students
    elseif ($_GET["url"] == "fetch-student") {
        die(json_encode($program->fetch($_POST["key"], $_POST["value"], $_POST["archived"])));
    } elseif ($_GET["url"] == "update-student") {
        die(json_encode($student->update($_POST)));
    }

    // Deadline
    elseif ($_GET["url"] == "add-deadline") {
        if (! isset($_POST["lecturer"]) || empty($_POST["lecturer"])) {
            die(json_encode(["success" => false, "message" => "Semester required!"]));
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
        die(json_encode($deadline->add($_POST)));
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

    // All PUT request will be sent here
} else if ($_SERVER['REQUEST_METHOD'] == "PUT") {
    parse_str(file_get_contents("php://input"), $_PUT);

    // All DELETE request will be sent here
} else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
    parse_str(file_get_contents("php://input"), $_DELETE);
} else {
    http_response_code(405);
}
