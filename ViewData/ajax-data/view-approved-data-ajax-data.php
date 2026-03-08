<?php
error_reporting(1);
require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

$cn = ConnectDB();

if (!empty($_POST)) {
    $request = $_REQUEST;
    $DataFromID = xss_clean($_REQUEST["DataFromID"]);
    $DataUserID = xss_clean($_REQUEST["DataUserID"]);
    $DataChkAll = xss_clean($_REQUEST["DataChkAll"]);
    $DataCompanyID = xss_clean($_REQUEST["DataCompanyID"]);
    $LoggedUserName = xss_clean($_REQUEST["LoggedUserName"]);
    $LoggedUserID = xss_clean($_REQUEST["LoggedUserID"]);
    $DataStartDate = xss_clean($_REQUEST["DataStartDate"]);
    $DataEndDate = xss_clean($_REQUEST["DataEndDate"]);

    $col[] = "id";
	$col[] = "id";
	$col[] = "SampleHHNo";
	$col[] = "PSU";
	$col[] = "DivisionName";
	$col[] = "DistrictName";
	$col[] = "userid";
	$col[] = "MobileNumber";
	$col[] = "DataName";
	$col[] = "EntryDate";
	$col[] = "IsApproved";
	$col[] = "DeviceID";

    $qry = "SELECT xfr.id, 
					xfr.SampleHHNo, 
					xfr.PSU, 
					ui.UserName, 
					ui.id as userid, 
					ui.FullName, 
					ui.MobileNumber, 
					xfr.DataName, 
					xfr.DeviceID, 
					xfr.EntryDate, 
					xfr.FormGroupId, 
					xfr.IsApproved, 
					xfr.XFormsFilePath,  
					COALESCE(xfr.IsEdited, 0) AS IsRowEdited, 
					pl.DivisionName, 
					pl.DistrictName,
					(
						SELECT TOP 1 mdp.ColumnValue 
						FROM masterdatarecord_Approved mdp 
						WHERE mdp.XFormRecordId = xfr.id 
							AND mdp.FormId = xfr.FormId 
							AND mdp.UserID = xfr.UserID 
							AND mdp.CompanyId = xfr.CompanyId 
							AND mdp.PSU = xfr.PSU 
							AND mdp.SampleHHNo = xfr.SampleHHNo 
							AND mdp.ColumnName = N'surveyEndDate'
					) AS StartTime,
					(
						SELECT TOP 1 mdp.ColumnValue 
						FROM masterdatarecord_Approved mdp 
						WHERE mdp.XFormRecordId = xfr.id 
							AND mdp.FormId = xfr.FormId 
							AND mdp.UserID = xfr.UserID 
							AND mdp.CompanyId = xfr.CompanyId 
							AND mdp.PSU = xfr.PSU 
							AND mdp.SampleHHNo = xfr.SampleHHNo 
							AND mdp.ColumnName = N'surveyStartDate'
					) AS EndTime 
			FROM xformrecord xfr 
				JOIN userinfo ui ON xfr.UserID = ui.id 
				JOIN PSUList pl ON pl.PSUUserID = ui.id AND xfr.PSU = pl.PSU ";
	if (strpos($LoggedUserName, 'cs') !== false) {
		$qry .= " JOIN assignsupervisor a ON a.UserID = ui.id AND a.SupervisorID = $LoggedUserID ";
	}
	if (strpos($LoggedUserName, 'val') !== false) {
		if (strpos($LoggedUserName, 'cval') === false) {
			$qry .= " JOIN assignsupervisor a ON a.UserID = ui.id AND a.ValidatorID = $LoggedUserID ";
		}
	}
	$qry .= "WHERE xfr.IsApproved = 1  
				AND (xfr.IsChecked = 0 OR xfr.IsChecked IS NULL)
				AND xfr.FormId = $DataFromID 
				AND xfr.CompanyId = $DataCompanyID";

	if (!empty($DataStartDate) && !empty($DataEndDate)) {
		$qry .= " AND (xfr.EntryDate BETWEEN '$DataStartDate' AND '$DataEndDate')";
	}
	if (!empty($DataUserID)) {
		$qry .= " AND xfr.UserID = $DataUserID";
	}
	
	$DivisionCode = xss_clean($_REQUEST['DivisionCode']);
	$DistrictCode = xss_clean($_REQUEST['DistrictCode']);
	$UpazilaCode = xss_clean($_REQUEST['UpazilaCode']);
	$UnionWardCode = xss_clean($_REQUEST['UnionWardCode']);
	$MauzaCode = xss_clean($_REQUEST['MauzaCode']);
	$VillageCode = xss_clean($_REQUEST['VillageCode']);
	
	if (!empty($DivisionCode)) {
		$qry .= " AND ( pl.DivisionCode = '" . $DivisionCode . "') ";
	}
	if (!empty($DistrictCode)) {
		$qry .= " AND ( pl.DistrictCode = '" . $DistrictCode . "') ";
	}
	if (!empty($UpazilaCode)) {
		$qry .= " AND ( pl.UpazilaCode = '" . $UpazilaCode . "') ";
	}
	if (!empty($UnionWardCode)) {
		$qry .= " AND ( pl.UnionWardCode = '" . $UnionWardCode . "') ";
	}
	if (!empty($MauzaCode)) {
		$qry .= " AND ( pl.MauzaCode = '" . $MauzaCode . "') ";
	}
	if (!empty($VillageCode)) {
		$qry .= " AND ( pl.VillageCode = '" . $VillageCode . "') ";
	}

    if (!empty($request['search']['value'])) {
        $qry .= " AND (xfr.id like'" . $request['search']['value'] . "%'";
        $qry .= " OR ui.id like'%" . $request['search']['value'] . "%'";
        $qry .= " OR ui.UserName like'%" . $request['search']['value'] . "%'";
        $qry .= " OR ui.FullName like'%" . $request['search']['value'] . "%'";
        $qry .= " OR ui.MobileNumber like'%" . $request['search']['value'] . "%'";
        $qry .= " OR xfr.DataName like'%" . $request['search']['value'] . "%'";
        $qry .= " OR xfr.SampleHHNo like'%" . $request['search']['value'] . "%'";
        $qry .= " OR xfr.DeviceID like'%" . $request['search']['value'] . "%'";
        $qry .= " OR xfr.EntryDate like'%" . $request['search']['value'] . "%'";
		$qry .= " OR pl.DivisionName like'%" . $request['search']['value'] . "%'";
		$qry .= " OR pl.DistrictName like'%" . $request['search']['value'] . "%')";
    }

    $rs = db_query($qry, $cn);
    $TotalData = db_num_rows($rs);
    $totalFilter = $TotalData;

    if ($col[$request['order'][0]['column']]=="SampleHHNo") {
		$col[$request['order'][0]['column']] = "CAST(".$col[$request['order'][0]['column']]." AS INT)";
	}

    if ($request['length'] < 0) {
        $qry .= " ORDER BY " . $col[$request['order'][0]['column']] . " " . $request['order'][0]['dir'];
    } else {
        $qry .= " ORDER BY " . $col[$request['order'][0]['column']] . " " . $request['order'][0]['dir'] . " OFFSET " . $request['start'] . " ROWS FETCH NEXT " . $request['length'] . " ROWS ONLY";
    }

    $resQry = $app->getDBConnection()->fetchAll($qry);

    $data = array();

    foreach ($resQry as $row) {
        $RecordID = $row->id;
        $HhNo = $row->SampleHHNo;
        $PSU = $row->PSU;

        $UserID = $row->userid;
        $UserName = $row->UserName;
        $UserFullName = $row->FullName;
        $UserData = "$UserFullName ($UserName/$UserID)";

        $UserMobileNo = $row->MobileNumber;
        $UserMobileNo = whatsAppLink($UserMobileNo);

        $DataName = $row->DataName;
        $XFormsFilePath = $row->XFormsFilePath;
        $DeviceID = $row->DeviceID;

        $EntryDate = '';
        if (!empty($row->EntryDate)) {
            $EntryDate = date('d-m-Y H:i:s', strtotime($row->EntryDate));
        }

        $IsApproved = $row->IsApproved;

        $DataStatus = GetDataStatus($IsApproved);
		
		$DivisionName = $row->DivisionName;
		$DistrictName = $row->DistrictName;

        $IsEdited = $row->IsRowEdited;
		
		$Duration = 'N/A';
		
		if ($row->StartTime != NULL) {
			$start = strtotime($row->StartTime);
			$end = strtotime($row->EndTime);
			try {
				$start_date = new DateTime($row->StartTime);
			} catch (Exception $e) {
			}
			try {
				$since_start = $start_date->diff(new DateTime($row->EndTime));
			} catch (Exception $e) {
			}

			$Duration = '';
			if ($since_start->d) {
				$Duration = $since_start->d . ' Days ';
			}elseif ($since_start->h) {
				$Duration = $since_start->h . ' hours ';
			}elseif ($since_start->i) {
				$Duration = $since_start->i . ' minutes ';
			}elseif ($since_start->s) {
				$Duration = $since_start->s . ' seconds ';
			}else {
				$Duration = '0 seconds ';
			}
		}
		
		$SubData = array();

        $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">

                    <button title=\"$btnTitleView\" type=\"button\" class=\"simple-ajax-modal btn btn-outline-dark\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#viewDataModalForViewOnly\" onclick=\"ShowDataDetailForViewOnly('$DataFromID','$RecordID', '$IsApproved', '$PSU', '$LoggedUserID', '$UserID', '$XFormsFilePath')\" onmouseover=\"this.style.backgroundColor='#e0e0e0'; this.querySelector('img').style.filter='grayscale(0%)';\" onmouseout=\"this.style.backgroundColor='transparent'; this.querySelector('img').style.filter='grayscale(100%)';\"><img src=\"../../img/view-files.png\" alt=\"View\" style=\"width:16px; height:16px; filter: grayscale(100%); transition: filter 0.3s ease;\"></button>

                    <button title=\"$btnTitleView\" type=\"button\" class=\"simple-ajax-modal btn btn-outline-primary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#viewDataModal\" 
                    onclick=\"ShowDataDetail('$DataFromID', '$RecordID', '$IsApproved', '$PSU', '$LoggedUserID', '$UserID', '$XFormsFilePath')\"><i class=\"fas fa-eye\"></i></button>
                    
                    <button title=\"$btnTitleNotice\" type=\"button\" class=\"btn btn-outline-secondary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#sendNoticeModal$RecordID\"><i class=\"fas fa-bell\"></i></button>
                </div>
                <script type=\"text/javascript\">
                    function ShowDataDetail(dataFromID, recordID, isAproved, psu, loggedUserID, agentID, XFormsFilePath, data) {
                            $.ajax({
                                url: 'ViewData/ajax-data/data-detail-view.php',
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
                    function ShowDataDetailForViewOnly(dataFromID,recordID, isAproved, psu, loggedUserID, agentID, XFormsFilePath, data) {
                        $.ajax({
                            url: 'ViewData/ajax-data/data-detail-view-approved-data-for-view-only.php',
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
                                $('#dataViewDivForViewOnly').html(response);
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
                </div>
                
                <!-- View Data Modal For View Only-->
                <div class=\"modal fade bd-example-modal-xl\" id=\"viewDataModalForViewOnly\" tabindex=\"-1\" aria-labelledby=\"editDataModalLabelForViewOnly\" aria-hidden=\"true\">
                  <div class=\"modal-dialog modal-xl\">
                    <div id=\"dataViewDivForViewOnly\" class=\"modal-content\">
                      
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
                                <input type=\"text\" class=\"form-control\" name=\"UserName\" id=\"UserName$RecordID\" value=\"$UserData\" readonly>
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
		
		$SubData[] = $RecordID;
        $SubData[] = $HhNo;
        $SubData[] = $PSU;
		$SubData[] = $DivisionName;
		$SubData[] = $DistrictName;
        $SubData[] = $UserData;
        $SubData[] = $UserMobileNo;
		$SubData[] = $DataName;
        $SubData[] = $EntryDate;
        $SubData[] = $DataStatus;
		$SubData[] = $Duration;
        $SubData[] = $DeviceID;
		$SubData[] = $IsEdited;

        $data[] = $SubData;
    }

    $json_data = array(
        "draw" => intval($request['draw']),
        "recordsTotal" => $TotalData,
        "recordsFiltered" => $totalFilter,
        "data" => $data
    );

    echo json_encode($json_data);
}

