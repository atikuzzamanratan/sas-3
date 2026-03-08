<?php

error_reporting(E_ALL);

require '../../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../../Config/config.php";
include "../../Lib/lib.php";

$RecordID = xss_clean($_REQUEST['id']);
$AgentID = getValue('xformrecord', 'UserID', "id = $RecordID");
$IsApproved = xss_clean($_REQUEST['status']);
$PSU = xss_clean($_REQUEST['psu']);
$DataFromID = xss_clean($_REQUEST['formID']);

$LoggedUserID = xss_clean($_REQUEST['loggedUserID']);
$LoggedUserName = getValue('userinfo', 'UserName', "id = $LoggedUserID");


if ($IsApproved == 0) {
    $MasterDataQuery = "EXEC ViewDetailDataWithLabelPending $RecordID";

    $MasterDataTimeQuery = "SELECT ColumnName, ColumnValue FROM masterdatarecord_Pending WHERE XFormRecordId = ? AND (ColumnName = 'surveyStartDate' OR ColumnName = 'surveyEndDate') ORDER BY ColumnName ASC  ";
    $MasterDataTimeRS = $app->getDBConnection()->fetchAll($MasterDataTimeQuery, $RecordID);
} elseif ($IsApproved == 1) {
    $MasterDataQuery = "EXEC ViewDetailDataWithLabelApproved $RecordID";

    $MasterDataTimeQuery = "SELECT ColumnName, ColumnValue FROM masterdatarecord_Approved WHERE XFormRecordId = ? AND (ColumnName = 'surveyStartDate' OR ColumnName = 'surveyEndDate') ORDER BY ColumnName ASC  ";
    $MasterDataTimeRS = $app->getDBConnection()->fetchAll($MasterDataTimeQuery, $RecordID);
} elseif ($IsApproved == 2) {
    $MasterDataQuery = "EXEC ViewDetailDataWithLabelUnApproved $RecordID";

    $MasterDataTimeQuery = "SELECT ColumnName, ColumnValue FROM masterdatarecord_UnApproved WHERE XFormRecordId = ? AND (ColumnName = 'surveyStartDate' OR ColumnName = 'surveyEndDate') ORDER BY ColumnName ASC  ";
    $MasterDataTimeRS = $app->getDBConnection()->fetchAll($MasterDataTimeQuery, $RecordID);
}
//die($MasterDataTimeQuery);
$t = 0;
$startTime = '';
$endTime = '';
foreach ($MasterDataTimeRS as $row) {
    if ($t == 1) {
        $startTime = $row->ColumnValue;
    } else {
        $endTime = $row->ColumnValue;
    }
    $t++;
}

$start = strtotime($startTime);
$end = strtotime($endTime);
try {
    $start_date = new DateTime($startTime);
} catch (Exception $e) {
}
try {
    $since_start = $start_date->diff(new DateTime($endTime));
} catch (Exception $e) {
}

$Duration = '';
if ($since_start->d) {
    $Duration = $since_start->d . ' Days ';
} elseif ($since_start->h) {
    $Duration = $since_start->h . ' hours ';
} elseif ($since_start->i) {
    $Duration = $since_start->i . ' minutes ';
} elseif ($since_start->s) {
    $Duration = $since_start->s . ' seconds ';
}


$dataViewTable = "
<div class=\"modal-header\">
    <h5 class=\"modal-title\" id=\"editDataModalLabel\">Data Detail View</h5>
    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
</div>
<div class=\"modal-body\">
<form action='' name='CommentsFields' id='CommentsFields' >
<table align=\"left\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"table table-striped table-bordered datatables\" id=\"example\">
    <thead>";

$dataViewTable .= "<tr role=\"row\">
	<th width=\"50%\">Column Lebel</th>
	<th width=\"50%\">Column Value</th>
</tr>
</thead>
<tbody>
<tr align=\"left\" class=\"textRpt\">
	<td><b>Record ID</b></td>
	<td><b>$RecordID</b></td>
</tr>
<tr align=\"left\" class=\"textRpt\">
	<td><b>PSU</b></td>
	<td><b>$PSU</b></td>
</tr>
<tr align=\"left\" class=\"textRpt\">
	<td style='color: red'><b>Data Collection Duration</b></td>
	<td style='color: red'><b>$Duration</b></td>
