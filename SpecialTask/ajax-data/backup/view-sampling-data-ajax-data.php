<?php
error_reporting(1);

require '../../vendor/autoload.php';

$app = new Solvers\Dsql\Application();

include "../../Config/config.php";
include "../../Lib/lib.php";

if ($_REQUEST['interval'] != '') {
    $interval = $app->cleanInput($_REQUEST['interval']);
}

$query = "SELECT XFormID, PSU, UserID, HHeadName, MobileNumber, HAddress, MainSampleHH, CumulativeNo, ListingHH, geopoint FROM ##Sample ORDER BY MainSampleHH, PSU ASC";
$queryRS = $app->getDBConnection()->fetchAll($query);

$data = array();
$il = 1;

foreach ($queryRS as $row) {
    $XFormID = $row->XFormID;
    $PSU = $row->PSU;
    $HHeadName = $row->HHeadName;
    $MobileNumber = $row->MobileNumber;
    $TotalMember = $row->HAddress;
    $SampleHH = $row->MainSampleHH;
    $CumulativeNo = number_format($row->CumulativeNo, 2);
    $ListingHH = $row->ListingHH;
    $geopoint = $row->geopoint;

    $TotalLatLong = explode(" ", $geopoint);
    $lat = $TotalLatLong[0];
    $long = $TotalLatLong[1];

    $SubData = array();

    $SubData[] = $il;
    $SubData[] = $XFormID;
    $SubData[] = $PSU;
    $SubData[] = $HHeadName;
    $SubData[] = $MobileNumber;
    $SubData[] = $TotalMember;
    $SubData[] = $SampleHH;
    $SubData[] = $CumulativeNo;
    $SubData[] = $ListingHH;

    $location = "<div style= \"display: flex; align-items: center; justify-content: center;\">
                    <button type=\"button\" class=\"btn btn-outline-danger\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#viewDataModal\" onclick=\"ShowDataDetail('$XFormID', '$SampleHH')\"><i class=\"fa-solid fa-location-dot\"></i></button>
                </div>
                
                <script type=\"text/javascript\">
                    function ShowDataDetail(recordID, sampleHH, data) {
                            $.ajax({
                                url: 'SpecialTask/ajax-data/data-map-view.php',
                                method: 'GET',
                                datatype: 'json',
                                data: {
                                    id: recordID,
                                    sampleHH: sampleHH
                                },
                                success: function (response) {
                                    //alert(response);
                                    $('#dataViewDiv').html(response);
                                }
                            }); 
                        return false;
                    }
                </script>
                
                 <!-- Modal View-->
                <div class=\"modal fade\" id=\"viewDataModal\" tabindex=\"-1\" aria-labelledby=\"viewDataModalLabel\" aria-hidden=\"true\">
                  <div class=\"modal-dialog\">
                    <div id=\"dataViewDiv\" class=\"modal-content\">
                      
                    </div>
                  </div>
                </div>";


    $SubData[] = $location;

    $il++;

    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';
