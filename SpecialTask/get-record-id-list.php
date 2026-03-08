<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

include "../Config/config.php";
include "../Lib/lib.php";

$app = new Application();

$UserID = xss_clean($_REQUEST["userID"]);
$CompanyID = xss_clean($_REQUEST["companyID"]);

$qry = "SELECT id FROM xformrecord WHERE UserID = ? AND CompanyId = ? ORDER BY id ASC";
$resQry = $app->getDBConnection()->fetchAll($qry, $UserID, $CompanyID);

$SelectOption = '<option value="">Choose a Record</option>';

foreach ($resQry as $row) {
    $SelectOption .= '<option value="' . $row->id . '">' . $row->id . '</option>';
}

echo $SelectOption;

