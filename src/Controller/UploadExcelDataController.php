<?php

namespace Src\Controller;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Src\Core\CourseCategory;
use Src\System\DatabaseMethods;

class UploadExcelDataController
{
    private $dm = null;

    private $fileObj = array();
    private $startRow = null;
    private $endRow = null;
    private $targetPath = null;
    private $errorsEncountered = 0;
    private $successEncountered = 0;

    private $db = null;
    private $user = null;
    private $pass = null;
    private $type = null;

    public function __construct($fileObj, $startRow = 1, $endRow = 0, $type = "course")
    {
        $this->db   = getenv('DB_DATABASE');
        $this->user = getenv('DB_USERNAME');
        $this->pass = getenv('DB_PASSWORD');

        $this->fileObj = $fileObj;
        $this->startRow = (int) $startRow;
        $this->endRow = (int) $endRow;
        $this->type = $type;
        $this->dm = new DatabaseMethods($this->db, $this->user, $this->pass);
    }

    public function saveCourseDataFile($data = null)
    {
        $allowedFileType = [
            'application/vnd.ms-excel',
            'text/xls',
            'text/xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        if (!in_array($this->fileObj["type"], $allowedFileType)) {
            return array("success" => false, "message" => "Invalid file type. Please choose an excel file!");
        }

        if ($this->fileObj['error'] == UPLOAD_ERR_OK) {

            // Create a unique file name
            $name = time() . '.xlsx';

            if ($this->type == "course") {
                // Create the full path to the file
                if (!is_dir(UPLOAD_DIR . "/documents/")) {
                    mkdir(UPLOAD_DIR . "/documents/", 0777, true);
                }
            } else if ($this->type == "result") {
                // Create the full path to the file
                if (!is_dir(UPLOAD_DIR . "/results/")) {
                    mkdir(UPLOAD_DIR . "/results/", 0777, true);
                }
            } else {
                return array("success" => false, "message" => "Invalid type specified for file upload!");
            }

            // Set the target path
            if ($this->type == "course") {
                $name = "course_" . $name;
                $this->targetPath = UPLOAD_DIR . "/documents/" . $name;
            } else if ($this->type == "result") {
                $name = "result_" . $data["class"] . "_" . $data["course"] . "_semester_" . $data["semester"] . "_" . $data["academicYear"] . $name;
                $this->targetPath = UPLOAD_DIR . "/results/" . $name;
            } else {
                return array("success" => false, "message" => "Invalid type specified for file upload! Please specify either 'course' or 'result'. 2");
            }

            // Delete file if exsists
            if (file_exists($this->targetPath)) {
                unlink($this->targetPath);
            }

            // Move the file to the target directory
            if (!move_uploaded_file($this->fileObj['tmp_name'], $this->targetPath)) {
                return array("success" => false, "message" => "Failed to upload file!");
            }

            if ($this->type == "result") {
                $query = "INSERT INTO `exam_results` (`fk_class`, `fk_semester `, `fk_staff`, `fk_course`, `project_based`, `notes`, `file_name`, `status`) 
                                          VALUES (:cs, :sm, :sf, :c, :pb, :n, :fn, 'pending')";
                $params = array(
                    ":cs" => $data["class"],
                    ":sm" => $data["semester"],
                    ":sf" => $data["staffId"],
                    ":c" => $data["course"],
                    ":pb" => $data["projectBased"] ? 1 : 0,
                    ":n" => $data["notes"],
                    ":fn" => $this->targetPath,
                );

                if ($this->dm->inputData($query, $params)) {
                    return array("success" => true, "message" => "File upload successful!");
                } else {
                    return array("success" => false, "message" => "Failed to upload file and save exam results!");
                }
            }
            return array("success" => true, "message" => "File upload successful!");
        }
        return array("success" => false, "message" => "Error: Invalid file object!");
    }

