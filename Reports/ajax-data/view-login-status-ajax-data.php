<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

$cn = ConnectDB();

if (!empty($_POST)) {
    $request = $_REQUEST;
    $DataStatus = xss_clean($_REQUEST["DataStatus"]);
    $DataUserID = xss_clean($_REQUEST["DataUserID"]);
    $DataChkAll = xss_clean($_REQUEST["DataChkAll"]);
    $LoggedUserName = xss_clean($_REQUEST["LoggedUserName"]);
    $LoggedUserID = xss_clean($_REQUEST["LoggedUserID"]);
    $DataStartDate = xss_clean($_REQUEST["DataStartDate"]);
    $DataEndDate = xss_clean($_REQUEST["DataEndDate"]);

    // $col = array(
    //     0 => 'id',
    //     1 => 'DivisionName',
	// 	2 => 'DistrictName',
    //     3 => 'UserID',
    //     4 => 'DateTime'
    // );









	$col = array(
		0 => 'DivName',        // Matches alias in SELECT
		1 => 'DistName',       // Matches alias in SELECT
		2 => 'UserName',       // Actual column in SELECT
		3 => 'MobileNumber',   // Actual column in SELECT
		4 => 'DateTime'        // Actual column in SELECT
	);









	
	$DivisionCode = xss_clean($_REQUEST['DivisionCode']);
	$DistrictCode = xss_clean($_REQUEST['DistrictCode']);
	$UpazilaCode = xss_clean($_REQUEST['UpazilaCode']);
	$UnionWardCode = xss_clean($_REQUEST['UnionWardCode']);
	$MauzaCode = xss_clean($_REQUEST['MauzaCode']);
	$VillageCode = xss_clean($_REQUEST['VillageCode']);
	
	$qry = "SELECT DISTINCT 
				uls.id, 
				ui.id AS UserId, 
				ui.UserName, 
				ui.FullName, 
				ui.MobileNumber, 
				(SELECT DivName FROM
					(
						SELECT STRING_AGG(divn, ', ') WITHIN GROUP (ORDER BY divn) AS DivName 
						FROM 
							(
								SELECT DISTINCT pl.DivisionName divn 
								FROM PSUList pl 
								WHERE pl.PSUUserID IN 
									(
										SELECT a.UserID 
										FROM assignsupervisor a 
										WHERE (a.DivCoordinatorID = ui.id 
											OR a.DistCoordinatorID = ui.id 
											OR a.SupervisorID = ui.id 
											OR a.UserID = ui.id) 
									)
							) fdiv
					) AS xs
				) AS DivName,
				(SELECT DistName FROM
					(
						SELECT STRING_AGG(dn, ', ') WITHIN GROUP (ORDER BY dn) AS DistName 
						FROM 
							(
								SELECT DISTINCT pl.DistrictName dn 
								FROM PSUList pl 
								WHERE pl.PSUUserID IN 
									(
										SELECT a.UserID 
										FROM assignsupervisor a 
										WHERE (a.DivCoordinatorID = ui.id 
											OR a.DistCoordinatorID = ui.id 
											OR a.SupervisorID = ui.id 
											OR a.UserID = ui.id) 
									)
							) f
					) AS xf
				) AS DistName,
				uls.DateTime  
			FROM UserLogStatus uls 
				JOIN userinfo ui ON uls.UserID = ui.id 
				JOIN assignsupervisor asp ON asp.DivCoordinatorID = ui.id OR asp.DistCoordinatorID = ui.id OR asp.SupervisorID = ui.id Or asp.UserID = ui.id
				JOIN PSUList pl ON pl.PSUUserID IN (
										SELECT a.UserID 
										FROM assignsupervisor a 
										WHERE (a.DivCoordinatorID = ui.id 
											OR a.DistCoordinatorID = ui.id 
											OR a.SupervisorID = ui.id 
											OR a.UserID = ui.id) 
									)
			WHERE uls.Status = $DataStatus 
				AND (uls.DateTime BETWEEN '$DataStartDate' AND '$DataEndDate') ";

    if ($DataChkAll != '1') {
        if (!empty($DataUserID)) {
            $qry .= " AND uls.UserID = $DataUserID ";
        }
    }
	
	if (!empty($DivisionCode)) {
		$qry .= " AND ( pl.DivisionCode = '" . $DivisionCode . "') ";
	}
	if (!empty($DistrictCode)) {
		$qry .= " AND ( pl.DistrictCode = '" . $DistrictCode . "') ";
	}
	if (!empty($UpazilaCode)) {
		$qry .= " AND ( pl.UpazilaCode = '" . $UpazilaCode . "') ";
	}
	if (!empty($UnionWardCode)) {
		$qry .= " AND ( pl.UnionWardCode = '" . $UnionWardCode . "') ";
	}
	if (!empty($MauzaCode)) {
		$qry .= " AND ( pl.MauzaCode = '" . $MauzaCode . "') ";
	}
	if (!empty($VillageCode)) {
		$qry .= " AND ( pl.VillageCode = '" . $VillageCode . "') ";
	}
	
	if (!empty($request['search']['value'])) {
        $qry .= " AND (ui.UserName like'%" . $request['search']['value'] . "%'";
		$qry .= " OR uls.UserID like'%" . $request['search']['value'] . "%'";
		$qry .= " OR ui.FullName like'%" . $request['search']['value'] . "%'";
		$qry .= " OR ui.MobileNumber like'%" . $request['search']['value'] . "%'";
        $qry .= " OR uls.DateTime like'%" . $request['search']['value'] . "%'";
		$qry .= " OR pl.DivisionName like'%" . $request['search']['value'] . "%'";
		$qry .= " OR pl.DistrictName like'%" . $request['search']['value'] . "%')";
    }
//echo $qry;exit;
    $rs = db_query($qry, $cn);
    $TotalData = db_num_rows($rs);
    $totalFilter = $TotalData;

    if ($request['length'] < 0) {
        $qry .= " ORDER BY " . $col[$request['order'][0]['column']] . " " . $request['order'][0]['dir'];
    } else {
        $qry .= " ORDER BY " . $col[$request['order'][0]['column']] . " " . $request['order'][0]['dir'] . " OFFSET " . $request['start'] . " ROWS FETCH NEXT " . $request['length'] . " ROWS ONLY";
    }

    $resQry = $app->getDBConnection()->fetchAll($qry);

    $data = array();

    foreach ($resQry as $row) {
        $RecordID = $row->id;
        $UserName = $row->UserName;
        $UserFullName = $row->FullName;
		$UserId = $row->UserId;
        $UserData = "$UserFullName ($UserName/$UserId)";
		
		$UserMobileNo = $row->MobileNumber;
        $UserMobileNo = whatsAppLink($UserMobileNo);

        // $EntryDate = date_format($row->DateTime, 'd-m-Y H:i:s');










	$EntryDate = date('d-m-Y H:i:s', strtotime($row->DateTime));









        $DivisionName = $row->DivName;
		$DistrictName = $row->DistName;

        $SubData = array();

        $SubData[] = $DivisionName;
		$SubData[] = $DistrictName;
        $SubData[] = $UserData;
        $SubData[] = $UserMobileNo;
        $SubData[] = $EntryDate;

        $data[] = $SubData;
    }

    $json_data = array(
        "draw" => intval($request['draw']),
        "recordsTotal" => $TotalData,
        "recordsFiltered" => $totalFilter,
        "data" => $data
    );

    echo json_encode($json_data);
}

