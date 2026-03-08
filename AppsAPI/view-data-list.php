<?php
error_reporting(1);

require '../vendor/autoload.php';

$app = new Solvers\Dsql\Application();

include "../Config/config.php";
include "../Lib/lib.php";

if ($_REQUEST['ui'] != '') {
    $UserID = $app->cleanInput($_REQUEST['ui']);
}

if ($_REQUEST['formId'] != '') {
    $FormID = $app->cleanInput($_REQUEST['formId']);
}

if ($_REQUEST['status'] != '') {
    $ShowStatus = $app->cleanInput($_REQUEST['status']);
}

if ($ShowStatus == '2' || $ShowStatus == '1' || $ShowStatus == '0') {
    $sql = "SELECT xfr.id, xfr.DataName, xfr.EntryDate 
    FROM xformrecord xfr JOIN userinfo ui ON xfr.UserID = ui.id  
    WHERE IsApproved = ? AND xfr.UserID = ? AND xfr.FormId = ?
    ORDER BY xfr.EntryDate DESC";
    $sqlResult = $app->getDBConnection()->query($sql, $ShowStatus, $UserID, $FormID);
} else {
    $sql = "SELECT dxfr.id, dxfr.DataName, dxfr.EntryDate
    FROM deletedxformrecord dxfr JOIN userinfo ui ON dxfr.UserID = ui.id 
    WHERE dxfr.UserID = ? AND dxfr.FormId = ?
    ORDER BY xfr.EntryDate DESC";
    $sqlResult = $app->getDBConnection()->query($sql, $UserID, $FormID);
}

$data = array();
$sl = 1;

foreach ($sqlResult as $row) {
    $RecordID = $row->id;
    $DataName = $row->DataName;

    $EntryDate = date_format($row->EntryDate, "d-m-Y H:i:s");

    //$encrypRecordID = encryptValue($RecordID.'|||'.$adminUserID.'|||'.$UserID);
    $encrypRecordID = $RecordID;

    $SubData = array();

    $SubData[] = $sl;
    $SubData[] = $RecordID;
    $SubData[] = $DataName;
    $SubData[] = $EntryDate;

    $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">
                    <button title=\"$btnTitleView\" type=\"button\" class=\"btn btn-outline-primary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#viewDataModal\" onclick=\"ShowDataDetail('$RecordID');\"><i class=\"fas fa-eye\"></i></button>";
    if ($ShowStatus == '2') {
        $actions .= "<a href=\"#\" title=\"$btnTitleEdit\" type=\"button\" class=\"btn btn-outline-warning\" style=\"display: inline-block;margin: 0 1px;\" onClick='NewWindow(\"../webhookURLs/review.php?xFormId=$encrypRecordID\", \"name\", \"600\", \"600\", \"Yes\"); return false;'><i class=\"fas fa-pencil-alt\"></i></a>";
    }
    $actions .="</div>
                
                <script type=\"text/javascript\">
                    function ShowDataDetail(recordID, data) {
                            $.ajax({
                                /*url: '../SpecialTask/ajax-data/data-map-view.php',*/
                                url: 'data-detail-view.php',
                                method: 'GET',
                                datatype: 'json',
                                data: {
                                    id: recordID
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


    $SubData[] = $actions;

    $sl++;

    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';
