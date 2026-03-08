<?php
session_start();

error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$id = xss_clean($_REQUEST['id']);
$tbl_name = xss_clean($_REQUEST['tbl']);

$loggedUserName = $app->cleanInput($_SESSION['User']);
$loggedUserID = $app->cleanInput($_SESSION['UserID']);

$loggedUserID = xss_clean($_REQUEST['loggedUserID']);

if (strpos($loggedUserName, 'val') === false) {
	$param = "IsApproved = 1, IsChecked = 1";
} else {
	$param = "IsApproved = 1, IsChecked = 1, ValidationDate=CURRENT_TIMESTAMP, ValidatorID=$loggedUserID";
}
$cond = "id = '$id'";

if (Edit($tbl_name, $param, $cond)) {
    $msg = 'Successfully Checked.';
} else {
    $msg = 'Failed to Check data!';
}
echo $msg;

