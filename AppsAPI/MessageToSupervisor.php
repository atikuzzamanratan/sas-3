<?php
require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

$FromUserID = xss_clean($_REQUEST['FromUserID']);
$Notification = xss_clean($_REQUEST['Message']);

$resCompanyId = $app->getDBConnection()->fetch("SELECT CompanyID FROM userinfo Where id = ?", $FromUserID);
$CompanyID = $resCompanyId->CompanyID;

$resSupervisorId = $app->getDBConnection()->fetch("SELECT SupervisorID FROM assignsupervisor where UserID = ?", $FromUserID);
$ToUserID = $resSupervisorId->SupervisorID;

$qryString = "INSERT INTO Notification (FromUserID, ToUserID, Notification, Status, CompanyID) VALUES (?, ?, ?, ?, ?)";
$qryRes = $app->getDBConnection()->query($qryString, $FromUserID, $ToUserID, $Notification, 0, $CompanyID);

if (true) {
    $response["success"] = 1;
} else {
    $response["success"] = 0;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
