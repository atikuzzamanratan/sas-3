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


$qryGetData = "SELECT assup.id, assup.DistCoordinatorID, ui.UserName, ui.FullName, assup.SupervisorID, assup.UserID 
FROM assignsupervisor assup
INNER JOIN userinfo ui ON assup.DistCoordinatorID = ui.id
WHERE assup.DistCoordinatorID <> '' AND assup.CompanyID = ?";
$rsQryGetData = $app->getDBConnection()->fetchAll($qryGetData, $SelectedCompanyId);

$data = array();
$il = 1;

foreach ($rsQryGetData as $row) {
    $id = $row->id;
    $DistCoordinatorID = $row->DistCoordinatorID;
    $DistCoUserName = $row->UserName;
    $DistCoFullName = $row->FullName;
    $DistCoData = "$DistCoFullName ($DistCoUserName/$DistCoordinatorID)";

    $SupervisorID = $row->SupervisorID;
    $SupervisorUserName = getValue('userinfo', 'UserName', "id = $SupervisorID");
    $SupervisorFullName = getValue('userinfo', 'FullName', "id = $SupervisorID");
    $SupervisorData = "$SupervisorFullName ($SupervisorUserName/$SupervisorID)";

    $UserID = $row->UserID;
    $UserUserName = getValue('userinfo', 'UserName', "id = $UserID");
    $UserFullName = getValue('userinfo', 'FullName', "id = $UserID");
    $UserData = "$UserFullName ($UserUserName/$UserID)";

    $SubData = array();

    $SubData[] = $il;
    $SubData[] = $DistCoData;
    $SubData[] = $SupervisorData;
    $SubData[] = $UserData;
    if (strpos($loggedUserName, 'admin') !== false) {
        $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">
                    <button title=\"$btnTitleDelete\" type=\"button\" class=\"btn btn-outline-danger\" style=\"display: inline-block\" onclick=\"DeleteItem('$id');\"><i class=\"far fa-trash-alt\"></i></button>
                </div>
                ";
    }
    $SubData[] = $actions;

    $il++;

    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

