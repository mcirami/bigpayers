<?php

namespace LeadMax\TrackYourStats\Report\Repositories;



abstract class Repository
{

    protected $offset;

    protected $limit;

    private $dbConnection = false;


    public function __construct($db_connection)
    {
        if ($db_connection instanceof \PDO) {
            $this->dbConnection = $db_connection;
        }
    }

    public function setRequiredKeysIfNotSet($report, $keys)
    {
        foreach ($report as &$row) {
            foreach ($keys as $key => $val) {
                if (isset($row[$key]) == false) {
                    $row[$key] = $val;
                }
            }
        }

        return $report;
    }

    public function mergeReport($clicks, $conversions)
    {

        if (empty($clicks) && empty($conversions)) {
            return $clicks;
        }

        if (empty($clicks) || empty($conversions)) {
            if (empty($clicks)) {
                return $conversions;
            }

            if (empty($conversions)) {
                return $clicks;
            }
        }

        $commonKeys = $this->findCommonArrayKeys($clicks, $conversions);

        if (empty($commonKeys)) {
            return [];
        }

        $mergedReport = [];

        $commonId = $commonKeys[0];

        $clicks = $this->setArrayKeyToInnerArrayKeyValue($commonId, $clicks);
        $conversions = $this->setArrayKeyToInnerArrayKeyValue($commonId, $conversions);
        

        $unMatchedKeys = $this->getUnmatchedKeys($commonKeys, reset($conversions));

       
        foreach ($clicks as $clickKey => $clickRow) {
            if (isset($conversions[$clickRow[$commonId]])) {
                if ($this->valuesMatchWithTheseKeys($commonKeys, $clickRow, $conversions[$clickRow[$commonId]])) {
                    $conversionRow = $this->stripKeysFromArray($commonKeys, $conversions[$clickRow[$commonId]]);
                    unset($conversions[$clickRow[$commonId]]);
                    $mergedReport[] = array_merge($clickRow, $conversionRow);
                }
            } else {
                foreach ($unMatchedKeys as $key) {
                    $clickRow[$key] = 0;
                }
                $mergedReport[] = $clickRow;

            }


        }
        
        // check for any unmatched conversions into clicks
        if (empty($conversions) == false) {

            $unMatchedClickKeys = $this->getUnmatchedKeys(array_keys(reset($conversions)), reset($clicks));

            foreach ($conversions as $row) {
                foreach ($unMatchedClickKeys as $key) {
                    $row[$key] = 0;
                }
                $mergedReport[] = $row;
            }
        }

        
        return $mergedReport;
    }


    private function getUnmatchedKeys($keys, $array)
    {
        $arrayKeys = array_keys($array);

        $unMatchedKeys = [];

        foreach ($arrayKeys as $key) {
            if (in_array($key, $keys) == false) {
                $unMatchedKeys[] = $key;
            }

        }


        return $unMatchedKeys;
    }

    private function setArrayKeyToInnerArrayKeyValue($key, $array)
    {
        $new = [];
        foreach ($array as $row) {
            if (isset($row[$key])) {
                $new[$row[$key]] = $row;
            }
        }

        return $new;
    }

    private function stripKeysFromArray($keys, $array)
    {
        foreach ($array as $rowKey => $row) {
            if (in_array($rowKey, $keys)) {
                unset($array[$rowKey]);
            }

        }

        return $array;
    }

    private function valuesMatchWithTheseKeys($keys, $arrayOne, $arrayTwo)
    {

        foreach ($keys as $key) {
            if (isset($arrayOne[$key]) && isset($arrayTwo[$key])) {
                if ($arrayOne[$key] != $arrayTwo[$key]) {
                    return false;
                }
            } else {
                return false;
            }
        }

        return true;
    }

    private function findCommonArrayKeys($one, $two)
    {
        $commonKeys = [];

        if (empty($one) || empty($two)) {
            return [];
        }

        $oneKeys = array_keys(reset($one));
        $twoKeys = array_keys(reset($two));

        foreach ($oneKeys as $oneKey) {
            if (in_array($oneKey, $twoKeys)) {
                $commonKeys[] = $oneKey;
            }
        }


        return $commonKeys;
    }


    public function getDB()
    {
        return $this->dbConnection;
    }


    public function setOffset($offset)
    {
        if (is_numeric($offset)) {
            $this->offset = $offset;
        }
    }

    public function setLimit($limit)
    {
        if (is_numeric($limit)) {
            $this->limit = $limit;
        }
    }


    abstract protected function query($dateFrom, $dateTo): \PDOStatement;

    abstract public function between($dateFrom, $dateTo): array;


    public function count($dateFrom, $dateTo): int
    {
        return $this->query($dateFrom, $dateTo)->rowCount();
    }

}