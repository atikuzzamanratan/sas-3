<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

if ($_REQUEST['par'] != '') {
    $param = $app->cleanInput($_REQUEST['par']);
}

if ($_REQUEST['ci'] != '') {
    $SelectedCompanyId = $app->cleanInput($_REQUEST['ci']);
}

if ($_REQUEST['si'] != '') {
    $SelectedSupervisorID = $app->cleanInput($_REQUEST['si']);
}

if ($_REQUEST['lun'] != '') {
    $loggedUserName = $app->cleanInput($_REQUEST['lun']);
}

if ($param == "1") {
    $qry = "SELECT agns.id, 
				agns.DataEntryDate, 
				agns.ValidatorID,
				agns.UserID,
				ui.FullName, 
				ui.UserName, 
				ui.MobileNumber 
			FROM assignsupervisor agns
				JOIN userinfo ui on agns.UserID = ui.id 
			WHERE agns.ValidatorID IS NOT NULL";
    
$resQry = $app->getDBConnection()->fetchAll($qry);
} else {
    $qry = "SELECT agns.id, 
				agns.DataEntryDate, 
				agns.ValidatorID, 
				agns.UserID, 
				ui.FullName, 
				ui.UserName, 
				ui.MobileNumber 
			FROM assignsupervisor agns
				JOIN userinfo ui on agns.UserID = ui.id
			WHERE agns.ValidatorID = ?";

    $resQry = $app->getDBConnection()->fetchAll($qry, $SelectedSupervisorID);
}

$data = array();
$il = 1;

foreach ($resQry as $row) {
    $recordID = $row->id;

    $SupervisorID = $row->ValidatorID;
    $SupervisorUserName = getValue('userinfo', 'UserName', "id = $SupervisorID");
    $SupervisorFullName = getValue('userinfo', 'FullName', "id = $SupervisorID");
    $SupervisorData = $SupervisorFullName . ' (' . $SupervisorUserName . '/' . $SupervisorID . ')';

    $UserID = $row->UserID;
    $UserName = $row->UserName;
    $UserFullName = $row->FullName;
    $UserData = $UserFullName . ' (' . $UserName . '/' . $UserID . ')';

    $CreateDate = date_format($row->DataEntryDate, "d-m-Y");

    $SubData = array();

    $SubData[] = $il;
    $SubData[] = $SupervisorData;
    $SubData[] = $UserData;
    $SubData[] = $CreateDate;

    $il++;

    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

