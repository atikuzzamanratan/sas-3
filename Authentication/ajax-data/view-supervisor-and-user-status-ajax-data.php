<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

if ($_REQUEST['ci'] != '') {
    $CompanyID = $app->cleanInput($_REQUEST['ci']);
}

if ($_REQUEST['sid'] != '') {
    $SupervisorID = $app->cleanInput($_REQUEST['sid']);
}

if ($_REQUEST['fid'] != '') {
    $FormID = $app->cleanInput($_REQUEST['fid']);
}

if ($FormID == $formIdMainData) {
    $qry = "SELECT DISTINCT a.SupervisorID, p.PSU, p.PSUUserID, u.UserName, u.FullName, p.NumberOfRecordForMainSurvey as Target, 
    (SELECT COUNT(id) FROM xformrecord WHERE PSU = p.PSU AND FormId = ?) as Collected 
    FROM PSUList p JOIN userinfo u ON p.PSUUserID = u.id JOIN assignsupervisor a ON p.PSUUserID = a.UserID 
    WHERE a.SupervisorID = ? AND a.CompanyID = ?
    ORDER BY PSU ASC";
} elseif ($FormID == $formIdSamplingData) {
    $qry = "SELECT DISTINCT a.SupervisorID, p.PSU, p.PSUUserID, u.UserName, u.FullName, p.NumberOfRecord as Target, 
    (SELECT COUNT(id) FROM xformrecord WHERE PSU = p.PSU AND FormId = ?) as Collected 
    FROM PSUList p JOIN userinfo u ON p.PSUUserID = u.id JOIN assignsupervisor a ON p.PSUUserID = a.UserID 
    WHERE a.SupervisorID = ? AND a.CompanyID = ?
    ORDER BY PSU ASC";
}
$resQry = $app->getDBConnection()->fetchAll($qry, $FormID, $SupervisorID, $CompanyID);

$data = array();
$il = 1;

foreach ($resQry as $row) {
    $SupervisorID = $row->SupervisorID;
    $SupervisorUserName = getValue('userinfo', 'UserName', "id = $SupervisorID");
    $SupervisorFullName = getValue('userinfo', 'FullName', "id = $SupervisorID");
    $SupervisorData = "$SupervisorFullName ($SupervisorUserName/$SupervisorID)";

    $PSU = $row->PSU;

    $PSUUserID = $row->PSUUserID;
    $UserName = $row->UserName;
    $FullName = $row->FullName;
    $UserData = "$FullName ($UserName/$PSUUserID)";

    $Target = $row->Target;
    $Collected = $row->Collected;
    $Remaining = $Target - $Collected;
    $Ratio = Ratio($Collected, $Target);

    $SubData = array();

    $SubData[] = $il;
    $SubData[] = $PSU;
    $SubData[] = $SupervisorData;
    $SubData[] = $UserData;
    $SubData[] = $Target;
    $SubData[] = $Collected;
    $SubData[] = $Remaining;
    $SubData[] = $Ratio;

    $il++;

    $data[] = $SubData;
}

/*$data = array();
$SubData[] = $qry;
$data[] = $SubData;*/

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

