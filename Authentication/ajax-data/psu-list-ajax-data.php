<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

$loggedUserID = $app->cleanInput($_REQUEST['lu']);
$loggedUserName = $app->cleanInput($_REQUEST['un']);

if ($_REQUEST['ci'] != '') {
    $CompanyID = $app->cleanInput($_REQUEST['ci']);
}

if (strpos($loggedUserName, 'val') !== false) {
$qry = "SELECT pl.id, 
			pl.PSU, 
			pl.DivisionName, 
			pl.DivisionCode, 
			pl.DistrictName, 
			pl.DistrictCode, 
			pl.CityCorporationName, 
			pl.CityCorporationCode, 
			pl.UpazilaName, 
			pl.UpazilaCode, 
			pl.MunicipalityName, 
			pl.MunicipalityCode, 
			pl.UnionWardName, 
			pl.UnionWardCode, 
			pl.RMO, 
			pl.MauzaName, 
			pl.MauzaCode, 
			pl.VillageName, 
			pl.VillageCode, 
			pl.PSUUserID, 
			pl.NumberOfRecord,
			ui.UserName AS PSUUserName, 
			ui.FullName AS PSUUserFullName, 
			ui.MobileNumber AS PSUUserMobileNumber 
		FROM PSUList pl 
			JOIN userinfo ui ON ui.id = pl.PSUUserID 
			JOIN assignsupervisor AS a ON pl.PSUUserID = a.UserID 
		WHERE pl.CompanyID = ? AND a.ValidatorID = ?
		ORDER BY pl.PSU ASC";
$resQry = $app->getDBConnection()->fetchAll($qry, $CompanyID, $loggedUserID);
} else {
$qry = "SELECT id, PSU, DivisionName, DivisionCode, DistrictName, DistrictCode, CityCorporationName, CityCorporationCode, UpazilaName, UpazilaCode, 
MunicipalityName, MunicipalityCode, UnionWardName, UnionWardCode, RMO, MauzaName, MauzaCode, VillageName, VillageCode, PSUUserID, NumberOfRecord,
(SELECT UserName FROM userinfo WHERE id = PSUUSerID) AS PSUUserName, (SELECT FullName FROM userinfo WHERE id = PSUUSerID) AS PSUUserFullName, 
(SELECT MobileNumber FROM userinfo WHERE id = PSUUSerID) AS PSUUserMobileNumber 
FROM PSUList WHERE CompanyID = ? ORDER BY PSU ASC";
$resQry = $app->getDBConnection()->fetchAll($qry, $CompanyID);
}

if (strpos($loggedUserName, 'cval') !== false) {
$qry = "SELECT pl.id, 
			pl.PSU, 
			pl.DivisionName, 
			pl.DivisionCode, 
			pl.DistrictName, 
			pl.DistrictCode, 
			pl.CityCorporationName, 
			pl.CityCorporationCode, 
			pl.UpazilaName, 
			pl.UpazilaCode, 
			pl.MunicipalityName, 
			pl.MunicipalityCode, 
			pl.UnionWardName, 
			pl.UnionWardCode, 
			pl.RMO, 
			pl.MauzaName, 
			pl.MauzaCode, 
			pl.VillageName, 
			pl.VillageCode, 
			pl.PSUUserID, 
			pl.NumberOfRecord,
			ui.UserName AS PSUUserName, 
			ui.FullName AS PSUUserFullName, 
			ui.MobileNumber AS PSUUserMobileNumber 
		FROM PSUList pl 
			JOIN userinfo ui ON ui.id = pl.PSUUserID 
			JOIN assignsupervisor AS a ON pl.PSUUserID = a.UserID 
		WHERE pl.CompanyID = ?
		ORDER BY pl.PSU ASC";
$resQry = $app->getDBConnection()->fetchAll($qry, $CompanyID);
}

$data = array();
$il = 1;

foreach ($resQry as $row) {
    $PSU = $row->PSU;
    $DivisionName = $row->DivisionName;
    $DistrictName = $row->DistrictName;
    $CityCorporationName = $row->CityCorporationName;
    $UpazilaName = $row->UpazilaName;
    $MunicipalityName = $row->MunicipalityName;
    $UnionWardName = $row->UnionWardName;
    $MauzaName = $row->MauzaName;
    $VillageName = $row->VillageName;
    $AssignedUser = $row->PSUUserFullName . ' (' . $row->PSUUserName . ')';
	$AssignedMobile = $row->PSUUserMobileNumber;

    $SubData = array();

    $SubData[] = $PSU;
    $SubData[] = $DivisionName;
    $SubData[] = $DistrictName;
    $SubData[] = $CityCorporationName;
    $SubData[] = $UpazilaName;
    $SubData[] = $MunicipalityName;
    $SubData[] = $UnionWardName;
    $SubData[] = $MauzaName;
    $SubData[] = $VillageName;
    $SubData[] = $AssignedUser;
	$SubData[] = whatsAppLink($AssignedMobile);

    $il++;

    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

