<?php
$qryFormName = "SELECT id, FormName FROM datacollectionform WHERE Status = 'Active' AND CompanyID = ? ORDER BY id ASC";
$rsQryFormName = $app->getDBConnection()->fetchAll($qryFormName, $loggedUserCompanyID);
$qrySupervisor = "SELECT id FROM assignsupervisor WHERE SupervisorID = ?";
$rsSupervisor = $app->getDBConnection()->fetch($qrySupervisor, $loggedUserID);
$SuperID = $rsSupervisor->id;

if (strpos($user, 'dist') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = ? AND a.DistCoordinatorID = ?";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID, $loggedUserID);
} else {
    $divQuery = "SELECT DISTINCT DivisionName , DivisionCode FROM PSUList WHERE CompanyID = ? ORDER BY DivisionName ASC";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID);
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
                                        <option value="">Select Form</option>
                                        <?PHP
                                        foreach ($rsQryFormName as $row) {
                                            echo '<option value="' . $row->id . '">' . $row->FormName . '</option>';
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
                                        <?php if (strpos($loggedUserName, 'admin') === false) { ?>
                                        required
                                        <?php } ?>
                                        onchange="ShowDropDown('DivisionCode', 'DistrictDiv', 'ShowDistrict', 'ShowUpazila')">
                                        <option value="">Choose division</option>
                                        <?PHP
                                        foreach ($rsDivQuery as $row) {
                                            echo '<option value="' . $row->DivisionCode . '">' . $row->DivisionName . '</option>';
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
                            <script>
                                $(document).ready(function() {
                                    $('#DivisionCode').on('change', function() {
                                        //alert('helllo Tessst');
                                        if (this.value > 1) {
                                            $("#geoDiv").show();
                                        } else {
                                            $("#geoDiv").hide();
                                        }
                                    });
                                });
                            </script>
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">or User Select<span
                                        class="required"></span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate"
                                        name="SelectedUserID"
                                        id="SelectedUserID" title="Please select user">
                                        <option label="" value="">Choose user</option>
                                        <?PHP
                                        if ($loggedUserName == 'admin') {
                                            $qryDistUser = "SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '$dataCollectorNamePrefix%' ORDER BY UserName ASC";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser);
                                        } else if (strpos($loggedUserName, 'admin') !== false) {
                                            $qryDistUser = "SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '$dataCollectorNamePrefix%' AND CompanyID = ? ORDER BY UserName ASC";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserCompanyID);
                                        } else if ($SuperID) {
                                            $qryDistUser = "SELECT u.id, u.UserName, u.FullName FROM assignsupervisor as a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND u.UserName LIKE '$dataCollectorNamePrefix%' AND a.SupervisorID = ?";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserID);
                                        } else if (strpos($loggedUserName, 'dist') !== false) {
                                            $qryDistUser = "SELECT u.id, u.UserName, u.FullName FROM assignsupervisor as a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND u.UserName LIKE '$dataCollectorNamePrefix%' AND a.DistCoordinatorID = ?";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserID);
                                        } else {
                                            $qryDistUser = "SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '$dataCollectorNamePrefix%' AND CompanyID = ? and UserName = ? ORDER BY UserName ASC";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserCompanyID, $loggedUserName);
                                        }

                                        foreach ($resQryDistUser as $row) {
                                            echo '<option value="' . $row->id . '">' . $row->UserName . ' | ' . substr($row->FullName, 0, 102) . '</option>';
                                        }
                                        ?>

                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2">Date range<span
                                        class="required">*</span></label>
                                <div class="col-lg-6">
                                    <div class="input-daterange input-group">
                                        <input type="date" class="form-control" id="startDate"
                                            name="startDate" required>
                                        <span class="input-group-text border-start-0 border-end-0 rounded-0">to</span>
                                        <input type="date" class="form-control" id="endDate" name="endDate" required>
                                    </div>
                                </div>
                            </div>

                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-primary" name="show" type="submit" id="show"
                                            value="Show">
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </section>
                <?php
                if ($_REQUEST['show'] === 'Show') {
                    $FormID = xss_clean($_REQUEST['FormID']);
                    $DivisionCode = $_REQUEST['DivisionCode'];
                    $DistrictCode = $_REQUEST['DistrictCode'];
                    $UpazilaCode = $_REQUEST['UpazilaCode'];
                    $UnionWardCode = $_REQUEST['UnionWardCode'];
                    $MauzaCode = $_REQUEST['MauzaCode'];
                    $VillageCode = $_REQUEST['VillageCode'];
                    $SelectedUserID = $_REQUEST['SelectedUserID'];
                    $StartDate = xss_clean($_REQUEST['startDate']);
                    $EndDate = xss_clean($_REQUEST['endDate']);

                    $StartDateTime = date("Y-m-d", strtotime($StartDate)) . $defaultStartTimeString;
                    $EndDateTime = date("Y-m-d", strtotime($EndDate)) . $defaultEndTimeString;

                    $DataDate = "$StartDateTime to $EndDateTime";

                    $FormName = getValue('datacollectionform', 'FormName', "id = $FormID");

                    $MasterDataQuery = "SELECT ColumnName, ColumnLabel FROM xformcolumnname WHERE FormId = ? and ColumnName NOT IN 
                                        (SELECT X.ColumnName FROM xformcolumnname X	
                                        INNER JOIN (SELECT ColumnName FROM ModuleInfo WHERE FormId = ?) M ON X.ColumnName LIKE CONCAT(M.ColumnName, '%')) 
                                        ORDER BY id";
                    $MasterDataRS = $app->getDBConnection()->fetchAll($MasterDataQuery, $FormID, $FormID);

                    $TotalColumns = count($MasterDataRS);
                ?>

                    <section class="card">


                        <header class="card-header">
                            <div class="card-title">Data Report : <?php echo $FormName; ?></div>
                            <div class="card-subtitle">Total Columns : <?php echo $TotalColumns; ?></div>
                            <div class="card-subtitle">Date : <?php echo $DataDate; ?></div>
                            <div class="form-group ml-2 row col-lg-1 ">
                                <button class="btn ml-2 btn-success"
                                    onclick="exportTableToExcel('viewDataReport', 'DataReport-<?php echo $StartDateTime; ?>-<?php echo $EndDateTime; ?>')">
                                    Download
                                </button>
                            </div>
                        </header>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-responsive-lg table-bordered table-striped table-sm mb-0" id="viewDataReport">
                                    <thead>
                                        <tr>
                                            <th>Record ID</th>
                                            <th>User ID</th>
                                            <?php
                                            foreach ($MasterDataRS as $rowMD) {
                                                echo "<th>" . $rowMD->ColumnLabel . "</th>";
                                            }
                                            ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <?php
                                            $CallProcQuery = "spReport_MasterDataReport_Pivot '$FormID', '$StartDateTime', '$EndDateTime','$SelectedUserID','$DivisionCode','$DistrictCode','$UpazilaCode','$UnionWardCode','$MauzaCode','$VillageCode'";
                                            $CallProcOutput = db_query($CallProcQuery, $cn);
                                            $TotalRow = db_num_rows($CallProcOutput);

                                            while ($row = db_fetch_array($CallProcOutput)) {

                                                echo "<tr>";
                                                echo "<td>" . $row['FinalxID'] . "</td>";
                                                echo "<td>" . $row['FUserID'] . "</td>";

                                                foreach ($MasterDataRS as $ColumnName) {
                                                    if (is_null($row[$ColumnName['ColumnName']])) {
                                                        echo "<td>NULL</td>";
                                                    } else {
                                                        echo "<td>" . $row[$ColumnName['ColumnName']] . "</td>";
                                                    }
                                                }
                                                echo "</tr>";

                                                $totalRowsCount += 1;
                                            }

                                            ?>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <td><?php echo "&nbsp; Total Row : <b>" . $totalRowsCount . '</b>'; ?> </td>
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