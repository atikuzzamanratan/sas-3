<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$requestingUserID = $_REQUEST['userid'];
$requestingPsu = xss_clean($_REQUEST['psu']);
$requestingFormID = xss_clean($_REQUEST['formid']);

$userName = getValue('userinfo', 'UserName', "id = $requestingUserID");
$userFullName = getValue('userinfo', 'FullName', "id = $requestingUserID");
$userFullName = getValue('userinfo', 'FullName', "id = $requestingUserID");
$userMobileNumber = getValue('userinfo', 'MobileNumber', "id = $requestingUserID");

$userInfo = "$userFullName ($userName)";

//মাননীয় সুপারভাইজার মহোদয়, <br>আমি আমার PSU এর লিস্টিং সম্পন্ন করেছি। <br>অনুগ্রহ করে তথ্য যাচাই করে আমার PSU এর স্যাম্পলিং জেনারেট করে দিলে মূল ফর্মের তথ্য সংগ্রহ শুরু করতে পারব। <br>অনুরোধক্রমে <br>

$msgText = "মাননীয় সুপারভাইজার মহোদয়, <br>আমি আমার <b>PSU: $requestingPsu</b> এর লিস্টিং সম্পন্ন করেছি।<br>অনুগ্রহ করে তথ্য যাচাই করে আমার <b>PSU: $requestingPsu</b> এর স্যাম্পলিং জেনারেট করে দিলে মূল ফর্মের তথ্য সংগ্রহ শুরু করতে পারব। <br><br>অনুরোধক্রমে,<br>$userInfo<br>মোবাইল নং: $userMobileNumber";

$msgText = xss_clean($msgText);

$resCompanyId = $app->getDBConnection()->fetch("SELECT CompanyID FROM userinfo Where id = ?", $requestingUserID);
$CompanyID = $resCompanyId->CompanyID;

$resSupervisorId = $app->getDBConnection()->fetch("SELECT SupervisorID FROM assignsupervisor where UserID = ?", $requestingUserID);
$ToUserID = $resSupervisorId->SupervisorID;

if (Save('Notification', 'FromUserID, ToUserID, Notification, Status, CompanyID', "$requestingUserID, $ToUserID, N'$msgText', 0, $CompanyID")) {
    echo 'Successfully sent request.';
} else {
    echo 'Failed to send request!';
}
