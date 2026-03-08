<?php
$qryFormName = "SELECT id, FormName FROM datacollectionform WHERE Status = 'Active' AND CompanyID = ? ORDER BY id ASC";
$rsQryFormName = $app->getDBConnection()->fetchAll($qryFormName, $loggedUserCompanyID);

if (strpos($loggedUserName, 'dist') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = ? AND a.DistCoordinatorID = ?";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID, $loggedUserID);
} elseif (strpos($loggedUserName, 'cs') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = ? AND a.SupervisorID = ?";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID, $loggedUserID);
} elseif (strpos($loggedUserName, 'val') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = ? AND a.ValidatorID = ?";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID, $loggedUserID);
} else {
    $divQuery = "SELECT DISTINCT DivisionName , DivisionCode FROM PSUList WHERE CompanyID = ? ORDER BY DivisionName ASC";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID);
}

if (strpos($loggedUserName, 'cval') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = ?";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID);
}

if (strpos($loggedUserName, 'div') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionCode, p.DivisionName 
				FROM PSUList AS p 
					JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
				WHERE  p.CompanyID = ? AND a.DivCoordinatorID = ? 
				ORDER BY DivisionName ASC";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID, $loggedUserID);
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
                                                echo '<option value="' . $row->id . '"'. (isset($FormID) && !empty($FormID) && $FormID == $row->id ? ' selected' : '') .'>' . $row->FormName . '</option>';
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

                            <?php if (strpos($loggedUserName, 'admin') !== false) { ?>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-sm-end pt-2"></label>
                                    <div class="col-lg-6">
                                        <div class="checkbox-custom checkbox-warning">
                                            <input id="chkAll" value="chkAll" type="checkbox" name="chkAll" />
                                            <!--<label for="chkAll">All</label>-->
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

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
                    $check = $_REQUEST['chkAll'];

                    $FormName = getValue('datacollectionform', 'FormName', "id = $FormID");

                    if (!empty($DivisionCode)) {
                        $DivisionName = getValue('PSUList', 'DISTINCT(DivisionName)', "DivisionCode = $DivisionCode");
                    }

                    if (!empty($DistrictCode)) {
                        $DistrictName = getValue('PSUList', 'DISTINCT(DistrictName)', "DivisionCode = $DivisionCode AND DistrictCode = $DistrictCode");
                        $DistrictName = ' > ' . $DistrictName;
                    }

                    if (!empty($UpazilaCode)) {
                        $UpazilaName = getValue(
                            'PSUList',
                            'DISTINCT(UpazilaName)',
                            "DivisionCode = $DivisionCode AND DistrictCode = $DistrictCode AND UpazilaCode = $UpazilaCode"
                        );
                        $UpazilaName = ' > ' . $UpazilaName;
                    }

                    if (!empty($UnionWardCode)) {
                        $UnionWardName = getValue(
                            'PSUList',
                            'DISTINCT(UnionWardName)',
                            "DivisionCode = $DivisionCode AND DistrictCode = $DistrictCode AND UpazilaCode = $UpazilaCode AND UnionWardCode = $UnionWardCode"
                        );
                        $UnionWardName = ' > ' . $UnionWardName;
                    }

                    if (!empty($MauzaCode)) {
                        $MauzaName = getValue(
                            'PSUList',
                            'DISTINCT(MauzaName)',
                            "DivisionCode = $DivisionCode AND DistrictCode = $DistrictCode AND UpazilaCode = $UpazilaCode AND UnionWardCode = $UnionWardCode AND MauzaCode = $MauzaCode"
                        );
                        $MauzaName = ' > ' . $MauzaName;
                    }

                    if (!empty($VillageCode)) {
                        $VillageName = getValue(
                            'PSUList',
                            'DISTINCT(VillageName)',
                            "DivisionCode = $DivisionCode AND DistrictCode = $DistrictCode AND UpazilaCode = $UpazilaCode AND UnionWardCode = $UnionWardCode AND MauzaCode = $MauzaCode AND VillageCode = $VillageCode"
                        );
                        $VillageName = ' > ' . $VillageName;
                    }

                    $column = '';
                    if ($FormID == $formIdSamplingData) {
                        $column = "NumberOfRecord";
                    } else if ($FormID == $formIdMainData) {
                        $column = "NumberOfRecordForMainSurvey";
                    }

                    $location = "&DivisionCode=" . $DivisionCode . "&DistrictCode=" . $DistrictCode . "&UpazilaCode=" . $UpazilaCode . "&UnionWardCode=" . $UnionWardCode . "&MauzaCode=" . $MauzaCode . "&VillageCode=" . $VillageCode;

                    $dataURL = $baseURL . 'Reports/ajax-data/data-growth-by-psu-ajax-data.php?fid=' . $FormID . '&column=' . $column . '&loggedUserCompanyID=' . $loggedUserCompanyID . $location;

                ?>
                    <section class="card">
                        <div class="card-header">
                            <div class="card-title">Data Status : <?php echo $FormName; ?></div>
                            <div class="card-subtitle"><?php echo $DivisionName . $DistrictName . $UpazilaName . $UnionWardName . $MauzaName . $VillageName; ?></div>
                            <div class="form-group ml-2 row col-lg-1 " style="margin-left: 1px; margin-top:20px;">
                                <button class="btn ml-2 btn-success"
                                    onclick="exportTableToExcel('DataGrowthByPSU', 'DataGrowthReportByPSU')">
                                    Download
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped" id="DataGrowthByPSU" style="table-layout: fixed; width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">SL</th>
                                        <th>Division Name</th>
                                        <th>District Name</th>
                                        <th>User</th>
                                        <th>Mobile No</th>
                                        <th>PSU</th>
                                        <th>Target</th>
                                        <th>Collected</th>
                                        <th>Remaining</th>
                                        <th>Progress</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </section>
                <?php
                }
                ?>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        
        var dataTable = $('#DataGrowthByPSU').DataTable({
			dom: '<"row"<"col-lg-6"l><"col-lg-6"f>><"table-responsive"t>p',
			bProcessing: true,
			sAjaxSource: "<?php echo $dataURL; ?>",
            "columns": [
                { "data": null },
                { "data": 0 },
                { "data": 1 },
                { "data": 2 },
                { "data": 3 },
                { "data": 4 },
                { "data": 5 },
                { "data": 6 },
                { "data": 7 },
                { "data": 8 },
            ],
            columnDefs: [{ orderable: false, targets: 0 }],
            "rowCallback": function(row, data, index) {
                var pageInfo = dataTable.page.info();
                var slNumber = pageInfo.start + index + 1;
                $('td:eq(0)', row).html(slNumber);
            }
		});
    });
</script>

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

<style>
    #DataGrowthByPSU th:first-child,
    #DataGrowthByPSU td:first-child {
        width: 40px !important;   /* adjust as needed */
        text-align: center;       /* optional for better look */
        white-space: nowrap;      /* prevents wrapping */
    }
</style>
