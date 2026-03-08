<?php

error_reporting(E_ALL);

require '../../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../../Config/config.php";
include "../../Lib/lib.php";

$RecordID = xss_clean($_REQUEST['id']);
$IsApproved = xss_clean($_REQUEST['status']);
$PSU = xss_clean($_REQUEST['psu']);
$LoggedUserID = xss_clean($_REQUEST['loggedUserID']);
$AgentID = xss_clean($_REQUEST['agentID']);
$XFormsFilePath = xss_clean($_REQUEST['XFormsFilePath']);

$LoggedUserName = getValue('userinfo', 'UserName', "id = $LoggedUserID");
$AgentFullName = getValue('userinfo', 'FullName', "id = $AgentID");
$AgentUserName = getValue('userinfo', 'UserName', "id = $AgentID");

$MasterDataQuery = "EXEC EditDetailDataWithLabel $RecordID";
$MasterDataQueryRS = $app->getDBConnection()->fetchAll($MasterDataQuery);

$count = count($MasterDataQueryRS);

$previousValueArray = array();
$previousNodeArray = array();
$newValueArray = array();

$dataViewTable = "
<div class=\"modal-header\">
    <h5 class=\"modal-title\" id=\"editDataModalLabel\">Edit Information</h5>
    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
</div>
<div class=\"modal-body\">";

$dataViewTable .= "<form id=\"editForm\" method=\"POST\" action=\"\">";

if ($count) {
    $dataViewTable .= "
                        <div class=\"form-group\">
                            <label for=\"recordID\">Record ID</label>
                            <input type=\"text\" class=\"form-control\" name=\"recordID\" id=\"recordID\" value=\"$RecordID\" readonly>
                        </div>";
    foreach ($MasterDataQueryRS as $row) {
        $Id = $row->ID;
        $ColumnName = $row->ColumnNameOriginal;
        $ColumnNameModified = $row->ColumnLabelModified;
        $ColumnValue = $row->ColumnValueOriginal;
        $ColumnValueLabel = $row->ColumnValueModified;
        $ColumnDataType = $row->ColumnDataType;
        $ColumnChoiceValueAll = $row->ColumnChoiceValueAll;
        $ColumnChoiceLabelAll = $row->ColumnChoiceLabelAll;
        $PassingValue = $ColumnName . "|" . $Id . "|" . $ColumnDataType;
        $previousValueArray[] = $ColumnValue;
        $previousNodeArray[] = $ColumnName;

        $previousValueArrayStr = implode(',', $previousValueArray);

        $dataViewTable .= "
                        <div class=\"form-group\">
                            <label for=\"UserName\">$ColumnNameModified</label>";
        if (trim($ColumnDataType) == 'select_one') {
            $ColumnChoiceValueAllArray = explode('|', $ColumnChoiceValueAll);
            $ColumnChoiceLabelAllArray = explode('|', $ColumnChoiceLabelAll);

            $dataViewTable .= "<select name=\"$PassingValue\" id=\"UserName\" class=\"form-control\">";
            $i = 0;
            foreach ($ColumnChoiceValueAllArray as $ColumnChoiceValueData) {
                if ($ColumnValue == $ColumnChoiceValueData) {
                    $dataViewTable .= "<option value=\"$ColumnChoiceValueData\" selected>$ColumnChoiceLabelAllArray[$i]</option>";
                } else {
                    $dataViewTable .= "<option value=\"$ColumnChoiceValueData\">$ColumnChoiceLabelAllArray[$i]</option>";
                }
                $i++;
            }

            $dataViewTable .= "</select>";

        } elseif (trim($ColumnDataType) == 'select_multiple') {
            $ColumnChoiceValueAllArray = explode('|', $ColumnChoiceValueAll);
            $ColumnChoiceLabelAllArray = explode('|', $ColumnChoiceLabelAll);
            $ColumnValueArrayMultiple = explode(' ', $ColumnValue);

            $dataViewTable .= "<select name=\"$PassingValue []\" id=\"UserName\" class=\"form-control\" multiple>";
            $j = 0;
            foreach ($ColumnChoiceValueAllArray as $ColumnChoiceValueData) {
                if (in_array($ColumnChoiceValueData, $ColumnValueArrayMultiple)) {
                    $dataViewTable .= "<option value=\"$ColumnChoiceValueData\" selected>$ColumnChoiceLabelAllArray[$j]</option>";
                } else {
                    $dataViewTable .= "<option value=\"$ColumnChoiceValueData\">$ColumnChoiceLabelAllArray[$j]</option>";
                }
                $j++;
            }

            $dataViewTable .= "</select>";

        } else {
            $dataViewTable .= "<input type=\"text\" class=\"form-control\" name=\"$PassingValue\" id=\"$PassingValue\" value=\"$ColumnValue\">";
        }

        $dataViewTable .= "<label for=\"UserName\">$ColumnValueLabel</label>
                        </div>";
    }
} else {
    $dataViewTable .= "
<div class=\"form-group\">
    <div class=\"col-lg-12\">
        Only UN-APPROVED data can be edited. Your data ID: $RecordIDis not in UN-APPROVED Stage.
    </div>
</div>";
}


$dataViewTable .= "
    <div class=\"modal-footer\">";


$dataViewTable .= "<button type=\"button\" class=\"btn btn-danger\" name=\"update\" id=\"update\" value=\"Update\" 
                            onclick= \"UpdateData('$previousValueArrayStr');\">Update</button>";
$dataViewTable .= "<button type=\"button\" class=\"btn btn-primary\" data-bs-dismiss=\"modal\">Close</button>";

$dataViewTable .= "
    <div>
</div>";

$dataViewTable .= "</form>";

echo $dataViewTable;
?>

<script>
    function UpdateData(recordStr) {
       //recordValue = recordStr.split(",");
        alert(recordStr);
        return false;
        //alert(recordID);
        //window.location.reload();
    }
</script>
