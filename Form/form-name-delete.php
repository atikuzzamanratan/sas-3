<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$FormId = xss_clean($_REQUEST["id"]);
$filePath = xss_clean($_REQUEST["filePath"]);

$cond = "id='$FormId'";

if (Delete('datacollectionform', $cond)) {
    unlink($baseURL.$filePath);
    echo 'Successfully deleted.';
} else
    echo 'Failed to delete data!';

