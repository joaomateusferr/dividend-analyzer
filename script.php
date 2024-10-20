<?php

$Page = getPage('https://investidor10.com.br/etfs-global/sphd/');
$TableData = parseTable($Page, "@class, 'table-dividends-history'");

$Limit = strtotime("-1 year");

$Result = [];

$Result['NDIV11'] = [];
$Result['NDIV11']['AnnualPayment'] = 0;

foreach($TableData as $Index => $Line){

    if(isset($TableData[$Index]['Data COM']))
        unset($TableData[$Index]['Data COM']);

    $Pagamento = $TableData[$Index]['Pagamento'];

    $DataParcial = explode('/', $TableData[$Index]['Pagamento']);
    $TableData[$Index]['Pagamento'] = $DataParcial[1] . '/' . $DataParcial[0] . '/' . $DataParcial[2];
    $TableData[$Index]['Pagamento'] = strtotime($TableData[$Index]['Pagamento'].' 00:00:00');
    $TableData[$Index]['Valor'] = (float) str_replace(",",".",$TableData[$Index]['Valor']);

    if($TableData[$Index]['Pagamento'] >= $Limit && $TableData[$Index]['Pagamento'] <= time()){
        echo $Pagamento."\n";
        $Result['NDIV11']['AnnualPayment'] = $Result['NDIV11']['AnnualPayment'] + $TableData[$Index]['Valor'];
    }

}

$Result['NDIV11']['MonthlyPayment'] = $Result['NDIV11']['AnnualPayment']/12;


var_dump($Result);


function parseTable (string $Page, string $TableQuery) : array {

    $TableData = [];
    $TableFields = [];

    $Document = new DOMDocument();
    @$Document->loadHTML($Page);
    $XPath = new DOMXPath($Document);

    $Table = $XPath->query("//table[contains($TableQuery)]");

    $TableHead = $XPath->query(".//thead/tr", $Table[0]);

    foreach($TableHead[0] as $ColumnsContent){

        $TableColumnsLines = $XPath->query(".//th", $ColumnsContent);

        foreach($TableColumnsLines as $ColumnsLines){

            $TableFields[] = trim($ColumnsLines->nodeValue);

        }

    }

    $TableRows = $XPath->query(".//tbody/tr", $Table[0]);

    foreach ($TableRows as $TableRowsContent) {

        $TableDataLines = $XPath->query(".//td", $TableRowsContent);

        $LineInformation = [];

        foreach($TableDataLines as $Index => $DataLine){

            $LineInformation[$TableFields[$Index]] = trim($DataLine->nodeValue);

        }

        $TableData[] = $LineInformation;

    }

    return $TableData;

}

function getPage (string $Url) : string {

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => $Url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:35.0) Gecko/20100101 Firefox/35.0',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;

}

