<?php

namespace Src\Core;

use Src\Base\Log;
use Src\System\DatabaseMethods;

class FeeItem
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
            case 'id':
                $concat_stmt = "AND `id` = :v";
                break;

            case 'name':
                $concat_stmt = "AND `name` = :v";
                break;

            default:
                $concat_stmt = "";
                break;
        }

        $query = "SELECT * FROM `fee_item` WHERE `archived` = :ar $concat_stmt ORDER BY `id` DESC";
        $params = $value ? array(":v" => $value, ":ar" => $archived) : array(":ar" => $archived);
        return $this->dm->getData($query, $params);
    }

    public function add(array $data)
    {
        $errors = [];
        $success_count = 0;

        foreach ($data["items"] as $item) {
            $selectQuery = "SELECT * FROM `fee_item` WHERE `id` = :n";
            $feeStructureItemData = $this->dm->getData($selectQuery, array(":n" => $item["id"]));
            if (!empty($feeStructureItemData)) {
                array_push($errors, "Fee item {$feeStructureItemData[0]["name"]} already exist in database!");
            } else {
                $query = "INSERT INTO `fee_item` (`name`) VALUES(:n)";
                $params = array(":n" => $item["name"]);
                $query_result = $this->dm->inputData($query, $params);
                if ($query_result) {
                    $this->log->activity($_SESSION["user"], "INSERT", "Added new fee item {$item["name"]}");
                    $success_count++;
                } else {
                    array_push($errors, "Encounter a server error while adding fee item {$item["name"]} to database!");
                }
            }
        }

        return array(
            "success" => true,
            "message" => "{$success_count} fee items added!",
            "errors" => $errors
        );
    }

    public function update(array $data)
    {
        $query = "UPDATE fee_item SET `name`=:n `archived`=:ar WHERE `id` = :i";
        $params = array(
            ":i" => $data["fee_item"],
            ":n" => $data["name"],
            ":ar" => 0
        );
        $query_result = $this->dm->inputData($query, $params);
        if ($query_result) {
            $this->log->activity($_SESSION["user"], "UPDATE", "Updated information for fee item {$data["fee_item"]}");
            return array("success" => true, "message" => "Fee item successfully updated!");
        }
        return array("success" => false, "message" => "Failed to update fee item!");
    }

    public function archive($id)
    {
        $query = "UPDATE fee_item SET archived = 1 WHERE `id` = :i";
        $query_result = $this->dm->inputData($query, array(":i" => $id));
        if ($query_result) {
            $this->log->activity($_SESSION["user"], "DELETE", "Archived fee item {$id}");
            return array("success" => true, "message" => "Fee item with id {$id} successfully archived!");
        }
        return array("success" => false, "message" => "Failed to add new fee item!");
    }

    public function delete($id)
    {
        $query = "DELETE FROM fee_item WHERE id = :i";
        $query_result = $this->dm->inputData($query, array(":i" => $id));
        if ($query_result) {
            $this->log->activity($_SESSION["user"], "DELETE", "Deleted fee item {$id}");
            return array("success" => true, "message" => "Fee item with code {$id} successfully deleted!");
        }
        return array("success" => false, "message" => "Failed to delete fee item!");
    }
}
