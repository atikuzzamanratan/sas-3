<?php
$qrySupervisor = "SELECT id, EditPermission, DeletePermission, ApprovePermission FROM assignsupervisor WHERE SupervisorID = ?";
$resQrySupervisor = $app->getDBConnection()->fetch($qrySupervisor, $loggedUserID);
$SuperID = $resQrySupervisor->id;

if ($_REQUEST['show'] === 'Show') {
    $SelectedUserID = xss_clean($_REQUEST['SelectedUserID']);
    $SelectedPSUID = xss_clean($_REQUEST['SelectedPSUID']);
    $NumberOfSample = xss_clean($_REQUEST['NumberOfSample']);
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
                                <label class="col-lg-3 control-label text-lg-end pt-2">User Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="SelectedUserID"
                                            id="SelectedUserID"
                                            onchange="getPSUID(document.getElementById('SelectedUserID').value)"
                                            required>
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

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Sample per PSU<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <input type="number" class="form-control" id="NumberOfSample"
                                           name="NumberOfSample"
                                           value="" required>
                                    <!--<input type="number" class="form-control" id="NumberOfSample" name="NumberOfSample"
                                        value="<?php /*echo isset($NumberOfSample) ? $NumberOfSample : ''; */ ?>" required>-->
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
                                    $('#SelectedPSUID').html(response);
                                }
                            });
                            return false;
                        }

                        $(document).ready(function () {
                            getPSUID(<?php echo isset($SelectedUserID) ? $SelectedUserID : 'null'; ?>, <?php echo isset($SelectedPSUID) ? $SelectedPSUID : 'null'; ?>);
                        });
                    </script>
                </section>
                <?php
                if ($_REQUEST['show'] === 'Show') {

                    $SelectedUserName = getValue('userinfo', 'UserName', "id = $SelectedUserID");
                    $SelectedUserFullName = getValue('userinfo', 'FullName', "id = $SelectedUserID");
                    $SelectedUserData = "$SelectedUserFullName ($SelectedUserName/$SelectedUserID)";

                    $SelectedUserName = getValue('userinfo', 'UserName', "id = $SelectedUserID");
                    $SelectedUserFullName = getValue('userinfo', 'FullName', "id = $SelectedUserID");
                    $SelectedUserData = "$SelectedUserFullName ($SelectedUserName/$SelectedUserID)";

                    $CountData = getValue('SampleMapping', 'COUNT(id)', "CompanyID = $loggedUserCompanyID and PSU = $SelectedPSUID");

                    if ($CountData) {
                        MsgBox("Sample data for PSU $UserPSU is already exist");
                    } else {
                        $IsSampleChecked = getValue('PSUList', 'COUNT(*)', "CompanyID = $loggedUserCompanyID AND PSU = $SelectedPSUID AND IsSampleChecked = 1");
                        if ($IsSampleChecked == '0') {
                            ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Your sampling list checking is not ok! Please check your sampling list
                                    from
                                    <a href="<?php echo $baseURL ?>index.php?parent=CheckSampling">
                                        Check sampling
                                    </a>
                                    menu.
                                </strong>
                            </div>
                            <?php
                            exit();
                        }

                        $sql = "EXEC SamplingRecord";
                        if ($SelectedUserID != '') {
                            $sql .= " $SelectedUserID";
                        } else {
                            $sql .= " NULL";
                        }
                        if ($NumberOfSample != '') {
                            $sql .= ", $NumberOfSample";
                        } else {
                            $sql .= ",NULL";
                        }
                        if ($SelectedPSUID != '') {
                            $sql .= ", $SelectedPSUID";
                        } else {
                            $sql .= ",NULL";
                        }

                        $sql .= ", $loggedUserCompanyID";

                        $app->getDBConnection()->query($sql);

                        $sql2 = "SELECT XFormID, PSU, UserID, HHeadName, MobileNumber, HAddress, MainSampleHH, CumulativeNo, ListingHH, geopoint FROM ##Sample 
                                ORDER BY MainSampleHH, PSU ASC";

                        $rsSampleCheck = $app->getDBConnection()->fetchAll($sql2);
                        $CountSampleCheck = count($rsSampleCheck);

                        $SamplingDataArray = [];
                        $i = 1;
                        foreach ($rsSampleCheck as $row) {
                            $data = [];

                            $data['XFormID'] = $row->XFormID;
                            $data['PSU'] = $row->PSU;
                            $data['UserID'] = $row->UserID;
                            $data['HHeadName'] = $row->HHeadName;
                            $data['MobileNumber'] = $row->MobileNumber;
                            $data['HAddress'] = $row->HAddress;
                            $data['MainSampleHH'] = $row->MainSampleHH;
                            $data['CumulativeNo'] = $row->CumulativeNo;
                            $data['ListingHH'] = $row->ListingHH;
                            $data['geopoint'] = $row->geopoint;

                            $SamplingDataArray[] = $data;
                            $i++;
                        }

                        $_SESSION['SamplingDataArray'] = $SamplingDataArray;

                        if ($CountSampleCheck) {
                            $queryIntervalValue = "SELECT PSU, COUNT(id) AS CollectedData FROM xformrecord WHERE FormId = ? AND PSU = ? AND UserID = ? AND IsApproved = ? and CompanyID = ? GROUP BY PSU";
                            $IntervalRow = $app->getDBConnection()->fetch($queryIntervalValue, $formIdSamplingData, $SelectedPSUID, $SelectedUserID, 1, $loggedUserCompanyID);

                            $ApprovedCollectedDataVal = $IntervalRow->CollectedData;
                            $IntervalValueCount = round(($ApprovedCollectedDataVal / $NumberOfSample), 2);
                        }

                        $dataURL = $baseURL . "SpecialTask/ajax-data/view-sampling-data-ajax-data.php?interval=$IntervalValueCount";
                        ?>
                        <section class="card">
                            <header class="card-header">
                                <div class="card-title">User : <?php echo $SelectedUserData; ?> | PSU
                                    : <?php echo $SelectedPSUID; ?></div>
                                <!--   <div class="card-subtitle">Approved Data : <?php echo $ApprovedCollectedDataVal; ?></div>
                                <div class="card-subtitle">Sample Number : <?php echo $NumberOfSample; ?></div>
                                <div class="card-subtitle">Interval Value: <?php echo $IntervalValueCount; ?></div>
								-->
                            </header>
                            <div class="card-body" id="tableWrap">
                                <table class="table table-bordered table-striped" id="datatable-approveddata-ajax"
                                       data-url="<?php echo $dataURL; ?>">
                                    <thead>
                                    <tr>
                                        <th>SL</th>
                                        <th>Record ID</th>
                                        <th>PSU</th>
                                        <th>HH Head</th>
                                        <th>Mobile</th>
                                        <th>Address</th>
                                        <th>Sample HH</th>
                                        <th>Cumulative Interval</th>
                                        <th>Listing HH</th>
                                        <th>Location</th>
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
                <section class="card">
                    <div class="card-body">
                        <form class="form-horizontal form-bordered" action="" method="post">
                            <div class="form-group row pb-3">
                                <input type="hidden" id="UserID" name="UserID" value="<?php echo $SelectedUserID; ?>">
                                <input type="hidden" id="UserPSU" name="UserPSU" value="<?php echo $SelectedPSUID; ?>">
                            </div>
                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-warning" name="Process" type="submit" id="Process"
                                               value="Save Final Sampling">
                                    </div>
                                </div>
                            </footer>
                        </form>
                        <?php
                        if ($_REQUEST['Process'] === "Save Final Sampling") {
                            $UserID = $_REQUEST['UserID'];
                            $UserPSU = $_REQUEST['UserPSU'];

                            $testData = $_SESSION['SamplingDataArray'] ?? [];

                            $SamplingDataArray = $testData;

                            if (empty($SamplingDataArray)) {
                                echo "No data to insert.";
                                exit;
                            }

                            $insSql = '';

                            foreach ($SamplingDataArray as $sample) {
                                $insRecordID = $sample['XFormID'];
                                $insPSU = $sample['PSU'];
                                $insUserID = $sample['UserID'];
                                $insHHeadName = $sample['HHeadName'];
                                $insMobileNumber = $sample['MobileNumber'];
                                $insHAddress = $sample['HAddress'];
                                $insMainSampleHH = $sample['MainSampleHH'];
                                $insCumulativeNo = $sample['CumulativeNo'];
                                $insListingHH = $sample['ListingHH'];
                                $insgeopoint = $sample['geopoint'];

                                $insSql .= "INSERT INTO SampleMapping (XFormID, PSU, UserID, HHeadName, MobileNumber, HHAddress, MainHHNumber, IntervalValue, SampleHHNumber, geopoint, CompanyID) VALUES ('$insRecordID', '$insPSU', '$insUserID', N'$insHHeadName', '$insMobileNumber', N'$insHAddress', '$insMainSampleHH', '$insCumulativeNo', '$insListingHH', '$insgeopoint', $loggedUserCompanyID);";
                            }

                            if (InsertData($insSql)) {
                                $Msg = "Your sample has been genarated for PSU : $UserPSU";
                                $qry = "INSERT INTO Notification (FromUserID, ToUserID, Notification, Status, CompanyID) VALUES ($loggedUserID, $UserID, N'$Msg', 0, $loggedUserCompanyID)";
                                $app->getDBConnection()->query($qry);

                                MsgBox("Data Successfully Processed");
                                $redirectURL = $baseURL . "index.php?parent=ViewMainSampleHHList";
                                ReDirect($redirectURL);
                            }
                            unset($_SESSION['SamplingDataArray']);
                        }
                        ?>
                    </div>
                </section>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>