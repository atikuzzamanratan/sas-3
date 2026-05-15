<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

if ($_REQUEST['frmID'] != '') {
    $FormID = $app->cleanInput($_REQUEST['frmID']);
}

if ($_REQUEST['colName'] != '') {
    $colName = $app->cleanInput($_REQUEST['colName']);
}

if ($_REQUEST['targetData'] != '') {
    $targetData = $app->cleanInput($_REQUEST['targetData']);
}

$qryCreate = "EXEC find_Duplicate_Missing_HH_For_All_Updated $FormID, '$colName', $targetData";


$resQry = $app->getDBConnection()->fetchAll($qryCreate);

$data = array();
$il = 1;
foreach ($resQry as $row) {
    $UserID = $row->UserID;
    $UserName = getValue('userinfo', 'UserName', "id = $UserID");
    $UserFullName = getValue('userinfo', 'FullName', "id = $UserID");
    $UserMobileNo = getValue('userinfo', 'MobileNumber', "id = $UserID");
    $UserMobileNo = whatsAppLink($UserMobileNo);

    $PSU = $row->PSU;
	$UniqueData = $row->UniqueData;
	$MissingData = $row->Missing;
	$Duplicate = $row->Duplicate;
	$Collected = $row->Collected;

    $SubData = array();

    $SubData[] = $il;
    $SubData[] = $UserName;
    $SubData[] = $UserFullName;
    $SubData[] = $UserMobileNo;
	$SubData[] = $PSU;
	$SubData[] = $UniqueData;
	$SubData[] = $MissingData;
    $SubData[] = $Duplicate;
    $SubData[] = $Collected;

    $il++;

    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

