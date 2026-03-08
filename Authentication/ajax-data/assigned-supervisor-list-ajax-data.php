<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

if ($_REQUEST['cid'] != '') {
    $LoggedCompanyId = $app->cleanInput($_REQUEST['cid']);
}

if ($_REQUEST['uname'] != '') {
    $LoggedUserName = $app->cleanInput($_REQUEST['uname']);
}

if ($LoggedUserName == 'admin') {
    $qryGetData = "SELECT ur.UserId, ui.UserName, ur.RoleId FROM userrole ur LEFT OUTER JOIN userinfo ui ON ur.UserId = ui.id
    WHERE ui.UserName <> ? ORDER BY ur.RoleId ";
    $rsQryGetData = $app->getDBConnection()->fetchAll($qryGetData, $LoggedUserName);
} else {
    $qryGetData = "select ur.UserId, ui.UserName, ur.RoleId FROM userrole ur LEFT OUTER JOIN userinfo ui ON ur.UserId = ui.id 
    WHERE ui.UserName <> ? AND ui.CompanyID = ? ORDER BY ur.RoleId";
    $rsQryGetData = $app->getDBConnection()->fetchAll($qryGetData, $LoggedUserName, $LoggedCompanyId);
}

$data = array();
$il = 1;

foreach ($rsQryGetData as $row) {
    $UserId = $row->UserId;
    $UserName = $row->UserName;
    $UserData = $UserName . ' (' . $UserId . ')';
    $RoleId = $row->RoleId;
    $RoleName = getValue('roleinfo', 'RoleName', "RoleId = '$RoleId'");

    $SubData = array();

    $SubData[] = $il;
    $SubData[] = $UserData;
    $SubData[] = $RoleId;
    $SubData[] = $RoleName;

    $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">
                    <button title=\"$btnTitleDelete\" type=\"button\" class=\"btn btn-outline-danger\" style=\"display: inline-block\" onclick=\"DeleteItem('$UserId', '$RoleId');\"><i class=\"far fa-trash-alt\"></i></button>
                </div>
                ";

    $SubData[] = $actions;

    $il++;

    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

