<?php

error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$RecordID = xss_clean($_REQUEST['id']);

$qry = "SELECT PSU, IsApproved, XFormsFilePath FROM xformrecord WHERE id = ?";
$qryRs = $app->getDBConnection()->query($qry, $RecordID);

foreach ($qryRs as $row) {
    $IsApproved = $row->IsApproved;
    $PSU = $row->PSU;
    $XFormsFilePath = $row->XFormsFilePath;
}

if ($IsApproved == 0) {
    $MasterDataQuery = "EXEC ViewDetailDataWithLabelPending $RecordID";

    $MasterDataTimeQuery = "SELECT ColumnName, ColumnValue FROM masterdatarecord_Pending WHERE XFormRecordId = ? AND (ColumnName = 'surveyStartDate' OR ColumnName = 'surveyEndDate') ORDER BY ColumnName ASC  ";
    $MasterDataTimeRS = $app->getDBConnection()->fetch($MasterDataTimeQuery, $RecordID);
} elseif ($IsApproved == 1) {
    $MasterDataQuery = "EXEC ViewDetailDataWithLabelApproved $RecordID";

    $MasterDataTimeQuery = "SELECT ColumnName, ColumnValue FROM masterdatarecord_Approved WHERE XFormRecordId = ? AND (ColumnName = 'surveyStartDate' OR ColumnName = 'surveyEndDate') ORDER BY ColumnName ASC  ";
    $MasterDataTimeRS = $app->getDBConnection()->fetch($MasterDataTimeQuery, $RecordID);
} elseif ($IsApproved == 2) {
    $MasterDataQuery = "EXEC ViewDetailDataWithLabelUnApproved $RecordID";

    $MasterDataTimeQuery = "SELECT ColumnName, ColumnValue FROM masterdatarecord_UnApproved WHERE XFormRecordId = ? AND (ColumnName = 'surveyStartDate' OR ColumnName = 'surveyEndDate') ORDER BY ColumnName ASC  ";
    $MasterDataTimeRS = $app->getDBConnection()->fetch($MasterDataTimeQuery, $RecordID);
}

$dataViewTable = "
<div class=\"modal-header\">
    <h5 class=\"modal-title\" id=\"editDataModalLabel\">Data Detail View</h5>
    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
</div>
<div class=\"modal-body\">
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
    </tr>";

$MasterDataQueryRS = $app->getDBConnection()->fetchAll($MasterDataQuery);
foreach ($MasterDataQueryRS as $MDRrow) {
    $originalColumnName = $MDRrow->ColumnNameOriginal;
    $ModifiedColumnLabel = $MDRrow->ColumnLabelModified;
    $originalColumnValue = $MDRrow->ColumnValueOriginal;
    $ModifiedColumnValue = $MDRrow->ColumnValueModified;

    $dataViewTable .= "
    <tr>
        <td style=\"word-wrap: break-word\" width=\"50%\">$ModifiedColumnLabel</td>";

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
        <td style=\"word-wrap: break-word\" width=\"50%\">$ModifiedColumnValue</td>";
    } else {
        $dataViewTable .= "
        <td style=\"word-wrap: break-word\" width=\"50%\">$ModifiedColumnValue</td>";
    }

    $dataViewTable .= "
    </tr>";
}

$dataViewTable .= "
    </tbody>
</table>";

$dataViewTable .= "<div class=\"modal-footer\">";

$dataViewTable .= "<button type=\"button\" class=\"btn btn-primary\" data-bs-dismiss=\"modal\">Close</button>";

$dataViewTable .= "<div>
</div>";

echo $dataViewTable;

