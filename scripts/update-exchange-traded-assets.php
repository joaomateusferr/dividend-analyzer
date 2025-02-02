<?php

$CurrencyIsoCode = isset($argv[1]) ? $argv[1] : exit(1); //Fill in the currency iso code!

require_once dirname(__FILE__).'/../settings/configuration_file.php';

try{

    $CommonInformationConnection = new SQLite(SQLiteConstants::getDBFilePath());

    $Sql = 'SELECT iso_code FROM currencys WHERE iso_code = :iso_code';
    $Stmt = $CommonInformationConnection->prepare($Sql);
    $Stmt->bindParam(":iso_code", $CurrencyIsoCode);
    $Result = $Stmt->execute();

    if(empty($Result))
        exit(2); //Currency iso code not registered

    $Sql = 'SELECT rowid, ticker, asset_qualification_id, asset_type_id FROM exchange_traded_assets WHERE iso_code = :iso_code';
    $Stmt = $CommonInformationConnection->prepare($Sql);
    $Stmt->bindParam(":iso_code", $CurrencyIsoCode);
    $Result = $Stmt->execute();

    $Asets = $AssetTypeIds = $IdByTickers = [];

    if($Result){

        while($Row = $Stmt->fetch()){

            $FullTicker = $Row['ticker'];
            $IdByTickers[$Row['ticker']] = $Row['rowid'];

            if(!empty($Row['asset_qualification_id']))
                $FullTicker .= $Row['asset_qualification_id'];

            $Asets[$FullTicker] = $Row['asset_type_id'];

            if(!isset($AssetTypeIds[$Row['asset_type_id']]))
                $AssetTypeIds[$Row['asset_type_id']] = true;

        }

    }

    $AssetTypeIds = array_keys($AssetTypeIds);

    $Sql = 'SELECT rowid, identifier FROM asset_types WHERE rowid IN ("'.implode('","', $AssetTypeIds).'")';
    $Stmt = $CommonInformationConnection->prepare($Sql);
    $Result = $Stmt->execute();

    $AssetTypesByIds = [];

    if($Result){

        while($Row = $Stmt->fetch()){

            $AssetTypesByIds[$Row['rowid']] = $Row['identifier'];

        }

    }

    foreach($Asets as $FullTicker => $AssetTypeId){

        $Asets[$FullTicker] = $AssetTypesByIds[$AssetTypeId];

    }

    $Sql = 'SELECT rowid, identifier FROM asset_types WHERE rowid IN ("'.implode('","', $AssetTypeIds).'")';
    $Stmt = $CommonInformationConnection->prepare($Sql);
    $Result = $Stmt->execute();

    $AssetTypesByIds = [];

    if($Result){

        while($Row = $Stmt->fetch()){

            $AssetTypesByIds[$Row['rowid']] = $Row['identifier'];

        }

    }

    $AssetData = Investidor10::getAssetsData($Asets);

    unset($Asets);

    foreach($AssetData as $FullTicker => $AssetInfo){

        $AssetInfo['Ticker'] = $FullTicker;

        $Ticker = preg_replace('/\d+$/', '', $FullTicker);

        $AssetInfo['UpdateDate'] = time();

        if($Ticker != $FullTicker)
            $AssetInfo['Ticker'] = $Ticker;

        $AssetInfo['rowid'] = $IdByTickers[$AssetInfo['Ticker']];
        unset($AssetInfo['Ticker']);

        $Sql = "UPDATE exchange_traded_assets SET market_price = :MarketPrice, update_date = :UpdateDate, average_annual_dividend = :AnnualPayment, net_average_annual_dividend = :NetAnnualPayment WHERE rowid = :rowid";
        $Stmt = $CommonInformationConnection->prepare($Sql);
        $Result = $Stmt->execute($AssetInfo);

    }

    $CommonInformationConnection->close();

} catch (Exception $Ex) {

    error_log($Ex->getMessage());

}