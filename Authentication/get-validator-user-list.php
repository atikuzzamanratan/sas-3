<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

include "../Config/config.php";
include "../Lib/lib.php";

$app = new Application();

$CompanyID = xss_clean($_REQUEST["companyID"]);
$searchParam = xss_clean($_REQUEST["searchParam"]);

$qry = "SELECT id, UserName, FullName FROM userinfo WHERE CompanyID = ? AND UserName LIKE '$searchParam%' ORDER BY UserName ASC";
$resQry = $app->getDBConnection()->fetchAll($qry, $CompanyID);

$SelectOption = '<option value="">Choose Validator</option>';

foreach ($resQry as $row) {
    $SelectOption .= '<option value="' . $row->id . '">' . $row->UserName . ' || ' . $row->FullName . '</option>';
}

echo $SelectOption;

