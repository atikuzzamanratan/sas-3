<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

include "../Config/config.php";
include "../Lib/lib.php";

$app = new Application();

$UserID = xss_clean($_REQUEST["userID"]);
$PSUID = xss_clean($_REQUEST["psuID"]);

$qry = "SELECT PSU FROM PSUList WHERE PSUUserID = ? ";
$resQry = $app->getDBConnection()->fetchAll($qry, $UserID);

$SelectOption = '<option value="">Choose a PSU</option>';

foreach ($resQry as $row) {
    $SelectOption .= '<option value="' . $row->PSU . '" ' . (isset($PSUID) && $PSUID != '' && $PSUID == $row->PSU ? 'selected' : '') . '>' . $row->PSU . '</option>';
}

echo $SelectOption;

