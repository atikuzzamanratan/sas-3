<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

include "../Config/config.php";
include "../Lib/lib.php";

$app = new Application();

$FormID = xss_clean($_REQUEST["formID"]);
$ModuleName = xss_clean($_REQUEST["moduleName"]);
$ColumnName = xss_clean($_REQUEST["columnName"]);
$MaleColumnName = xss_clean($_REQUEST["maleColumnName"]);
$FemaleColumnName = xss_clean($_REQUEST["femaleColumnName"]);
$gender = xss_clean($_REQUEST["gender"]);

if ($gender == 'both') {
    $maleQry = "SELECT ColumnName, ColumnLabel FROM ModuleInfo WHERE FormId = $FormID AND ModuleName = N'$ModuleName'";
    $femaleQry = "SELECT ColumnName, ColumnLabel FROM ModuleInfo WHERE FormId = $FormID AND ModuleName = N'$ModuleName'";

    $maleResQry = $app->getDBConnection()->fetchAll($maleQry);
    $femaleResQry = $app->getDBConnection()->fetchAll($femaleQry);

    $maleSelectOption = '<option value="">Choose a column</option>';
    foreach ($maleResQry as $row) {
        $maleSelectOption .= '<option value="' . $row->ColumnName . '"' . (isset($MaleColumnName) && !empty($MaleColumnName) && $row->ColumnName == $MaleColumnName ? ' selected' : '') . '>' . $row->ColumnName . ' (' . strip_tags($row->ColumnLabel) . ')' . '</option>';
    }

    $femaleSelectOption = '<option value="">Choose a column</option>';
    foreach ($femaleResQry as $row) {
        $femaleSelectOption .= '<option value="' . $row->ColumnName . '"' . (isset($FemaleColumnName) && !empty($FemaleColumnName) && $row->ColumnName == $FemaleColumnName ? ' selected' : '') . '>' . $row->ColumnName . ' (' . strip_tags($row->ColumnLabel) . ')' . '</option>';
    }
    echo json_encode([$maleSelectOption, $femaleSelectOption]);

} else {
    $qry = "SELECT ColumnName, ColumnLabel FROM ModuleInfo WHERE FormId = $FormID AND ModuleName = N'$ModuleName'";
    $resQry = $app->getDBConnection()->fetchAll($qry);
    $SelectOption = '<option value="">Choose a column</option>';
    foreach ($resQry as $row) {
        $SelectOption .= '<option value="' . $row->ColumnName . '"' . (isset($ColumnName) && !empty($ColumnName) && $row->ColumnName == $ColumnName ? ' selected' : '') . '>' . $row->ColumnName . ' (' . strip_tags($row->ColumnLabel) . ')' . '</option>';
    }
    echo $SelectOption;
}
