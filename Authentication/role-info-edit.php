<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$roleId = xss_clean($_REQUEST["id"]);
$roleName = xss_clean($_REQUEST["name"]);

$param = "RoleName = '$roleName'";
$cond = "RoleId = '$roleId'";

if (Edit('roleinfo', $param, $cond)) {
    echo 'Successfully updated.';
} else
    echo 'Failed to update!';

