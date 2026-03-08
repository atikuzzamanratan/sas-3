<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$id = xss_clean($_REQUEST["id"]);
$tbl = xss_clean($_REQUEST["tbl"]);

$param = "DistCoordinatorID = NULL";
$cond = "id = '$id'";

//echo $param . ' || ' . $cond;

if (Edit($tbl, $param, $cond)) {
    echo 'Successfully updated.';
} else
    echo 'Failed to update!';
