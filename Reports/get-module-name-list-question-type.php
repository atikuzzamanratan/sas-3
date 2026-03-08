<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

include "../Config/config.php";
include "../Lib/lib.php";

$app = new Application();

$FormID = xss_clean($_REQUEST["formID"]);
$moduleName = xss_clean($_REQUEST["moduleName"]);
$questionType = xss_clean($_REQUEST["questionType"]);

$qry = "SELECT DISTINCT ModuleName FROM ModuleInfo WHERE FormId = ? AND QuestionType = ?";
$resQry = $app->getDBConnection()->fetchAll($qry, $FormID, $questionType);

$SelectOption = '<option value="">Choose a module</option>';

foreach ($resQry as $row) {
    $SelectOption .= '<option value="' . $row->ModuleName . '" ' . ($row->ModuleName == $moduleName ? 'selected' : '') . '>' . $row->ModuleName . '</option>';
}

echo $SelectOption;
