<?php

class SQLite {

    Private $DatabaseFileName = '';
    Private $PDO = null;
    Private $Options = [
        PDO::ATTR_TIMEOUT => 30, // Set timeout to 30s
        PDO::ATTR_EMULATE_PREPARES => false, // Disable emulation mode for "real" prepared statements
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Disable errors in the form of exceptions
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Make the default fetch be an associative array
    ];


    public function __construct(string $DatabaseFileName, array $Options = []) {

        if(empty($DatabaseFileName))
            throw new Exception('Empty database file name');

        $this->DatabaseFileName = $DatabaseFileName;

        if(!empty($Options))
            $this->Options = array_merge($this->Options, $Options); //Replace duplicates with $Options data

        $this->connect();

    }

    private function connect(){

        $DSN = "sqlite:$this->DatabaseFileName";

        try {
            $this->PDO = new PDO($DSN, '', '', $this->Options);
        } catch (Exception $Exception) {
            error_log($Exception->getMessage());
        }

    }

    public function close(){

        try{
            $this->PDO->query('KILL CONNECTION_ID()');
        } catch (Exception $Exception){
            //this will generate an error anyway we only handle the error when killing the connection
        }

        $this->PDO = null;
    }

    public function prepare(string $Sql){
        return $this->PDO->prepare($Sql);
    }

}