<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$userId = xss_clean($_REQUEST["userId"]);
$roleId = xss_clean($_REQUEST["roleId"]);
$tbl_name = xss_clean($_REQUEST["tbl"]);

$cond = "UserId = $userId AND RoleId = '$roleId'";

if (Delete($tbl_name, $cond)) {
    echo 'Successfully deleted.';
} else
    echo 'Failed to delete data!';

