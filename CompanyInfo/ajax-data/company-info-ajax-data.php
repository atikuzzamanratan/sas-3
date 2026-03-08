<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

if ($_REQUEST['par'] != '') {
    $param = $app->cleanInput($_REQUEST['par']);
}

if ($_REQUEST['id'] != '') {
    $CompanyID = $app->cleanInput($_REQUEST['id']);
}

if ($param === '1') {
    $qry = "SELECT id, CompanyName, ContactPersonName, Address, Phone, IsActive FROM dataownercompany ORDER BY CompanyName";
    $resQry = $app->getDBConnection()->fetchAll($qry);
} else {
    $qry = "SELECT id, CompanyName, ContactPersonName, Address, Phone, IsActive FROM dataownercompany WHERE id = ?";
    $resQry = $app->getDBConnection()->fetchAll($qry, $CompanyID);
}

$data = array();
$il = 1;

foreach ($resQry as $row) {
    $Id = $row->id;
    $CompanyName = $row->CompanyName;
    $ContactPersonName = $row->ContactPersonName;
    $Address = $row->Address;
    $Phone = $row->Phone;
    $IsActive = $row->IsActive;
    if ($IsActive == 1) {
        $Status = "Active";
    } elseif ($IsActive == 0) {
        $Status = "Inactive";
    }

    $SubData = array();

    $SubData[] = $il;
    $SubData[] = $CompanyName;
    $SubData[] = $ContactPersonName;
    $SubData[] = $Address;
    $SubData[] = $Phone;
    $SubData[] = $Status;

    $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">
                    <button type=\"button\" class=\"btn btn-outline-primary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#editDataModal$Id\"><i class=\"fas fa-pencil-alt\"></i></button>
                    <button type=\"button\" class=\"btn btn-outline-danger\" style=\"display: inline-block\" onclick=\"DeleteItem('$Id');\"><i class=\"far fa-trash-alt\"></i></button>
                </div> 
                
                 <!-- Modal Edit-->
                <div class=\"modal fade\" id=\"editDataModal$Id\" tabindex=\"-1\" aria-labelledby=\"editDataModalLabel\" aria-hidden=\"true\">
                  <div class=\"modal-dialog\">
                    <div class=\"modal-content\">
                      <div class=\"modal-header\">
                      <h5 class=\"modal-title\" id=\"editDataModalLabel\">Edit Form</h5>
                        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
                      </div>
                      <div class=\"modal-body\">
                        <form id=\"editForm\" method=\"POST\" action=\"\">
                            <div class=\"form-group\">
                                <label for=\"CompanyName\">Name</label>
                                <input type=\"text\" class=\"form-control\" name=\"CompanyName\" id=\"CompanyName$Id\" value=\"$CompanyName\" readonly>
                            </div>
                            <div class=\"form-group\">
                                <label for=\"ContactPerson\">Contact Person<span class=\"required\">*</span></label>
                                <input type=\"text\" class=\"form-control\" name=\"ContactPerson\" id=\"ContactPerson$Id\" value=\"$ContactPersonName\" required>
                            </div>
                            <div class=\"form-group\">
                                <label for=\"Address\">Address<span class=\"required\">*</span></label>
                                <input type=\"text\" class=\"form-control\" name=\"Address\" id=\"Address$Id\" value=\"$Address\" required>
                            </div>
                            <div class=\"form-group\">
                                <label for=\"Phone\">Phone<span class=\"required\">*</span></label>
                                <input type=\"text\" class=\"form-control\" name=\"Phone\" id=\"Phone$Id\" value=\"$Phone\" required>
                            </div>
                            
                            <div class=\"form-group\">
                                <label for=\"Status\">Status</label>
                                <select name=\"Status\" id=\"Status$Id\" class=\"form-control\">
                                    <option value=\"$IsActive\" selected>$Status</option>
                                    <option value=\"1\">Active</option>
                                    <option value=\"0\">In-Active</option>
                                </select>
                            </div>
                            
                            <div class=\"modal-footer\">
                                <button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">Close</button>
                                <button type=\"button\" class=\"btn btn-primary\" name=\"Save\" id=\"Save\" value=\"Update\" 
                                onclick= \"
                                var cName = document.getElementById('CompanyName$Id').value;
                                var cContact = document.getElementById('ContactPerson$Id').value;
                                var cAddress = document.getElementById('Address$Id').value;
                                var cPhone = document.getElementById('Phone$Id').value;
                                var cStatus = document.getElementById('Status$Id').value;

                                EditItem('$Id', cName, cContact, cAddress, cPhone, cStatus);
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

