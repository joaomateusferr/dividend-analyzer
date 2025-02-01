<?php

class TypeConstants
{

    private const CurrencyByType = [
        'ACAO' => 'BRL',
        'FII' => 'BRL',
        'ETF-BR' => 'BRL',
        'BDR' => 'BRL',
        'ADR' => 'USD',
        'ETF-US' => 'USD',
        'REIT' => 'USD',
        'STOCK' => 'USD',
    ];

    public static function getCurrencyFromType(string $Type) : string | bool {

        if(isset(self::CurrencyByType[$Type]))
            return self::CurrencyByType[$Type];

        return false;

    }

}