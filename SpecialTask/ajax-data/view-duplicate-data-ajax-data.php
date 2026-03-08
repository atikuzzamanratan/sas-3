<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

$baseURL = get_base_url();

if ($_REQUEST['selFormID'] != '') {
    $SelectedFormID = $app->cleanInput($_REQUEST['selFormID']);
}

if ($_REQUEST['selCompanyID'] != '') {
    $SelectedCompanyID = $app->cleanInput($_REQUEST['selCompanyID']);
}

if ($_REQUEST['param1'] != '') {
    $param1 = $app->cleanInput($_REQUEST['param1']);
}

if ($_REQUEST['param2'] != '') {
    $param2 = $app->cleanInput($_REQUEST['param2']);
}

if ($_REQUEST['param3'] != '') {
    $param3 = $app->cleanInput($_REQUEST['param3']);
}

if ($_REQUEST['param4'] != '') {
    $param4 = $app->cleanInput($_REQUEST['param4']);
}

if ($_REQUEST['param5'] != '') {
    $param5 = $app->cleanInput($_REQUEST['param5']);
}

$DuplicateDataQuery = "EXEC FindDuplicateRecord '$SelectedFormID', '$SelectedCompanyID', '$param1', '$param2', '$param3', '$param4', '$param5'";
$DuplicateDataQueryRS = $app->getDBConnection()->fetchAll($DuplicateDataQuery);

$processDataTableDropQuery = "DROP TABLE ##temp_DuplicateRecordTable;";
$app->getDBConnection()->query($processDataTableDropQuery);

$data = array();
$il = 1;

foreach ($DuplicateDataQueryRS as $row) {
    $xFromRecordID = $row->XFormRecordId;
    $duplicateRecordID = $row->DuplicateRecordID;

    $SubData = array();

    $SubData[] = $il;
    $SubData[] = $xFromRecordID;
    $SubData[] = $duplicateRecordID;

    $il++;

    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';
