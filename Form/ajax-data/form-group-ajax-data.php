<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

if ($_REQUEST['par'] != '') {
    $param = $app->cleanInput($_REQUEST['par']);
}

if ($_REQUEST['fgi'] != '') {
    $FormGroupID = $app->cleanInput($_REQUEST['fgi']);
}

if ($param === '1') {
    $qryFromGroupDetail = "SELECT dcfg.id, dcfg.FormGroupName, dcfg.FormGroupDesc, doc.CompanyName, dcfg.Status FROM  datacollectionformgroup dcfg 
        INNER JOIN dataownercompany doc ON dcfg.CompanyID = doc.id";
} else {
    $qryFromGroupDetail = "SELECT dcfg.id, dcfg.FormGroupName, dcfg.FormGroupDesc, doc.CompanyName, dcfg.Status FROM  datacollectionformgroup dcfg
        INNER JOIN dataownercompany doc ON dcfg.CompanyID = doc.id WHERE dcfg.id = '$FormGroupID'";
}

$rsQryFromGroupDetail = $app->getDBConnection()->fetchAll($qryFromGroupDetail);

$data = array();
$il = 1;

foreach ($rsQryFromGroupDetail as $row) {
    $GroupId = $row->id;
    $GroupName = $row->FormGroupName;
    $Description = $row->FormGroupDesc;
    $Company = $row->CompanyName;
    $Status = $row->Status;

    $SubData = array();

    $SubData[] = $il;
    $SubData[] = $GroupName;
    $SubData[] = $Description;
    $SubData[] = $Company;
    $SubData[] = $Status;

    $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">
                    <button title=\"$btnTitleEdit\" type=\"button\" class=\"btn btn-outline-primary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#exampleModal$GroupId\"><i class=\"fas fa-pencil-alt\"></i></button>
                    <button title=\"$btnTitleDelete\" type=\"button\" class=\"btn btn-outline-danger\" style=\"display: inline-block\" onclick=\"DeleteItem('$GroupId');\"><i class=\"far fa-trash-alt\"></i></button>
                </div>   
                 <!-- Modal -->
                <div class=\"modal fade\" id=\"exampleModal$GroupId\" tabindex=\"-1\" aria-labelledby=\"exampleModalLabel\" aria-hidden=\"true\">
                  <div class=\"modal-dialog\">
                    <div class=\"modal-content\">
                      <div class=\"modal-header\">
                      <h5 class=\"modal-title\" id=\"exampleModalLabel\">Edit Form Group</h5>
                        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
                      </div>
                      <div class=\"modal-body\">
                        <form id=\"editForm\" method=\"POST\" action=\"\">
                            <div class=\"form-group\">
                                <label for=\"FormGroupName\">Name</label>
                                <input type=\"text\" class=\"form-control\" name=\"FormGroupName\" id=\"FormGroupName$GroupId\" value=\"$GroupName\">
                            </div>
                            <div class=\"form-group\">
                                <label for=\"FormGroupDesc\">Description</label>
                                <input type=\"text\" class=\"form-control\" name=\"FormGroupDesc\" id=\"FormGroupDesc$GroupId\" value=\"$Description\">
                            </div>
                            <div class=\"form-group\">
                                <label for=\"Status\">Status</label>
                                <select name=\"Status\" id=\"Status$GroupId\" class=\"form-control\">
                                    <option selected>$Status</option>
                                    <option value=\"Active\">Active</option>
                                    <option value=\"InActive\">InActive</option>
                                </select>
                            </div>
                            
                            <div class=\"modal-footer\">
                                <button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">Close</button>
                                <button type=\"button\" class=\"btn btn-primary\" name=\"Save\" id=\"Save\" value=\"Update\" 
                                onclick= \"
                                var fgName = document.getElementById('FormGroupName$GroupId').value;
                                var fgDesc = document.getElementById('FormGroupDesc$GroupId').value;
                                var fgStatus = document.getElementById('Status$GroupId').value;

                                EditItem('$GroupId', fgName, fgDesc, fgStatus);
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

