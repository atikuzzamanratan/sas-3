<?php
require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

require_once "../Config/config.php";

$AuthToken = $_REQUEST['authToken'];
$UserId = $_REQUEST["UserID"];
if ($AuthToken != $AuthTokenValue) {
	header('Content-Type: application/json; charset=utf-8');
    echo json_encode($unAuthorizedMsg);
} else {
    $userCompanyQry = $app->getDBConnection()->fetch("SELECT CompanyID FROM userinfo Where id = ?", $UserId);
    $CompanyID = $userCompanyQry->CompanyID;

    $noticeQueryString = "SELECT ntf.[id], ntf.[FromUserID], ui.FullName, ntf.[Notification], ntf.[Status], ntf.[DataEntryDate], ntf.[NotificationReadTime]  
    FROM [Notification] ntf JOIN userinfo ui ON ntf.[FromUserID] = ui.id WHERE ntf.ToUserID = ? AND ntf.CompanyID = ? ORDER BY DataEntryDate DESC, [Status] DESC";

    $noticeQueryFetchResult = $app->getDBConnection()->fetchAll($noticeQueryString, $UserId, $CompanyID);

    $NotificationArrayList = array();

    foreach ($noticeQueryFetchResult as $row) {
        $NotificationArray["id"] = $row->id;
        $NotificationArray["FromUserID"] = $row->FromUserID;
        $NotificationArray["FullName"] = $row->FullName;
        $NotificationArray["Notification"] = $row->Notification;
        $NotificationArray["Status"] = $row->Status;
        $NotificationArray["DataEntryDate"] = date_format($row->DataEntryDate, "Y-m-d H:i:s");
        $NotificationArray["NotificationReadTime"] = date_format($row->NotificationReadTime, "Y-m-d H:i:s");
        $Notification[] = $NotificationArray;
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($Notification);
}
