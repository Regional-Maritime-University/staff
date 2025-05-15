<?php

namespace Src\Core;

use Src\Base\Log;
use Src\System\DatabaseMethods;

class Course
{

    private $dm = null;
    private $log = null;

    public function __construct($db, $user, $pass)
    {
        $this->dm = new DatabaseMethods($db, $user, $pass);
        $this->log = new Log($db, $user, $pass);
    }

    public function fetch(string $key = "", string $value = "", bool $archived = false)
    {
        switch ($key) {
            case 'code':
                $concat_stmt = "AND c.`code` = :v";
                break;

            case 'name':
                $concat_stmt = "AND c.`name` = :v";
                break;

            case 'category':
                $concat_stmt = "AND c.`fk_category` = :v";
                break;

            case 'department':
                $concat_stmt = "AND c.`fk_department` = :v";
                break;

            case 'semester':
                $concat_stmt = "AND c.`semester` = :v";
                break;

            default:
                $concat_stmt = "";
                break;
        }

        $query = "SELECT c.`code`, c.`name`, c.`credit_hours`, c.`contact_hours`, c.`semester`, c.`level`, c.`archived`, 
                c.`fk_category` AS category_id, cg.`name` AS category, c.`fk_department` AS `department_id`, d.`name` AS `department_name` 
                FROM `course` AS c, `course_category` AS cg, `department` AS d 
                WHERE c.`fk_category` = cg.`id` AND c.`fk_department` = d.`id` AND c.`archived` = :ar $concat_stmt ORDER BY c.`code` ASC";
        $params = $value ? array(":v" => $value, ":ar" => $archived) : array(":ar" => $archived);
        return $this->dm->getData($query, $params);
    }

    public function add(array $data)
    {
        $selectQuery = "SELECT * FROM `course` WHERE `code` = :c";
        $courseData = $this->dm->getData($selectQuery, array(":c" => $data["courseCode"]));

        if (!empty($courseData)) {
            return array(
                "success" => false,
                "message" => "{$courseData[0]["name"]} with code {$courseData[0]["code"]} already exist in database!"
            );
        }

        $query = "INSERT INTO course (`code`, `name`, `credit_hours`, `contact_hours`, `semester`, `level`, 
                `fk_category`, `fk_department`) 
                VALUES(:c, :n, :ch, :th, :s, :l, :cg, :dm)";
        $params = array(
            ":c" => $data["courseCode"],
            ":n" => $data["courseName"],
            ":ch" => $data["creditHours"],
            ":th" => $data["contactHours"],
            ":s" => $data["semester"],
            ":l" => $data["level"],
            ":cg" => $data["category"],
            ":dm" => $data["departmentId"],
        );
        $query_result = $this->dm->inputData($query, $params);
        if ($query_result) {
            $this->log->activity($_SESSION["staff"]["number"], "INSERT", "secretary", "Course Creation", "Added new course {$data["courseName"]} ({$data["courseCode"]})");
            return array("success" => true, "message" => "New course successfully added!");
        }
        return array("success" => false, "message" => "Failed to add new course!");
    }

    public function update(array $data)
    {
        $query = "UPDATE course SET 
        `code`=:c, `name`=:n, `credit_hours`=:ch, `contact_hours`=:th, `semester`=:s, `level`=:l, 
        `fk_category`=:cg, `fk_department`=:dm, `archived`=:ar WHERE `code` = :c";
        $params = array(
            ":c" => $data["courseCode"],
            ":n" => $data["courseName"],
            ":ch" => $data["creditHours"],
            ":th" => $data["contactHours"],
            ":s" => $data["semester"],
            ":l" => $data["level"],
            ":cg" => $data["category"],
            ":dm" => $data["departmentId"],
            ":ar" => 0
        );
        $query_result = $this->dm->inputData($query, $params);
        if ($query_result) {
            $this->log->activity($_SESSION["staff"]["number"], "UPDATE", "secretary", "Course Modification", "Updated information of course {$data["courseName"]} ({$data["courseCode"]})");
            return array("success" => true, "message" => "Course successfully updated!");
        }
        return array("success" => false, "message" => "Failed to update course!");
    }

    public function archive($code)
    {
        $query = "UPDATE course SET archived = 1 WHERE `code` = :c";
        $query_result = $this->dm->inputData($query, array(":c" => $code));
        if ($query_result) {
            $this->log->activity($_SESSION["staff"]["number"], "UPDATE", "secretary", "Course Modification", "Archived course {$code}");
            return array("success" => true, "message" => "Course with code {$code} successfully archived!");
        }
        return array("success" => false, "message" => "Failed to archive new course!");
    }

    public function unarchive(array $courses)
    {
        $unarchived = 0;
        foreach ($courses as $course) {
            $query = "UPDATE `course` SET `archived` = 0 WHERE `code` = :c";
            $query_result = $this->dm->inputData($query, array(":c" => $course));
            if ($query_result) {
                $this->log->activity($_SESSION["staff"]["number"], "UPDATE", "secretary", "Course Modification", "Unarchived course {$course}");
                $unarchived += 1;
            }
        }
        return array(
            "success" => true,
            "message" => "{$unarchived} successfully unarchived!",
            "errors" => "Failed to unarchive " . (count($courses) - $unarchived) . " courses"
        );
    }

    public function delete($code)
    {
        $query = "DELETE FROM course WHERE code = :c";
        $query_result = $this->dm->inputData($query, array(":c" => $code));
        if ($query_result) {
            $this->log->activity($_SESSION["staff"]["number"], "DELETE", "secretary", "Course Modification", "Deleted course {$code}");
            return array("success" => true, "message" => "Course with code {$code} successfully deleted!");
        }
        return array("success" => false, "message" => "Failed to delete course!");
    }

    public function total(string $key = "", string $value = "", bool $archived = false)
    {
        $concat_stmt = "";
        switch ($key) {
            case 'category':
                $concat_stmt = "AND c.`fk_category` = :v";
                break;

            case 'department':
                $concat_stmt = "AND c.`fk_department` = :v";
                break;

            default:
                $concat_stmt = "";
                break;
        }
        $query = "SELECT COUNT(c.`code`) AS total FROM `course` AS c, `course_category` AS cg, `department` AS d 
        WHERE c.`fk_category` = cg.`id` AND c.`fk_department` = d.`id` AND c.`archived` = :ar $concat_stmt";
        $params = $value ? array(":v" => $value, ":ar" => $archived) : array(":ar" => $archived);
        return $this->dm->getData($query, $params);
    }
}
