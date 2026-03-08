<?php
error_reporting(1);

require '../vendor/autoload.php';

$app = new Solvers\Dsql\Application();

include "../Config/config.php";
include "../Lib/lib.php";

if ($_REQUEST['ui'] != '') {
    $UserID = $app->cleanInput($_REQUEST['ui']);
}

if ($_REQUEST['psu'] != '') {
    $PSU = $app->cleanInput($_REQUEST['psu']);
}

if ($_REQUEST['FormID'] != '') {
    $FormID = $app->cleanInput($_REQUEST['FormID']);
}

$query = "SELECT s.id, s.XFormID, s.PSU ,s.UserID, s.HHeadName, s.MobileNumber, s.HHAddress, s.MainHHNumber, s.IntervalValue, s.SampleHHNumber, s.CompanyID, u.UserName, 
        u.FullName, s.geopoint FROM SampleMapping AS s 
        JOIN userinfo AS u ON s.UserID = u.id 
        WHERE s.UserID = ? and s.PSU = ? 
        ORDER BY PSU, MainHHNumber ASC";
$queryRS = $app->getDBConnection()->fetchAll($query, $UserID, $PSU);

$data = array();

foreach ($queryRS as $row) {
    $XFormID = $row->XFormID;
    $PSU = $row->PSU;
    $UserID = $row->UserID;
    $UserName = $row->UserName;
    $FullName = $row->FullName;
    $HHeadName = $row->HHeadName;
    $MobileNumber = $row->MobileNumber;
    $HHAddress = $row->HHAddress;
    $MainHHNumber = $row->MainHHNumber;
    $IntervalValue = $row->IntervalValue;
    $SampleHHNumber = $row->SampleHHNumber;
    $geopoint = $row->geopoint;

    $queryIsCollected = "SELECT count(*) as totalCollected from xformrecord where FormId = $FormID and UserID = ? and PSU = ? and SampleHHNo = ?";
    $rowQueryIsCollected = $app->getDBConnection()->fetch($queryIsCollected, $UserID, $PSU, $MainHHNumber);
    $hasCollected = $rowQueryIsCollected->totalCollected;
    
    if ($hasCollected > 0) {
        $CollectStatus = "<span style='color: green'>Collected</span>";
    }else{
        $CollectStatus = "<span style='color: red'>Not Collected</span>";
    }

    $SubData = array();

    $SubData[] = $MainHHNumber;
    $SubData[] = $SampleHHNumber;
    $SubData[] = $CollectStatus;
    $SubData[] = $HHeadName;
    $SubData[] = $MobileNumber;
    $SubData[] = $HHAddress;

    $location = "<div style= \"display: flex; align-items: center; justify-content: center;\">
                    <button type=\"button\" class=\"btn btn-outline-primary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#viewDataModal\" onclick=\"ShowDataDetail('$XFormID', '$SampleHHNumber')\"><i class=\"fa-solid fa-location-dot\"></i></button>
                </div>
                
                <script type=\"text/javascript\">
                    function ShowDataDetail(recordID, sampleHH, data) {
                            $.ajax({
                                /*url: '../SpecialTask/ajax-data/data-map-view.php',*/
                                url: 'data-map-view.php',
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

    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';
