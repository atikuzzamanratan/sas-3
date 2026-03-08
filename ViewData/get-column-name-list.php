<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

include "../Config/config.php";
include "../Lib/lib.php";

$app = new Application();

$FormID = xss_clean($_REQUEST["formID"]);
$ColumnName = xss_clean($_REQUEST["columnName"]);

$qry = "SELECT ColumnName, ColumnLabel FROM xformcolumnname WHERE FormId = ?";
$resQry = $app->getDBConnection()->fetchAll($qry, $FormID);

$SelectOption = '<option value="">Choose a column</option>';

foreach ($resQry as $row) {
    $SelectOption .= '<option value="' . $row->ColumnName . '"' . (isset($ColumnName) && !empty($ColumnName) && $row->ColumnName == $ColumnName ? ' selected' : '') . '>' . $row->ColumnName . ' (' . $row->ColumnLabel. ')' . '</option>';
}

echo $SelectOption;

