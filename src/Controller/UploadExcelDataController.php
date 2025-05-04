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

    public function __construct($fileObj, $startRow = 1, $endRow = 0)
    {
        $this->db   = getenv('DB_DATABASE');
        $this->user = getenv('DB_USERNAME');
        $this->pass = getenv('DB_PASSWORD');

        $this->fileObj = $fileObj;
        $this->startRow = (int) $startRow;
        $this->endRow = (int) $endRow;
        $this->dm = new DatabaseMethods($this->db, $this->user, $this->pass);
    }

    public function saveDataFile()
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

            // Create the full path to the file
            $this->targetPath = UPLOAD_DIR . "/documents/" . $name;

            // Delete file if exsists
            if (file_exists($this->targetPath)) {
                unlink($this->targetPath);
            }

            // Move the file to the target directory
            if (!move_uploaded_file($this->fileObj['tmp_name'], $this->targetPath))
                return array("success" => false, "message" => "Failed to upload file!");
            return array("success" => true, "message" => "File upload successful!");
        }
        return array("success" => false, "message" => "Error: Invalid file object!");
    }

    public function extractExcelData()
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

    public function run($departmentID)
    {
        // save file to uploads folder
        $file_upload_msg = $this->saveDataFile();
        if (!$file_upload_msg["success"]) return $file_upload_msg;

        //extraxt data into array
        $extracted_data = $this->extractExcelData($departmentID);
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
    }
}
