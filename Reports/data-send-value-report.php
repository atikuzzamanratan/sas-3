<?php
$qryFormName = "SELECT id, FormName FROM datacollectionform WHERE Status = 'Active' AND CompanyID = ? ORDER BY id ASC";
$rsQryFormName = $app->getDBConnection()->fetchAll($qryFormName, $loggedUserCompanyID);
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

    $FormID = xss_clean($_REQUEST['FormID']);
    $columnName = xss_clean($_REQUEST['columnName']);
    $SelectedUserID = xss_clean($_REQUEST['SelectedUserID']);

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
                                        id="FormID" title="Please select a form" required
                                        onchange="getColumnName(document.getElementById('FormID').value)">
                                        <option>Choose a Form</option>
                                            <?PHP
                                            foreach ($rsQryFormName as $row) {
                                                echo '<option value="' . $row->id . '"' . (isset($FormID) && !empty($FormID) && $FormID == $row->id ? 'selected' : '') . '>' . $row->FormName . '</option>';
                                            }
                                            ?>
                                       
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Column Select<span
                                        class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="columnName"
                                        id="columnName" title="Please select a column" required>
                                        <option value="">Choose a column</option>
                                        <?php
                                        $qry = "SELECT ColumnName, ColumnLabel FROM xformcolumnname WHERE FormId = ?";
                                        $resQry = $app->getDBConnection()->fetchAll($qry, $formIdMainData);
                                        foreach ($resQry as $row) {
                                            echo '<option value="' . $row->ColumnName . '"' . (isset($columnName) && !empty($columnName) && $columnName == $row->ColumnName ? 'selected' : '') . '>' . $row->ColumnName . ' (' . $row->ColumnLabel . ')' . '</option>';
                                        }
                                        ?>
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

                                        onchange="ShowDropDown4('DivisionCode', 'DistrictDiv','userDiv', 'DistrictUser', ['DivisionCode'], {'RequiredUser':1})">
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
                                <label class="col-lg-3 control-label text-lg-end pt-2">User Select<span
                                        class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="SelectedUserID"
                                        id="SelectedUserID" required title="Please select user">
                                        <option value="">Choose a User</option>
                                        <?PHP
                                        if ($loggedUserName == 'admin') {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName <> '$loggedUserName' ORDER BY UserName ASC");
                                        } else if (strpos($loggedUserName, 'admin') !== false) {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '%$dataCollectorNamePrefix%' AND CompanyID = ? ORDER BY UserName ASC", $loggedUserCompanyID);
                                        } else if ($SuperID) {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT u.id, u.UserName, u.FullName FROM assignsupervisor AS a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND a.SupervisorID = ?", $loggedUserID);
                                        } else if (strpos($loggedUserName, 'dist') !== false) {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT u.id, u.UserName, u.FullName FROM assignsupervisor AS a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND a.DistCoordinatorID = ?", $loggedUserID);
                                        } else if (strpos($loggedUserName, 'val') !== false) {
                                            if (strpos($loggedUserName, 'cval') === false) {
                                                $qryDistUser = $app->getDBConnection()->query("SELECT u.id, u.UserName, u.FullName FROM assignsupervisor AS a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND a.ValidatorID = ?", $loggedUserID);
                                            } else {
                                                $qryDistUser = $app->getDBConnection()->query("SELECT u.id, u.UserName, u.FullName FROM assignsupervisor AS a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1");
                                            }
                                        } else {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND CompanyID= ? AND UserName = ? ORDER BY UserName ASC", $loggedUserCompanyID, $loggedUserName);
                                        }

                                        foreach ($qryDistUser as $row) {
                                            echo '<option value="' . $row->id . '"' . (isset($SelectedUserID) && !empty($SelectedUserID) && $SelectedUserID == $row->id ? 'selected' : '') . '>' . $row->UserName . ' | ' . substr($row->FullName, 0, 102) . '</option>';
                                        }
                                        ?>

                                    </select>
                                </div>
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
                    <script type="text/javascript">
                        function getColumnName(formID, columnName) {
                            $.ajax({
                                url: "Reports/get-column-name-list.php",
                                method: "GET",
                                datatype: "html",
                                data: {
                                    formID: formID,
                                    columnName: columnName
                                },
                                success: function(response) {
                                    //alert(response);
                                    $('#columnName').html(response);
                                }
                            });
                            return false;
                        }
                    </script>
                </section>
                <?php
                if ($_REQUEST['show'] === 'Show') {
                    $ColumnName = $_REQUEST['columnName'];
                    $UserID = $_REQUEST['SelectedUserID'];

                    $dataURL = $baseURL . 'Reports/ajax-data/data-send-value-report-ajax-data.php?frmID=' . $FormID . '&uid=' . $UserID . '&colName=' . $ColumnName;

                    //Modified the Logic to show the column name
                    $SelectedColumnName = ucwords(str_replace("_", " ", $ColumnName));
                ?>
                    <section class="card">
                        <div class="card-header">
                            <div class="card-subtitle"><?php echo $DivisionName . $DistrictName . $UpazilaName . $UnionWardName . $MauzaName . $VillageName; ?></div>
                            <div class="form-group ml-2 row col-lg-1 " style="margin-left: 1px; margin-top:20px;">
                                <button class="btn ml-2 btn-success"
                                    onclick="exportTableToExcel('datatable-ajax', 'DataSendValueReport')">
                                    Download
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped" id="datatable-ajax"
                                data-url="<?php echo $dataURL; ?>">
                                <thead>
                                    <tr>
                                        <th>SL</th>
                                        <!-- <th>Division Name</th> -->
                                        <!-- <th>District Name</th> -->
                                        <th>User</th>
                                        <th>Mobile No</th>
                                        <th>Record ID</th>
                                        <th><?php echo $SelectedColumnName; ?></th>
                                        <th>Column Value</th>
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

<script>
    $(document).ready(function() {
        // Initial population on page load
        populateDropdowns(
            <?php echo isset($DivisionCode) && $DivisionCode !== '' ? $DivisionCode : 'null'; ?>,
            <?php echo isset($DistrictCode) && $DistrictCode !== '' ? $DistrictCode : 'null'; ?>,
            <?php echo isset($UpazilaCode) && $UpazilaCode !== '' ? $UpazilaCode : 'null'; ?>,
            <?php echo isset($UnionWardCode) && $UnionWardCode !== '' ? $UnionWardCode : 'null'; ?>,
            <?php echo isset($MauzaCode) && $MauzaCode !== '' ? $MauzaCode : 'null'; ?>,
            <?php echo isset($VillageCode) && $VillageCode !== '' ? $VillageCode : 'null'; ?>
        );

        getColumnName(<?php echo isset($FormID) && $FormID !== '' ? $FormID : 'null'; ?>, <?php echo isset($ColumnName) && $ColumnName !== '' ? "'" . $ColumnName . "'" : 'null'; ?>);
    });
</script>