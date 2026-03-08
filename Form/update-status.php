<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$Id = xss_clean($_REQUEST["id"]);
$tbl = xss_clean($_REQUEST["tbl"]);
$Status = xss_clean($_REQUEST["status"]);

$param = "Status='$Status'";
$cond = "id='$Id'";

if (Edit($tbl, $param, $cond)) {
    echo 'Successfully updated.';
} else
    echo 'Failed to update data!';

