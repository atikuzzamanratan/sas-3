<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$GroupId = xss_clean($_REQUEST["id"]);

$cond = "id='$GroupId'";


if (Delete('datacollectionformgroup', $cond)) {
    echo 'Successfully deleted.';
} else
    echo 'Failed to delete data!';