    public function saveResultDataFile($data = null)
    {
        $allowedFileType = [
            'application/vnd.ms-excel',
            'text/xls',
            'text/xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        if (!in_array($this->fileObj["type"], $allowedFileType)) {
            return array("success" => false, "message" => "Invalid file type. Please choose an excel file!");
        }

        if ($this->fileObj['error'] == UPLOAD_ERR_OK) {

            // Create a unique file name
            $ext = '.xlsx';

            if ($this->type == "result") {
                // Create the full path to the file
                if (!is_dir(UPLOAD_DIR . "/results/")) {
                    mkdir(UPLOAD_DIR . "/results/", 0777, true);
                }
            } else {
                return array("success" => false, "message" => "Invalid type specified for file upload! Please specify 'result'.");
            }

            // Set the target path
            $file_name = "result_" . $data["class"] . "_" . $data["course"] . "_semester_" . $data["semester"] . "_" . $data["academicYear"] . $ext;
            $this->targetPath = UPLOAD_DIR . "/results/" . $file_name;

            // Delete file if exsists
            if (file_exists($this->targetPath)) {
                unlink($this->targetPath);
            }

            // Move the file to the target directory
            if (!move_uploaded_file($this->fileObj['tmp_name'], $this->targetPath)) {
                return array("success" => false, "message" => "Failed to upload file!");
            }

            // Check if results already uploaded using class code, semester id, and course code
            $inputQuery = "";

            $existingResultQuery = "SELECT `id` FROM `exam_results` WHERE `fk_class` = :cs AND `fk_semester` = :sm AND `fk_course` = :cr";
            $existingResult = $this->dm->getData($existingResultQuery, array(":cs" => $data["class"], ":sm" => $data["semester"], ":cr" => $data["course"]));

            if (!empty($existingResult)) {
                $inputQuery = "UPDATE `exam_results` SET 
                                `fk_staff` = :sf, 
                                `exam_score_weight` = :esw, 
                                `project_score_weight` = :psw, 
                                `assessment_score_weight` = :asw, 
                                `project_based` = :pb, 
                                `notes` = :nt, 
                                `file_name` = :fn, 
                                `status` = 'pending' 
                            WHERE `fk_class` = :cs AND `fk_semester` = :sm AND `fk_course` = :cr";
            } else {
                $inputQuery = "INSERT INTO `exam_results` 
                                (`fk_class`, `fk_semester`, `fk_staff`, `fk_course`, `exam_score_weight`, `project_score_weight`, `assessment_score_weight`, `project_based`, `notes`, `file_name`, `status`) 
                            VALUES (:cs, :sm, :sf, :cr, :esw, :psw, :asw, :pb, :nt, :fn, 'pending')";
            }

            $params = array(
                ":cs" => $data["class"],
                ":sm" => $data["semester"],
                ":sf" => $data["staffId"],
                ":cr" => $data["course"],
                ":esw" => $data["examScoreWeight"],
                ":psw" => $data["projectScoreWeight"],
                ":asw" => $data["assessmentScoreWeight"],
                ":pb" => $data["projectBased"] ? 1 : 0,
                ":nt" => $data["notes"],
                ":fn" => $file_name,
            );

            if ($this->dm->inputData($inputQuery, $params)) {
                return array("success" => true, "message" => "File upload successful!");
            } else {
                return array("success" => false, "message" => "Failed to upload file and save exam results!");
            }
        }
        return array("success" => false, "message" => "Error: Invalid file object!");
    }

    public function extractCourseExcelData()
    {
        $Reader = new Xlsx();
        $spreadSheet = $Reader->load($this->targetPath);
        $excelSheet = $spreadSheet->getActiveSheet();
        $spreadSheetArray = $excelSheet->toArray();

        if ($this->endRow == 0) $this->endRow = count($spreadSheetArray);
        if ($this->startRow > 1) $this->startRow -= 1;

        $dataset = array();

        $courseCategories = (new CourseCategory($this->db, $this->user, $this->pass))->fetch();
        if (empty($courseCategories)) {
            $this->errorsEncountered += 1;
            return array(
                "success" => false,
                "message" => "Ooops! Couldn't fetch course categories!"
            );
        }

        $courseCategories = array_column($courseCategories, "id", "name");

        for ($i = $this->startRow; $i <= $this->endRow - 1; $i++) {
            $code = $spreadSheetArray[$i][0];
            $name = $spreadSheetArray[$i][1];
            $credit = $spreadSheetArray[$i][2];
            $contact = $spreadSheetArray[$i][3];
            $semester = $spreadSheetArray[$i][4];
            $level = $spreadSheetArray[$i][5];
            $category = $spreadSheetArray[$i][6];

            if (!$category) {
                $this->errorsEncountered += 1;
                return array(
                    "success" => false,
                    "message" => "Ooops! Invalid course category!"
                );
            }

            $category = strtolower($category);

            if (in_array($category, $courseCategories)) {
                $category = array_search($category, $courseCategories);
            } else {
                $this->errorsEncountered += 1;
                return array(
                    "success" => false,
                    "message" => "Ooops! Invalid course category!"
                );
            }

            array_push($dataset, array(
                "code" => $code,
                "name" => $name,
                "credit_hours" => $credit,
                "contact_hours" => $contact,
                "semester" => $semester,
                "level" => $level,
                "category" => $category,
            ));
        }

        return $dataset;
    }

