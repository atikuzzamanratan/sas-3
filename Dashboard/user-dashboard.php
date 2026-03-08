<?php
if ($_GET['UserID'] != '') {
    $UserID = $app->cleanInput($_GET['UserID']);
}

if ($_GET['FormID'] != '') {
    $FormID = $app->cleanInput($_GET['FormID']);
}

$qrySupervisor = "SELECT id, EditPermission, DeletePermission, ApprovePermission FROM assignsupervisor WHERE SupervisorID = ?";
$resQrySupervisor = $app->getDBConnection()->fetch($qrySupervisor, $loggedUserID);
$SuperID = $resQrySupervisor->id;

if (is_null($FormID) || is_null($UserID)) {
    ?>

    <div class="inner-wrapper">
        <section role="main" class="content-body">
            <header class="page-header">
                <h2><?php echo $MenuLebel; ?></h2>

                <?php include_once 'Components/header-home-button.php'; ?>
            </header>

            <!-- start: page -->
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-8 mb-3">
                    <section class="card">
                        <div class="card-body">
                            <form class="form-horizontal form-bordered" action="" method="post">
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2">Form Select<span
                                                class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <select data-plugin-selectTwo class="form-control populate" name="FormID"
                                                id="FormID" required>
                                                <optgroup label="Select Form">
                                                <?PHP
                                                $userForms = $app->getDBConnection()->query('select distinct id, FormName from datacollectionform WHERE CompanyID = ?', $loggedUserCompanyID);

                                                foreach ($userForms as $row) {
                                                    echo '<option value="' . $row->id . '">' . $row->FormName . '</option>';
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
                                        <select data-plugin-selectTwo class="form-control populate" name="UserID"
                                                id="UserID" required>
                                            <option value="">Select User</option>
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
                                                echo '<option value="' . $row->id . '">' . $row->UserName . ' | ' . substr($row->FullName, 0, 102) . '</option>';
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
                                        </div>
                                    </div>
                                </footer>
                            </form>
                        </div>

                    </section>
                </div>
                <div class="col-lg-2"></div>
            </div>
            <!-- end: page -->
        </section>
    </div>
    <?php

    if ($_REQUEST['show'] === 'Show') {
        $FormID = $_REQUEST['FormID'];
        $UserID = $_REQUEST['UserID'];
        ReDirect("index.php?parent=UserDashboard&FormID=$FormID&UserID=$UserID");
    }
} else {
    $_SESSION["FORMID"] = $FormID;
    $_SESSION["USERID"] = $UserID;
    include 'show-user-dashboard.php';
}
