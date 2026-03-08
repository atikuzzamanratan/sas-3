<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

include "../Config/config.php";
include "../Lib/lib.php";

$app = new Application();

$UserID = xss_clean($_REQUEST["userID"]);
$CompanyID = xss_clean($_REQUEST["companyID"]);

$qry = "SELECT id, UserName FROM userinfo WHERE IsActive = 1 AND id <> '$UserID' AND CompanyID = ? ORDER BY id ASC";
$resQry = $app->getDBConnection()->fetchAll($qry, $CompanyID);

$UserIDList = [];

foreach ($resQry as $row) {
    $UserIDList[] = $row->id;
}

echo implode(',',$UserIDList);

