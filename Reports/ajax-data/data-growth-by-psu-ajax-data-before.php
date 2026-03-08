<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

if ($_REQUEST['sql'] != '') {
    $sql = $app->cleanInput($_REQUEST['sql']);
}

if ($_REQUEST['fid'] != '') {
    $FormID = $app->cleanInput($_REQUEST['fid']);
}

$resQry = $app->getDBConnection()->fetchAll($sql);

$data = array();
$il = 1;

foreach ($resQry as $row) {
    $Collected = $row->Collected;
    if (is_null($Collected) or $Collected == '') {
        $Collected = 0;
    }
    if ($FormID == $formIdSamplingData) {
        $NumberOfRecord = $row->NumberOfRecord;
    } else if ($FormID == $formIdMainData) {
        $NumberOfRecord = $row->NumberOfRecordForMainSurvey;
    }

    $User = $row->FullName . ' (' . $row->UserName . ')';
    $MobileNo = $row->MobileNumber;
    $PSU = $row->PSU;
    $DivisionName = $row->DivisionName;
    $DistrictName = $row->DistrictName;
    $Remaining = $NumberOfRecord - $Collected;

    if (!is_null($NumberOfRecord)) {
        $DataCollectionPercentage = ($Collected / $NumberOfRecord) * 100;
        $Ratio = number_format($DataCollectionPercentage, 2);
        if ($Ratio >= 100) {
            $Ratio = "<b style='color:#357a38'>$Ratio%</b>";
        } elseif ($Ratio >= 90) {
            $Ratio = "<b style='color:#6fbf73'>$Ratio%</b>";
        } elseif ($Ratio >= 80) {
            $Ratio = "<b style='color:#AA00FF'>$Ratio%</b>";
        } elseif ($Ratio >= 70) {
            $Ratio = "<b style='color:#009688'>$Ratio%</b>";
        } elseif ($Ratio >= 60) {
            $Ratio = "<b style='color:#2196F3'>$Ratio%</b>";
        } else {
            $Ratio = "<b style='color:#f6685e'>$Ratio%</b>";
        }
    } else {
        $Ratio = "<b style='color:#f6685e'>0%</b>";
    }

    $SubData = array();

    $SubData[] = $il;
    $SubData[] = $DivisionName;
    $SubData[] = $DistrictName;
    $SubData[] = $User;
    $SubData[] = $MobileNo;
    $SubData[] = $PSU;
    $SubData[] = $NumberOfRecord;
    $SubData[] = $Collected;
    $SubData[] = $Remaining;
    $SubData[] = $Ratio;

    $il++;

    $data[] = $SubData;
}
/*$data = array();
$SubData[] = $sql;
$data[] = $SubData;*/

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';
