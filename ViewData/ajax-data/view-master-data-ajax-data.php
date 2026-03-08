<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

$baseURL = get_base_url();

if ($_REQUEST['selFormID'] != '') {
    $SelectedFormID = $app->cleanInput($_REQUEST['selFormID']);
}

if ($_REQUEST['selUserID'] != '') {
    $SelectedUserID = $app->cleanInput($_REQUEST['selUserID']);
}

if ($_REQUEST['selColName'] != '') {
    $SelectedColumnName = $app->cleanInput($_REQUEST['selColName']);
}

if ($_REQUEST['selDataStatus'] != '') {
    $SelectedDataStatus = $app->cleanInput($_REQUEST['selDataStatus']);
}

if ($_REQUEST['lun'] != '') {
    $LoggedUserName = $app->cleanInput($_REQUEST['lun']);
}

if ($_REQUEST['lui'] != '') {
    $LoggedUserID = $app->cleanInput($_REQUEST['lui']);
}

if ($_REQUEST['ci'] != '') {
    $SelectedCompanyID = $app->cleanInput($_REQUEST['ci']);
}

if ($SelectedDataStatus == 0) {
    $tbl = 'masterdatarecord_Pending';
} elseif ($SelectedDataStatus == 1) {
    $tbl = 'masterdatarecord_Approved';
} elseif ($SelectedDataStatus == 2) {
    $tbl = 'masterdatarecord_UnApproved';
}

// $qry = "SELECT $tbl.XFormRecordId, $tbl.DataName, $tbl.PSU, ui.UserName, ui.FullName, $tbl.ColumnName, $tbl.ColumnValue, $tbl.EntryDate, $tbl.id, pl.DivisionName, pl.DistrictName  
// FROM $tbl 
// 	JOIN userinfo ui ON $tbl.UserID = ui.id 
// 	JOIN PSUList pl ON pl.PSUUserID = ui.id AND $tbl.PSU = pl.PSU 
// WHERE ui.id = $SelectedUserID AND $tbl.IsApproved = $SelectedDataStatus AND masterdatarecord_Approved.FormId=$SelectedFormID AND $tbl.ColumnName = '$SelectedColumnName' 
// ORDER BY $tbl.XFormRecordId ASC";
// $resQry = $app->getDBConnection()->fetchAll($qry);













$qry = "
SELECT 
    mdr.XFormRecordId,
    mdr.DataName,
    mdr.PSU,
    ui.UserName,
    ui.FullName,
    CAST(mdr.ColumnName AS NVARCHAR(MAX)) AS ColumnName,
    CAST(mdr.ColumnValue AS NVARCHAR(MAX)) AS ColumnValue,
    mdr.EntryDate,
    mdr.id,
    pl.DivisionName,
    pl.DistrictName
FROM $tbl AS mdr
JOIN userinfo AS ui ON mdr.UserID = ui.id
LEFT JOIN PSUList AS pl ON TRY_CAST(mdr.PSU AS BIGINT) = pl.PSU
WHERE ui.id = ?
  AND mdr.IsApproved = ?
  AND mdr.FormId = ?
  AND CAST(mdr.ColumnName AS NVARCHAR(MAX)) = ?
ORDER BY mdr.XFormRecordId ASC";
$resQry = $app->getDBConnection()->fetchAll($qry, $SelectedUserID, $SelectedDataStatus, $SelectedFormID, $SelectedColumnName);














$data = array();
$il = 1;

foreach ($resQry as $row) {
    $RecordID = $row->XFormRecordId;
    $XFormsFilePath = getValue('xformrecord', 'XFormsFilePath', "id = $RecordID");
    $DataName = $row->DataName;
    $PSU = $row->PSU;

    $UserName = $row->UserName;
    $UserFullName = $row->FullName;
    $UserData = "$UserFullName ($UserName)";

    $ColumnName = $row->ColumnName;
    $ColumnValue = $row->ColumnValue;
    $EntryDate = date_format($row->EntryDate, 'd-m-Y H:i:s');
		
	$DivisionName = $row->DivisionName;
	$DistrictName = $row->DistrictName;

    $SubData = array();

    $SubData[] = $RecordID;
    $SubData[] = $PSU;
	$SubData[] = $DivisionName;
	$SubData[] = $DistrictName;
    $SubData[] = $UserData;
    $SubData[] = $DataName;
    //$SubData[] = $ColumnName;
    $SubData[] = $ColumnValue;
    $SubData[] = $EntryDate;

    // $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">
    //                 <button type=\"button\" class=\"simple-ajax-modal btn btn-outline-primary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#viewDataModal\" 
    //                 onclick=\"ShowDataDetail('$RecordID', '$SelectedDataStatus', '$PSU', '$LoggedUserID', '$SelectedUserID', '$XFormsFilePath')\"><i class=\"fas fa-eye\"></i></button>
    //             </div>
    //             <script type=\"text/javascript\">
    //                 function ShowDataDetail(recordID, isAproved, psu, loggedUserID, agentID, XFormsFilePath, data) {
    //                         $.ajax({
    //                             url: 'ViewData/ajax-data/data-detail-view.php',
    //                             method: 'GET',
    //                             datatype: 'json',
    //                             data: {
    //                                 id: recordID,
    //                                 status: isAproved,
    //                                 psu: psu,
    //                                 loggedUserID: loggedUserID,
    //                                 agentID: agentID,
    //                                 XFormsFilePath: XFormsFilePath
    //                             },
    //                             success: function (response) {
    //                                 $('#dataViewDiv').html(response);
    //                             }
    //                         }); 
    //                     return false;
    //                 }
    //             </script>
    //             <!-- View Data Modal-->
    //             <div class=\"modal fade bd-example-modal-lg\" id=\"viewDataModal\" tabindex=\"-1\" aria-labelledby=\"editDataModalLabel\" aria-hidden=\"true\">
    //               <div class=\"modal-dialog modal-lg\">
    //                 <div id=\"dataViewDiv\" class=\"modal-content\">
                      
    //                 </div>
    //               </div>
    //             </div>";

    $actions = '<div style="display:flex;align-items:center;justify-content:center;">
                    <button type="button"
                            class="btn btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#viewDataModal"
                            onclick="ShowDataDetail('
                                . "'$SelectedFormID','$RecordID','$SelectedDataStatus','$PSU','$LoggedUserID','$SelectedUserID','$XFormsFilePath'" .
                            ')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>';

    $SubData[] = $actions;

    $il++;

    $data[] = $SubData;
}

/*$data = array();
$SubData[] = $qry;
$data[] = $SubData;*/

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

