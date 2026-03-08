<?php

error_reporting(E_ALL);

require '../../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../../Config/config.php";
include "../../Lib/lib.php";

$DataFromID = xss_clean($_REQUEST['dataFromID']);
$RecordID = xss_clean($_REQUEST['id']);
$IsApproved = xss_clean($_REQUEST['status']);
$PSU = xss_clean($_REQUEST['psu']);
$LoggedUserID = xss_clean($_REQUEST['loggedUserID']);
$AgentID = xss_clean($_REQUEST['agentID']);
$XFormsFilePath = xss_clean($_REQUEST['XFormsFilePath']);

$LoggedUserName = getValue('userinfo', 'UserName', "id = $LoggedUserID");
$AgentFullName = getValue('userinfo', 'FullName', "id = $AgentID");
$AgentUserName = getValue('userinfo', 'UserName', "id = $AgentID");

$SupervisorPermission = "SELECT EditPermission, DeletePermission, ApprovePermission FROM assignsupervisor WHERE SupervisorID = ? AND UserID = ?";
$RowSupervisorPermission = $app->getDBConnection()->fetch($SupervisorPermission, $LoggedUserID, $AgentID);
$EditPermission = $RowSupervisorPermission->EditPermission;
$DeletePermission = $RowSupervisorPermission->DeletePermission;
$ApprovePermission = $RowSupervisorPermission->ApprovePermission;

$GetCauseOfUnapprove = getValue('xformrecord', 'Cause', " id = $RecordID");
$CauseOfUnapprove = str_replace('Cause of un-approve: ', '', $GetCauseOfUnapprove);
if (strlen($CauseOfUnapprove) === 0) {
    $CauseOfUnapprove = "NULL";
}

//$Permissions = "A: $ApprovePermission | E: $EditPermission | D:$DeletePermission";

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
<p class=\"modal-title\" id=\"editDataModalLabel\"><b>Cause of un-approve</b>: <span style='color: red'>$CauseOfUnapprove</span></p><br>
<table align=\"left\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"table table-striped table-bordered datatables\" id=\"example\">
    <thead>
    <tr role=\"row\">
        <th width=\"50%\">Column Lebel</th>
        <th width=\"50%\">Column Value</th>
    </tr>
    </thead>
    <tbody>
    <tr align=\"left\" class=\"textRpt\">
        <td width=\"50%\"><b>Record ID</b></td>
        <td width=\"50%\"><b>$RecordID</b></td>
    </tr>
    <tr align=\"left\" class=\"textRpt\">
        <td width=\"50%\"><b>PSU</b></td>
        <td width=\"50%\"><b>$PSU</b></td>
    </tr>
    <tr align=\"left\" class=\"textRpt\">
        <td width=\"50%\" style='color: red'><b>Data Collection Duration</b></td>
        <td width=\"50%\" style='color: red'><b>$Duration</b></td>
    </tr>";

    $dataViewTable .= "
    </tbody>
</table>";

$AllModuleInfoQuery = "SELECT ModuleName, ColumnName, Gender  FROM ModuleInfo WHERE FormId = $DataFromID ORDER BY id ASC";

$MasterDataQueryRS = $app->getDBConnection()->fetchAll($MasterDataQuery);
$AllModuleInfoRS = $app->getDBConnection()->fetchAll($AllModuleInfoQuery);

$ModuleModifiedColumnValue = [];
$RowGender = array();

foreach ($AllModuleInfoRS as $row) {
    //$ModuleModifiedColumnValue[$row['ColumnName']] = $row['ModuleName'];
    $ModuleModifiedColumnValue[substr($row['ColumnName'], 0, 2)] = $row['ModuleName'] . '_';
	if ($row['Gender'] == 2) {
		$RowGender[$row['ColumnName']] = $row['Gender'];
		//var_dump($RowGender['C_01_2']);exit;
	}
}

$ModuleGroupData = [];
foreach ($MasterDataQueryRS as $MDRrow) {
    //$ModuleName = $ModuleModifiedColumnValue[$MDRrow->ColumnNameOriginal] != '' ? $ModuleModifiedColumnValue[$MDRrow->ColumnNameOriginal] : 'Others';
    $ModuleName = substr($MDRrow->ColumnNameOriginal, 1, 1) == '_' ? substr($MDRrow->ColumnNameOriginal, 0, 1) : 'Others';
    $ModuleGroupData[$ModuleName][] = $MDRrow;
}

$dataViewTable .= "<div class=\"accordion\" id=\"accordionExample\">";

