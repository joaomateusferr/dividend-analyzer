<?php

require_once dirname(__FILE__).'/../../settings/configuration_file.php';

$CommonInformationConnection = new SQLite(SQLiteConstants::getDBFilePath());

$Queries = [
    'CREATE TABLE financial_institutions (name TEXT NOT NULL, alias TEXT DEFAULT NULL)',
    'CREATE TABLE currencys (iso_code TEXT NOT NULL PRIMARY KEY, name TEXT NOT NULL, symbol TEXT DEFAULT NULL)',
    'CREATE TABLE asset_qualifications (id INTEGER NOT NULL PRIMARY KEY,name TEXT NOT NULL)',
    'CREATE TABLE asset_types (name TEXT NOT NULL, identifier TEXT NOT NULL)',
    'CREATE INDEX asset_types_identifier ON asset_types (identifier)',
    'CREATE TABLE exchanges (id TEXT NOT NULL PRIMARY KEY, name TEXT NOT NULL, alias TEXT DEFAULT NULL)',
    'CREATE INDEX exchanges_alias_index ON exchanges (alias)',
    'CREATE TABLE exchange_traded_assets(
        ticker TEXT NOT NULL,
        asset_qualification_id INTEGER DEFAULT NULL,
        exchange_id INTEGER DEFAULT NULL,
        market_price REAL NOT NULL DEFAULT 0,
        update_date INTEGER DEFAULT NULL,
        average_annual_dividend REAL NOT NULL DEFAULT 0,
        net_average_annual_dividend REAL NOT NULL DEFAULT 0,
        asset_type_id INTEGER NOT NULL,
        asset_subtype_id INTEGER DEFAULT NULL,
        iso_code TEXT NOT NULL,
        FOREIGN KEY (asset_qualification_id) REFERENCES asset_qualifications(id),
        FOREIGN KEY (exchange_id) REFERENCES exchanges(id),
        FOREIGN KEY (asset_type_id) REFERENCES asset_types(rowid),
        FOREIGN KEY (asset_subtype_id) REFERENCES asset_types(rowid),
        FOREIGN KEY (iso_code) REFERENCES currencys(iso_code)
    )',
    'CREATE INDEX asset_type_id ON exchange_traded_assets (asset_type_id)',
    'CREATE INDEX iso_code ON exchange_traded_assets (iso_code)'
];

foreach($Queries as $Sql){

    $Stmt = $CommonInformationConnection->prepare($Sql);
    $Result = $Stmt->execute();

}