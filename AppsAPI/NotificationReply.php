<?php
require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

$FromUserID = xss_clean($_REQUEST['FromUserID']);
$ToUserID = xss_clean($_REQUEST['ToUserID']);
$Notification = xss_clean($_REQUEST['Notification']);

$resCompanyID = $app->getDBConnection()->fetch("SELECT CompanyID FROM userinfo Where id = ?", $FromUserID);
$CompanyID = $resCompanyID->CompanyID;

$qryString = "INSERT INTO Notification (FromUserID, ToUserID, Notification, Status, CompanyID) VALUES (?, ?, ?, ?, ?)";
$qryRes = $app->getDBConnection()->query($qryString, $FromUserID, $ToUserID, $Notification, 0, $CompanyID);

if (true) {
    $response["success"] = 1;
} else {
    $response["success"] = 0;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
