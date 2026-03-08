<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

include "../Config/config.php";
include "../Lib/lib.php";

$app = new Application();

$FormID = xss_clean($_REQUEST["formID"]);
$ColName = xss_clean($_REQUEST["colName"]);
$ColOption = xss_clean($_REQUEST["colOption"]);

// $qry = "SELECT ChoiceValue, ChoiceLabel, ChoiceListName FROM ChoiceInfo WHERE FormId = $FormID AND ChoiceListName = (SELECT ChoiceListName FROM xformcolumnname WHERE ColumnName = '$ColName')";
// $resQry = $app->getDBConnection()->fetchAll($qry);



$qry = "
SELECT ChoiceValue, ChoiceLabel
FROM ChoiceInfo
WHERE FormId = ?
  AND ChoiceListName = (
        SELECT TOP 1 CAST(ChoiceListName AS varchar(200))
        FROM xformcolumnname
        WHERE FormId = ? AND ColumnName = ?
    )
ORDER BY TRY_CAST(ChoiceValue AS int)
";
$resQry = $app->getDBConnection()->fetchAll($qry, $FormID, $FormID, $ColName);



// foreach ($resQry as $row) {
//     echo $SelectOption = '<option value="' . $row->ChoiceValue . '" ' . (isset($ColOption) && $ColOption !== '' && $ColOption == $row->ChoiceValue ? 'selected' : '') . '>' . $row->ChoiceLabel . '</option>';
// }


// build <select> wrapper so jQuery can replace the entire dropdown properly
$SelectOption  = '<select class="form-control populate" ';
$SelectOption .= 'id="'.$_REQUEST['colOptionID'].'" ';
$SelectOption .= 'name="'.$_REQUEST['colOptionID'].'">';
$SelectOption .= '<option value="">Choose option</option>';

foreach ($resQry as $row) {
    $selected = ($ColOption !== '' && $ColOption == $row->ChoiceValue) ? ' selected' : '';
    $SelectOption .= '<option value="'.$row->ChoiceValue.'"'.$selected.'>'
                     .$row->ChoiceValue.' - '.$row->ChoiceLabel.'</option>';
}

$SelectOption .= '</select>';

echo $SelectOption;