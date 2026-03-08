<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

include "../Config/config.php";
include "../Lib/lib.php";

$app = new Application();

$FormID = xss_clean($_REQUEST["formID"]);

$qry = "SELECT DISTINCT ModuleName FROM ModuleInfo WHERE FormId = ?";
$resQry = $app->getDBConnection()->fetchAll($qry, $FormID);

$SelectOption = '<option value="">Choose a module</option>';

foreach ($resQry as $row) {
    $SelectOption .= '<option value="' . $row->ModuleName . '">' . $row->ModuleName . '</option>';
}

echo $SelectOption;