</tr>";

$dataViewTable .= "
    </tbody>
</table>";
//die($PSU);
$AllModuleInfoQuery = "SELECT ModuleName, ColumnName  FROM ModuleInfo WHERE FormId = $DataFromID ORDER BY id ASC";

$MasterDataQueryRS = $app->getDBConnection()->fetchAll($MasterDataQuery);
$AllModuleInfoRS = $app->getDBConnection()->fetchAll($AllModuleInfoQuery);

$ModuleModifiedColumnValue = [];

foreach ($AllModuleInfoRS as $row) {
    //$ModuleModifiedColumnValue[$row['ColumnName']] = $row['ModuleName'];
    $ModuleModifiedColumnValue[substr($row['ColumnName'], 0, 2)] = $row['ModuleName'] . '_';
}

$ModuleGroupData = [];
$isEditedAry = array();
foreach ($MasterDataQueryRS as $MDRrow) {
    //$ModuleName = $ModuleModifiedColumnValue[$MDRrow->ColumnNameOriginal] != '' ? $ModuleModifiedColumnValue[$MDRrow->ColumnNameOriginal] : 'Others';
    $ModuleName = substr($MDRrow->ColumnNameOriginal, 1, 1) == '_' ? substr($MDRrow->ColumnNameOriginal, 0, 1) : 'Others';
    $ModuleGroupData[$ModuleName][] = $MDRrow;
	if ($MDRrow->IsEdited > 0) {
		$isEditedAry[$ModuleName] = $MDRrow->IsEdited;
	}
}

$dataViewTable .= "<div class=\"accordion\" id=\"accordionExample\">";

foreach ($ModuleGroupData as $ModuleName => $ModuleData) {
    $dataViewTable .= "
        <div class=\"accordion-item\">
                <h2 class=\"accordion-header\" id=\"Accordion-$ModuleName\">
                    <button class=\"accordion-button collapsed px-3 py-0\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapse-$ModuleName\" aria-expanded=\"true\" aria-controls=\"collapse-$ModuleName\" ".((!empty($isEditedAry[$ModuleName]) && $isEditedAry[$ModuleName] == 2) ? 'style="background-color: #fff192;"' : ((!empty($isEditedAry[$ModuleName]) && $isEditedAry[$ModuleName] == 1) ? 'style="background-color: #FBC6C2;"' : '')).">
                        $ModuleName - Module
                    </button>
                </h2>
            <div id=\"collapse-$ModuleName\" class=\"accordion-collapse collapse\" aria-labelledby=\"Accordion-$ModuleName\" data-bs-parent=\"#accordionExample\" style=\"overflow: auto;\">
                <div class=\"accordion-body\">
                <table align=\"left\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"table table-striped table-bordered datatables\" id=\"example\">
                    <thead>
                        <tr role=\"row\">
                            <th width=\"50%\">Column Lebel</th>
                            <th width=\"50%\">Column Value</th>
                        </tr>
                    </thead>
                    <tbody>";

    $dataViewTable .= getAccordionTableData($ModuleData, $XFormsFilePath, $baseURL, $googleMapApiKey, $LoggedUserName, $EditPermission, $IsEditable, $AgentFullName, $AgentUserName);

    $dataViewTable .= "</tbody>
                </table>
                </div>
            </div>
        </div>
    ";
}
$dataViewTable .= "</div></form>";

$dataViewTable .= "<div class=\"modal-footer\">";
/*if ((strpos($LoggedUserName, 'admin') !== false) or $ApprovePermission == 1 or (strpos($LoggedUserName, 'dist') !== false) || (strpos($LoggedUserName, 'val') !== false)) {
    $dataViewTable .= "<button type=\"button\" class=\"btn btn-success\" name=\"update\" id=\"update\" value=\"Update\" 
                        onclick= \"ApproveDataRecord('$RecordID');\"> Approve</button>";
}*/

if ((strpos($LoggedUserName, 'admin') !== false) and ($IsApproved != 2)) {
    $dataViewTable .= "<button type=\"button\" class=\"btn btn-warning\" name=\"update\" id=\"update\" value=\"Update\" 
                        onclick= \"UnapproveDataRecord('$RecordID', '$AgentID');\"> Un-approve</button>";
}

