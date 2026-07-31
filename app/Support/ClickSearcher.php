<?php

namespace App\Support;

class ClickSearcher
{
    public $clickId;

    public function __construct($clickId)
    {
        $this->clickId = $clickId;
    }

    public function clickVars()
    {
        $database = DatabaseConnection::getInstance();
        $statement = $database->prepare('SELECT * FROM click_vars WHERE click_id = :click_id');
        $statement->bindParam(':click_id', $this->clickId);
        $statement->execute();

        return $statement;
    }

    public function clickData()
    {
        $database = DatabaseConnection::getInstance();
        $statement = $database->prepare('SELECT * FROM clicks WHERE idclicks = :click_id');
        $statement->bindParam(':click_id', $this->clickId);
        $statement->execute();

        return $statement;
    }
}
