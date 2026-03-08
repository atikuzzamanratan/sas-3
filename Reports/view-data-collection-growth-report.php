<?php
$qryFormName = "SELECT id, FormName FROM datacollectionform WHERE Status = 'Active' AND CompanyID = ? ORDER BY id";
$rsQryFormName = $app->getDBConnection()->fetchAll($qryFormName, $loggedUserCompanyID);

$divQuery = "SELECT DISTINCT DivisionCode, DivisionName FROM GeoInformation ORDER BY DivisionName ASC";
$rsDivQuery = $app->getDBConnection()->fetchAll($divQuery);

if (strpos($loggedUserName, 'div') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionCode, p.DivisionName 
				FROM PSUList AS p 
					JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
				WHERE  p.CompanyID = ? AND a.DivCoordinatorID = ? 
				ORDER BY DivisionName ASC";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID, $loggedUserID);
} elseif (strpos($loggedUserName, 'val') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionCode, p.DivisionName 
				FROM PSUList AS p 
					JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
				WHERE  p.CompanyID = ? AND a.ValidatorID = ? 
				ORDER BY DivisionName ASC";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID, $loggedUserID);
}

if (strpos($loggedUserName, 'cval') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionCode, p.DivisionName 
				FROM PSUList AS p 
					JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
				WHERE  p.CompanyID = ?
				ORDER BY DivisionName ASC";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID);
}

if ($_REQUEST['show'] === 'Show') {

    $FormID = xss_clean($_REQUEST['FormID']);

    $DivisionCode = xss_clean($_REQUEST['DivisionCode']);
    $DistrictCode = xss_clean($_REQUEST['DistrictCode']);
    $UpazilaCode = xss_clean($_REQUEST['UpazilaCode']);
    $UnionWardCode = xss_clean($_REQUEST['UnionWardCode']);
    $MauzaCode = xss_clean($_REQUEST['MauzaCode']);
    $VillageCode = xss_clean($_REQUEST['VillageCode']);
}
?>

