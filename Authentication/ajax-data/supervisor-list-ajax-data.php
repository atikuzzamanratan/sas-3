<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

if ($_REQUEST['ci'] != '') {
    $SelectedCompanyId = $app->cleanInput($_REQUEST['ci']);
}

if ($_REQUEST['lun'] != '') {
    $loggedUserName = $app->cleanInput($_REQUEST['lun']);
}


$qryGetData = "SELECT asup.CompanyID, asup.SupervisorID, ui.UserName, ui.FullName FROM assignsupervisor asup 
JOIN userinfo ui ON asup.SupervisorID = ui.id 
WHERE asup.CompanyID = ? 
GROUP BY asup.CompanyID, asup.SupervisorID, ui.UserName, ui.FullName";
$rsQryGetData = $app->getDBConnection()->fetchAll($qryGetData, $SelectedCompanyId);

$data = array();
$il = 1;

foreach ($rsQryGetData as $row) {
    $CompanyID = $row->CompanyID;
    $CompanyName = getValue('dataownercompany', 'CompanyName', "id = '$CompanyID'");
    $SupervisorID = $row->SupervisorID;
    $UserName = $row->UserName;
    $UserData = $UserName . ' (' . $SupervisorID . ')';
    $FullName = $row->FullName;

    $SubData = array();

    $SubData[] = $il;
    $SubData[] = $CompanyName;
    $SubData[] = $UserData;
    $SubData[] = $FullName;
    if (strpos($loggedUserName, 'admin') !== false) {
        $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">
                    <button title=\"$btnTitleDelete\" type=\"button\" class=\"btn btn-outline-danger\" style=\"display: inline-block\" onclick=\"DeleteItem('$SupervisorID');\"><i class=\"far fa-trash-alt\"></i></button>
                </div>
                ";
    }
    $SubData[] = $actions;

    $il++;

    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

