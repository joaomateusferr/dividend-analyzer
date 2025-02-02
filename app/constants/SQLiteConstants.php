<?php

class SQLiteConstants
{

    private const DBFileName = 'dividend-analyzer.db';

    public static function getDBFilePath() : string {
        return dirname(__FILE__).'/../../data/'.self::DBFileName;
    }

}