<div class="inner-wrapper">
    <section role="main" class="content-body">
        <header class="page-header">
            <h2><?php echo $MenuLebel; ?></h2>

            <?php include_once 'Components/header-home-button.php'; ?>
        </header>

        <!-- start: page -->
        <div class="row">
            <div class="col-lg-12 mb-0">
                <section class="card">
                    <div class="card-body">
                        <form class="form-horizontal form-bordered" action="" method="post">
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Form Select<span
                                        class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="FormID"
                                        id="FormID" required>
                                        <optgroup label="Select Form">
                                            <?PHP
                                            foreach ($rsQryFormName as $row) {
                                                echo '<option value="' . $row->id . '"' . (isset($FormID) && !empty($FormID) && $row->id == $FormID ? ' selected' : '') . '>' . $row->FormName . '</option>';
                                            }
                                            ?>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Division Select
                                    <?php if (strpos($loggedUserName, 'admin') === false) { ?>
                                        <span class="required">*</span>
                                    <?php } ?>
                                </label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="DivisionCode"
                                        id="DivisionCode"
                                        <?php if (strpos($loggedUserName, 'admin') === false) { ?>
                                        required
                                        <?php } ?>
                                        onchange="ShowDropDown('DivisionCode', 'DistrictDiv', 'ShowDistrict', 'ShowUpazila')">
                                        <option value="">Choose division</option>
                                        <?PHP
                                        foreach ($rsDivQuery as $row) {
                                            echo '<option value="' . $row->DivisionCode . '"' . (isset($DivisionCode) && !empty($DivisionCode) && $row->DivisionCode == $DivisionCode ? ' selected' : '') . '>' . $row->DivisionName . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div id="geoDiv" style="display: none">
                                <div class="form-group row pb-3" id="DistrictDiv"></div>
                                <div class="form-group row pb-3" id="UpazilaDiv"></div>
                                <div class="form-group row pb-3" id="UnionWardDiv"></div>
                                <div class="form-group row pb-3" id="MauzaDiv"></div>
                                <div class="form-group row pb-3" id="VillageDiv"></div>
                            </div>

                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-primary" name="show" type="submit" id="show"
                                            value="Show">

                                        <button type="button" class="btn btn-secondary ms-4" id="clearForm">Clear</button>
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </section>
                <?php
                if ($_REQUEST['show'] === 'Show') {

                    $FormName = getValue('datacollectionform', 'FormName', "id = $FormID");
//die("Form Name: ".$DivisionCode);
                    $ReportCondition = "";
                    $getFieldValue = 'DivisionName';

                    if (!empty($DivisionCode)) {
                        $DivisionName = getValue('PSUList', 'DISTINCT(DivisionName)', "DivisionCode = $DivisionCode");
//die('Division Name: '.$DivisionName);
                        $getFieldValue = 'DistrictName';
                        $ReportCondition .= " AND ( ps.DivisionCode = '" . $DivisionCode . "') ";
                    }

                    if (!empty($DistrictCode)) {
                        $DistrictName = getValue('PSUList', 'DISTINCT(DistrictName)', "DivisionCode = $DivisionCode AND DistrictCode = $DistrictCode");
                        $DistrictName = ' > ' . $DistrictName;

                        $getFieldValue = 'UpazilaName';
                        $ReportCondition .= " AND ( ps.DistrictCode = '" . $DistrictCode . "') ";
                    }

                    if (!empty($UpazilaCode)) {
                        $UpazilaName = getValue(
                            'PSUList',
                            'DISTINCT(UpazilaName)',
                            "DivisionCode = $DivisionCode AND DistrictCode = $DistrictCode AND UpazilaCode = $UpazilaCode"
                        );
                        $UpazilaName = ' > ' . $UpazilaName;

                        $getFieldValue = 'UnionWardName';
                        $ReportCondition .= " AND ( ps.UpazilaCode = '" . $UpazilaCode . "') ";
                    }

                    if (!empty($UnionWardCode)) {
                        $UnionWardName = getValue(
                            'PSUList',
                            'DISTINCT(UnionWardName)',
                            "DivisionCode = $DivisionCode AND DistrictCode = $DistrictCode AND UpazilaCode = $UpazilaCode AND UnionWardCode = $UnionWardCode"
                        );
                        $UnionWardName = ' > ' . $UnionWardName;

                        $getFieldValue = 'MauzaName';
                        $ReportCondition .= " AND ( ps.UnionWardCode = '" . $UnionWardCode . "') ";
                    }

                    if (!empty($MauzaCode)) {
                        $MauzaName = getValue(
                            'PSUList',
                            'DISTINCT(MauzaName)',
                            "DivisionCode = $DivisionCode AND DistrictCode = $DistrictCode AND UpazilaCode = $UpazilaCode AND UnionWardCode = $UnionWardCode AND MauzaCode = $MauzaCode"
                        );
                        $MauzaName = ' > ' . $MauzaName;

                        $getFieldValue = 'VillageName';
                        $ReportCondition .= " AND ( ps.MauzaCode = '" . $MauzaCode . "') ";
                    }

                    if (!empty($VillageCode)) {
                        $VillageName = getValue(
                            'PSUList',
                            'DISTINCT(VillageName)',
                            "DivisionCode = $DivisionCode AND DistrictCode = $DistrictCode AND UpazilaCode = $UpazilaCode AND UnionWardCode = $UnionWardCode AND MauzaCode = $MauzaCode AND VillageCode = $VillageCode"
                        );
                        $VillageName = ' > ' . $VillageName;
                    }

                    $check = $_REQUEST['chkAll'];
                    if ($FormID == $formIdMainData) {
                        $QueryTarget = "SELECT SUM(SQ.Target) as Target FROM (SELECT DISTINCT PSU, NumberOfRecordForMainSurvey as Target FROM PSUList WHERE FarmName='' and CompanyID = ?";
                    } elseif ($FormID == $formIdSamplingData) {
                        $QueryTarget = "SELECT SUM(SQ.Target) as Target FROM (SELECT DISTINCT PSU, NumberOfRecord as Target FROM PSUList WHERE FarmName='' and CompanyID = ?";
                    }


                    $QueryCollected = "SELECT COUNT(id) as Collected FROM xformrecord WHERE CompanyID = ? AND FormId=$FormID AND PSU 
                    IN(SELECT DISTINCT PSU FROM PSUList WHERE 1=1 ";

                    if (!empty($DivisionCode)) {
                        $QueryTarget .= " AND ( DivisionCode = '" . $DivisionCode . "') ";
                        $QueryCollected .= " AND ( DivisionCode = '" . $DivisionCode . "') ";
                    }

                    if (!empty($DistrictCode)) {
                        $QueryTarget .= " AND ( DistrictCode = '" . $DistrictCode . "') ";
                        $QueryCollected .= " AND ( DistrictCode = '" . $DistrictCode . "') ";
                    }

                    if (!empty($UpazilaCode)) {
                        $QueryTarget .= " AND ( UpazilaCode = '" . $UpazilaCode . "') ";
                        $QueryCollected .= " AND ( UpazilaCode = '" . $UpazilaCode . "') ";
                    }

                    if (!empty($UnionWardCode)) {
                        $QueryTarget .= " AND ( UnionWardCode = '" . $UnionWardCode . "') ";
                        $QueryCollected .= " AND ( UnionWardCode = '" . $UnionWardCode . "') ";
                    }

                    if (!empty($MauzaCode)) {
                        $QueryTarget .= " AND ( MauzaCode = '" . $MauzaCode . "') ";
                        $QueryCollected .= " AND ( MauzaCode = '" . $MauzaCode . "') ";
                    }

                    if (!empty($VillageCode)) {
                        $QueryTarget .= " AND ( VillageCode = '" . $VillageCode . "') ";
                        $QueryCollected .= " AND ( VillageCode = '" . $VillageCode . "') ";
                    }

                    $QueryTarget .= ") SQ";
                    $QueryCollected .= ")";

					/*
                    echo $QueryTarget;
                    echo '<br>';
                    echo $QueryCollected;
					exit;
					*/
                    
                    $targetRecordField = $FormID == $formIdSamplingData ? 'NumberOfRecord' : 'NumberOfRecordForMainSurvey';
                    $locationReportQuery = "SELECT ps.$getFieldValue as Name, 
												SUM(DISTINCT ps.$targetRecordField) as Target, 
												COUNT(CASE WHEN xfr.FormId = $FormID THEN xfr.id END) as Collected 
											FROM PSUList ps 
												LEFT JOIN xformrecord xfr ON xfr.PSU = ps.PSU 
											WHERE ps.FarmName='' and ps.CompanyID = $loggedUserCompanyID $ReportCondition 
											GROUP BY ps.$getFieldValue";
//die("Top District Code: ".$DistrictCode);					
					if (empty($DivisionCode)) {
						if ($FormID == $formIdMainData) {
							//$locationReportQuery = "";
							$targetSQL = "SELECT ps.DivisionName, 
											ISNULL(SUM(ps.NumberofRecordForMainSurvey),0) AS Target
										FROM PSUList ps 
										WHERE ps.CompanyID = ? AND ps.FarmName=''
										GROUP BY ps.DivisionName";
						} elseif ($FormID == $formIdSamplingData) {
							$targetSQL = "SELECT ps.DivisionName, 
											ISNULL(SUM(ps.NumberOfRecord),0) AS Target
										FROM PSUList ps 
										WHERE ps.CompanyID = ? AND ps.FarmName=''
										GROUP BY ps.DivisionName";
						}
						$rsDistrictTarget = $app->getDBConnection()->fetchAll($targetSQL, $loggedUserCompanyID);
						$DistTargetArray = array();
						foreach ($rsDistrictTarget as $rowTarget) {
							$DistTargetArray[strtolower($rowTarget->DivisionName)] = $rowTarget->Target;
						}
					}
					if (!empty($DivisionCode)) {
						if ($FormID == $formIdMainData) {
							//$locationReportQuery = "";
							$targetSQL = "SELECT ps.DistrictName, 
											ISNULL(SUM(ps.NumberofRecordForMainSurvey),0) AS Target
										FROM PSUList ps 
										WHERE ps.CompanyID = ? AND ps.DivisionCode = ? AND ps.FarmName=''
										GROUP BY ps.DistrictName";
						} elseif ($FormID == $formIdSamplingData) {
							$targetSQL = "SELECT ps.DistrictName, 
											ISNULL(SUM(ps.NumberOfRecord),0) AS Target
										FROM PSUList ps 
										WHERE ps.CompanyID = ? AND ps.DivisionCode = ? AND ps.FarmName=''
										GROUP BY ps.DistrictName";
						}
						$rsDistrictTarget = $app->getDBConnection()->fetchAll($targetSQL, $loggedUserCompanyID, $DivisionCode);
						$DistTargetArray = array();
						foreach ($rsDistrictTarget as $rowTarget) {
							$DistTargetArray[strtolower($rowTarget->DistrictName)] = $rowTarget->Target;
						}
					}
					if (!empty($DistrictCode)) {
						if ($FormID == $formIdMainData) {
							//$locationReportQuery = "";
							$targetSQL = "SELECT ps.UpazilaName, 
											ISNULL(SUM(ps.NumberofRecordForMainSurvey),0) AS Target
										FROM PSUList ps 
										WHERE ps.CompanyID = ? AND ps.DivisionCode = ? AND ps.DistrictCode = ? AND ps.FarmName=''
										GROUP BY ps.UpazilaName";
						} elseif ($FormID == $formIdSamplingData) {
							$targetSQL = "SELECT ps.UpazilaName, 
											ISNULL(SUM(ps.NumberOfRecord),0) AS Target
										FROM PSUList ps 
										WHERE ps.CompanyID = ? AND ps.DivisionCode = ? AND ps.DistrictCode = ? AND ps.FarmName=''
										GROUP BY ps.UpazilaName";
						}
//die("SQL: ".$targetSQL);
						$rsDistrictTarget = $app->getDBConnection()->fetchAll($targetSQL, $loggedUserCompanyID, $DivisionCode, $DistrictCode);
						$DistTargetArray = array();
						foreach ($rsDistrictTarget as $rowTarget) {
							$DistTargetArray[strtolower($rowTarget->UpazilaName)] = $rowTarget->Target;
						}
					}
					if (!empty($UpazilaCode)) {
						if ($FormID == $formIdMainData) {
							//$locationReportQuery = "";
							$targetSQL = "SELECT ps.UnionWardName, 
											ISNULL(SUM(ps.NumberofRecordForMainSurvey),0) AS Target
										FROM PSUList ps 
										WHERE ps.CompanyID = ? AND ps.DivisionCode = ? AND ps.DistrictCode = ? AND ps.UpazilaCode = ? AND ps.FarmName=''
										GROUP BY ps.UnionWardName";
						} elseif ($FormID == $formIdSamplingData) {
							$targetSQL = "SELECT ps.UnionWardName, 
											ISNULL(SUM(ps.NumberOfRecord),0) AS Target
										FROM PSUList ps 
										WHERE ps.CompanyID = ? AND ps.DivisionCode = ? AND ps.DistrictCode = ? AND ps.UpazilaCode = ? AND ps.FarmName=''
										GROUP BY ps.UnionWardName";
						}
//var_dump($loggedUserCompanyID, $DivisionCode, $DistrictCode, $UpazilaCode);
//die("SQL: ".$targetSQL);
						$rsDistrictTarget = $app->getDBConnection()->fetchAll($targetSQL, $loggedUserCompanyID, $DivisionCode, $DistrictCode, $UpazilaCode);
						$DistTargetArray = array();
						foreach ($rsDistrictTarget as $rowTarget) {
							$DistTargetArray[strtolower($rowTarget->UnionWardName)] = $rowTarget->Target;
						}
					}
					
//die($locationReportQuery);
                    $rsTarget = $app->getDBConnection()->fetch($QueryTarget, $loggedUserCompanyID);
                    $Target = $rsTarget->Target;
                    
                    $rsCollected = $app->getDBConnection()->fetch($QueryCollected, $loggedUserCompanyID);
                    $Collected = $rsCollected->Collected;

                    $CountData = $Target + $Collected;
                    
                    $DataCollectionPercentage = Ratio($Collected, $Target);
                   
                    $rsLocationReport = $app->getDBConnection()->fetchAll($locationReportQuery);
                    
                ?>
                    <div class="card">
                        <div class="row">
                            <div class="col-lg-12 mb-3">
                                <section class="card">
                                    <div class="card-header">
                                        <div class="card-title">Data Status : <?php echo $FormName; ?></div>
                                        <div class="card-subtitle"><?php echo $DivisionName . $DistrictName . $UpazilaName . $UnionWardName . $MauzaName . $VillageName; ?></div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-12 text-center">
                                                <div class="liquid-meter-wrapper liquid-meter-lg mt-3">
                                                    <div class="liquid-meter">
                                                        <meter min="0" max="100"
                                                            value="<?php echo $DataCollectionPercentage; ?>"
                                                            id="meterSales"></meter>
                                                    </div>
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-responsive-lg table-bordered table-striped table-sm mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Target</th>
                                                                <th>Collected</th>
                                                                <th>Remaining</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td><?php echo $Target; ?></td>
                                                                <td><?php echo $Collected; ?></td>
                                                                <td><?php echo $Target - $Collected; ?></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-12 text-center">
                                                <div class="table-responsive">
                                                    <table class="table table-responsive-lg table-bordered table-striped table-sm mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Name</th>
                                                                <th>Target</th>
                                                                <th>Collected</th>
                                                                <th>Remaining</th>
                                                                <th>Progress</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($rsLocationReport as $row) { ?>
                                                                <tr>
                                                                    <td><?php echo $row->Name; ?></td>
                                                                    <td><?php echo $DistTargetArray[strtolower($row->Name)]; ?></td>
                                                                    <td><?php echo $row->Collected; ?></td>
                                                                    <td><?php echo ($DistTargetArray[strtolower($row->Name)] - $row->Collected) > 0 ? $DistTargetArray[strtolower($row->Name)] - $row->Collected : 0; ?></td>
                                                                    <td><?php echo $DistTargetArray[strtolower($row->Name)] > 0 ? Ratio($row->Collected, $DistTargetArray[strtolower($row->Name)]) : '0.00%'; ?></td>
                                                                </tr>
                                                            <?php } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
                <!-- end: page -->
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

<script>
      $(document).ready(function() {
        populateDropdowns(
            <?php echo isset($DivisionCode) && $DivisionCode !== '' ? $DivisionCode : 'null'; ?>,
            <?php echo isset($DistrictCode) && $DistrictCode !== '' ? $DistrictCode : 'null'; ?>,
            <?php echo isset($UpazilaCode) && $UpazilaCode !== '' ? $UpazilaCode : 'null'; ?>,
            <?php echo isset($UnionWardCode) && $UnionWardCode !== '' ? $UnionWardCode : 'null'; ?>,
            <?php echo isset($MauzaCode) && $MauzaCode !== '' ? $MauzaCode : 'null'; ?>,
            <?php echo isset($VillageCode) && $VillageCode !== '' ? $VillageCode : 'null'; ?>
        );
    });
</script>