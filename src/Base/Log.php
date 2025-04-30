<?php

namespace Src\Base;

use Src\System\DatabaseMethods;

class Log
{
    private $dm = null;

    public function __construct($db, $user, $pass)
    {
        $this->dm = new DatabaseMethods($db, $user, $pass);
    }

    public function activity($user_id, $operation, $type, $action, $description)
    {
        $query = "INSERT INTO `activity_logs`(`user_id`, `operation`, `type`, `action`, `description`) VALUES (:u,:o,:t,:a,:d)";
        $params = array(":u" => $user_id, ":o" => $operation, ":t" => $type, ":a" => $action, ":d" => $description);
        $this->dm->inputData($query, $params);
    }
}
