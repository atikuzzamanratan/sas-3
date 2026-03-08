<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

if ($_REQUEST['ci'] != '') {
    $LoggedCompanyId = $app->cleanInput($_REQUEST['ci']);
}

if ($_REQUEST['fid'] != '') {
    $FormID = $app->cleanInput($_REQUEST['fid']);
}

if ($_REQUEST['lui'] != '') {
    $LoggedUserID = $app->cleanInput($_REQUEST['lui']);
}

$LoggedUserNamePSU = getValue('userinfo', 'UserName', "id = $LoggedUserID");

$qrySupervisor = "SELECT id, EditPermission, DeletePermission, ApprovePermission FROM assignsupervisor WHERE SupervisorID = ?";
$resQrySupervisor = $app->getDBConnection()->fetch($qrySupervisor, $LoggedUserID);
$SuperID = $resQrySupervisor->id;

if ($FormID == $formIdMainData) {
    $fieldForRecord = "NumberOfRecordForMainSurvey AS Target";
} elseif ($FormID == $formIdSamplingData) {
    $fieldForRecord = "NumberOfRecord AS Target";
}

if ($LoggedUserNamePSU == 'admin') {
    $qryGetData = "SELECT id, PSUUserID, PSU, $fieldForRecord FROM PSUList WHERE CompanyID = ? ORDER BY PSUUserID";
} else if (strpos($LoggedUserNamePSU, 'admin') !== false) {
    $qryGetData = "SELECT id, PSUUserID, PSU, $fieldForRecord FROM PSUList WHERE CompanyID = ? ORDER BY PSUUserID";
} else if ($SuperID) {
    if ($FormID == $formIdMainData) {
        $qryGetData = "SELECT pl.id, pl.PSUUserID, pl.PSU, pl.NumberOfRecordForMainSurvey Target FROM PSUList pl
    JOIN assignsupervisor asp ON pl.PSUUserID = asp.UserID WHERE asp.SupervisorID = $LoggedUserID AND asp.UserID > 0 AND pl.CompanyID = ? ORDER BY pl.PSUUserID";
    } else {
        $qryGetData = "SELECT pl.id, pl.PSUUserID, pl.PSU, pl.NumberOfRecord Target FROM PSUList pl
    JOIN assignsupervisor asp ON pl.PSUUserID = asp.UserID WHERE asp.SupervisorID = $LoggedUserID AND asp.UserID > 0 AND pl.CompanyID = ? ORDER BY pl.PSUUserID";
    }

} else {
    $qryGetData = "SELECT id, PSUUserID, PSU, $fieldForRecord FROM PSUList WHERE CompanyID = ? ORDER BY PSUUserID";
}

//echo $qryGetData;

//$qryGetData = "SELECT id, PSUUserID, PSU, $fieldForRecord FROM PSUList WHERE CompanyID = ? ORDER BY PSUUserID";

$rsQryGetData = $app->getDBConnection()->fetchAll($qryGetData, $LoggedCompanyId);

$data = array();
$il = 1;

foreach ($rsQryGetData as $row) {
    $recordID = $row->id;
    $UserId = $row->PSUUserID;
    $UserName = getValue('userinfo', 'UserName', "id = '$UserId'");
    $FullName = getValue('userinfo', 'FullName', "id = '$UserId'");

    if ($UserId == null) {
        $UserData = "Not Assigned";
    } else {
        $UserData = "$FullName ($UserName/$UserId)";
    }

    $PSU = $row->PSU;
    $Target = $row->Target;
    $Collected = getValue('xformrecord', 'COUNT(PSU)', "UserID = '$UserId' AND PSU = '$PSU' AND FormId = '$FormID'");
    $Remaining = $Target - $Collected;
    $CollectionRatio = Ratio($Collected, $Target);

    $SubData = array();

    $SubData[] = $il;
    $SubData[] = $UserData;
    $SubData[] = $PSU;
    $SubData[] = $Target;
    $SubData[] = $Collected;
    $SubData[] = $Remaining;
    $SubData[] = $CollectionRatio;

    $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">";

    if ((strpos($LoggedUserName, 'admin') !== false) || (strpos($LoggedUserName, $supervisorNamePrefix) !== false)) {
        $actions .= "<button title=\"$btnTitleEdit\" type=\"button\" class=\"btn btn-outline-primary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#editDataModal$recordID\"><i class=\"fas fa-pencil-alt\"></i></button>";
    }

    if (strpos($LoggedUserName, 'admin') !== false) {
        $actions .= "<button title=\"$btnTitleDelete\" type=\"button\" class=\"btn btn-outline-danger\" style=\"display: inline-block\" onclick=\"DeleteItem('$recordID');\"><i class=\"far fa-trash-alt\"></i></button>";
    }

    $actions .= "</div>
                 <!-- Modal Edit-->
                <div class=\"modal fade\" id=\"editDataModal$recordID\" tabindex=\"-1\" aria-labelledby=\"editDataModalLabel\" aria-hidden=\"true\">
                  <div class=\"modal-dialog\">
                    <div class=\"modal-content\">
                      <div class=\"modal-header\">
                      <h5 class=\"modal-title\" id=\"editDataModalLabel\">Edit Form</h5>
                        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
                      </div>
                      <div class=\"modal-body\">
                        <form id=\"editForm\" method=\"POST\" action=\"\">
                            <div class=\"form-group\">
                                <label for=\"Survey\">Survey</label>
                                <input type=\"text\" class=\"form-control\" name=\"Survey\" id=\"Survey$recordID\" value=\"$FormType\" readonly>
                            </div>
                            <div class=\"form-group\">
                                <label for=\"User\">User</label>
                                <input type=\"text\" class=\"form-control\" name=\"User\" id=\"User$recordID\" value=\"$UserData\" readonly>
                            </div>
                            <div class=\"form-group\">
                                <label for=\"Psu\">PSU</label>
                                <input type=\"text\" class=\"form-control\" name=\"Psu\" id=\"Psu$recordID\" value=\"$PSU\" readonly>
                            </div>
                            <div class=\"form-group\">
                                <label for=\"Target\">Number of Data</label>
                                <input type=\"number\" class=\"form-control\" name=\"Target\" id=\"Target$recordID\" value=\"$Target\">
                            </div>
                            
                            <div class=\"modal-footer\">
                                <button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">Close</button>
                                <button type=\"button\" class=\"btn btn-primary\" name=\"Save\" id=\"Save\" value=\"Update\" 
                                onclick= \"
                                var pTarget = document.getElementById('Target$recordID').value;

                                EditItem('$recordID', '$FormType', pTarget);
                                \">
                                Save changes
                                </button>
                             </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>";

    $SubData[] = $actions;

    $il++;

    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