    public function extractResultExcelData($data)
    {
        $Reader = new Xlsx();
        $spreadSheet = $Reader->load($this->targetPath);
        $excelSheet = $spreadSheet->getActiveSheet();
        $spreadSheetArray = $excelSheet->toArray();

        if ($this->endRow == 0) $this->endRow = count($spreadSheetArray);
        if ($this->startRow > 1) $this->startRow -= 1;

        $dataset = array();

        for ($i = $this->startRow; $i <= $this->endRow - 1; $i++) {
            $studentId = $spreadSheetArray[$i][0];
            $examScore = $spreadSheetArray[$i][11];
            $projectScore = $data["projectBased"] ? $spreadSheetArray[$i][12] : 0;
            $assessmentScore = $data["projectBased"] ? $spreadSheetArray[$i][13] : $spreadSheetArray[$i][12];
            // $final_score = $data["projectBased"] ? $spreadSheetArray[$i][14] : $spreadSheetArray[$i][13];

            if (!$studentId || !$examScore || !$assessmentScore) {
                continue;
            }

            array_push($dataset, array(
                "student_id" => $studentId,
                "exam_score" => $examScore,
                "project_score" => $projectScore,
                "assessment_score" => $assessmentScore,
                // "final_score" => $final_score,
            ));
        }

        return $dataset;
    }

    public function saveCourseData($course, $departmentID)
    {
        if (empty($course)) {
            $this->errorsEncountered += 1;
            return array(
                "success" => false,
                "message" => "Ooops! Empty course data received."
            );
        }

        $selectQuery = "SELECT * FROM `course` WHERE `code` = :c";
        $courseData = $this->dm->getData($selectQuery, array(":c" => $course["code"]));
        if (!empty($courseData)) {
            $this->errorsEncountered += 1;
            return array(
                "success" => false,
                "message" => "{$courseData[0]["name"]} with code {$courseData[0]["code"]} already exist in database!"
            );
        }

        $insertQuery = "INSERT INTO `course` (`code`, `name`, `credit_hours`, `contact_hours`, `semester`, `level`, `fk_category`, `fk_department`) 
                        VALUES (:c, :n, :ch, :th, :s, :l, :fkc, :fkd)";
        $params = array(
            ":c" => $course["code"],
            ":n" => $course["name"],
            ":ch" => $course["credit_hours"],
            ":th" => $course["contact_hours"],
            ":s" => $course["semester"],
            ":l" => $course["level"],
            ":fkc" => strtolower($course["category"]) == "compulsory" ? 1 : (strtolower($course["category"]) == "elective" ? 2 : 3),
            ":fkd" => $departmentID
        );

        if ($this->dm->inputData($insertQuery, $params)) {
            return array(
                "success" => true,
                "message" => "Successfully added course!"
            );
        } else {
            $this->errorsEncountered += 1;
            return array(
                "success" => false,
                "message" => "Ooops! Server error: failed to add course!"
            );
        }
    }

