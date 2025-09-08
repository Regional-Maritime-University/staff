<?php

namespace Src\Core;

use Src\Base\Log;
use Src\System\DatabaseMethods;

class Classes
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

            case 'department':
                $concat_stmt = "AND d.`id` = :v";
                break;

            case 'program':
                $concat_stmt = "AND c.`fk_program` = :v";
                break;

            default:
                $concat_stmt = "";
                break;
        }

        $query = "SELECT 
                    c.`code`, 
                    c.`fk_staff` AS supervisor_id, 
                    CONCAT(s.`first_name`, ' ', s.`last_name`) AS supervisor_name, 
                    c.`year`, 
                    c.`category`, 
                    c.`fk_program` AS program_id, 
                    p.`name` AS program_name, 
                    p.`index_code` AS program_code, 
                    d.`id` AS department_id, 
                    d.`name` AS department_name,

                    -- Total students directly linked to this class
                    (SELECT COUNT(*) 
                     FROM student st
                     WHERE st.`fk_class` = c.`code` 
                       AND st.`archived` = 0) AS total_students

                FROM class AS c
                JOIN programs AS p ON c.`fk_program` = p.`id`
                JOIN department AS d ON p.`department` = d.`id`
                LEFT JOIN staff AS s ON c.`fk_staff` = s.`number`
                WHERE c.`archived` = :ar $concat_stmt
                ORDER BY c.`year` DESC, c.`code` ASC";

        $params = $value
            ? array(":v" => $value, ":ar" => $archived)
            : array(":ar" => $archived);

        $result = $this->dm->getData($query, $params);
        return $result
            ? array("success" => true, "data" => $result)
            : array("success" => false, "data" => "No classes found!");
    }

    public function assign(array $data)
    {
        $response = array("success" => false, "message" => "An error occurred while assigning class!");
        switch ($data["action"]) {
            case 'lecturer':
                $query = "UPDATE `class` SET `fk_staff` = :s WHERE `code` = :c";
                $params = array(
                    ":c" => $data["code"],
                    ":s" => $data["lecturer"]
                );
                $this->dm->inputData($query, $params);
                $this->log->activity($_SESSION["staff"]["number"], "UPDATE", "secretary", "Assign Class", "Assigned class {$data["code"]} to lecturer {$data["lecturer"]}");
                $response = array("success" => true, "message" => "Class {$data["code"]} successfully assigned to lecturer {$data["lecturer"]}!");
                break;

            case 'student':
                $total = 0;
                $query = "UPDATE `student` SET `fk_class` = :c WHERE `index_number` = :s";
                foreach ($data["students"] as $student) {
                    $params = array(
                        ":c" => $data["code"],
                        ":s" => $student
                    );
                    $this->dm->inputData($query, $params);
                    $this->log->activity($_SESSION["staff"]["number"], "UPDATE", "secretary", "Assign Class", "Assigned class {$data["code"]} to student {$student}");
                    $total += 1;
                }
                $response = array("success" => true, "message" => "{$total} students successfully assigned to class {$data["code"]}!");
                break;

            default:
                $response = array("success" => false, "message" => "Invalid action specified for class assignment!");
                break;
        }
        return $response;
    }

    public function add(array $data)
    {
        $query1 = "SELECT class.`code`, programs.`name` FROM `class`, `programs` WHERE class.`fk_program` = programs.`id` AND  class.`code` = :c AND class.`category` = :cg";
        $params1 = array(
            ":c" => $data["code"],
            ":cg" => $data["category"]
        );
        $result1 = $this->dm->getData($query1, $params1);
        if ($result1) {
            return array("success" => false, "message" => "{$data["code"]} already exists!");
        }

        $query = "INSERT INTO `class` (`code`, `year`, `fk_program`, `category`, `archived`) 
                VALUES (:c, :y, :p, :cg, 0)";

        $params = array(
            ":c" => $data["code"],
            ":y" => $data["year"],
            ":p" => $data["program"],
            ":cg" => $data["category"]
        );

        $query_result = $this->dm->inputData($query, $params);
        if ($query_result) {
            $this->log->activity($_SESSION["staff"]["number"], "INSERT", "secretary", "Created Class", "Added new class {$data["code"]}");
            return array("success" => true,  "message" => "Class {$data["code"]} successfully added!");
        } else {
            return array("success" => false, "message" => "Encountered a server error while adding class {$data["code"]} to database!");
        }
    }

    public function update(array $data)
    {
        $query = "UPDATE class SET 
                `year` = :y, `fk_program` = :p, `category` = :cg, code = :c 
                WHERE `code` = :oc";
        $params = array(
            ":y" => $data["year"],
            ":p" => $data["program"],
            ":cg" => $data["category"],
            ":c" => $data["code"],
            ":oc" => $data["oldCode"]
        );

        $query_result = $this->dm->inputData($query, $params);
        if ($query_result) {
            $this->log->activity($_SESSION["staff"]["number"], "UPDATE", "secretary", "Update Class", "Updated class {$data["code"]}");
            return array("success" => true, "message" => "Class updated!");
        } else {
            return array("success" => false, "message" => "Encountered a server error while updating class {$data["code"]} in database!");
        }
    }

    public function archive($code)
    {
        $query = "UPDATE `class` SET `archived` = 1 WHERE `code` = :c";
        $query_result = $this->dm->inputData($query, array(":c" => $code));
        if ($query_result) {
            $this->log->activity($_SESSION["staff"]["number"], "UPDATE", "secretary", "Archive Class", "Archived class {$code}");
            return array("success" => true, "message" => "Class successfully archived!");
        } else {
            return array("success" => false, "message" => "Failed to archive class {$code}!");
        }
    }

    public function unarchive(array $classes)
    {
        $unarchived = 0;
        foreach ($classes as $code) {
            $query = "UPDATE `class` SET `archived` = 0 WHERE `code` = :i";
            $query_result = $this->dm->inputData($query, array(":i" => $code));
            if ($query_result) {
                $this->log->activity($_SESSION["staff"]["number"], "UPDATE", "secretary", "Class Unarchive", "Unarchived class {$code}");
                $unarchived += 1;
            }
        }
        return array(
            "success" => true,
            "message" => "{$unarchived} successfully unarchived!",
            "errors" => "Failed to unarchive " . (count($classes) - $unarchived) . " classes"
        );
    }

    public function delete(array $classes)
    {
        $deleted = 0;
        foreach ($classes as $code) {
            $query = "DELETE FROM `class` WHERE `code` = :i";
            $query_result = $this->dm->inputData($query, array(":i" => $code));
            if ($query_result) {
                $this->log->activity($_SESSION["staff"]["number"], "DELETE", "secretary", "Class Deletion", "Deleted class {$code}");
                $deleted += 1;
            }
        }
        return array(
            "success" => true,
            "message" => "{$deleted} successfully deleted!",
            "errors" => "Failed to delete " . (count($classes) - $deleted) . " classes"
        );
    }

    public function total(string $key = "", string $value = "", bool $archived = false)
    {
        $concat_stmt = "";
        switch ($key) {
            case 'category':
                $concat_stmt = "AND p.`category` = :v";
                break;

            case 'department':
                $concat_stmt = "AND p.`fk_department` = :v";
                break;

            default:
                $concat_stmt = "";
                break;
        }
        $query = "SELECT COUNT(p.`id`) AS total FROM `class` AS p, `forms` AS f, `department` AS d 
                WHERE p.`type` = f.`id` AND p.`department` = d.`id` AND p.archived = :ar $concat_stmt";
        $params = $value ? array(":v" => $value, ":ar" => $archived) : array(":ar" => $archived);
        return $this->dm->getData($query, $params);
    }

    function fetchClassCourses($classCode)
    {
        // fetch courses from db for a class
        $query = "SELECT DISTINCT 
                    c.code AS course_code,
                    c.name AS course_name,
                    c.credit_hours,
                    cl.code AS class_code,
                    s.number AS lecturer_id,
                    CONCAT(s.prefix, ' ', s.first_name, ' ', s.last_name) AS lecturer_name
                FROM section sec
                JOIN class cl 
                    ON sec.fk_class = cl.code
                JOIN course c 
                    ON sec.fk_course = c.code
                JOIN lecturer_courses lc 
                    ON lc.fk_course = c.code
                JOIN staff s 
                    ON lc.fk_staff = s.number
                WHERE cl.code = :code";
        $params = array(":code" => $classCode);
        $result = $this->dm->getData($query, $params);
        return $result ? array("success" => true, "data" => $result) : array("success" => false, "message" => "No courses found for this class!");
    }

    function fetchClassStudents($classCode)
    {
        $query = "SELECT 
                    st.`index_number`,
                    st.`first_name`,
                    st.`middle_name`,
                    st.`last_name`,
                    st.`email`,
                    st.`phone_number` 
                FROM `student` st
                WHERE st.`fk_class` = :code AND st.`archived` = 0";
        $params = array(":code" => $classCode);
        $result = $this->dm->getData($query, $params);
        return $result ? array("success" => true, "data" => $result) : array("success" => false, "message" => "No students found for this class!");
    }
}
