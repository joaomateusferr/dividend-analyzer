<?php

class SQLiteConstants
{

    public static function getDBFilePath(string $Database) : string {
        return dirname(__FILE__).'/../../data/'.$Database.'.db';
    }

}