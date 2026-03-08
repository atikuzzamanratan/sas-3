<?php
error_reporting(1);


function safeGetValue($table, $field, $cond)
{
    try {
        if (empty(trim($cond)) || preg_match('/=\s*$/', $cond)) {
            return "";
        }

        $conn = PDOConnectDB();
        $SQL = "SELECT $field AS tt FROM $table WHERE $cond";
        $stmt = $conn->query($SQL);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['tt'] ?? "";
    } catch (Exception $e) {
        return "";
    }
}


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


$qryGetData = "SELECT DISTINCT assup.id, 
	assup.DivCoordinatorID, 
	assup.DistCoordinatorID, 
	ui.UserName, 
	ui.FullName, 
	assup.SupervisorID, 
	assup.UserID, 
	pl.DivisionName, 
	pl.DistrictName 
FROM assignsupervisor assup
	INNER JOIN userinfo ui ON assup.DivCoordinatorID = ui.id  
	LEFT JOIN PSUList pl ON pl.PSUUserID = assup.UserID 
	LEFT JOIN xformrecord xfr ON xfr.PSU = pl.PSU AND xfr.UserID = assup.UserID
WHERE assup.DivCoordinatorID <> '' AND assup.CompanyID = ?";
$rsQryGetData = $app->getDBConnection()->fetchAll($qryGetData, $SelectedCompanyId);

$data = array();
$il = 1;

foreach ($rsQryGetData as $row) {
    $id = $row->id;
    $DivCoordinatorID = $row->DivCoordinatorID;
    $DivCoUserName = $row->UserName;
    $DivCoFullName = $row->FullName;
    $DivCoData = "$DivCoFullName ($DivCoUserName/$DivCoordinatorID)";
	
	$DistCoordinatorID = $row->DistCoordinatorID;
    //$DistCoUserName = $row->DistUserName;
    //$DistCoFullName = $row->DistFullName;
    //$DistCoData = "$DistCoFullName ($DistCoUserName/$DistCoordinatorID)";
	$DistCoordinatorUserName = safeGetValue('userinfo', 'UserName', "id = $DistCoordinatorID");
    $DistCoordinatorFullName = safeGetValue('userinfo', 'FullName', "id = $DistCoordinatorID");
	$DistCoordinatorData = "$DistCoordinatorFullName ($DistCoordinatorUserName/$DistCoordinatorID)";

    $SupervisorID = $row->SupervisorID;
    $SupervisorUserName = safeGetValue('userinfo', 'UserName', "id = $SupervisorID");
    $SupervisorFullName = safeGetValue('userinfo', 'FullName', "id = $SupervisorID");
	$SupervisorMobile = whatsAppLink(safeGetValue('userinfo', 'MobileNumber', "id = $SupervisorID"));
    $SupervisorData = "$SupervisorFullName ($SupervisorUserName/$SupervisorID)";

    $UserID = $row->UserID;
    $UserUserName = safeGetValue('userinfo', 'UserName', "id = $UserID");
    $UserFullName = safeGetValue('userinfo', 'FullName', "id = $UserID");
    $UserData = "$UserFullName ($UserUserName/$UserID)";
	
	$DistrictName = $row->DistrictName;
	$DivisionName = $row->DivisionName;

    $SubData = array();

    $SubData[] = $il;
    $SubData[] = $DivCoData;
	$SubData[] = $DistCoordinatorData;
	$SubData[] = $SupervisorData;
	$SubData[] = $SupervisorMobile;
    $SubData[] = $UserData;
    $SubData[] = $DivisionName;
	$SubData[] = $DistrictName;

    $actions = "";

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

