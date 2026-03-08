<?php
error_reporting(0);
header('Content-Type: application/json');

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

$param = $_REQUEST['par'] ?? '';
$loggedUserCompanyID = $_REQUEST['company'] ?? '';
$loggedUserId = $_REQUEST['loggedInUser'] ?? '';
$UserID = $_REQUEST['ui'] ?? '';

$loggedUserName = '';
if (!empty($loggedUserId)) {
    $loggedUserName = getName('UserName', 'userinfo', $loggedUserId);
}

// ✅ Fixed logic for "All Users" and specific users
if ($param === '1') {
    // All users case
    if ($loggedUserName === 'admin' || $loggedUserName === 'sasadmin' || $loggedUserName === 'cpsadmin') {
        // Superusers see all records
        $qry = "SELECT * FROM assignformtoagent";
        $params = [];
    } else {
        // Regular users see all forms within their company
        $qry = "SELECT * FROM assignformtoagent WHERE CompanyID = ?";
        $params = [$loggedUserCompanyID];
    }
} else {
    // Specific user selected
    $qry = "SELECT * FROM assignformtoagent WHERE UserID = ?";
    $params = [$UserID];
}

$rs = $app->getDBConnection()->fetchAll($qry, ...$params);

$data = [];
$il = 1;
foreach ($rs as $row) {
    $ProvisionEndDate = !empty($row->ProvisionEndDate) ? date('d-m-Y', strtotime($row->ProvisionEndDate)) : '';
    $CreatedDate = !empty($row->CreatedDate) ? date('d-m-Y', strtotime($row->CreatedDate)) : '';

    $FormName = getName('FormName', 'datacollectionform', $row->FormID);
    $FormGroupName = getName('FormGroupName', 'datacollectionformgroup', $row->FormGroupId);
    $CompanyName = getName('CompanyName', 'dataownercompany', $row->CompanyID);
    $UserName = getName('UserName', 'userinfo', $row->UserID);
    $UserFullName = getName('FullName', 'userinfo', $row->UserID);

    // $statusAction = "<select class='form-select' style='font-size: 13px; padding: 2px 6px; height: auto;' onchange='updateStatus($row->id, this.value)'>
    //     <option value='Active' " . ($row->Status == 'Active' ? 'selected' : '') . ">Active</option>
    //     <option value='InActive' " . ($row->Status == 'InActive' ? 'selected' : '') . ">InActive</option>
    // </select>";






    $statusAction = "<select class='form-select' 
                        style='font-size:13px; line-height:normal; padding:2px 6px; height:28px; vertical-align:middle; width:auto;' 
                        onchange='updateStatus($row->id, this.value)'>
                        <option value=\"Active\" " . ($row->Status == 'Active' ? 'selected' : '') . ">Active</option>
                        <option value=\"InActive\" " . ($row->Status == 'InActive' ? 'selected' : '') . ">InActive</option>
                    </select>";








    $actions = "<div style='display:flex;justify-content:center;'>
        <button class='btn btn-outline-danger' onclick='DeleteItem($row->id);'><i class='far fa-trash-alt'></i></button>
    </div>";

    $data[] = [
        $il++,
        $CompanyName,
        "$UserFullName ($UserName)",
        $FormName,
        $FormGroupName,
        $statusAction,
        $ProvisionEndDate,
        $CreatedDate,
        $row->CreatedBy,
        $actions
    ];
}

echo json_encode(['data' => $data]);
exit;