foreach ($ModuleGroupData as $ModuleName => $ModuleData) {
    $dataViewTable .= "
        <div class=\"accordion-item\">
                <h2 class=\"accordion-header\" id=\"Accordion-$ModuleName\">
                    <button class=\"accordion-button collapsed px-3 py-0\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapse-$ModuleName\" aria-expanded=\"true\" aria-controls=\"collapse-$ModuleName\">
                        $ModuleName - Module
                    </button>
                </h2>
            <div id=\"collapse-$ModuleName\" class=\"accordion-collapse collapse\" aria-labelledby=\"Accordion-$ModuleName\" data-bs-parent=\"#accordionExample\" style=\"overflow: auto;\">
                <div class=\"accordion-body\">
                <table align=\"left\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"table table-striped table-bordered datatables\" id=\"example\">
                    <thead>
                        <tr role=\"row\">
                            <th width=\"40%\">Column Lebel</th>
                            <th width=\"40%\">Column Value</th>
                        </tr>
                    </thead>
                    <tbody>";
    
    $dataViewTable .= getAccordionTableData($ModuleData, $XFormsFilePath, $baseURL, $googleMapApiKey, $LoggedUserName, $EditPermission, $IsEditable, $AgentFullName, $AgentUserName, $IsApproved, $RowGender);

    $dataViewTable .= "</tbody>
                </table>
                </div>
            </div>
        </div>
    ";
}

$dataViewTable .= "</div>";

$dataViewTable .= "<div class=\"modal-footer\">";

$dataViewTable .= "<button type=\"button\" class=\"btn btn-primary\" data-bs-dismiss=\"modal\">Close</button>";

$dataViewTable .= "<div>
</div>";

echo $dataViewTable;

function getAccordionTableData($ModuleData, $XFormsFilePath, $baseURL, $googleMapApiKey, $LoggedUserName, $EditPermission, $IsEditable, $AgentFullName, $AgentUserName, $IsApproved, $RowGender) {
    $dataViewTable = "";
    foreach ($ModuleData as $MDRrow) {
        $originalColumnName = $MDRrow->ColumnNameOriginal;
        $ModifiedColumnLabel = $MDRrow->ColumnLabelModified;
        $originalColumnValue = $MDRrow->ColumnValueOriginal;
        $ModifiedColumnValue = $MDRrow->ColumnValueModified;
    
        if (empty($RowGender[$originalColumnName])) {
			$Gender = 0;
		} else {
			$Gender = $RowGender[$originalColumnName];
		}
		
        $dataViewTable .= "
        <tr>
            <td style=\"word-wrap: break-word;".($Gender==2 ? 'background-color:#ead1dc;' : '')."\" width=\"50%\">$ModifiedColumnLabel";
    
        if ($MDRrow->Comments != '') {
            $dataViewTable .= "<span style='float: right;'><img class=\"jBoxTip\" src=\"../../img/Comments.png\" border=0 style=\"cursor: pointer;width: 50px;\" title='$MDRrow->Comments'></span>";
        }
    
        $dataViewTable .= "</td>";
    
        if (strtoupper($originalColumnName) == 'PICTURE' or strtoupper($originalColumnName) == 'IMAGE' or (strpos(strtoupper($originalColumnName), "IMAGE") !== false) or (strpos(strtoupper($originalColumnName), "PICTURE") !== false)) {
            $ImageFilePathArray = explode("/", $XFormsFilePath);
            $ImageFilePath = $baseURL . $ImageFilePathArray[1] . "/" . $ImageFilePathArray[2] . "/" . $ImageFilePathArray[3] . "/" . $ImageFilePathArray[4] . "/" . $ModifiedColumnValue;
            $ImageFilePath = $baseURL . $ImageFilePathArray[0] . "/" . $ImageFilePathArray[1] . "/" . $ImageFilePathArray[2] . "/" . $ModifiedColumnValue;
    
            $dataViewTable .= "
            <td><img src=\"" . $ImageFilePath . "\" width=200 height=200 ></td>";
        } else if ($originalColumnName == 'audio') {
            $ImageFilePathArray = explode("/", $XFormsFilePath);
            $ImageFilePath = $baseURL . $ImageFilePathArray[0] . "/" . $ImageFilePathArray[1] . "/" . $ImageFilePathArray[2] . "/" . $ModifiedColumnValue;
            $dataViewTable .= "
            <td width=\"50%\"><audio preload=\"none\" controls><source src=\"" . $ImageFilePath . "\" type=\"audio/mpeg\"></audio></td>";
        } else if ($originalColumnName == 'geopoint') {
            $TotalLatLong = explode(' ', $ModifiedColumnValue);
            $lat = $TotalLatLong[0];
            $long = $TotalLatLong[1];
            $dataViewTable .= "
            <td width=\"50%\">
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
            <td style=\"word-wrap: break-word;".($Gender==2 ? 'background-color:#ead1dc;' : '')."\" width=\"50%\">$ModifiedColumnValue</td>";
        } else if ($originalColumnName == 'surveyStartDate' || $originalColumnName == 'surveyEndDate') {
            $date_time = str_replace("T", " ", $ModifiedColumnValue);
            
            
            $dataViewTable .= "
            <td style=\"word-wrap: break-word;".($Gender==2 ? 'background-color:#ead1dc;' : '')."\" width=\"50%\">$date_time</td>";
        } else {
            $dataViewTable .= "
            <td style=\"word-wrap: break-word;".($Gender==2 ? 'background-color:#ead1dc;' : '')."\" width=\"50%\">$ModifiedColumnValue</td>";
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