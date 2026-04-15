<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 8/10/2017
 * Time: 11:37 AM
 */

namespace LeadMax\TrackYourStats\System;


// when switched to homestead, MySQL was a newer version and required group bys in certain queries, to fix this, we can disable that setting..

// this class could be used for other db shit, but right now its just holding that db fix.


class Database
{

    static function FIX_ERROR_ONLY_FULL_GROUP_BY()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));";
        $db->query($sql);

    }


}