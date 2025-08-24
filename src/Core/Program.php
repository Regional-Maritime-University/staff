<?php

namespace Src\Core;

use Src\Base\Log;
use Src\System\DatabaseMethods;

class Program
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
                $concat_stmt = "AND p.`id` = :v";
                break;

            case 'code':
                $concat_stmt = "AND p.`code` = :v";
                break;

            case 'name':
                $concat_stmt = "AND p.`name` = :v";
                break;

            case 'category':
                $concat_stmt = "AND p.`category` = :v";
                break;

            case 'department':
                $concat_stmt = "AND p.`department` = :v";
                break;

            default:
                $concat_stmt = "";
                break;
        }

        $query = "SELECT p.`id`, p.`name`, p.`merit`, p.`department` AS department_id, d.`name` AS department_name, 
                p.`regulation`, p.`category`, p.`code`, p.`index_code`, p.`faculty`, p.`duration`, p.`dur_format`, 
                p.`num_of_semesters`, p.`type` AS type_id, f.`name` AS `type` , p.`regular`, p.`weekend`, p.`group`, p.`archived` 
                FROM `programs` AS p, `forms` AS f, `department` AS d 
                WHERE p.`type` = f.`id` AND p.`department` = d.`id` AND p.`archived` = :ar $concat_stmt ORDER BY `updated_at` DESC";
        $params = $value ? array(":v" => $value, ":ar" => $archived) : array(":ar" => $archived);

        $result = $this->dm->getData($query, $params);
        return $result ? array("success" => true, "data" => $result) : array("success" => false, "message" => "No program found!");
    }

    public function add(array $data)
    {
        $query1 = "SELECT id FROM `programs` WHERE `name` = :n OR `code` = :c OR `index_code` = :i";
        $params1 = array(
            ":n" => strtoupper($data["name"]),
            ":c" => $data["code"],
            ":i" => $data["index_code"]
        );
        $result1 = $this->dm->getData($query1, $params1);
        if ($result1) {
            return array("success" => false, "message" => "Program with name {$data["name"]}, code {$data["code"]} or index code {$data["index_code"]} already exists!");
        }

        $query = "INSERT INTO `programs` (`name`, `merit`, `department`, `regulation`, 
                `category`, `code`, `index_code`, `faculty`, `duration`, `dur_format`, 
                `num_of_semesters`, `type`, `regular`, `weekend`, `group`) 
                VALUES(:n, :m, :dm, :rl, :cg, :c, :ic, :f, :d, :df,  :ns,  :t,  :r,  :w, :g)";

        switch ($data["category"]) {
            case 'UPGRADE':
            case 'MASTERS':
                $type = 1;
                break;
            case 'DEGREE':
                $type = 2;
                break;
            case 'DIPLOMA':
                $type = 3;
                break;
            case 'SHORT':
                $type = 4;
                break;

            default:
                $type = null;
                break;
        }

        $params = array(
            ":n" => strtoupper($data["name"]),
            ":m" => strtoupper($data["name"]),
            ":dm" => $data["department"],
            ":rl" => $data["regulation"],
            ":cg" => $data["category"],
            ":c" => $data["code"],
            ":ic" => $data["index_code"],
            ":f" => $data["faculty"],
            ":d" => $data["duration"],
            ":df" => $data["dur_format"],
            ":ns" => $data["num_of_semesters"],
            ":t" => $type,
            ":r" => $data["regular"],
            ":w" => $data["weekend"],
            ":g" => $data["group"]
        );

        $query_result = $this->dm->inputData($query, $params);
        if ($query_result) {
            $this->log->activity($_SESSION["staff"]["number"], "INSERT", "secretary", "Created Program", "Added new programme {$data["name"]} of programme type {$data["category"]}");
            return array("success" => true,  "message" => "Program added!");
        } else {
            return array("success" => false, "message" => "Encountered a server error while adding program {$data["name"]} to database!");
        }
    }

    public function update(array $data)
    {
        $query = "UPDATE programs SET 
        `name`=:n, `merit`=:m, `department`=:dm, `regulation`=:rl, 
        `category`=:cg, `code`=:c, `index_code`=:ic, `faculty`=:f, 
        `duration`=:d, `dur_format`=:df, `num_of_semesters`=:ns, 
        `type`=:t, `regular`=:r, `weekend`=:w, `group`=:g , `archived`=:ar WHERE `id` = :i";

        $params = array(
            ":n" => $data["name"],
            ":m" => $data["merit"],
            ":dm" => $data["department"],
            ":rl" => $data["regulation"],
            ":cg" => $data["category"],
            ":c" => $data["code"],
            ":ic" => $data["index_code"],
            ":f" => $data["faculty"],
            ":d" => $data["duration"],
            ":df" => $data["dur_format"],
            ":ns" => $data["num_of_semesters"],
            ":t" => $data["type"],
            ":r" => $data["regular"],
            ":w" => $data["weekend"],
            ":g" => $data["group"],
            ":ar" => $data["archived"],
            ":i" => $data["id"]
        );
        $query_result = $this->dm->inputData($query, $params);
        if ($query_result) {
            $this->log->activity($_SESSION["staff"]["number"], "UPDATE", "secretary", "Created Program", "Updated program {$data["name"]} of program type {$data["type"]}");
            return array("success" => true, "message" => "Program updated!");
        } else {
            return array("success" => false, "message" => "Encountered a server error while updating program {$data["name"]} in database!");
        }
    }

    public function archive($id)
    {
        $query = "UPDATE `programs` SET `archived` = 1 WHERE `id` = :i";
        $query_result = $this->dm->inputData($query, array(":i" => $id));
        if ($query_result) {
            $this->log->activity($_SESSION["staff"]["number"], "UPDATE", "secretary", "Program Archive", "Archived program {$id}");
            return array("success" => true, "message" => "Program successfully archived!");
        } else {
            return array("success" => false, "message" => "Failed to archive program {$id}!");
        }
    }

    public function unarchive(array $programs)
    {
        $unarchived = 0;
        foreach ($programs as $program) {
            $query = "UPDATE `programs` SET `archived` = 0 WHERE `id` = :i";
            $query_result = $this->dm->inputData($query, array(":i" => $program));
            if ($query_result) {
                $this->log->activity($_SESSION["staff"]["number"], "UPDATE", "secretary", "Program Unarchive", "Unarchived program {$program}");
                $unarchived += 1;
            }
        }
        return array(
            "success" => true,
            "message" => "{$unarchived} successfully unarchived!",
            "errors" => "Failed to unarchive " . (count($programs) - $unarchived) . " programs"
        );
    }

    public function delete(array $programs)
    {
        $deleted = 0;
        foreach ($programs as $program) {
            $query = "DELETE FROM `programs` WHERE `id` = :i";
            $query_result = $this->dm->inputData($query, array(":i" => $program));
            if ($query_result) {
                $this->log->activity($_SESSION["staff"]["number"], "DELETE", "secretary", "Program Deletion", "Deleted programme {$program}");
                $deleted += 1;
            }
        }
        return array(
            "success" => true,
            "message" => "{$deleted} successfully deleted!",
            "errors" => "Failed to delete " . (count($programs) - $deleted) . " programs"
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
        $query = "SELECT COUNT(p.`id`) AS total FROM `programs` AS p, `forms` AS f, `department` AS d 
                WHERE p.`type` = f.`id` AND p.`department` = d.`id` AND p.archived = :ar $concat_stmt";
        $params = $value ? array(":v" => $value, ":ar" => $archived) : array(":ar" => $archived);
        return $this->dm->getData($query, $params);
    }

    public function archiveCurriculumCourse($programId, $courseCode)
    {
        $query = "UPDATE `curriculum` SET `archived` = 1 WHERE `fk_program` = :p AND `fk_course` = :c";
        $query_result = $this->dm->inputData($query, array(":p" => $programId, ":c" => $courseCode));
        if ($query_result) {
            $this->log->activity($_SESSION["staff"]["number"], "UPDATE", "secretary", "Program Archive", "Archived course ({$courseCode}) for program {$programId}");
            return array("success" => true, "message" => "Course ({$courseCode}) for this program successfully archived!");
        } else {
            return array("success" => false, "message" => "Failed to archive course ({$courseCode}) for this program!");
        }
    }
}
