<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$Id = xss_clean($_REQUEST["id"]);
$CompanyName = xss_clean($_REQUEST["name"]);
$ContactPerson = xss_clean($_REQUEST["contactPerson"]);
$Address = xss_clean($_REQUEST["address"]);
$Phone = xss_clean($_REQUEST["phone"]);
$Status = xss_clean($_REQUEST["status"]);

$param = "ContactPersonName=N'$ContactPerson', Address=N'$Address', Phone='$Phone', IsActive='$Status'";
$cond = "id='$Id'";

if (Edit('dataownercompany', $param, $cond)) {
    echo 'Successfully updated.';
} else
    echo 'Failed to update!';

