<?php
$qrySupervisor = "SELECT id FROM assignsupervisor WHERE SupervisorID = ?";
$rsSupervisor = $app->getDBConnection()->fetch($qrySupervisor, $loggedUserID);
$SuperID = $rsSupervisor->id;

if (strpos($loggedUserName, 'dist') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = ? AND a.DistCoordinatorID = ?";
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

if ($_REQUEST['show'] === 'Show') {

    $DivisionCode = xss_clean($_REQUEST['DivisionCode']);
    $DistrictCode = xss_clean($_REQUEST['DistrictCode']);
    $UpazilaCode = xss_clean($_REQUEST['UpazilaCode']);
    $UnionWardCode = xss_clean($_REQUEST['UnionWardCode']);
    $MauzaCode = xss_clean($_REQUEST['MauzaCode']);
    $VillageCode = xss_clean($_REQUEST['VillageCode']);
}
?>

<?php
$sql = "SELECT PSU FROM SampleMapping GROUP BY PSU ORDER BY PSU ASC";
$rows = $app->getDBConnection()->query($sql);
$psus = array();
foreach ($rows as $row) {
    $psus[] = $row->PSU;
}
$psuList = implode(",", $psus);
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
                                <label class="col-lg-3 control-label text-sm-end pt-2">Division Select
                                    <?php if (strpos($loggedUserName, 'admin') === false) { ?>
                                        <span class="required">*</span>
                                    <?php } ?>
                                </label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="DivisionCode"
                                        id="DivisionCode"

                                        onchange="ShowDropDown4('DivisionCode', 'DistrictDiv','userDiv', 'DistrictUser', ['DivisionCode'])">
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


                ?>

                    <input type="hidden" id="DistrictCodeSelected" value="<?php echo $DistrictCode; ?>">
                    <input type="hidden" id="UpazilaCodeSelected" value="<?php echo $UpazilaCode; ?>">
                    <input type="hidden" id="UnionWardCodeSelected" value="<?php echo $UnionWardCode; ?>">
                    <input type="hidden" id="MauzaCodeSelected" value="<?php echo $MauzaCode; ?>">
                    <input type="hidden" id="VillageCodeSelected" value="<?php echo $VillageCode; ?>">
                    <br>

                    <section class="card">


                        <header class="card-header">
                            <div class="card-subtitle"></div>
                            <div class="card-subtitle"></div>
                            <div class="card-subtitle"></div>
                            <div class="card-title">Total Sample List</div>
                            <div class="card-subtitle"><?php echo "Total Number of PSU : <b>" . count($psus) . '</b>'; ?></div>
                            <div class="form-group ml-2 row col-lg-1 " style="margin-left: 1px; margin-top:20px;">
                                <button class="btn ml-2 btn-success"
                                    onclick="exportTableToExcel('ViewTotalSampleList', 'TotalSampleListReport')">
                                    Download
                                </button>
                            </div>
                        </header>
                        <div class="card-body">
                            <div class="table-responsive table-container">
                                <table class="table table-responsive-lg table-bordered table-striped table-sm mb-0" id="ViewTotalSampleList">
                                    <thead>
                                        <tr>
                                            <th>Division Name</th>
                                            <th>District Name</th>
                                            <th>Number of PSU</th>
                                            <th>PSU List</th>
                                        </tr>
                                    </thead>
                                    <!-- <tbody> -->
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <td></td>
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
        var body = $('body');

        var DivisionCode = body.find('#DivisionCode').find(":selected").val();

        var DistrictCode = body.find('#DistrictCodeSelected').val(),
            UpazilaCode = body.find('#UpazilaCodeSelected').val(),
            UnionWardCode = body.find('#UnionWardCodeSelected').val(),
            MauzaCode = body.find('#MauzaCodeSelected').val(),
            VillageCode = body.find('#VillageCodeSelected').val();

        var dataTable = body.find('#ViewTotalSampleList').DataTable({
            "aLengthMenu": [
                [20, 10, 50, 100, 500, 1000, 5000, 100000000],
                [20, 10, 50, 100, 500, 1000, 5000, "All"]
            ],
            "processing": true,
            "serverSide": true,
            "ajax": {
                data: {
                    DivisionCode: DivisionCode,
                    DistrictCode: DistrictCode,
                    UpazilaCode: UpazilaCode,
                    UnionWardCode: UnionWardCode,
                    MauzaCode: MauzaCode,
                    VillageCode: VillageCode
                },
                url: "<?php echo $dataURL = $baseURL . "SpecialTask/ajax-data/view-total-sample-list-ajax-data.php"; ?>",
                type: "POST"
            }
        });
    });
</script>

<script>
    populateDropdowns(
        <?php echo isset($DivisionCode) && $DivisionCode !== '' ? $DivisionCode : 'null'; ?>,
        <?php echo isset($DistrictCode) && $DistrictCode !== '' ? $DistrictCode : 'null'; ?>,
        <?php echo isset($UpazilaCode) && $UpazilaCode !== '' ? $UpazilaCode : 'null'; ?>,
        <?php echo isset($UnionWardCode) && $UnionWardCode !== '' ? $UnionWardCode : 'null'; ?>,
        <?php echo isset($MauzaCode) && $MauzaCode !== '' ? $MauzaCode : 'null'; ?>,
        <?php echo isset($VillageCode) && $VillageCode !== '' ? $VillageCode : 'null'; ?>
    );
</script>