    public function run($departmentID, $data = null)
    {

        //extraxt data into array
        if ($this->type == "course") {
            // save file to uploads folder
            $file_upload_msg = $this->saveCourseDataFile($data);
            if (!$file_upload_msg["success"]) return $file_upload_msg;

            $extracted_data = $this->extractCourseExcelData();
            if (empty($extracted_data)) return $extracted_data;

            $error_list = [];
            $output = [];
            $count = 0;

            if (!$departmentID) {
                return array(
                    "success" => false,
                    "message" => "{$extracted_data[0]["department"]} department doesn't exist!"
                );
            } else {
                // add results for each applicant to db
                foreach ($extracted_data as $course) {
                    $result = $this->saveCourseData($course, $departmentID);
                    if (!$result["success"]) array_push($error_list, $result["message"]);
                    if ($result["success"]) $this->successEncountered += 1;
                    $count++;
                }
            }

            $output = array(
                "success" => $this->errorsEncountered == 0 ? true : ($this->successEncountered > 0 ? true : false),
                "message" => "Successfully added {$this->successEncountered} courses and {$this->errorsEncountered} errors encountered! " . (($this->errorsEncountered > 0 && $this->errorsEncountered) == 1 ? implode(",", $error_list) : "Check the error list for more details!"),
                "errors" => $error_list
            );

            return $output;
        } else if ($this->type == "result") {
            if (!$data) {
                return array("success" => false, "message" => "No data received for results upload!");
            }

            // save file to uploads folder
            $file_upload_msg = $this->saveResultDataFile($data);
            if (!$file_upload_msg["success"]) return $file_upload_msg;

            $extracted_data = $this->extractResultExcelData($data);
            if (empty($extracted_data)) return array("success" => false, "message" => "No data found in the file!");

            // Check if class exists
            $classQuery = "SELECT `code` FROM `class` WHERE `code` = :ci";
            $classData = $this->dm->getData($classQuery, array(":ci" => $data["class"]));
            if (empty($classData)) {
                array_push($error_list, "Class with code {$data['class']} does not exist!");
                $this->errorsEncountered += 1;
                return array("success" => false, "message" => implode(", ", $error_list));
            }

            // Check if course exists
            $courseQuery = "SELECT `code` FROM `course` WHERE `code` = :ci";
            $courseData = $this->dm->getData($courseQuery, array(":ci" => $data["course"]));
            if (empty($courseData)) {
                array_push($error_list, "Course with code {$data['course']} does not exist!");
                $this->errorsEncountered += 1;
                return array("success" => false, "message" => implode(", ", $error_list));
            }

            // Check if semester exists
            $semesterQuery = "SELECT `id` FROM `semester` WHERE `id` = :si";
            $semesterData = $this->dm->getData($semesterQuery, array(":si" => $data["semester"]));
            if (empty($semesterData)) {
                array_push($error_list, "Semester with ID {$data['semester']} does not exist!");
                $this->errorsEncountered += 1;
                return array("success" => false, "message" => implode(", ", $error_list));
            }

            // Check if staff exists
            $staffQuery = "SELECT `number` FROM `staff` WHERE `number` = :si";
            $staffData = $this->dm->getData($staffQuery, array(":si" => $data["staffId"]));
            if (empty($staffData)) {
                array_push($error_list, "Staff with Number {$data['staffId']} does not exist!");
                $this->errorsEncountered += 1;
                return array("success" => false, "message" => implode(", ", $error_list));
            }

            $error_list = [];
            $output = [];
            $count = 0;

            // add results for each applicant to db
            foreach ($extracted_data as $result) {
                // Check if student exists
                $studentQuery = "SELECT `index_number` FROM `student` WHERE `index_number` = :si";
                $studentData = $this->dm->getData($studentQuery, array(":si" => $result["student_id"]));

                if (empty($studentData)) {
                    array_push($error_list, "Student with ID {$result['student_id']} does not exist!");
                    $this->errorsEncountered += 1;
                    continue;
                }

                $resultInsertQuery = "UPDATE `student_results` SET `exam_score` = :es, `project_score` = :ps, `continues_assessments_score` = :cas 
                                    WHERE `fk_student` = :i AND `fk_course` = :c AND `fk_semester` = :s";

                $params = array(
                    ":i" => $result["student_id"],
                    ":c" => $data["course"],
                    ":s" => $data["semester"],
                    ":es" => $result["exam_score"],
                    ":ps" => $result["project_score"],
                    ":cas" => $result["assessment_score"]
                );

                $resultInsertOutput = $this->dm->inputData($resultInsertQuery, $params);

                if (!$resultInsertOutput) {
                    array_push($error_list, "Failed to add result for student ID {$result['student_id']} in course {$result['course_code']}!");
                    $this->errorsEncountered += 1;
                } else {
                    $this->successEncountered += 1;
                }
                $count++;
            }

            $output = array(
                "success" => true,
                "message" => "Successfully updated {$this->successEncountered} results and {$this->errorsEncountered} errors encountered! " . (($this->errorsEncountered > 0 && count($error_list) > 0) ? implode(",", $error_list) : "Check the error list for more details!"),
                "errors" => $error_list
            );

            if ($this->successEncountered && !$this->errorsEncountered) {
                $updateDeadlineQuery = "UPDATE `deadlines` SET `status`= 'submitted', `updated_at`= CURRENT_TIMESTAMP 
                                        WHERE `fk_semester`=:sm AND `fk_course`=:cr AND `fk_class`=:cl";
                $updateDeadlineParams = [':cl' => $data["class"], ':cr' => $data["course"], ':sm' => $data["semester"]];
                if ($this->dm->inputData($updateDeadlineQuery, $updateDeadlineParams)) {
                    $semesterId = $data["semester"];

                    // Recalculate GPA for this semester
                    $this->dm->inputData("CALL recalc_results_and_gpa(:sem)", [":sem" => $semesterId]);

                    return array(
                        "success" => true,
                        "message" => "Successfully updated {$this->successEncountered} results and {$this->errorsEncountered} errors encountered! "
                    );
                } else {
                    return array(
                        "success" => false,
                        "message" => "Failed to update deadline status " . (($this->errorsEncountered > 0 && count($error_list) > 0) ? implode(",", $error_list) : "Check the error list for more details!"),
                        "errors" => $error_list
                    );
                }
            } else {
                return array(
                    "success" => false,
                    "message" => "Successfully updated {$this->successEncountered} results and {$this->errorsEncountered} errors encountered! " . (($this->errorsEncountered > 0 && count($error_list) > 0) ? implode(",", $error_list) : "Check the error list for more details!"),
                    "errors" => $error_list
                );
            }
        }
    }
}
