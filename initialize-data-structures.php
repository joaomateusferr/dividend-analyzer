<?php

$DataBase = new SQLite3('dividend-analyzer.db');

$DataBase->exec('CREATE TABLE financial_institutions (name TEXT NOT NULL, alias TEXT DEFAULT NULL)');
$DataBase->exec('CREATE TABLE currencys (iso_code TEXT NOT NULL PRIMARY KEY, name TEXT NOT NULL, symbol TEXT DEFAULT NULL)');
$DataBase->exec('CREATE TABLE asset_qualifications (id INTEGER NOT NULL PRIMARY KEY,name TEXT NOT NULL)');
$DataBase->exec('CREATE TABLE asset_types (name TEXT NOT NULL, identifier TEXT NOT NULL)');
$DataBase->exec('CREATE INDEX asset_types_identifier ON asset_types (identifier)');
$DataBase->exec('CREATE TABLE exchanges (id TEXT NOT NULL PRIMARY KEY, name TEXT NOT NULL, alias TEXT DEFAULT NULL)');
$DataBase->exec('CREATE INDEX exchanges_alias_index ON exchanges (alias)');
$DataBase->exec('CREATE TABLE exchange_traded_assets(ticker TEXT NOT NULL, asset_qualification_id INTEGER DEFAULT NULL, exchange_id INTEGER DEFAULT NULL, market_price REAL NOT NULL DEFAULT 0, update_date INTEGER DEFAULT NULL, average_annual_dividend REAL NOT NULL DEFAULT 0, net_average_annual_dividend REAL NOT NULL DEFAULT 0, asset_type_id INTEGER NOT NULL, asset_subtype_id INTEGER DEFAULT NULL, iso_code TEXT NOT NULL, FOREIGN KEY (asset_qualification_id) REFERENCES asset_qualifications(id), FOREIGN KEY (exchange_id) REFERENCES exchanges(id),FOREIGN KEY (asset_type_id) REFERENCES asset_types(rowid), FOREIGN KEY (asset_subtype_id) REFERENCES asset_types(rowid), FOREIGN KEY (iso_code) REFERENCES currencys(iso_code))');
$DataBase->exec('CREATE INDEX asset_type_id ON exchange_traded_assets (asset_type_id)');
$DataBase->exec('CREATE INDEX iso_code ON exchange_traded_assets (iso_code)');