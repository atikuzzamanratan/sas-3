<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

if ($_REQUEST['frmID'] != '') {
    $FormID = $frmID = $app->cleanInput($_REQUEST['frmID']);
}

if ($_REQUEST['dataCollectorNamePrefix'] != '') {
    $dataCollectorNamePrefix = $app->cleanInput($_REQUEST['dataCollectorNamePrefix']);
}

if ($_REQUEST['DivisionCode'] != '') {
    $DivisionCode = $app->cleanInput($_REQUEST['DivisionCode']);
}

if ($_REQUEST['DistrictCode'] != '') {
    $DistrictCode = $app->cleanInput($_REQUEST['DistrictCode']);
}

if ($_REQUEST['UpazilaCode'] != '') {
    $UpazilaCode = $app->cleanInput($_REQUEST['UpazilaCode']);
}

if ($_REQUEST['UnionWardCode'] != '') {
    $UnionWardCode = $app->cleanInput($_REQUEST['UnionWardCode']);
}

if ($_REQUEST['MauzaCode'] != '') {
    $MauzaCode = $app->cleanInput($_REQUEST['MauzaCode']);
}

if ($_REQUEST['VillageCode'] != '') {
    $VillageCode = $app->cleanInput($_REQUEST['VillageCode']);
}

$qryCreate = "SELECT ui.id, 
				ui.UserName, 
				ui.FullName, 
				ui.MobileNumber, 
				sup.UserName AS SupUserName,
				sup.FullName AS SupFullName,
				sup.MobileNumber AS SupMobileNumber,
				ISNULL(COUNT(xfr.id), 0) AS Number, ";
if ($FormID==$formIdMainData) {
	$qryCreate .= " (SELECT DISTINCT STRING_AGG(pl.DistrictName, ', ') WITHIN GROUP (ORDER BY pl.DistrictName) FROM PSUList pl WHERE pl.PSUUserID = ui.id) AS DistrictName, ";
	$qryCreate .= " (SELECT ISNULL(SUM(pli.NumberofRecordForMainSurvey),0) FROM PSUList pli WHERE pli.PSUUserID = ui.id and pli.FarmName='') AS FormTarget";
} elseif ($FormID ==$formIdSamplingData) {
	$qryCreate .= " (SELECT DISTINCT STRING_AGG(pl.DistrictName, ', ') WITHIN GROUP (ORDER BY pl.DistrictName) FROM PSUList pl WHERE pl.PSUUserID = ui.id) AS DistrictName, ";
	$qryCreate .= " (SELECT ISNULL(SUM(pli.NumberOfRecord),0) FROM PSUList pli WHERE pli.PSUUserID = ui.id and pli.FarmName='') AS FormTarget";
}elseif ($FormID==$formIdFarmData) {
    $qryCreate .= " (SELECT DISTINCT STRING_AGG(pl.DistrictName, ', ') WITHIN GROUP (ORDER BY pl.DistrictName) FROM PSUList pl WHERE pl.PSUUserID = ui.id) AS DistrictName, ";
    $qryCreate .= " (SELECT ISNULL(SUM(pli.NumberofRecordForMainSurvey),0) FROM PSUList pli WHERE pli.PSUUserID = ui.id and pli.FarmName<>'') AS FormTarget";
}

$qryCreate .= " FROM userinfo ui 
				LEFT JOIN assignsupervisor asup ON asup.UserID = ui.id
				LEFT JOIN userinfo sup ON sup.id = asup.SupervisorID 
				LEFT JOIN xformrecord xfr ON ui.id = xfr.UserID AND xfr.FormID = $FormID
			WHERE ui.UserName LIKE '$dataCollectorNamePrefix%' 
				AND ui.IsActive = 1 
			";

if (!empty($DivisionCode)) {
	$qryCreate .= " AND ui.id IN (SELECT PSUUserID FROM PSUList WHERE DivisionCode = $DivisionCode";
	if (!empty($DistrictCode)) {
		$qryCreate .= " AND DistrictCode = $DistrictCode";
	}
	if (!empty($UpazilaCode)) {
		$qryCreate .= " AND UpazilaCode = $UpazilaCode";
	}
	if (!empty($UnionWardCode)) {
		$qryCreate .= " AND UnionWardCode = $UnionWardCode";
	}
	if (!empty($MauzaCode)) {
		$qryCreate .= " AND MauzaCode = $MauzaCode";
	}
	if (!empty($VillageCode)) {
		$qryCreate .= " AND VillageCode = $VillageCode";
	}
	$qryCreate .= ")";
}

$qryCreate .= "GROUP BY ui.id, 
				ui.UserName, 
				ui.FullName, 
				ui.MobileNumber, 
				sup.UserName, 
				sup.FullName, 
				sup.MobileNumber
			ORDER BY Number DESC;";
			
//die($qryCreate);

$resQry = $app->getDBConnection()->fetchAll($qryCreate);

$data = array();

foreach ($resQry as $row) {
    $UserDBID = $row->id;

    $UserName = $row->UserName;
    $UserFullName = $row->FullName;
    $UserMobileNo = $row->MobileNumber;
    $UserMobileNo = whatsAppLink($UserMobileNo);

    $TotalDataSent = $row->Number;
	$DistrictName = $row->DistrictName;
	$SupFullName = $row->SupFullName;
	$SupMobileNumber = whatsAppLink($row->SupMobileNumber);
	$TotalTarget = $row->FormTarget;

    $DataCollectionPercentage = Ratio($TotalDataSent, $TotalTarget);

    $SubData = array();

    $SubData[] = $UserName;
    $SubData[] = $UserFullName;
    $SubData[] = $UserMobileNo;
	$SubData[] = $DistrictName;
	$SubData[] = $SupFullName;
	$SubData[] = $SupMobileNumber;
    $SubData[] = $TotalTarget;
    $SubData[] = $TotalDataSent;
    $SubData[] = $DataCollectionPercentage;

    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