/*if (((strpos($LoggedUserName, 'admin') !== false) or $DeletePermission == 1) and (strpos($LoggedUserName, 'dist') === false)) {
    $dataViewTable .= "<button type=\"button\" class=\"btn btn-danger\" name=\"delete\" id=\"delete\" value=\"Delete\" 
                        onclick= \"DeleteDataRecord('$RecordID', '$AgentID');\"> Delete</button>";
}*/

$dataViewTable .= "<button type=\"button\" class=\"btn btn-primary\" data-bs-dismiss=\"modal\">Close</button>";

$dataViewTable .= "</div>";

echo $dataViewTable;

function getAccordionTableData($ModuleData, $XFormsFilePath, $baseURL, $googleMapApiKey, $LoggedUserName, $EditPermission, $IsEditable, $AgentFullName, $AgentUserName)
{
    $dataViewTable = "";
    foreach ($ModuleData as $MDRrow) {
        $originalColumnName = $MDRrow->ColumnNameOriginal;
        $ModifiedColumnLabel = $MDRrow->ColumnLabelModified;
        $originalColumnValue = $MDRrow->ColumnValueOriginal;
        $ModifiedColumnValue = $MDRrow->ColumnValueModified;
        $IsEditable = $MDRrow->IsEditable;
		$IsEdited = $MDRrow->IsEdited;

        $dataViewTable .= "
        <tr>
            <td style=\"word-wrap: break-word;".(($IsEdited == 1) ? 'background-color: #FBC6C2;' : (($IsEdited == 2) ? 'background-color: #fff192;' : ''))."\">$ModifiedColumnLabel";

        if ($MDRrow->Comments != '') {
            $dataViewTable .= "<span style='float: right;'><img class='jBoxTip' src='../../img/Comments.jpg' border=0 style='cursor: pointer;width: 50px;' title='$MDRrow->Comments'></span>";
        }

        $dataViewTable .= "</td>";

        if (strtoupper($originalColumnName) == 'PICTURE' or strtoupper($originalColumnName) == 'IMAGE' or (strpos(strtoupper($originalColumnName), "IMAGE") !== false) or (strpos(strtoupper($originalColumnName), "PICTURE") !== false)) {
            $ImageFilePathArray = explode("/", $XFormsFilePath);
            $ImageFilePath = $baseURL . $ImageFilePathArray[1] . "/" . $ImageFilePathArray[2] . "/" . $ImageFilePathArray[3] . "/" . $ImageFilePathArray[4] . "/" . $ModifiedColumnValue;
            $ImageFilePath = $baseURL . $ImageFilePathArray[0] . "/" . $ImageFilePathArray[1] . "/" . $ImageFilePathArray[2] . "/" . $ModifiedColumnValue;

            $dataViewTable .= "
            <td ".(($IsEdited == 1) ? 'style="background-color: #FBC6C2;"' : (($IsEdited == 2) ? 'style="background-color: #fff192;"' : ''))."><img src=\"" . $ImageFilePath . "\" width=200 height=200 ></td>";
        } else if ($originalColumnName == 'audio') {
            $ImageFilePathArray = explode("/", $XFormsFilePath);
            $ImageFilePath = $baseURL . $ImageFilePathArray[0] . "/" . $ImageFilePathArray[1] . "/" . $ImageFilePathArray[2] . "/" . $ModifiedColumnValue;
            $dataViewTable .= "
            <td ".(($IsEdited == 1) ? 'style="background-color: #FBC6C2;"' : (($IsEdited == 2) ? 'style="background-color: #fff192;"' : ''))."><audio preload=\"none\" controls><source src=\"" . $ImageFilePath . "\" type=\"audio/mpeg\"></audio></td>";
        } else if ($originalColumnName == 'geopoint') {
            $TotalLatLong = explode(' ', $ModifiedColumnValue);
            $lat = $TotalLatLong[0];
            $long = $TotalLatLong[1];
            $dataViewTable .= "
            <td colspan='2' ".(($IsEdited == 1) ? 'style="background-color: #FBC6C2;"' : (($IsEdited == 2) ? 'style="background-color: #fff192;"' : '')).">
                <div id=\"map\" style=\"width:100%;height:400px;\"></div>
                <script> 
                  function initMap(lat, lon) { 
                    var uluru = {lat: lat, lng: lon}; 
                    var map = new google.maps.Map(document.getElementById('map'), { 
                      zoom: 11, 
                      center: uluru 
                    }); 
                    var marker = new google.maps.Marker({ 
                      position: uluru, 
                      map: map,
                      title: \"Sender: $AgentFullName ($AgentUserName) | Geopoint: $lat, $long\"
                    }); 
                  } 
                </script> 
                <script async defer 
                src= \"https://maps.googleapis.com/maps/api/js?key= $googleMapApiKey&callback=initMap($lat, $long)\"> 
                </script> 
            </td>";
        } else if ($originalColumnName == 'geoshape') {
            $geoshape_str_arr = explode(";", substr($ModifiedColumnValue, 0, -1));
            $geoshape_latlan_init = "";
            foreach ($geoshape_str_arr as $single_shape) {
                $single_shape_arr = explode(" ", $single_shape);
                $single_lat = $single_shape_arr[0];
                $single_lng = $single_shape_arr[1];
                $geoshape_latlan_init .= "{ lat: " . $single_lat . ", lng: " . $single_lng . " },";

                $lastLat = $single_lat;
                $lastLng = $single_lng;
            }
            $geoshape_final_coordinates = $geoshape_latlan_init;
            $dataViewTable .= "
            <td style=\"word-wrap: break-word\">$ModifiedColumnValue</td>";
        } else if ($originalColumnName == 'surveyStartDate' || $originalColumnName == 'surveyEndDate') {
            $date_time = str_replace("T", " ", $ModifiedColumnValue);


            $dataViewTable .= "
            <td colspan='2' style=\"word-wrap: break-word;".(($IsEdited == 1) ? 'background-color: #FBC6C2;' : (($IsEdited == 2) ? 'background-color: #fff192;' : ''))."\">$date_time</td>";
        } else if ($originalColumnName == 'Is_Eligible') {
            $dataViewTable .= "
            <td  colspan='2' style=\"word-wrap: break-word;".(($IsEdited == 1) ? 'background-color: #FBC6C2;' : (($IsEdited == 2) ? 'background-color: #fff192;' : ''))."\">$ModifiedColumnValue</td>";
        } else {
            if ($IsEditable == 0) {
                $dataViewTable .= "
                    <td colspan='2' style=\"word-wrap: break-word;".(($IsEdited == 1) ? 'background-color: #FBC6C2;' : (($IsEdited == 2) ? 'background-color: #fff192;' : ''))."\">$ModifiedColumnValue</td>";
            } else {
                $dataViewTable .= "
                    <td style=\"word-wrap: break-word;".(($IsEdited == 1) ? 'background-color: #FBC6C2;' : (($IsEdited == 2) ? 'background-color: #fff192;' : ''))."\">$ModifiedColumnValue</td>";
            }
        }

        if (strpos($ModifiedColumnLabel, "geopoint") === false
            && $originalColumnName != 'surveyStartDate'
            && $originalColumnName != 'surveyEndDate'
            && $originalColumnName != 'Is_Eligible'
            && $IsEditable != 0
            && ($EditPermission == 1
                || (strpos($LoggedUserName, 'val') !== false)
                || strpos($LoggedUserName, 'sasadmin') !== false)) {

            preg_match_all("/\((.*?)\)/", $ModifiedColumnLabel, $matches);
            $fieldName = $matches[1][count($matches[1]) - 1];

            $dataViewTable .= "<td ".(($IsEdited == 1) ? 'style="background-color: #FBC6C2;"' : (($IsEdited == 2) ? 'style="background-color: #fff192;"' : ''))."><input type='text' class='form-control' name='field[$fieldName]'></td>";
        }

        $dataViewTable .= "
        </tr>";
    }

    return $dataViewTable;
}

?>


<script>
    new jBox('Tooltip', {
        attach: '.jBoxTip',
        theme: 'TooltipDark',
        animation: 'zoomOut',
        adjustDistance: {
            top: 62 + 8,
            right: 5,
            bottom: 5,
            left: 5
        },
        zIndex: 9999
    });
</script>