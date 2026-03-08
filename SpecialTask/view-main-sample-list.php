<?php
// -------------------------------------------
// Fetch Supervisor Permissions and ID
// -------------------------------------------
$qrySupervisor = "SELECT id, EditPermission, DeletePermission, ApprovePermission FROM assignsupervisor WHERE SupervisorID = ?";
$resQrySupervisor = $app->getDBConnection()->fetch($qrySupervisor, $loggedUserID);
$SuperID = $resQrySupervisor->id;

// -------------------------------------------
// Capture Selected UserID and PSU ID from Request (if form submitted)
// -------------------------------------------
if ($_REQUEST['show'] === 'Show') {
    $SelectedUserID = $_REQUEST['UserID'];
    $SelectedPSUID = $_REQUEST['SelectedPSUID'];
}
?>
<!-- Main Content Wrapper -->
<div class="inner-wrapper">
    <section role="main" class="content-body">

        <!-- Page Header with Dynamic Menu Label and Home Button -->
        <header class="page-header">
            <h2><?php echo $MenuLebel; ?></h2>

            <?php include_once 'Components/header-home-button.php'; ?>
        </header>

        <!-- Start of Page Content -->
        <div class="row">
            <div class="col-lg-12 mb-0">

                <!-- User & PSU Selection Form Card -->
                <section class="card">
                    <div class="card-body">
                        <form class="form-horizontal form-bordered" action="" method="post">

                            <!-- User Select Dropdown -->
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2">
                                    User Select<span class="required">*</span>
                                </label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="UserID" id="UserID"
                                            onchange="getPSUID(document.getElementById('UserID').value)" required>
                                        <option value="">Select User</option>

                                        <?php
                                        // Populate User dropdown based on logged in user role and permissions
                                        if ($loggedUserName == 'admin') {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName <> '$loggedUserName' ORDER BY UserName ASC");
                                        } else if (strpos($loggedUserName, 'admin') !== false) {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '%$dataCollectorNamePrefix%'  AND CompanyID = ? ORDER BY UserName ASC", $loggedUserCompanyID);
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

                                        // Output <option> for each user with selected attribute if matches previously selected
                                        foreach ($qryDistUser as $row) {
                                            echo '<option value="' . $row->id . '" ' . (isset($SelectedUserID) && $SelectedUserID == $row->id ? 'selected' : '') . '>' . $row->UserName . ' | ' . substr($row->FullName, 0, 102) . '</option>';
                                        }
                                        ?>

                                    </select>
                                </div>
                            </div>

                            <!-- PSU Select Dropdown (Populated dynamically via JS AJAX) -->
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">
                                    PSU Select<span class="required">*</span>
                                </label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="SelectedPSUID" id="SelectedPSUID" title="Please select a psu" required>
                                        <!-- Options populated by AJAX -->
                                    </select>
                                </div>
                            </div>

                            <!-- Form Submit Button -->
                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-primary" name="show" type="submit" id="show" value="Show">
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </section>

                <?php
                // -------------------------------------------
                // After form submission, show the sample list for selected User and PSU
                // -------------------------------------------
                if ($_REQUEST['show'] === 'Show') {
                    $UserID = $_REQUEST['UserID'];

                    // Retrieve selected user's username and full name for display
                    $SelectedUserName = getValue('userinfo', 'UserName', "id = $UserID");
                    $SelectedUserFullName = getValue('userinfo', 'FullName', "id = $UserID");
                    $SelectedUserData = "$SelectedUserFullName ($SelectedUserName/$UserID)";

                    // URL for fetching AJAX sample list data
                    $dataURL = $baseURL . 'SpecialTask/ajax-data/sample-list-ajax-data.php?ui=' . $UserID . '&psu=' . $SelectedPSUID;
                ?>
                    <!-- Sample List Display Card -->
                    <section class="card">
                        <header class="card-header">
                            <div class="card-title">User : <?php echo $SelectedUserData; ?></div>
                            <div class="form-group ml-2 row col-lg-1 " style="margin-left: 1px; margin-top:20px;">
                                <button class="btn ml-2 btn-success" onclick="exportTableToExcel('datatable-ajax', 'MainSampleListReport')">
                                    Download
                                </button>
                            </div>
                        </header>

                        <div class="card-body">
                            <table class="table table-bordered table-striped" id="datatable-ajax" data-url="<?php echo $dataURL; ?>">
                                <thead>
                                    <tr>
                                        <th>ক্রমিক নং</th>
                                        <th>রেকর্ড আইডি</th>
                                        <th>পিএসইউ</th>
                                        <th>বিভাগ</th>
                                        <th>জেলা</th>
                                        <th>খানা প্রধানের নাম</th>
                                        <th>মোবাইল</th>
                                        <th>ঠিকানা</th>
                                        <th>নমুনা খানা নম্বর</th>
                                        <th>লিস্টিং খানার নম্বর</th>
                                        <th>খানার অবস্থান</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data populated via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </section>
                    <!-- End: Sample List Display -->
                <?php
                }
                ?>

            </div>
        </div>
        <!-- End of Page Content -->

    </section>
</div>

<!--
    JavaScript Section
    - Function getPSUID: Fetch PSU options dynamically based on selected User via AJAX.
    - On page load, if a user is selected, pre-populate PSU dropdown.
-->
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
            success: function(response) {
                // Populate the PSU dropdown with response HTML
                $('#SelectedPSUID').html(response);
            }
        });
        return false;
    }

    $(document).ready(function() {
        // On page load, if UserID is already selected, fetch and fill PSU dropdown
        if ($('#UserID').val() !== '') {
            getPSUID($('#UserID').val(), '<?php echo isset($SelectedPSUID) && $SelectedPSUID !== '' ? $SelectedPSUID : ''; ?>');
        }
    });
</script>

<!--
    JavaScript DeleteItem function:
    - Confirms deletion from user
    - Sends AJAX request to delete item from database table assignformtoagent
    - Reloads page after deletion
-->
<script type="text/javascript">
    function DeleteItem(id, data) {
        if (confirm("Are you sure to delete this data?")) {
            $.ajax({
                url: "Form/delete.php",
                method: "GET",
                datatype: "json",
                data: {
                    id: id,
                    tbl: 'assignformtoagent'
                },
                success: function(response) {
                    alert(response);
                    window.location.reload();
                }
            });
        }
        return false;
    }
</script>
