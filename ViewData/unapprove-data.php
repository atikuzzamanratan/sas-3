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
$CommentsFields = json_decode(xss_clean($_REQUEST['CommentsFields']), true);

$loggedUserName = $app->cleanInput($_SESSION['User']);
$loggedUserID = $app->cleanInput($_SESSION['UserID']);

//var_dump($CommentsFields);exit;

$qryMSData = " SELECT PSU, EntryDate, DataName FROM $tbl_name WHERE id = ?";
$row = $app->getDBConnection()->fetch($qryMSData, $id);

$dataEntryDate = date_format($row->EntryDate, 'd/m/Y H:i:s');

$Msg = 'Hello, ' . getName('FullName', 'userinfo', $sendTo) . '.
    <br/>Your data has been un-approved!
    <br/>Record ID: ' . $id . '.
    <br/>PSU No: ' . $row->PSU . '.
    <br/>Data Name: ' . $row->DataName . ' .
    <br/>Data Sending Date: ' . $row->EntryDate . '.
    <br/><font color="red">' . $cause . '</font>
    <br>Please <a href="' . $baseURL . 'webhookURLs/review.php?xFormId=' . $id . '" target="_blank" rel="opener" >CLICK HERE</a> to edit and resend your data to server.';

$qry = "INSERT INTO Notification (FromUserID, ToUserID, Notification, CompanyID) VALUES (?, ?, ?, ?)";
$app->getDBConnection()->query($qry, $sendFrom, $sendTo, $Msg, $companyID);

if ($FromState == 'Approved') {
	$commentsTblName = 'masterdatarecord_Approved';
} elseif ($FromState == 'Pending') {
	$commentsTblName = 'masterdatarecord_Pending';
}
//die($FromState);
date_default_timezone_set("Asia/Dhaka");
$newCommentsFound = 0;

$commentsSQL = "UPDATE $commentsTblName SET IsEdited=0 WHERE XFormRecordId=$id";
$app->getDBConnection()->query($commentsSQL);

foreach ($CommentsFields AS $v) {

	// $fieldName = $v['name'];
	$fieldName = str_replace(['CommentsFields[',']'], '', $v['name']);
	
	if ($v['value'] != '') {
		$EntryDate = date('d-m-Y H:i:s');
		$currentUserName = getValue('userinfo', 'UserName', "id = $sendFrom");
		$comm = "<b>$currentUserName</b> Says at $EntryDate:<br>&nbsp;&nbsp;&nbsp;".xss_clean($v['value'])."<br>";
		$commentsSQL = "UPDATE $commentsTblName SET Comments=COALESCE(Comments, '')+N'$comm', IsCorrected=1, IsEdited=1 WHERE XFormRecordId=$id AND ColumnName=N'$fieldName'";
//die($commentsSQL);
		$app->getDBConnection()->query($commentsSQL);
		$newCommentsFound = 1;
	}
}

/*
if ($newCommentsFound==0) {
	$msg = 'No New Comments Found for Un-Approval !!!';
} else {
	$qryShiftingState = "INSERT INTO DataShiftingCause (FromState, ToState, Cause, UpdatedBy, CompanyID) VALUES (?, ?, ?, ?, ?)";
	$app->getDBConnection()->query($qryShiftingState, $FromState, 'UnApprove', $cause, $sendFrom, $companyID);

	$param = "IsApproved = 2, Cause = N'$cause'";
	$cond = "id = '$id'";

	if (Edit($tbl_name, $param, $cond)) {
		$msg = 'Successfully updated.';
	} else {
		$msg = 'Failed to update data!';
	}
}
*/

$qryShiftingState = "INSERT INTO DataShiftingCause (FromState, ToState, Cause, UpdatedBy, CompanyID) VALUES (?, ?, ?, ?, ?)";
$app->getDBConnection()->query($qryShiftingState, $FromState, 'UnApprove', $cause, $sendFrom, $companyID);

if (strpos($loggedUserName, 'val') === false) {
	$param = "IsApproved = 2, Cause = N'$cause'";
} else {
	$param = "IsApproved = 2, ValidationDate=CURRENT_TIMESTAMP, ValidatorID=$loggedUserID, Cause = N'$cause'";
}
$cond = "id = '$id'";

if (Edit($tbl_name, $param, $cond)) {
    $msg = 'Successfully updated.';

    // ✅ Reset IsCorrected flag after trigger moves data to UnApproved table
    try {
        $resetQry = "UPDATE masterdatarecord_UnApproved
                     SET IsCorrected = 0
                     WHERE XFormRecordId = ?";
        $app->getDBConnection()->query($resetQry, $id);
    } catch (Exception $e) {
        error_log("[unapprove-data.php] Failed to reset IsCorrected: " . $e->getMessage());
    }

} else {
    $msg = 'Failed to update data!';
}


echo $msg;

