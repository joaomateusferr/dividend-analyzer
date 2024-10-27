<?php

$Asets = [

    'HGBS11' => [
        'Type' => 'FII',
    ],
    'KNRI11' => [
        'Type' => 'FII',
    ],
    'HGLG11' => [
        'Type' => 'FII',
    ],
    'ALZR11' => [
        'Type' => 'FII',
    ],
    'XPML11' => [
        'Type' => 'FII',
    ],
    'XPLG11' => [
        'Type' => 'FII',
    ],
    'CPTS11' => [
        'Type' => 'FII',
    ],
    'CPTS11' => [
        'Type' => 'FII',
    ],
    'BCIA11' => [
        'Type' => 'FII',
    ],
    'VALE3' => [
        'Type' => 'AÇÃO',
    ],
    'BBDC4' => [
        'Type' => 'AÇÃO',
    ],
    'NDIV11' => [
        'Type' => 'ETF',
    ],
    'IVV' => [
        'Type' => 'ETF-US',
    ],
    'VNQ' => [
        'Type' => 'ETF-US',
    ],
    'NOBL' => [
        'Type' => 'ETF-US',
    ],
    'SCHD' => [
        'Type' => 'ETF-US',
    ],
    'SPHQ' => [
        'Type' => 'ETF-US',
    ],
    'SPHD' => [
        'Type' => 'ETF-US',
    ],
];

$Investidor10AsetTypeMap = [
    'FII' => 'fiis',
    'AÇÃO' => 'acoes',
    'ETF-US' => 'etfs-global',
    'ETF' => 'etfs',
];

$Result = [];
$TimeLimit = strtotime("-1 year");

foreach($Asets as $Ticker => $Infos){

    $Investidor10Url = '';
    $Investidor10Url .= 'https://investidor10.com.br/';
    $Investidor10Url .= $Investidor10AsetTypeMap[$Infos['Type']].'/';
    $Investidor10Url .= strtolower($Ticker).'/';

    $Page = getPage($Investidor10Url);

    if(empty($Page)){
        echo"Error - $Ticker - $Investidor10Url\n";
        continue;
    }

    $TableData = parseTable($Page, "@class, 'table-dividends-history'");

    $Result[$Ticker] = [];
    $Result[$Ticker]['AnnualPayment'] = 0;

    foreach($TableData as $Index => $Line){

        if(isset($TableData[$Index]['Data COM']))
            unset($TableData[$Index]['Data COM']);

        $DataParcial = explode('/', $TableData[$Index]['Pagamento']);
        $TableData[$Index]['Pagamento'] = $DataParcial[1] . '/' . $DataParcial[0] . '/' . $DataParcial[2];
        $TableData[$Index]['Pagamento'] = strtotime($TableData[$Index]['Pagamento'].' 00:00:00');
        $TableData[$Index]['Valor'] = (float) str_replace(",",".",$TableData[$Index]['Valor']);

        if($TableData[$Index]['Pagamento'] >= $TimeLimit && $TableData[$Index]['Pagamento'] <= time()){
            $Result[$Ticker]['AnnualPayment'] = $Result[$Ticker]['AnnualPayment'] + $TableData[$Index]['Valor'];
        }

    }

    $Result[$Ticker]['MonthlyPayment'] = $Result[$Ticker]['AnnualPayment']/12;

    sleep(1);

}

foreach($Result as $Ticker => $Infos){
    echo $Ticker,' - '.$Infos['AnnualPayment'].' - '.$Infos['MonthlyPayment']."\n";
}


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

