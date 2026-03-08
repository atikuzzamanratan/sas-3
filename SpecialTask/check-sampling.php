<?php
$qrySupervisor = "SELECT id, EditPermission, DeletePermission, ApprovePermission FROM assignsupervisor WHERE SupervisorID = ?";
$resQrySupervisor = $app->getDBConnection()->fetch($qrySupervisor, $loggedUserID);
$SuperID = $resQrySupervisor->id;

if($_REQUEST['show'] === 'Show'){
    $SelectedFormID = $_REQUEST['SelectedFormID'];
    $SelectedUserID = $_REQUEST['SelectedUserID'];
    $SelectedPSUID = $_REQUEST['SelectedPSUID'];
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
                                    <select data-plugin-selectTwo id="SelectedFormID" name="SelectedFormID"
                                            class="form-control populate" required>
                                        <optgroup label="Choose form">
                                            <?PHP
                                            $qryForm = $app->getDBConnection()->query("SELECT id, FormName FROM datacollectionform WHERE id=$formIdSamplingData and CompanyID = ?", $loggedUserCompanyID);

                                            foreach ($qryForm as $row) {
                                                echo '<option value="' . $row->id . '" ' . (isset($SelectedFormID) && $SelectedFormID == $row->id ? 'selected' : '') . '>' . $row->FormName . '</option>';
                                            }
                                            ?>
                                        </optgroup>

                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2">User Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="SelectedUserID"
                                            id="SelectedUserID"
                                            onchange="getPSUID(document.getElementById('SelectedUserID').value)" required>
                                        <option value="">Choose a User</option>
                                        <?PHP
                                        if ($loggedUserName == 'admin') {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName <> '$loggedUserName' ORDER BY UserName ASC");
                                        } else if (strpos($loggedUserName, 'admin') !== false) {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '%$dataCollectorNamePrefix%'  AND CompanyID = ? ORDER BY UserName ASC", $loggedUserCompanyID);
                                        } else if ($SuperID) {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT u.id, u.UserName, u.FullName FROM assignsupervisor AS a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND a.SupervisorID = ?", $loggedUserID);
                                        } else if (strpos($loggedUserName, 'dist') !== false) {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT u.id, u.UserName, u.FullName FROM assignsupervisor AS a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND a.DistCoordinatorID = ?", $loggedUserID);
                                        } else {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND CompanyID= ? AND UserName = ? ORDER BY UserName ASC", $loggedUserCompanyID, $loggedUserName);
                                        }

                                        foreach ($qryDistUser as $row) {
                                            echo '<option value="' . $row->id . '" ' . (isset($SelectedUserID) && $SelectedUserID == $row->id ? 'selected' : '') . '>' . $row->UserName . ' | ' . substr($row->FullName, 0, 102) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">PSU Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="SelectedPSUID"
                                            id="SelectedPSUID" title="Please select a psu" required>
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
                        function getPSUID(userId, psuId = '') {
                            $.ajax({
                                url: "../SpecialTask/get-psu-id-list-for-sampling.php",
                                method: "GET",
                                datatype: "html",
                                data: {
                                    userID: userId,
                                    psuID: psuId
                                },
                                success: function (response) {
                                    //alert(response);
                                    $('#SelectedPSUID').html(response);
                                }
                            });
                            return false;
                        }
                    </script>
                </section>
                <?php
                $NumberOfSample = '';
                $CountSampleCheck = '';
                $CountDuplicate = '';

                if ($_REQUEST['show'] === 'Show') {
                    $SelectedFormID = xss_clean($_REQUEST['SelectedFormID']);
                    $SelectedUserID = xss_clean($_REQUEST['SelectedUserID']);
                    $SelectedPSUID = xss_clean($_REQUEST['SelectedPSUID']);

                    $SelectedUserName = getValue('userinfo', 'UserName', "id = $SelectedUserID");
                    $SelectedUserFullName = getValue('userinfo', 'FullName', "id = $SelectedUserID");
                    $SelectedUserData = "$SelectedUserFullName ($SelectedUserName/$SelectedUserID)";

                    $CountData = getValue('SampleMapping', 'COUNT(id)', "CompanyID = $loggedUserCompanyID and PSU = $SelectedPSUID");

                    if ($CountData) {
                        MsgBox("Sample data for PSU $UserPSU is already exist");
                    } else {
                        ?>
                        <section class="card">
                            <header class="card-header">
                                <div class="card-title">PSU : <?php echo $SelectedPSUID; ?> | User
                                    : <?php echo $SelectedUserData; ?></div>
                            </header>
                            <div class="card-body">
                                <?php
                                /*$QuerySampleCheck = "select XFormrecordId, PSU, UserID, ColumnValue, CompanyID FROM masterdatarecord_Approved WHERE
                                ColumnName='list_no' AND USerID = ? AND FormId = ? AND PSU = ? AND ColumnValue like '%[^0-9]%'";*/

                                $QuerySampleCheck = "select id, PSU, UserID, SampleHHNo, CompanyID FROM xformrecord WHERE USerID = ? AND FormId = ? AND PSU =? AND SampleHHNo like '%[^0-9]%'";

                                $rsSampleCheck = $app->getDBConnection()->fetchAll($QuerySampleCheck, $SelectedUserID, $SelectedFormID, $SelectedPSUID);
                                $CountSampleCheck = count($rsSampleCheck);

                                if ($CountSampleCheck) {
                                    ?>
                                    <div class="panel-heading">
                                        <h4 style="color: red">Garbage data found!</h4>
                                    </div>
                                    <table class="table table-responsive-md table-bordered mb-0">
                                        <thead>
                                        <tr>
                                            <th>SL</th>
                                            <th>Record ID</th>
                                            <th>PSU</th>
                                            <th>Household List No</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($rsSampleCheck as $row) {
                                            $XFormrecordId = $row->id;
                                            $PSU = $row->PSU;
                                            $ColumnValue = $row->SampleHHNo; ?>
                                            <tr>
                                                <td><?php echo $i; ?></td>
                                                <td><?php echo $XFormrecordId; ?></td>
                                                <td><?php echo $PSU; ?></td>
                                                <td><?php echo $ColumnValue; ?></td>
                                            </tr>
                                            <?php
                                            $i++;
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                    <?php
                                }

                                $QueryDuplicate = "EXEC [HHListingDuplicateFinder] $SelectedUserID, $SelectedPSUID, $SelectedFormID;";
                                $rsDuplicate = db_query($QueryDuplicate, $cn);
                                $CountDuplicate = db_num_rows($rsDuplicate);

                                $DropTempTableQuery = "drop table ##tt; drop table ##t; ";
                                db_query($DropTempTableQuery, $cn);

                                if ($CountDuplicate) {
                                    ?>
                                    <div class="panel-heading">
                                        <h4 style="color: red">Duplicate data found!</h4>
                                    </div>
                                    <table class="table table-responsive-md table-bordered mb-0">
                                        <thead>
                                        <tr>
                                            <th>SL</th>
                                            <th>Record ID</th>
                                            <th>Duplicate Value</th>
                                            <th>Duplicate Record ID</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $i = 1;
                                        while ($row = db_fetch_array($rsDuplicate)) {
                                            $Id = $row['ID'];
                                            $DuplicateData = $row['DuplicateData'];
                                            $DuplicateDataID = $row['DuplicateDataID']; ?>
                                            <tr>
                                                <td><?php echo $i; ?></td>
                                                <td><?php echo $Id; ?></td>
                                                <td><?php echo $DuplicateData; ?></td>
                                                <td><?php echo $DuplicateDataID; ?></td>
                                            </tr>
                                            <?php
                                            $i++;
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                    <?php
                                }

                                if (!$CountSampleCheck) {
                                    /*$sqlCollectedHH = "SELECT CONVERT(int,ColumnValue) AS 'CollectedHH' FROM masterdatarecord_Approved
                                    WHERE UserID = ? AND PSU = ? AND FormId = ? AND IsApproved = ? AND ColumnName IN ('list_no')
                                    ORDER BY CONVERT(int,ColumnValue) ASC";*/

                                    $sqlCollectedHH = "SELECT CONVERT(int,SampleHHNo) AS 'CollectedHH' FROM xformrecord WHERE UserID = ? AND PSU = ? AND FormId = ? AND IsApproved = ? ORDER BY CONVERT(int,SampleHHNo) ASC";
                                    $rsCollectedHH = $app->getDBConnection()->fetchAll($sqlCollectedHH, $SelectedUserID, $SelectedPSUID, $SelectedFormID, 1);

                                    $arrayCollectedHH = array();

                                    foreach ($rsCollectedHH as $rowHH) {
                                        $arrayCollectedHH[] = $rowHH->CollectedHH;
                                    }

                                    $arrMinMax = range(1, max($arrayCollectedHH));

                                    $resultListHH = array_diff($arrMinMax, $arrayCollectedHH);

                                    $collectedHHList = implode(', ', $arrayCollectedHH);

                                    $missingHHList = implode(', ', $resultListHH);

                                    $totalEligibleHousehold = getValue('masterdatarecord_Approved', 'COUNT(*)', "PSU = $SelectedPSUID AND UserID = $SelectedUserID AND ColumnName = 'Is_Eligible' AND ColumnValue = '1'");
                                    ?>

                                    <div class="card-body">
                                        <div class="alert alert-default alert-dismissible fade show" role="alert">
                                            <strong>Collected HouseHold No</strong> : <?php echo $collectedHHList; ?>
                                        </div>

                                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                            <strong>Missing HouseHold No</strong> :
                                            <?php
                                            if (count($resultListHH)) {
                                                echo $missingHHList;
                                            } else {
                                                echo "No missing household found.";
                                            }
                                            ?>
                                        </div>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <strong>Eligible Houshold</strong> : <?php echo $totalEligibleHousehold; ?>
                                        </div>
                                    </div>
                                    <?php
                                } else {
                                    $resultListHH = 0;
                                    ?>
                                    <div class="card-body">
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <strong><?php echo "Missing record unable to identify because of having garbage value in the list!"; ?></strong>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                                <div class="card-body">
                                    <?php
                                    if ((count($resultListHH) == 0) && ($CountSampleCheck < 1) && ($CountDuplicate == 0)) {
                                        /*$queryDataCheck = "SELECT COUNT(id) AS 'TotalData' FROM masterdatarecord_Approved
                                        WHERE UserID = ? AND PSU = ? AND FormId = ? AND IsApproved = ? AND ColumnName IN ('list_no')";*/

                                        $queryDataCheck = "SELECT COUNT(id) AS 'TotalData' FROM xformrecord WHERE UserID = ? AND PSU = ? AND FormId = ? AND IsApproved = ?";

                                        $CountRS = $app->getDBConnection()->fetch($queryDataCheck, $SelectedUserID, $SelectedPSUID, $SelectedFormID, 1);
                                        $CountData = $CountRS->TotalData;
                                        if ($CountData < 1) {
                                            ?>
                                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                                <strong>No household information was collected!</strong>
                                            </div>
                                            <?php
                                        } else {
                                            $UpdateCheckSamplingValue = "UPDATE PSUList SET IsSampleChecked = ? WHERE PSU = ?";
                                            $app->getDBConnection()->query($UpdateCheckSamplingValue, 1, $SelectedPSUID);
                                            ?>
                                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <strong>Sample checking process completed and found OK.</strong>
                                            </div>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <strong>Sample checking process completed and found error!</strong>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </section>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        getPSUID(<?php echo $SelectedUserID; ?>, <?php echo $SelectedPSUID; ?>);
    });
</script>