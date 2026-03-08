<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

$cn = ConnectDB();

if (!empty($_POST)) {
    $request = $_REQUEST;
    $DataFromID = xss_clean($_REQUEST["DataFromID"]);
    $DataUserID = xss_clean($_REQUEST["DataUserID"]);
    $DataChkAll = xss_clean($_REQUEST["DataChkAll"]);
    $LoggedUserName = xss_clean($_REQUEST["LoggedUserName"]);
    $LoggedUserID = xss_clean($_REQUEST["LoggedUserID"]);
    $DataStartDate = xss_clean($_REQUEST["DataStartDate"]);
    $DataEndDate = xss_clean($_REQUEST["DataEndDate"]);

    $col[] = "ValidatorID";
	$col[] = "UserName";
	$col[] = "myValidationDate";
	$col[] = "NoOfCheckData";

	$DivisionCode = xss_clean($_REQUEST['DivisionCode']);
	$DistrictCode = xss_clean($_REQUEST['DistrictCode']);
	$UpazilaCode = xss_clean($_REQUEST['UpazilaCode']);
	$UnionWardCode = xss_clean($_REQUEST['UnionWardCode']);
	$MauzaCode = xss_clean($_REQUEST['MauzaCode']);
	$VillageCode = xss_clean($_REQUEST['VillageCode']);

	$qry = "SELECT FORMAT(xfr.ValidationDate, 'dd-MM-yyyy') AS myValidationDate, 
				xfr.ValidatorID, 
				ui.UserName,
				ui.FullName,
				COUNT(xfr.id) AS NoOfCheckData
			FROM xformrecord xfr 
				JOIN userinfo ui ON ui.id = xfr.ValidatorID 
				JOIN PSUList pl ON pl.PSUUserID = xfr.UserID AND xfr.PSU = pl.PSU 
			WHERE 1=1  AND xfr.FormId = $DataFromID ";
	if (strpos($LoggedUserName, 'val') !== false) {
		if (strpos($LoggedUserName, 'cval') === false) {
			$qry .= "	AND xfr.ValidatorID = $LoggedUserID ";
		}
	}
	if ($DataChkAll == '0' && strpos($LoggedUserName, 'cs') !== false) {
		$qry .= "	AND xfr.ValidatorID = $DataUserID ";
	}
	if (!empty($DataStartDate) && !empty($DataEndDate)) {
		$qry .= " AND (xfr.EntryDate BETWEEN '$DataStartDate' AND '$DataEndDate')";
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
        $qry .= " AND (ui.id like'%" . $request['search']['value'] . "%'";
        $qry .= " OR ui.UserName like'%" . $request['search']['value'] . "%'";
		$qry .= " OR ui.UserName like'%" . $request['search']['value'] . "%')";
        //$qry .= " OR NoOfCheckData like'%" . $request['search']['value'] . "%)'";
    }
	$qry .= "GROUP BY FORMAT(xfr.ValidationDate, 'dd-MM-yyyy'), 
				xfr.ValidatorID, 
				ui.UserName, 
				ui.FullName ";
//die($qry);
    $rs = db_query($qry, $cn);
    $TotalData = db_num_rows($rs);
    $totalFilter = $TotalData;
//die("Total Filter: ".$totalFilter);
    if ($request['length'] < 0) {
        $qry .= " ORDER BY myValidationDate DESC ";
    } else {
        $qry .= " ORDER BY " . $col[$request['order'][0]['column']] . " " . $request['order'][0]['dir'] . " OFFSET " . $request['start'] . " ROWS FETCH NEXT " . $request['length'] . " ROWS ONLY";
    }

    $resQry = $app->getDBConnection()->fetchAll($qry);

    $data = array();

    foreach ($resQry as $row) {
        $RecordID = $row->ValidatorID;
        
        $UserID = $row->ValidatorID;
        $UserName = $row->UserName;
        $UserFullName = $row->FullName;
        $UserData = "$UserFullName ($UserName/$UserID)";

        //$UserMobileNo = $row->MobileNumber;
        //$UserMobileNo = whatsAppLink($UserMobileNo);

        $EntryDate = $row->myValidationDate;

        $NoOfCheckData = $row->NoOfCheckData;

        $SubData = array();
		
		$SubData[] = $UserData;
        $SubData[] = $EntryDate;
		$SubData[] = $NoOfCheckData;

        $data[] = $SubData;
    }
//var_dump($data);exit;
    $json_data = array(
        "draw" => intval($request['draw']),
        "recordsTotal" => $TotalData,
        "recordsFiltered" => $totalFilter,
        "data" => $data
    );

    echo json_encode($json_data);
}

