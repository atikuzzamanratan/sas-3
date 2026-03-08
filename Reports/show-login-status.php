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

    $SelectedStatus = xss_clean($_REQUEST['SelectedStatus']);

    $DivisionCode = xss_clean($_REQUEST['DivisionCode']);
    $DistrictCode = xss_clean($_REQUEST['DistrictCode']);
    $UpazilaCode = xss_clean($_REQUEST['UpazilaCode']);
    $UnionWardCode = xss_clean($_REQUEST['UnionWardCode']);
    $MauzaCode = xss_clean($_REQUEST['MauzaCode']);
    $VillageCode = xss_clean($_REQUEST['VillageCode']);

    $SelectedUserID = xss_clean($_REQUEST['SelectedUserID']);
    $StartDate = xss_clean($_REQUEST['startDate']);
    $EndDate = xss_clean($_REQUEST['endDate']);

    $CheckAll = xss_clean($_REQUEST['chkAll']);
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
                                <label class="col-lg-3 control-label text-sm-end pt-2">Login Status<span
                                        class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="SelectedStatus" name="SelectedStatus"
                                        class="form-control populate" required>
                                        <optgroup label="Select Status">
                                            <option value="1" <?php echo (isset($SelectedStatus) && $SelectedStatus == '1' ? 'selected' : ''); ?>>Login</option>
                                            <option value="0" <?php echo (isset($SelectedStatus) && $SelectedStatus == '0' ? 'selected' : ''); ?>>Logout</option>
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

                                        onchange="ShowDropDown4('DivisionCode', 'DistrictDiv','userDiv', 'DistrictUser', ['DivisionCode'], {'RequiredUser':0})">
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
                            <div class="form-group row pb-3" id="userDiv">
                                <label class="col-lg-3 control-label text-sm-end pt-2">User Select</label>
                                <div class="col-lg-6">
                                    <?php if (strpos($loggedUserName, 'admin') === false) { ?>
                                        <select data-plugin-selectTwo class="form-control populate"
                                            name="SelectedUserID"
                                            id="SelectedUserID" title="Please select user" required>
                                        <?php } else { ?>
                                            <select data-plugin-selectTwo class="form-control populate"
                                                name="SelectedUserID"
                                                id="SelectedUserID" title="Please select user">
                                            <?php } ?>
                                            <option value="">Choose user</option>
                                            <?PHP
                                            if ($loggedUserName == 'admin') {
                                                $qryDistUser = "SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 ORDER BY UserName ASC";
                                                $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser);
                                            } else if (strpos($loggedUserName, 'admin') !== false) {
                                                $qryDistUser = "SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND CompanyID = ? ORDER BY UserName ASC";
                                                $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserCompanyID);
                                            } else if ($SuperID) {
                                                $qryDistUser = "SELECT u.id, u.UserName, u.FullName FROM assignsupervisor as a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND a.SupervisorID = ?";
                                                $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserID);
                                            } else if (strpos($loggedUserName, 'dist') !== false) {
                                                $qryDistUser = "SELECT u.id, u.UserName, u.FullName FROM assignsupervisor as a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND a.DistCoordinatorID = ?";
                                                $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserID);
                                            } else if (strpos($loggedUserName, 'val') !== false) {
                                                if (strpos($loggedUserName, 'cval') === false) {
                                                    $qryDistUser = "SELECT u.id, u.UserName, u.FullName FROM assignsupervisor as a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND a.ValidatorID = ?";
                                                    $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserID);
                                                } else {
                                                    $qryDistUser = "SELECT u.id, u.UserName, u.FullName FROM assignsupervisor as a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1";
                                                    $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser);
                                                }
                                            } else {
                                                $qryDistUser = "SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND CompanyID = ? and UserName = ? ORDER BY UserName ASC";
                                                $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserCompanyID, $loggedUserName);
                                            }

                                            foreach ($resQryDistUser as $row) {
                                                echo '<option value="' . $row->id . '"' . (isset($SelectedUserID) && $row->id == $SelectedUserID ? 'selected' : '') . '>' . $row->UserName . ' | ' . substr($row->FullName, 0, 102) . '</option>';
                                            }
                                            ?>
                                            </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2">Date range<span class="required">*</span></label>
                                <div class="col-lg-6">
                                    <div class="input-daterange input-group">
                                        <input type="date" class="form-control" id="startDate"
                                            name="startDate" required value="<?php echo isset($StartDate) ? $StartDate : ''; ?>">
                                        <span class="input-group-text border-start-0 border-end-0 rounded-0">to</span>
                                        <input type="date" class="form-control" id="endDate" name="endDate" required value="<?php echo isset($EndDate) ? $EndDate : ''; ?>">
                                    </div>
                                </div>
                            </div>


                            <?php if (strpos($loggedUserName, 'admin') !== false) { ?>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-sm-end pt-2"></label>
                                    <div class="col-lg-6">
                                        <div class="checkbox-custom checkbox-warning">
                                            <input id="chkAll" value="chkAll" type="checkbox" name="chkAll" <?php echo (isset($CheckAll) && $CheckAll == 'chkAll' ? 'checked' : ''); ?> />
                                            <label for="chkAll">All Users</label>
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

                    if ($CheckAll == 'chkAll') {
                        $SelectedCheckAll = 1;
                    } else {
                        $SelectedCheckAll = 0;
                    }

                    if (!empty($StartDate)) {
                        $SelectedStartDate = date('Y-m-d', strtotime($StartDate)) . $defaultStartTimeString;
                    }
                    if (!empty($EndDate)) {
                        $SelectedEndDate = date('Y-m-d', strtotime($EndDate)) . $defaultEndTimeString;
                    }
                    if (empty($SelectedUserID) && empty($CheckAll)) {
                        MsgBox('Please select All Users or a specific User.');
                        ReloadPage();
                    } else {
                ?>

                        <input type="hidden" id="DistrictCodeSelected" value="<?php echo $DistrictCode; ?>">
                        <input type="hidden" id="UpazilaCodeSelected" value="<?php echo $UpazilaCode; ?>">
                        <input type="hidden" id="UnionWardCodeSelected" value="<?php echo $UnionWardCode; ?>">
                        <input type="hidden" id="MauzaCodeSelected" value="<?php echo $MauzaCode; ?>">
                        <input type="hidden" id="VillageCodeSelected" value="<?php echo $VillageCode; ?>">

                        <input type="hidden" id="DataUserID" value="<?php echo $SelectedUserID; ?>">
                        <input type="hidden" id="DataChkAll" value="<?php echo $SelectedCheckAll; ?>">
                        <input type="hidden" id="DataStatus" value="<?php echo $SelectedStatus; ?>">
                        <input type="hidden" id="LoggedUserName" value="<?php echo $loggedUserName; ?>">
                        <input type="hidden" id="LoggedUserID" value="<?php echo $loggedUserID; ?>">
                        <input type="hidden" id="DataStartDate"
                            value="<?php if (!empty($SelectedStartDate)) echo $SelectedStartDate; ?>">
                        <input type="hidden" id="DataEndDate"
                            value="<?php if (!empty($SelectedEndDate)) echo $SelectedEndDate; ?>">

                        <br>
                        <section class="card">
                            <div class="card-header">
                                <div class="card-subtitle"></div>
                                <div class="card-subtitle"></div>
                                <div class="card-subtitle"></div>
                                <div class="card-title"><?= ($SelectedStatus ? 'Login' : 'Logout'); ?> List</div>
                                <div class="card-subtitle"></div>
                                <div class="form-group ml-2 row col-lg-1 " style="margin-left: 1px; margin-top:20px;">
                                    <button class="btn ml-2 btn-success"
                                        onclick="exportTableToExcel('tblViewData', 'ShowLoginStatusReport')">
                                        Download
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped" id="tblViewData">
                                    <thead>
                                        <tr>
                                            <th>Division Name</th>
                                            <th>District Name</th>
                                            <th>User</th>
                                            <th>Mobile</th>
                                            <th>Date-Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                <?php
                    }
                }
                ?>
            </div>
        </div>
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

        var DataStatus = body.find('#DataStatus').val(),
            DataUserID = body.find('#DataUserID').val(),
            DataChkAll = body.find('#DataChkAll').val(),
            LoggedUserName = body.find('#LoggedUserName').val(),
            LoggedUserID = body.find('#LoggedUserID').val(),
            DataStartDate = body.find('#DataStartDate').val(),
            DataEndDate = body.find('#DataEndDate').val();

        var dataTable = body.find('#tblViewData').DataTable({
            "aLengthMenu": [
                [20, 10, 50, 100, 500, 1000, 5000, 100000000],
                [20, 10, 50, 100, 500, 1000, 5000, "All"]
            ],
            "processing": true,
            "serverSide": true,
            "ajax": {
                data: {
                    DataStatus: DataStatus,
                    DataUserID: DataUserID,
                    DataChkAll: DataChkAll,
                    LoggedUserName: LoggedUserName,
                    LoggedUserID: LoggedUserID,
                    DataStartDate: DataStartDate,
                    DataEndDate: DataEndDate,
                    DivisionCode: DivisionCode,
                    DistrictCode: DistrictCode,
                    UpazilaCode: UpazilaCode,
                    UnionWardCode: UnionWardCode,
                    MauzaCode: MauzaCode,
                    VillageCode: VillageCode
                },
                url: "<?php echo $dataURL = $baseURL . "Reports/ajax-data/view-login-status-ajax-data.php"; ?>",
                type: "POST"
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