<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$noticeid = $_REQUEST["noticeid"];

$param = "Status=1, NotificationReadTime=GETDATE()";
$cond = "id=$noticeid";

if (Edit('Notification', $param, $cond)) {
    echo 'Successfully updated.';
} else
    echo 'Failed to update!';

