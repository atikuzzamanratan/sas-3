<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$senderID = xss_clean($_REQUEST["senderID"]);
$toID = xss_clean($_REQUEST["toID"]);
$message = xss_clean($_REQUEST["message"]);
$companyID = xss_clean($_REQUEST["companyID"]);

$Field = "FromUserID, ToUserID, Notification, Status, DataEntryDate, CompanyID";
$Value = "'$senderID', '$toID', N'$message', '0', GETDATE(), '$companyID'";

//echo $Field . ' || ' . $Value;

if (Save('Notification', $Field, $Value)) {
    $msg = 'Message sent successfully.';
} else {
    $msg = 'Failed to send message!';
}
echo $msg;
