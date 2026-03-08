<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$RecordId = xss_clean($_REQUEST["id"]);
$pApprove = xss_clean($_REQUEST["pApprove"]);
$pEdit = xss_clean($_REQUEST["pEdit"]);
$pDelete = xss_clean($_REQUEST["pDelete"]);

$param = "ApprovePermission = '$pApprove', EditPermission = '$pEdit', DeletePermission = '$pDelete'";
$cond = "id = '$RecordId'";

//echo $param . ' || ' . $cond;

if (Edit('assignsupervisor', $param, $cond)) {
    echo 'Successfully updated.';
} else
    echo 'Failed to update!';
