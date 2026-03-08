<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$FormId = xss_clean($_REQUEST["id"]);
$FormDesc = xss_clean($_REQUEST["desc"]);
$Status = xss_clean($_REQUEST["status"]);

$param = "FormDescription = '$FormDesc', Status = '$Status'";
$cond = "id = '$FormId'";

if (Edit('datacollectionform', $param, $cond)) {
    echo 'Successfully updated.';
} else
    echo 'Failed to update!';

