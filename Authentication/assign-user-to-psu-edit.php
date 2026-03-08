<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$RecordId = xss_clean($_REQUEST["id"]);
$fType = xss_clean($_REQUEST["fType"]);
$pTarget = xss_clean($_REQUEST["pTarget"]);

if($fType == 'Main'){
    $param = "NumberOfRecordForMainSurvey = '$pTarget'";
}else{
    $param = "NumberOfRecord = '$pTarget'";
}

$cond = "id = '$RecordId'";

//echo $param . ' || ' . $cond;

if (Edit('PSUList', $param, $cond)) {
    echo 'Successfully updated.';
} else
    echo 'Failed to update!';
