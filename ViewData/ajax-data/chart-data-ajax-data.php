<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

if ($_REQUEST['qry'] != '') {
    $qry = $app->cleanInput($_REQUEST['qry']);
}

$resQry = $app->getDBConnection()->fetchAll($qry);

$data = array();

foreach ($resQry as $row) {
    $ResponseValue= $row->ColVal;
    $ResponseTitle= $row->ChoiceLabel;
    //$Response = "($ResponseValue) $ResponseTitle";
    //$Response = $ResponseTitle;
    $TotalResponse= $row->ColValTotal;
    $ResponseRatio = $row->ColValPercent . '%';

    $SubData = array();

    $SubData[] = $ResponseTitle;
    $SubData[] = $ResponseValue;
    $SubData[] = $TotalResponse;
    $SubData[] = $ResponseRatio;

    $data[] = $SubData;
}

/*$SubData[] = $qry;
$data[] = $SubData;*/

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

