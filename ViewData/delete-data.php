<?php
session_start();

error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$id = xss_clean($_REQUEST['id']);
$cause = xss_clean($_REQUEST['cause']);
$tbl_name = xss_clean($_REQUEST['tbl']);
$sendTo = xss_clean($_REQUEST['SendTo']);
$sendFrom = xss_clean($_REQUEST['sendFrom']);
$FromState = xss_clean($_REQUEST['FromState']);
$companyID = xss_clean($_REQUEST['companyID']);

$qryMSData = " SELECT PSU, EntryDate, DataName FROM $tbl_name WHERE id = ?";
$row = $app->getDBConnection()->fetch($qryMSData, $id);

$dataEntryDate = date_format($row->EntryDate, 'd/m/Y H:i:s');

$Msg = 'Hello, ' . getName('FullName', 'userinfo', $sendTo) . '.
    <br/>Your data has been Deleted/Rejected!
    <br/>Deleted record ID: ' . $id . '.
    <br/>PSU No: ' . $row->PSU . '.
    <br/>Data Name: ' . $row->DataName . ' .
    <br/>Data Sending Date: ' . $row->EntryDate . '.
    <br/>' . $cause;

$qry = "INSERT INTO Notification (FromUserID, ToUserID, Notification, CompanyID) VALUES (?, ?, ?, ?)";
$app->getDBConnection()->query($qry, $sendFrom, $sendTo, $Msg, $companyID);

$qryShiftingState = "INSERT INTO DataShiftingCause (FromState, ToState, Cause, UpdatedBy, CompanyID) VALUES (?, ?, ?, ?, ?)";
$app->getDBConnection()->query($qryShiftingState, $FromState, 'Delete', $cause, $sendFrom, $companyID);

$InsertDeletedRecordQry = "SET IDENTITY_INSERT deletedxformrecord ON;
    INSERT INTO deletedxformrecord (id, UserID, FormId, DataName, FormGroupId, CompanyId, DeviceID, XFormsFilePath, EntryDate, IsApproved, PSU, Cause, SampleHHNo, IsChecked, ValidatorID, ValidationDate)
    SELECT id, UserID, FormId, DataName, FormGroupId, CompanyId, DeviceID, XFormsFilePath, EntryDate, IsApproved, PSU, N'$cause', SampleHHNo, IsChecked, ValidatorID, ValidationDate FROM xformrecord WHERE id='$id';
                           SET IDENTITY_INSERT deletedxformrecord OFF;";
$app->getDBConnection()->query($InsertDeletedRecordQry);

$cond = "id='$id'";

if (Delete($tbl_name, $cond)) {
    $msg = 'Successfully deleted.';
} else {
    $msg = 'Failed to delete data!';
}
echo $msg;

