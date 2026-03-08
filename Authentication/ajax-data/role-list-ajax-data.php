<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

if ($_REQUEST['cid'] != '') {
    $adminCompanyId = $app->cleanInput($_REQUEST['cid']);
}

$qryGetData = "SELECT ri.RoleId, ri.RoleName, ri.CompanyID, doc.CompanyName FROM roleinfo ri
JOIN dataownercompany doc ON ri.CompanyID = doc.id
WHERE ri.CompanyID <> ? 
ORDER BY ri.CompanyID DESC";
$rsQryGetData = $app->getDBConnection()->fetchAll($qryGetData, $adminCompanyId);

$data = array();
$il = 1;

foreach ($rsQryGetData as $row) {
    $RoleId = $row->RoleId;
    $RoleName = $row->RoleName;
    $CompanyID = $row->CompanyID;
    $CompanyName = $row->CompanyName;

    $SubData = array();

    $SubData[] = $il;
    $SubData[] = $RoleId;
    $SubData[] = $RoleName;
    $SubData[] = $CompanyName;

    $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">
                    <button type=\"button\" class=\"btn btn-outline-primary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#editDataModal$RoleId\"><i class=\"fas fa-pencil-alt\"></i></button>
                    <button type=\"button\" class=\"btn btn-outline-danger\" style=\"display: inline-block\" onclick=\"DeleteItem('$RoleId');\"><i class=\"far fa-trash-alt\"></i></button>
                </div>
                <!-- Modal Edit-->
                <div class=\"modal fade\" id=\"editDataModal$RoleId\" tabindex=\"-1\" aria-labelledby=\"editDataModalLabel\" aria-hidden=\"true\">
                  <div class=\"modal-dialog\">
                    <div class=\"modal-content\">
                      <div class=\"modal-header\">
                      <h5 class=\"modal-title\" id=\"editDataModalLabel\">Edit Form</h5>
                        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
                      </div>
                      <div class=\"modal-body\">
                        <form id=\"editForm\" method=\"POST\" action=\"\">
                            <div class=\"form-group\">
                                <label for=\"roleID\">Role ID</label>
                                <input type=\"text\" class=\"form-control\" name=\"roleID\" id=\"roleID$RoleId\" value=\"$RoleId\" readonly>
                            </div>
                            <div class=\"form-group\">
                                <label for=\"roleName\">Description<span class=\"required\">*</span></label>
                                <input type=\"text\" class=\"form-control\" name=\"roleName\" id=\"roleName$RoleId\" value=\"$RoleName\" required>
                            </div>
                            
                            <div class=\"modal-footer\">
                                <button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">Close</button>
                                <button type=\"button\" class=\"btn btn-primary\" name=\"Save\" id=\"Save\" value=\"Update\" 
                                onclick= \"
                                var rName = document.getElementById('roleName$RoleId').value;

                                EditItem('$RoleId', rName);
                                \">
                                Save changes
                                </button>
                             </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
                ";

    $SubData[] = $actions;

    $il++;

    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

