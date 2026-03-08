<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$RecordID = xss_clean($_REQUEST['id']);
$sampleHH = xss_clean($_REQUEST['sampleHH']);

$qry = "SELECT ColumnValue FROM masterdatarecord_Approved WHERE ColumnName='geopoint' and XFormRecordId = $RecordID";
$row = $app->getDBConnection()->fetch($qry);

$TotalLatLong = explode(" ", $row->ColumnValue);
$lat = $TotalLatLong[0];
$long = $TotalLatLong[1];

$dataViewTable = "
<div class=\"modal-header\">
    <h5 class=\"modal-title\" id=\"viewDataModalLabel\">Location of Sample HH : $sampleHH ($lat | $long)</h5>
    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
</div>
<div class=\"modal-body\">
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
                  title: \"Geopoint: $lat, $long\"
                }); 
              } 
            </script> 
            <script async defer 
            src= \"https://maps.googleapis.com/maps/api/js?key= $googleMapApiKey&callback=initMap($lat, $long)\"> 
            </script> 
</div>";

echo $dataViewTable;
