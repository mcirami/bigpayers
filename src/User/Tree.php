<?php

namespace LeadMax\TrackYourStats\User;

use PDO;

// logic for heirachy tree for users

class Tree
{

    public static function getLR($affid)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT lft, rgt FROM rep WHERE idrep = :affid";
        $prep = $db->prepare($sql);
        $prep->bindParam(":affid", $affid);
        $prep->execute();

        return $prep->fetch(PDO::FETCH_ASSOC);
    }


    public static function getChildren($left, $right)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep WHERE rep.lft > :left AND rep.rgt < :right";

    }

    public static function findChildren($left, $right)
    {
        return ($right - $left - 1) / 2;
    }


    static function rebuild_tree($referrer_repid, $left)
    {


        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        // the right value of this node is the left value + 1
        $right = $left + 1;

        $sql = 'SELECT idrep FROM rep WHERE referrer_repid= :parent AND status = 1';
        $prep = $db->prepare($sql);
        $prep->bindParam(":parent", $referrer_repid);
        $prep->execute();


        // get all children of this node
        $result = $prep->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $row) {
            // recursive execution of this function for each
            // child of this node
            // $right is the current right value, which is
            // incremented by the rebuild_tree function
            $right = static::rebuild_tree($row['idrep'], $right);
        }

        // we've got the left value, and now that we've processed
        // the children of this node we also know the right value
        $sql = "UPDATE rep SET lft=:left, rgt=
        :right WHERE idrep=:parent AND status = 1";

        $prep = $db->prepare($sql);
        $prep->bindParam(":left", $left);
        $prep->bindParam(":right", $right);
        $prep->bindParam(":parent", $referrer_repid);

        $prep->execute();

        // return the right value of this node + 1
        return $right + 1;


    }


    static function display_tree($idrep)
    {

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();


        // retrieve the left and right value of the $root node
        $sql = 'SELECT lft, rgt FROM rep WHERE idrep= :root';
        $prep = $db->prepare($sql);
        $prep->bindParam(":root", $idrep);
        $prep->execute();
        $row = $prep->fetchAll(PDO::FETCH_ASSOC);


        // start with an empty $right stack
        $right = array();


        // now, retrieve all descendants of the $root node
        $sql = 'SELECT first_name, lft, rgt FROM rep 
        WHERE lft BETWEEN '.$row[0]['lft'].' AND '.
            $row[0]['rgt'].' ORDER BY lft ASC;';


        $prep = $db->prepare($sql);
        $prep->execute();
        $result = $prep->fetchAll(PDO::FETCH_ASSOC);

        // display each row
        foreach ($result as $row) {
            // only check stack if there is one
            if (count($right) > 0) {
                // check if we should remove a node from the stack
                while ($right[count($right) - 1] < $row['rgt']) {
                    array_pop($right);
                }
            }

            // display indented node title


            echo str_repeat('  ', count($right)).$row['first_name']."<br/>";

            // add this node to the stack
            $right[] = $row['rgt'];
        }


    }


}

