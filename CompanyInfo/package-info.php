<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

include "../Config/config.php";
include "../Lib/lib.php";

$app = new Application();

$packageId = xss_clean($_REQUEST["packageId"]);

$qry = "SELECT amount, maxUserNo, uploadCredit, storage, formPerAcc FROM packages WHERE id = '$packageId'";
$resQry = $app->getDBConnection()->fetchAll($qry);

$date = date('Y-m-d', strtotime('+1 years'));

foreach ($resQry as $row) {
    $amount = $row->amount;
    $maxUserNo = $row->maxUserNo;
    $uploadCredit = $row->uploadCredit;
    $storage = $row->storage;
    $formPerAcc = $row->formPerAcc;
}

echo "$amount" . "|" . "$maxUserNo" . "|" . "$uploadCredit" . "|" . "$storage" . "|" . "$formPerAcc" . "|" . $date;

