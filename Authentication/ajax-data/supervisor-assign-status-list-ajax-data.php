<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

if ($_REQUEST['par'] != '') {
    $param = $app->cleanInput($_REQUEST['par']);
}

if ($_REQUEST['ci'] != '') {
    $SelectedCompanyId = $app->cleanInput($_REQUEST['ci']);
}

if ($_REQUEST['si'] != '') {
    $SelectedSupervisorID = $app->cleanInput($_REQUEST['si']);
}

if ($_REQUEST['lun'] != '') {
    $loggedUserName = $app->cleanInput($_REQUEST['lun']);
}

if ($_REQUEST['editPer'] != '') {
    $UserEditPermission = $app->cleanInput($_REQUEST['editPer']);
}

if ($_REQUEST['delPer'] != '') {
    $UserDeletePermission = $app->cleanInput($_REQUEST['delPer']);
}

if ($param == "1") {
    $qry = "SELECT agns.id, agns.CompanyID, agns.DeletePermission, agns.EditPermission, agns.ApprovePermission, agns.DataEntryDate, 
    doc.CompanyName, agns.SupervisorID, agns.UserID, ui.FullName, ui.UserName, ui.MobileNumber FROM assignsupervisor agns
    JOIN dataownercompany doc ON agns.CompanyID = doc.id 
    JOIN userinfo ui on agns.UserID = ui.id";
    $resQry = $app->getDBConnection()->fetchAll($qry);
} else {
    $qry = "SELECT agns.id, agns.CompanyID, agns.DeletePermission, agns.EditPermission, agns.ApprovePermission, agns.DataEntryDate, 
    doc.CompanyName, agns.SupervisorID, agns.UserID, ui.FullName, ui.UserName, ui.MobileNumber FROM assignsupervisor agns
    JOIN dataownercompany doc ON agns.CompanyID = doc.id 
    JOIN userinfo ui on agns.UserID = ui.id
    WHERE agns.SupervisorID = ?";
    $resQry = $app->getDBConnection()->fetchAll($qry, $SelectedSupervisorID);
}

$data = array();
$il = 1;

foreach ($resQry as $row) {
    $recordID = $row->id;
    $CompanyName = $row->CompanyName;

    $SupervisorID = $row->SupervisorID;
    $SupervisorUserName = getValue('userinfo', 'UserName', "id = $SupervisorID");
    $SupervisorFullName = getValue('userinfo', 'FullName', "id = $SupervisorID");
    $SupervisorData = $SupervisorFullName . ' (' . $SupervisorUserName . '/' . $SupervisorID . ')';

    $UserID = $row->UserID;
    $UserName = $row->UserName;
    $UserFullName = $row->FullName;
    $UserData = $UserFullName . ' (' . $UserName . '/' . $UserID . ')';

    if($row->ApprovePermission){
        $ApprovePermission = 'YES';
        $isPerApprove = 'checked';
        $isPerApproveVal = '1';
    }else{
        $ApprovePermission = 'NO';
        $isPerApprove = '';
        $isPerApproveVal = '0';
    }

    if($row->EditPermission){
        $EditPermission = 'YES';
        $isPerEdit = 'checked';
        $isPerEditVal = '1';
    }else{
        $EditPermission = 'NO';
        $isPerEdit = '';
        $isPerEditVal = '0';
    }

    if($row->DeletePermission){
        $DeletePermission = 'YES';
        $isPerDelete = 'checked';
        $isPerDeleteVal = '1';
    }else{
        $DeletePermission = 'NO';
        $isPerDelete = '';
        $isPerDeleteVal = '0';
    }

    $CreateDate = date_format($row->DataEntryDate, "d-m-Y");

    $SubData = array();

    $SubData[] = $il;
    $SubData[] = $SupervisorData;
    $SubData[] = $UserData;
    $SubData[] = $EditPermission;
    $SubData[] = $DeletePermission;
    $SubData[] = $ApprovePermission;
    $SubData[] = $CreateDate;

    $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">";

    if ((strpos($loggedUserName, 'admin') !== false) || $UserEditPermission == '1') {
        $actions .= "<button title=\"$btnTitleEdit\" type=\"button\" class=\"btn btn-outline-primary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#editDataModal$recordID\"><i class=\"fas fa-pencil-alt\"></i></button>";
    }

    if ((strpos($loggedUserName, 'admin') !== false) || $UserDeletePermission == '1') {
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
                                <label for=\"CompanyName\">Project</label>
                                <input type=\"text\" class=\"form-control\" name=\"CompanyName\" id=\"CompanyName\" value=\"$CompanyName\" readonly>
                            </div>
                            <div class=\"form-group\">
                                <label for=\"Supervisor\">Supervisor</label>
                                <input type=\"text\" class=\"form-control\" name=\"Supervisor\" id=\"Supervisor\" value=\"$SupervisorData\" readonly>
                            </div>
                            <div class=\"form-group\">
                                <label for=\"User\">User</label>
                                <input type=\"text\" class=\"form-control\" name=\"User\" id=\"User\" value=\"$UserData\" readonly>
                            </div>
                            
                            <div class=\"form-group row pb-3\">
                                <label class=\"col-lg-3 control-label text-sm-end pt-2\">Permissions</label>
                                <div class=\"col-lg-9\">
                                    <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"
                                           class=\"table table-striped table-bordered datatables\"
                                           id=\"example\">
                                        <tr>
                                            <td>Approve</td>
                                            <td>
                                                <input class=\"checkbox-custom checkbox-default\" name=\"approve\" id=\"approve$recordID\" type=\"checkbox\"
                                                     value='$isPerApproveVal' $isPerApprove onChange= \"$(this).val(this.checked? '1': '0');\" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Edit</td>
                                            <td>
                                                <input class=\"checkbox-custom checkbox-default\" name=\"edit\" id=\"edit$recordID\" type=\"checkbox\"
                                                      value='$isPerEditVal' $isPerEdit  onChange= \"$(this).val(this.checked? '1': '0');\" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Delete</td>
                                            <td>
                                                <input class=\"checkbox-custom checkbox-default\" name=\"delete\" id=\"delete$recordID\" type=\"checkbox\"
                                                      value='$isPerDeleteVal' $isPerDelete onChange= \"$(this).val(this.checked? '1': '0');\" />
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div class=\"modal-footer\">
                                <button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">Close</button>
                                <button type=\"button\" class=\"btn btn-primary\" name=\"Save\" id=\"Save\" value=\"Update\" 
                                onclick= \"
                                var pApprove = document.getElementById('approve$recordID').value;
                                var pEdit = document.getElementById('edit$recordID').value;
                                var pDelete = document.getElementById('delete$recordID').value;

                                EditItem('$recordID', pApprove, pEdit, pDelete);
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

