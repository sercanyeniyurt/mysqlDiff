<?php

namespace mysqlDiff;

class mysqlDiff
{

    protected $connection;

    public function connectionTest($host,$port,$username,$password,$databasename){
        try {
            $this->connection = new \PDO("mysql:host=$host:$port;dbname=$databasename", $username, $password);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return 1;
        } catch(\PDOException $e) {
            return 0;
        }
    }

    public function connectionGetTablesTypes($host,$port,$username,$password,$databasename){
        try {
            $this->connection = new \PDO("mysql:host=$host:$port;dbname=$databasename", $username, $password);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $tables = $this->connection->query("SELECT table_name FROM information_schema.tables WHERE table_schema = '$databasename'");
            $tableList = $tables->fetchAll(\PDO::FETCH_ASSOC);

            $tableRow = [];
            foreach ($tableList as $tableName){
                $rowData = [];
                $tableTypes = $this->connection->query("SHOW FIELDS FROM ".$tableName['table_name']."");
                $rowData['name'] = $tableName['table_name'];
                $rowData['content'] = $tableTypes->fetchAll(\PDO::FETCH_ASSOC);
                array_push($tableRow,$rowData);
            }


            return $tableRow;

        } catch(\PDOException $e) {
            return 0;
        }
    }

}