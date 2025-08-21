<?php

namespace Src\Core;

use Src\Base\Log;
use Src\System\DatabaseMethods;

class Deadline
{

    private $dm = null;
    private $log = null;

    private $db = null;
    private $user = null;
    private $pass = null;

    public function __construct($db, $user, $pass)
    {
        $this->db = $db;
        $this->user = $user;
        $this->pass = $pass;
        $this->dm = new DatabaseMethods($db, $user, $pass);
        $this->log = new Log($db, $user, $pass);
    }

    public function fetch(string $key = "", string $value = "", bool $archived = false)
    {
        switch ($key) {
            case 'department':
                $concat_stmt = "AND c.`fk_department` = :v";
                break;

            case 'course':
                $concat_stmt = "AND c.`fk_course` = :v";
                break;

            case 'lecturer':
                $concat_stmt = "AND c.`fk_staff` = :v";
                break;

            case 'semester':
                $concat_stmt = "AND c.`fk_semester` = :v";
                break;

            default:
                $concat_stmt = "";
                break;
        }

        $query = "SELECT lca.`id`, lca.`fk_department` AS department_id, lca.`fk_staff` AS lecturer_id, lca.`fk_course` AS department_id, `archived` FROM `lecturer_courses` lca WHERE `archived` = :ar $concat_stmt";
        $params = $value ? array(":v" => $value, ":ar" => $archived) : array(":ar" => $archived);
        return $this->dm->getData($query, $params);
    }

    public function add(array $data)
    {
        $successCount = $failedCount = 0;
        $successCourses = $failedCourses = [];

        foreach ($data["courses"] as $course) {
            $selectQuery = "SELECT * FROM `course` WHERE `code` = :c";
            $courseData = $this->dm->getData($selectQuery, array(":c" => $course));

            $query = "INSERT INTO deadlines (`fk_department`, `fk_semester`, `fk_course`, `fk_staff`, `date`, `note`) VALUES(:dp, :sm, :cs, :st, :dt, :n)";
            $params = array(
                ":dp" => $data["department"],
                ":sm" => $data["semester"],
                ":cs" => $course,
                ":st" => $data["lecturer"],
                ":dt" => $data["date"],
                ":n" => $data["note"]
            );
            $result = $this->dm->inputData($query, $params);
            if ($result) {
                $this->log->activity($_SESSION["staff"]["number"], "INSERT", "secretary", "Results Submission Deadline", "Set a deadline for {$courseData[0]["name"]} ({$course})");
                array_push($successCourses, $course);
                $successCount++;
            } else {
                array_push($failedCourses, $course);
                $failedCount++;
            }
        }

        return array(
            "success" => $successCount > 0 ? true : false,
            "message" => $successCount > 0 ? "Successfully set deadline(s) for results submission for {$successCount} courses (" . implode(", ", $successCourses) . ") !" : "Failed to set deadline(s) for results submission for {$failedCount} courses (" . implode($failedCourses) . ") !"
        );
    }

    public function update(array $data)
    {
        $query = "UPDATE `course_category` SET `id`=:c, `name`=:n, `archived`=:ar WHERE `id` = :c";
        $params = array(
            ":c" => $data["course_category"],
            ":ar" => 0
        );
        $query_result = $this->dm->inputData($query, $params);
        if ($query_result) {
            $this->log->activity($_SESSION["staff"]["number"], "UPDATE", "secretary", "Course Category Modification", "Updated information for course category {$data["course_category"]}");
            return array("success" => true, "message" => "Course successfully updated!");
        }
        return array("success" => false, "message" => "Failed to update course category!");
    }

    public function archive($code)
    {
        $query = "UPDATE `course_category` SET archived = 1 WHERE `id` = :c";
        $query_result = $this->dm->inputData($query, array(":c" => $code));
        if ($query_result) {
            $this->log->activity($_SESSION["staff"]["number"], "UPDATE", "secretary", "Course category Modification", "Archived course category {$code}");
            return array("success" => true, "message" => "Course with code {$code} successfully archived!");
        }
        return array("success" => false, "message" => "Failed to archive new course category!");
    }

    public function unarchive(array $courses)
    {
        $unarchived = 0;
        foreach ($courses as $course) {
            $query = "UPDATE `course_category` SET `archived` = 0 WHERE `id` = :c";
            $query_result = $this->dm->inputData($query, array(":c" => $course));
            if ($query_result) {
                $this->log->activity($_SESSION["staff"]["number"], "UPDATE", "secretary", "Course category Modification", "Unarchived course category {$course}");
                $unarchived += 1;
            }
        }
        return array(
            "success" => true,
            "message" => "{$unarchived} successfully unarchived!",
            "errors" => "Failed to unarchive " . (count($courses) - $unarchived) . " course categories"
        );
    }

    public function delete(array $courses)
    {
        $deleted = 0;
        foreach ($courses as $course) {
            $query = "DELETE FROM `course_category` WHERE `id` = :c";
            $query_result = $this->dm->inputData($query, array(":c" => $course));
            if ($query_result) {
                $this->log->activity($_SESSION["staff"]["number"], "DELETE", "secretary", "Course Category Modification", "Deleted course {$course}");
                $deleted += 1;
            }
        }
        return array(
            "success" => true,
            "message" => "{$deleted} successfully deleted!",
            "errors" => "Failed to delete " . (count($courses) - $deleted) . " courses"
        );
    }
}
