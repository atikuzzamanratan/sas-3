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

if($FormID==$formIdFarmData){
    $filterFarm = "p.FarmName<>'' and ";
}else{
    $filterFarm = "p.FarmName='' and ";
}

if ($_REQUEST['column'] != '') {
    $column = $app->cleanInput($_REQUEST['column']);
}

if ($_REQUEST['loggedUserCompanyID'] != '') {
    $loggedUserCompanyID = $app->cleanInput($_REQUEST['loggedUserCompanyID']);
}

$DivisionCode = $app->cleanInput($_REQUEST['DivisionCode']);
$DistrictCode = $app->cleanInput($_REQUEST['DistrictCode']);
$UpazilaCode = $app->cleanInput($_REQUEST['UpazilaCode']);
$UnionWardCode = $app->cleanInput($_REQUEST['UnionWardCode']);
$MauzaCode = $app->cleanInput($_REQUEST['MauzaCode']);
$VillageCode = $app->cleanInput($_REQUEST['VillageCode']);



$sql1 = "
    IF object_id('tempdb..##TempTableForPSU') is not null
    BEGIN
        drop table ##TempTableForPSU
    END
    CREATE TABLE ##TempTableForPSU(PSU VARCHAR(500), Collected int);
    INSERT ##TempTableForPSU(PSU, Collected) SELECT PSU, COUNT(id) AS collected FROM xformrecord 
    WHERE FormId = '$FormID' AND CompanyID = '$loggedUserCompanyID' GROUP BY PSU;";

$app->getDBConnection()->Query($sql1);

$tempTable = "##TempTableForPSU";
$sql = "SELECT DISTINCT p.PSU,p.DivisionName, p.DistrictName, t.Collected, u.UserName, u.FullName, u.MobileNumber, p.PSUUserID, p.$column 
FROM PSUList AS p 
LEFT JOIN $tempTable AS t ON p.PSU = t.PSU
JOIN userinfo AS u ON p.PSUUserID=u.id 
WHERE ".$filterFarm." p.CompanyID = $loggedUserCompanyID AND 1=1";

if ($check === "chkAll") {
    $sql .= " ";
} else {
    if (!empty($DivisionCode)) {
        $sql .= " AND ( p.DivisionCode = '" . $DivisionCode . "') ";
    }
    if (!empty($DistrictCode)) {
        $sql .= " AND ( p.DistrictCode = '" . $DistrictCode . "') ";
    }
    if (!empty($UpazilaCode)) {
        $sql .= " AND ( p.UpazilaCode = '" . $UpazilaCode . "') ";
    }
    if (!empty($UnionWardCode)) {
        $sql .= " AND ( p.UnionWardCode = '" . $UnionWardCode . "') ";
    }
    if (!empty($MauzaCode)) {
        $sql .= " AND ( p.MauzaCode = '" . $MauzaCode . "') ";
    }
    if (!empty($VillageCode)) {
        $sql .= " AND ( p.VillageCode = '" . $VillageCode . "') ";
    }
}

$sql .= " ORDER BY p.PSU ASC;";

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

    // $SubData[] = $il;
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
