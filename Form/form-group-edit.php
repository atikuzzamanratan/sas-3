<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$GroupId = xss_clean($_REQUEST["id"]);
$FormGroupName = xss_clean($_REQUEST["name"]);
$FormGroupDesc = xss_clean($_REQUEST["desc"]);
$Status = xss_clean($_REQUEST["status"]);

$param = "FormGroupName='$FormGroupName',FormGroupDesc='$FormGroupDesc',Status='$Status'";
$cond = "id='$GroupId'";

//echo $msg = $param.' '.$cond;

if (Edit('datacollectionformgroup', $param, $cond)) {
    echo 'Successfully updated.';
} else
    echo 'Failed to update data!';

