<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

include "../Config/config.php";
include "../Lib/lib.php";

$app = new Application();

$formID = xss_clean($_REQUEST["formID"]);

$qry = "SELECT id, DataName FROM xformrecord WHERE FormID = ? ORDER BY id ASC";
$resQry = $app->getDBConnection()->fetchAll($qry, $formID);

foreach ($resQry as $row) {
    echo '<option value="' . $row->id . '">' . $row->id . ' - (' . $row->DataName . ')' . '</option>';
}

