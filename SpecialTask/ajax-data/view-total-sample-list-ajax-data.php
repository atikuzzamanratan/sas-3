<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

$cn = ConnectDB();

if (!empty($_POST)) {
    $request = $_REQUEST;
    
    $col[] = 'id';
    $col[] = 'DivisionName';
    $col[] = 'DistrictName';
    $col[] = 'NumberOfPSU';
	$col[] = 'PSUList';
	

    $qry = "SELECT a.* FROM (SELECT DISTINCT ROW_NUMBER() OVER(ORDER BY p.DivisionName ASC, p.DistrictName ASC) AS id,
				p.DivisionName, 
				p.DistrictName, 
				COUNT(sm.PSU) AS NumberOfPSU, 
				(
					SELECT DISTINCT STRING_AGG(smS.PSU, ', ') AS PList 
					FROM PSUList pS 
						JOIN 
						(
							SELECT PSU 
							FROM SampleMapping 
							GROUP BY PSU
						) AS smS ON smS.PSU = pS.PSU 
					WHERE pS.DivisionName = p.DivisionName 
						AND pS.DistrictName = p.DistrictName
				) AS PSU_List 
			FROM PSUList p 
				JOIN 
					(
						SELECT PSU 
						FROM SampleMapping 
						GROUP BY PSU
					) AS sm ON sm.PSU = p.PSU 
			WHERE 1=1 ";
	
	
	$DivisionCode = xss_clean($_REQUEST['DivisionCode']);
	$DistrictCode = xss_clean($_REQUEST['DistrictCode']);
	$UpazilaCode = xss_clean($_REQUEST['UpazilaCode']);
	$UnionWardCode = xss_clean($_REQUEST['UnionWardCode']);
	$MauzaCode = xss_clean($_REQUEST['MauzaCode']);
	$VillageCode = xss_clean($_REQUEST['VillageCode']);
	
	if (!empty($DivisionCode)) {
		$qry .= " AND ( p.DivisionCode = '" . $DivisionCode . "') ";
	}
	if (!empty($DistrictCode)) {
		$qry .= " AND ( p.DistrictCode = '" . $DistrictCode . "') ";
	}
	if (!empty($UpazilaCode)) {
		$qry .= " AND ( p.UpazilaCode = '" . $UpazilaCode . "') ";
	}
	if (!empty($UnionWardCode)) {
		$qry .= " AND ( p.UnionWardCode = '" . $UnionWardCode . "') ";
	}
	if (!empty($MauzaCode)) {
		$qry .= " AND ( p.MauzaCode = '" . $MauzaCode . "') ";
	}
	if (!empty($VillageCode)) {
		$qry .= " AND ( p.VillageCode = '" . $VillageCode . "') ";
	}

    if (!empty($request['search']['value'])) {
        $qry .= " AND (p.DivisionName like'%" . $request['search']['value'] . "%'";
		$qry .= " OR p.DistrictName like'%" . $request['search']['value'] . "%')";
    }
	
	$qry .= "GROUP BY p.DivisionName, 
				p.DistrictName) AS a ";
				
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
        $DivisionName = $row->DivisionName;
        $DistrictName = $row->DistrictName;

        $NumberOfPSU = $row->NumberOfPSU;
        $PSUList = $row->PSU_List;

        $SubData = array();

        $SubData[] = $DivisionName;
        $SubData[] = $DistrictName;
		$SubData[] = $NumberOfPSU;
		$SubData[] = $PSUList;

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

