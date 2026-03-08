<?php
// error_reporting(1);
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

if ($_REQUEST['frmID'] != '') {
    $frmID = $app->cleanInput($_REQUEST['frmID']);
}

if ($_REQUEST['lui'] != '') {
    $LoggedUserID = $app->cleanInput($_REQUEST['lui']);
}

if ($_REQUEST['lci'] != '') {
    $DataCompanyID = $app->cleanInput($_REQUEST['lci']);
}

if ($_REQUEST['dataStatus'] != '') {
    $IsApproved = $app->cleanInput($_REQUEST['dataStatus']);
}

if ($_REQUEST['colName1'] != '') {
    $colName1 = $app->cleanInput($_REQUEST['colName1']);
}

if ($_REQUEST['colName2'] != '') {
    $colName2 = $app->cleanInput($_REQUEST['colName2']);
}

if ($_REQUEST['colName3'] != '') {
    $colName3 = $app->cleanInput($_REQUEST['colName3']);
}

// if ($_REQUEST['sql'] != '') {
//     $qry = $app->cleanInput($_REQUEST['sql']);
// }

// $resQry = $app->getDBConnection()->fetchAll($qry);


if (!empty($_REQUEST['sql'])) {
    // Do NOT sanitize SQL here, it is system-generated
    $qry = $_REQUEST['sql'];
}

try {
    $resQry = $app->getDBConnection()->fetchAll($qry);
} catch (Exception $e) {
    // Prevent HTML/SQL text from breaking DataTables JSON
    // $errorMessage = str_replace("\n", " ", $e->getMessage());
    // echo json_encode(["aaData" => [], "error" => $errorMessage]);
    // exit;









    $errorMessage = $e->getMessage();

    if (stripos($errorMessage, 'Conversion failed') !== false || stripos($errorMessage, 'varchar value') !== false) {
        $friendlyMessage = 'Please select different columns — duplicate or invalid column selection detected.';
    } elseif (stripos($errorMessage, 'specified multiple times') !== false) {
        $friendlyMessage = 'The same column cannot be selected more than once.';
    } else {
        $friendlyMessage = 'An unexpected error occurred while retrieving data. Please check your selection.';
    }

    file_put_contents(__DIR__ . '/../debug_logs.txt', "[" . date('Y-m-d H:i:s') . "] SQL ERROR: $errorMessage\n", FILE_APPEND);

    echo json_encode(["aaData" => [], "customError" => $friendlyMessage]);
    exit;














}

// define missing variables to avoid notices
$btnTitleView = "View Record";
$btnTitleNotice = "Send Notification";



$data = array();

