<?php

namespace Src\Core;

use Src\Base\Log;
use Src\System\DatabaseMethods;

class CourseCategory
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
            case 'id':
                $concat_stmt = "AND c.`id` = :v";
                break;

            case 'name':
                $concat_stmt = "AND c.`name` = :v";
                break;

            default:
                $concat_stmt = "";
                break;
        }

        $query = "SELECT `id`, `name`, `archived` FROM `course_category` WHERE `archived` = :ar $concat_stmt";
        $params = $value ? array(":v" => $value, ":ar" => $archived) : array(":ar" => $archived);
        return $this->dm->getData($query, $params);
    }

    public function add(array $data)
    {
        $selectQuery = "SELECT * FROM `course_category` WHERE `name` = :c";
        $courseCategoryData = $this->dm->getData($selectQuery, array(":c" => $data["name"]));

        if (!empty($courseCategoryData)) {
            return array(
                "success" => false,
                "message" => "{$courseCategoryData[0]["name"]} with name {$courseCategoryData[0]["name"]} already exist in database!"
            );
        }

        $query_result = $this->dm->inputData("INSERT INTO course_category (`name`) VALUES(:n)", array(":n" => $data["name"]));
        if ($query_result) {
            $this->log->activity($_SESSION["staff"]["number"], "INSERT", "secretary", "Course Category Creation", "Added new course category {$data["name"]}");
            return array("success" => true, "message" => "New course category successfully added!");
        }
        return array("success" => false, "message" => "Failed to add new course category!");
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
