<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$UserId = xss_clean($_REQUEST["id"]);
$UserName = xss_clean($_REQUEST["uname"]);
$UserPass = xss_clean($_REQUEST["pass"]);
$encPassword = password_hash($UserPass, PASSWORD_DEFAULT);
$UserFullName = xss_clean($_REQUEST["fullName"]);
$UserMobileNo = xss_clean($_REQUEST["mobileNo"]);
$UserEmail = xss_clean($_REQUEST["email"]);
$UserStatus = xss_clean($_REQUEST["status"]);


$param = "UserName='$UserName', Password='$UserPass', enc_passw='$encPassword', FullName=N'$UserFullName', MobileNumber='$UserMobileNo', EmailAddress='$UserEmail', IsActive=$UserStatus";
$cond = "id='$UserId'";

if (empty($UserId) || empty($UserPass) || empty($UserFullName) || empty($UserMobileNo)) {
    echo 'Sorry, some information are missing!';
} else {
    if (Edit('userinfo', $param, $cond)) {
        echo 'Successfully updated.';
    } else
        echo 'Failed to update!';
}

