<?php namespace LeadMax\TrackYourStats\Database;


abstract class Version
{

    private $db_connection = false;


    public function __construct($db_connection)
    {
        if ($db_connection instanceof \PDO) {
            $this->db_connection = $db_connection;
        } else {
            throw new \Exception("Must pass PDO Connection to Version");
        }
    }


    public function tableExists($tableName)
    {
        $db   = $this->getDB();
        $sql  = "SHOW TABLES LIKE '{$tableName}'";
        $prep = $db->prepare($sql);
        $prep->execute();

        if ($prep->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function commit()
    {
        $this->getDB()->commit();
    }

    public function beginTransaction()
    {
        $this->getDB()->beginTransaction();
    }

    public function rollBack()
    {
        $this->getDB()->rollBack();
    }

    public function getDB()
    {
        return $this->db_connection;
    }


    public function setDB($db_connection)
    {
        if ($db_connection instanceof \PDO) {
            $this->db_connection = $db_connection;
        } else {
            throw new \Exception("setDB() requires a PDO Object!");
        }
    }

    public function tableHasIndexes($table, $indexes)
    {
        $sql  = "SHOW INDEX FROM {$table};";
        $prep = $this->getDB()->prepare($sql);
        if ($prep->execute()) {
            $result = $prep->fetchAll(\PDO::FETCH_ASSOC);
            $keys   = array();
            foreach ($result as $row) {
                $keys[] = $row["Key_name"];
            }


            foreach ($indexes as $index) {
                if ( ! in_array($index, $keys)) {
                    return false;
                }
            }

            return true;

        } else {
            return false;
        }
    }

    public function tableHasColumns(string $table, array $columns)
    {
        $sql  = "DESCRIBE {$table};";
        $prep = $this->getDB()->prepare($sql);

        if ($prep->execute()) {
            $result       = $prep->fetchAll(\PDO::FETCH_ASSOC);
            $knownColumns = array();
            foreach ($result as $row) {
                if (isset($row["Field"])) {
                    $knownColumns[] = $row["Field"];
                }
            }

            if ( ! empty($knownColumns)) {
                foreach ($columns as $column) {
                    if ( ! in_array($column, $knownColumns)) {
                        return false;
                    }
                }

                return true;
            } else {
                return false;
            }


        } else {
            var_dump($prep->errorInfo());
            throw new \Exception("Error trying to execute tableHasColumns on Version '{$this->getVersion()}'");
        }
    }

    public abstract function getVersion();

    public abstract function update();

    public abstract function verifyUpdate(): bool;

}