foreach ($resQry as $row) {
    $RecordID = $row->XFormRecordId;

    $PSU = getValue('xformrecord', 'PSU', "id = $RecordID");
    //$XFormsFilePath = getValue('xformrecord', 'XFormsFilePath', "id = $RecordID");

    $UserID = $row->UserID;
    $UserName = getValue('userinfo', 'UserName', "id = $UserID");
    $UserFullName = getValue('userinfo', 'FullName', "id = $UserID");
    $User = "$UserFullName ($UserName/$UserID)";

    $MobileNo = getValue('userinfo', 'MobileNumber', "id = $UserID");
    $UserMobileNo = whatsAppLink($MobileNo);

    $IndicatorValue1 = $row->Column1;
    $IndicatorValueLabel1 = getValue('ChoiceInfo', 'ChoiceLabel', "ChoiceValue = '$IndicatorValue1' AND ChoiceListName = (SELECT ChoiceListName FROM xformcolumnname WHERE FormId = $frmID AND ColumnName = '$colName1')");



    $IndicatorValue2 = $row->Column2;
    $IndicatorValueLabel2 = getValue('ChoiceInfo', 'ChoiceLabel', "ChoiceValue = '$IndicatorValue2' AND ChoiceListName = (SELECT ChoiceListName FROM xformcolumnname WHERE FormId = $frmID AND ColumnName = '$colName2')");

    $IndicatorValue3 = $row->Column3;
    $IndicatorValueLabel3 = getValue('ChoiceInfo', 'ChoiceLabel', "ChoiceValue = '$IndicatorValue3' AND ChoiceListName = (SELECT ChoiceListName FROM xformcolumnname WHERE FormId = $frmID AND ColumnName = '$colName3')");

    if($IsApproved == 1){
        $viewURL = 'ViewData/ajax-data/data-detail-view.php';
    }elseif ($IsApproved == 0){
        $viewURL = 'ViewData/ajax-data/data-detail-view-pending-data.php';
    }elseif ($IsApproved == 2){
        $viewURL = 'ViewData/ajax-data/data-detail-view-unapproved-data.php';
    }


    $SubData = array();

    $SubData[] = $RecordID;
    $SubData[] = $User;
    $SubData[] = $UserMobileNo;
    $SubData[] = $IndicatorValueLabel1;
    $SubData[] = $IndicatorValueLabel2;
    $SubData[] = $IndicatorValueLabel3;


    $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">
                    <button title=\"$btnTitleView\" type=\"button\" class=\"simple-ajax-modal btn btn-outline-primary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#viewDataModal\" 
                    onclick=\"ShowDataDetail('$viewURL', '$frmID', '$RecordID', '$IsApproved', '$PSU', '$LoggedUserID', '$UserID', '$XFormsFilePath')\"><i class=\"fas fa-eye\"></i></button>
                    
                    <button title=\"$btnTitleNotice\" type=\"button\" class=\"btn btn-outline-secondary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#sendNoticeModal$RecordID\"><i class=\"fas fa-bell\"></i></button>
                </div>
                <script type=\"text/javascript\">
                    function ShowDataDetail(viewUrl, dataFromID, recordID, isAproved, psu, loggedUserID, agentID, XFormsFilePath, data) {
                    //alert(viewUrl);
                            $.ajax({
                                url: viewUrl,
                                method: 'GET',
                                datatype: 'json',
                                data: {
                                    dataFromID: dataFromID,
                                    id: recordID,
                                    status: isAproved,
                                    psu: psu,
                                    loggedUserID: loggedUserID,
                                    agentID: agentID,
                                    XFormsFilePath: XFormsFilePath
                                },
                                success: function (response) {
                                    //alert(response);
                                    $('#dataViewDiv').html(response);
                                }
                            }); 
                        return false;
                    }
                </script>
                
                <!-- View Data Modal-->
                <div class=\"modal fade bd-example-modal-lg\" id=\"viewDataModal\" tabindex=\"-1\" aria-labelledby=\"editDataModalLabel\" aria-hidden=\"true\">
                  <div class=\"modal-dialog modal-lg\">
                    <div id=\"dataViewDiv\" class=\"modal-content\">
                      
                    </div>
                  </div>
                </div>";

    $actions .= " 
                 <!-- Send Notification Modal-->
                <div class=\"modal fade\" id=\"sendNoticeModal$RecordID\" tabindex=\"-1\" aria-labelledby=\"editDataModalLabel\" aria-hidden=\"true\">
                  <div class=\"modal-dialog\">
                    <div class=\"modal-content\">
                      <div class=\"modal-header\">
                      <h5 class=\"modal-title\" id=\"editDataModalLabel\">Send Message</h5>
                        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
                      </div>
                      <div class=\"modal-body\">
                        <form id=\"editForm\" method=\"POST\" action=\"\">
                            <div class=\"form-group\">
                                <label for=\"UserName\">Recipient</label>
                                <input type=\"text\" class=\"form-control\" name=\"UserName\" id=\"UserName$RecordID\" value=\"$User\" readonly>
                                <input type=\"hidden\" class=\"form-control\" name=\"Userid\" id=\"Userid$RecordID\" value=\"$UserID\">
                            </div>
                            <div class=\"form-group\">
                                <label for=\"UserPass\">Message<span class=\"required\">*</span></label>
                                <textarea class=\"form-control\" rows=\"3\" id=\"message$RecordID\" data-plugin-textarea-autosize placeholder='write message here' required></textarea>
                            </div>
                            
                            <div class=\"modal-footer\">
                                <button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">Close</button>
                                <button type=\"button\" class=\"btn btn-primary\" name=\"Save\" id=\"Save\" value=\"Send\" 
                                onclick= \"
                                var toID = document.getElementById('Userid$RecordID').value;
                                var uMessage = document.getElementById('message$RecordID').value;

                                SendNotification('$LoggedUserID', toID, uMessage, '$DataCompanyID');
                                \">
                                Send Message
                                </button>
                             </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>";

    $SubData[] = $actions;

    $data[] = $SubData;

}

/*$SubData[] = $qry;
$data[] = $SubData;*/

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

