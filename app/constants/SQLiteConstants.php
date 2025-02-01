<?php

class SQLiteConstants
{

    private const DBFileName = 'dividend-analyzer.db';

    public static function getDBFileName() : string {
        return self::DBFileName;
    }

}