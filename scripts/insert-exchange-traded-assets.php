<?php

$AsetsPath = isset($argv[1]) ? $argv[1] : exit(1); //Fill in the asets file path!

require_once dirname(__FILE__).'/../settings/configuration_file.php';

if(pathinfo($AsetsPath, PATHINFO_EXTENSION) != 'json')
    exit(2); //Asets file is not a json!

$Json = file_get_contents($AsetsPath);

if(!json_validate($Json))
    exit(3);   //Asets file is not formatted as json!

$Assets = json_decode($Json, true);

try{

    $CurrencyByType = $Exchanges = $Investidor10Assets = [];

    foreach($Assets as $FullTicker => $Infos){

        if(!isset($Infos['Type'])){

            unset($Assets[$FullTicker]);
            continue;

        }

        if(!isset($CurrencyByType[$Infos['Type']])){

            $Currency = TypeConstants::getCurrencyFromType($Infos['Type']);

            if(!empty($Currency))
                $CurrencyByType[$Infos['Type']] = $Currency;

        }

        if(isset($Infos['Subtype']) && !isset($CurrencyByType[$Infos['Subtype']])){

            $Currency = TypeConstants::getCurrencyFromType($Infos['Subtype']);
            $CurrencyByType[$Infos['Subtype']] = $Currency;

        }

        if(isset($Infos['Exchange'])){

            if(!isset($Exchanges[$Infos['Exchange']]))
                $Exchanges[$Infos['Exchange']] = true;

        }

    }

    if(empty($CurrencyByType))
        exit("Empty CurrencyByType!");

    $Types = array_keys($CurrencyByType);

    foreach($CurrencyByType as $Index => $Currency){

        if(empty($Currency))
            unset($CurrencyByType[$Index]);

    }

    $CommonInformationConnection = new SQLite(SQLiteConstants::getDBFilePath());
    $Sql = 'SELECT rowid, identifier FROM asset_types WHERE identifier IN ("'.implode('","', $Types).'")';
    $Stmt = $CommonInformationConnection->prepare($Sql);
    $Result = $Stmt->execute();

    unset($Types);

    $IdByType = [];

    if($Result){

        while($Row = $Stmt->fetch()){

            $IdByType[$Row['identifier']] = $Row['rowid'];

        }

    }

    var_dump($IdByType);exit;

    if(empty($IdByType))
        exit("Empty IdByType!");

    if(!empty($Exchanges)){

        $ExchangeIds = array_keys($Exchanges);

        $Sql = 'SELECT id  FROM exchanges WHERE id IN ("'.implode('","', $ExchangeIds).'")';
        $Stmt = $CommonInformationConnection->prepare($Sql);
        $Result = $Stmt->execute();

        $ExchangeIds = [];

        if($Result){

            while($Row = $Stmt->fetch()){

                $ExchangeIds[$Row['id']] = true;

            }

        }

        foreach($Exchanges as $Id => $Info){

            if(!isset($ExchangeIds[$Id]))
                unset($Exchanges[$Id]);

        }

    }

    foreach($Assets as $FullTicker => $Infos){

        if(isset($Infos['Exchange']) && !isset($Exchanges[$Infos['Exchange']]))
            continue;

        $Investidor10Assets[$FullTicker] = $Infos['Type'];

    }

    $AssetData = Investidor10::getAssetsData($Investidor10Assets);

    unset($Investidor10Assets);

    foreach($AssetData as $FullTicker => $AssetInfo){

        $AssetInfo['Ticker'] = $FullTicker;

        $Ticker = preg_replace('/\d+$/', '', $FullTicker);

        $AssetInfo['AssetQualification'] = null;
        $AssetInfo['UpdateDate'] = time();

        if($Ticker != $FullTicker){
            $AssetInfo['Ticker'] = $Ticker;
            $AssetInfo['AssetQualification'] = str_replace($Ticker,"",$FullTicker);
            $AssetInfo['AssetQualification'] = (int) trim($AssetInfo['AssetQualification']);
        }

        $AssetInfo['Type'] = $Assets[$FullTicker]['Type'];
        $AssetInfo['Exchange'] = isset($Assets[$FullTicker]['Exchange']) ? $Assets[$FullTicker]['Exchange'] : null;
        $AssetInfo['Subtype'] = isset($Assets[$FullTicker]['Subtype']) ? $Assets[$FullTicker]['Subtype'] : null;

        if(!isset($IdByType[$AssetInfo['Type']]))
            continue;

        if(isset($AssetInfo['Subtype']) && !isset($IdByType[$AssetInfo['Subtype']]))
            continue;

        $AssetInfo['AssetSubtypeId'] = isset($AssetInfo['Subtype']) ? $IdByType[$AssetInfo['Subtype']] : null;

        if(!isset($CurrencyByType[$AssetInfo['Type']]))
            continue;

        $Sql = "INSERT INTO exchange_traded_assets (ticker, asset_qualification_id, exchange_id, market_price, update_date, average_annual_dividend, net_average_annual_dividend, asset_type_id, asset_subtype_id, iso_code) VALUES (:Ticker, :AssetQualification, :Exchange, :MarketPrice, :UpdateDate, :AnnualPayment, :NetAnnualPayment, :AssetTypeId, :AssetSubtypeId, :IsoCode)";
        $Stmt = $CommonInformationConnection->prepare($Sql);
        $Stmt->bindParam(":Ticker", $AssetInfo['Ticker']);
        $Stmt->bindParam(":AssetQualification", $AssetInfo['AssetQualification']);
        $Stmt->bindParam(":Exchange", $AssetInfo['Exchange']);
        $Stmt->bindParam(":MarketPrice", $AssetInfo['MarketPrice']);
        $Stmt->bindParam(":UpdateDate", $AssetInfo['UpdateDate']);
        $Stmt->bindParam(":AnnualPayment", $AssetInfo['AnnualPayment']);
        $Stmt->bindParam(":NetAnnualPayment", $AssetInfo['NetAnnualPayment']);
        $Stmt->bindParam(":AssetTypeId", $IdByType[$AssetInfo['Type']]);
        $Stmt->bindParam(":AssetSubtypeId", $AssetInfo['AssetSubtypeId']);
        $Stmt->bindParam(":IsoCode", $CurrencyByType[$AssetInfo['Type']]);
        $Result = $Stmt->execute();

    }

    $CommonInformationConnection->close();

} catch (Exception $Ex) {

    error_log($Ex->getMessage());

}