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

if ($_REQUEST['selUserID'] != '') {
    $SelectedUserID = $app->cleanInput($_REQUEST['selUserID']);
}

if ($_REQUEST['selStartDate'] != '') {
    $SelectedStartDate = $app->cleanInput($_REQUEST['selStartDate']);
}

if ($_REQUEST['selEndDate'] != '') {
    $SelectedEndDate = $app->cleanInput($_REQUEST['selEndDate']);
}

if ($_REQUEST['selCheckAll'] != '') {
    $SelectedCheckAll = $app->cleanInput($_REQUEST['selCheckAll']);
}

if ($_REQUEST['lun'] != '') {
    $LoggedUserName = $app->cleanInput($_REQUEST['lun']);
}

if ($_REQUEST['lui'] != '') {
    $LoggedUserID = $app->cleanInput($_REQUEST['lui']);
}

if ($_REQUEST['ci'] != '') {
    $SelectedCompanyID = $app->cleanInput($_REQUEST['ci']);
}

if ($SelectedCheckAll == '1') {
    $qry = "SELECT dxfr.id, dxfr.DataName, dxfr.SampleHHNo, dxfr.PSU, ui.id as UserID, ui.UserName, ui.FullName, ui.MobileNumber, dxfr.DeviceID, dxfr.EntryDate, dxfr.FormGroupId, dxfr.XFormsFilePath FROM deletedxformrecord dxfr 
    JOIN userinfo ui ON dxfr.UserID = ui.id 
    WHERE dxfr.FormId = ? AND dxfr.CompanyId = ? 
    ORDER BY dxfr.id DESC";
    $resQry = $app->getDBConnection()->fetchAll($qry, $SelectedFormID, $SelectedCompanyID);

} else {
    if (!empty($SelectedUserID)) {
        if (!empty($SelectedStartDate) && !empty($SelectedEndDate)) {
            $qry = "SELECT dxfr.id, dxfr.DataName, dxfr.SampleHHNo, dxfr.PSU, ui.id as UserID, ui.UserName, ui.FullName, ui.MobileNumber, dxfr.DeviceID, dxfr.EntryDate, dxfr.FormGroupId, dxfr.XFormsFilePath FROM deletedxformrecord dxfr 
            JOIN userinfo ui ON dxfr.UserID = ui.id 
            WHERE dxfr.FormId = ? AND dxfr.CompanyId = ? AND (dxfr.EntryDate BETWEEN ? AND ?)
            ORDER BY dxfr.id DESC";
            $resQry = $app->getDBConnection()->fetchAll($qry, $SelectedFormID, $SelectedCompanyID, $SelectedStartDate, $SelectedEndDate);
        } else {
            $qry = "SELECT dxfr.id, dxfr.DataName, dxfr.SampleHHNo, dxfr.PSU, ui.id as UserID, ui.UserName, ui.FullName, ui.MobileNumber, dxfr.DeviceID, dxfr.EntryDate, dxfr.FormGroupId, dxfr.XFormsFilePath FROM deletedxformrecord dxfr 
            JOIN userinfo ui ON dxfr.UserID = ui.id 
            WHERE dxfr.FormId = ? AND dxfr.CompanyId = ? AND dxfr.UserID = ?
            ORDER BY dxfr.id DESC";
            $resQry = $app->getDBConnection()->fetchAll($qry, $SelectedFormID, $SelectedCompanyID, $SelectedUserID);
        }
    }
}

$data = array();
$il = 1;

foreach ($resQry as $row) {
    $RecordID = $row->id;
    $HhNo = $row->SampleHHNo;
    $PSU = $row->PSU;

    $UserID = $row->UserID;
    $UserName = $row->UserName;
    $UserFullName = $row->FullName;
    $UserData = "$UserFullName ($UserName/$UserID)";

    $UserMobileNo = $row->MobileNumber;
    $DataName = $row->DataName;
    $XFormsFilePath = $row->XFormsFilePath;
    $DeviceID = $row->DeviceID;
    $EntryDate = date_format($row->EntryDate, 'd-m-Y H:i:s');

    $SubData = array();

    $SubData[] = $RecordID;
    $SubData[] = $HhNo;
    $SubData[] = $PSU;
    $SubData[] = $UserData;
    $SubData[] = $UserMobileNo;
    $SubData[] = $DataName;
    $SubData[] = $DeviceID;
    $SubData[] = $EntryDate;

    $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">
                    <button title='Restore Data' type=\"button\" class=\"btn btn-outline-success\" style=\"display: inline-block\" onclick=\"RestoreDeletedDataRecord('$RecordID');\"><i class=\"fas fa-trash-restore\"></i></button>
                    
                    <button title='Delete Permanently' type=\"button\" class=\"btn btn-outline-danger\" style=\"display: inline-block\" onclick=\"PermanentDeleteDataRecord('$RecordID');\"><i class=\"far fa-trash-alt\"></i></button>
                </div>";

    $SubData[] = $actions;

    $il++;

    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

