<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$Id = xss_clean($_REQUEST["id"]);
$tbl_name = xss_clean($_REQUEST["tbl"]);

$cond = "RoleId = '$Id'";

if (Delete($tbl_name, $cond)) {
    echo 'Successfully deleted.';
} else
    echo 'Failed to delete data!';

