<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

if ($_REQUEST['recordid'] != '') {
    $recordid = $app->cleanInput($_REQUEST['recordid']);
    $LoggedUserID = xss_clean($_REQUEST["LoggedUserID"]);
}
$qry = "SELECT xfr.id, xfr.FormId, xfr.SampleHHNo, xfr.PSU, psl.DivisionName, psl.DistrictName, xfr.UserID, xfr.DataName, xfr.XFormsFilePath, 
					COALESCE(xfr.IsEdited, 0) AS IsRowEdited, xfr.EntryDate, xfr.IsApproved, xfr.DeviceID FROM xformrecord xfr JOIN PSUList psl ON xfr.PSU=psl.PSU WHERE xfr.id = ?";
$resQry = $app->getDBConnection()->fetchAll($qry, $recordid);
//$resQry = $app->getDBConnection()->fetchAll($qry);

$data = array();

foreach ($resQry as $row) {
    $RecordID = $row->id;
    $HhNo = $row->SampleHHNo;
    $PSU = $row->PSU;
    $DivisionName = $row->DivisionName;
    $DistrictName = $row->DistrictName;

    $UserID = $row->UserID;
    $UserName = getValue('userinfo', 'UserName', "id = $UserID");
    $FullName = getValue('userinfo', 'FullName', "id = $UserID");
    $UserInfo = "$FullName ($UserName/$UserID)";

    $DataCompanyID = getValue('userinfo', 'CompanyID', "id = $UserID");

    $UserMobileNo = getValue('userinfo', 'MobileNumber', "id = $UserID");
    $UserMobileNo = whatsAppLink($UserMobileNo);

    $FormId = $row->FormId;
    if ($FormId == $formIdSamplingData) {
        $Survey = "$formTypeListing Survey";
    } elseif ($FormId == $formIdMainData) {
        $Survey = "$formTypeMain Survey";
    } elseif ($FormId == $formIdFarmData){
        $Survey = "$formTypeFarm Survey";
    }

    $DataName = $row->DataName;
    $XFormsFilePath = $row->XFormsFilePath;
    $DeviceID = $row->DeviceID;
    $EntryDate = date_format($row->EntryDate, 'd-m-Y H:i:s');

    $IsApproved = $row->IsApproved;
    $DataStatus = GetDataStatus($IsApproved);
		
	$IsEdited = $row->IsRowEdited;

    $Duration = 'N/A';

    $SubData = array();

    $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">
                    <button title=\"$btnTitleView\" type=\"button\" class=\"simple-ajax-modal btn btn-outline-primary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#viewDataModal\" onclick=\"ShowDataDetail('$RecordID', '$LoggedUserID', '$IsApproved', '$PSU', '$FormId')\"><i class=\"fas fa-eye\"></i></button>
                    
                     <button title=\"$btnTitleNotice\" type=\"button\" class=\"btn btn-outline-secondary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#sendNoticeModal$RecordID\"><i class=\"fas fa-bell\"></i></button>
                    
                </div>
                <script type=\"text/javascript\">
                    function ShowDataDetail(recordID, loggedUserID, status, psu, formID, data) {
                            $.ajax({
                                url: 'ViewData/ajax-data/data-detail-view-single-data.php',
                                method: 'GET',
                                datatype: 'json',
                                data: {
                                    id: recordID,
                                    loggedUserID: loggedUserID,
                                    status: status,
                                    psu: psu,
                                    formID: formID
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
                  <!--Send Notification Modal-->
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
                                <input type=\"text\" class=\"form-control\" name=\"UserName\" id=\"UserName$RecordID\" value=\"$UserInfo\" readonly>
                                <input type=\"hidden\" class=\"form-control\" name=\"Userid\" id=\"Userid$RecordID\" value=\"$UserID\">
                            </div>
                            <div class=\"form-group\">
                                <label for=\"UserPass\">Message<span class=\"required\">*</span></label>
                                <textarea class=\"form-control\" rows=\"4\" id=\"message$RecordID\" data-plugin-textarea-autosize placeholder='write message here' required></textarea>
                            </div>
                            
                            <div class=\"modal-footer\">
                                <button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">Close</button>
                                <button type=\"button\" class=\"btn btn-primary\" name=\"Save\" id=\"Save\" value=\"Send\" 
                                onclick= \"
                                var toID = document.getElementById('Userid$RecordID').value;
                                var uMessage = document.getElementById('message$RecordID').value;
                                
                                //alert(uMessage);

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

    $SubData[] = $RecordID;
    $SubData[] = $DataStatus;
    $SubData[] = $UserInfo;
    $SubData[] = $UserMobileNo;
    $SubData[] = $Survey;
    $SubData[] = $HhNo;
    $SubData[] = $PSU;
    $SubData[] = $DivisionName;
    $SubData[] = $DistrictName;
    $SubData[] = $EntryDate;
    $SubData[] = $DeviceID;
	//$SubData[] = $IsEdited;

    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

