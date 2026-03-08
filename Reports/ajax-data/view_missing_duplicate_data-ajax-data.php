<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

if ($_REQUEST['fid'] != '') {
    $FormID = $app->cleanInput($_REQUEST['fid']);
}

if ($_REQUEST['column'] != '') {
    $column = $app->cleanInput($_REQUEST['column']);
}

if ($_REQUEST['maxValue'] != '') {
    $maxValue = $app->cleanInput($_REQUEST['maxValue']);
}

$sql = "EXEC find_Duplicate_Missing_HH $FormID, '$column', $maxValue;";

$resQry = $app->getDBConnection()->fetchAll($sql);

$data = array();
$il = 1;

foreach ($resQry as $row) {
    $psu = $row->PSU;
    $uniqueData = $row->UniqueData;
    $missingData = $row->Missing;
    $duplicateData = $row->Duplicate;
    $collectedData = $row->Collected;

    $DivName = getValue('PSUList', 'DivisionName',"PSU = $psu");
    $DistName = getValue('PSUList', 'DistrictName',"PSU = $psu");

    $UserID = getValue('PSUList', 'PSUUserID', "PSU = $psu");
    $UserName = getValue('userinfo', 'UserName', "id = $UserID");
    $UserFullName = getValue('userinfo', 'FullName', "id = $UserID");
    $UserInfo = "$UserFullName ($UserName)";

    $SubData = array();

    //$SubData[] = $il;
    $SubData[] = $DivName;
    $SubData[] = $DistName;
    $SubData[] = $UserInfo;
    $SubData[] = $psu;
    $SubData[] = $uniqueData;
    $SubData[] = $missingData;
    $SubData[] = $duplicateData;
    $SubData[] = $collectedData;

    $il++;

    $data[] = $SubData;
}
/*$data = array();
$SubData[] = $sql;
$data[] = $SubData;*/

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';
