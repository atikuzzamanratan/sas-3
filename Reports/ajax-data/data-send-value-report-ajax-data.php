<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

if ($_REQUEST['frmID'] != '') {
    $FormID = $app->cleanInput($_REQUEST['frmID']);
}

if ($_REQUEST['uid'] != '') {
    $UserID = $app->cleanInput($_REQUEST['uid']);
}

if ($_REQUEST['colName'] != '') {
    $ColumnName = $app->cleanInput($_REQUEST['colName']);
}

$qry = "SELECT ui.UserName, ui.FullName, ui.MobileNumber, mdra.UserID, mdra.XFormRecordId, mdra.ColumnName, mdra.ColumnValue
FROM masterdatarecord_Approved mdra JOIN userinfo ui ON mdra.UserID = ui.id 
WHERE CONVERT(NVARCHAR(MAX), ColumnName) = ? AND UserID = ? AND FormId = ?
ORDER BY mdra.XFormRecordId";
$resQry = $app->getDBConnection()->fetchAll($qry, $ColumnName, $UserID, $FormID);

$data = array();
$il = 1;

foreach ($resQry as $row) {
    $UserName = $row->UserName;
    $User = $row->FullName . ' (' . $UserName . ')';
    $UserMobileNo = $row->MobileNumber;
    $RecordID = $row->XFormRecordId;
    $ColName = $row->ColumnName;
    $ColValue = $row->ColumnValue;

    $SubData = array();

    $SubData[] = $il;
    $SubData[] = $User;
    $SubData[] = $UserMobileNo;
    $SubData[] = $RecordID;
    $SubData[] = $ColName;
    $SubData[] = $ColValue;

    $il++;

    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

