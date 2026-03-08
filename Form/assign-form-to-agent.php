<?php
if ($loggedUserName == 'admin') {
    $qryCompnay = "SELECT id, CompanyName FROM dataownercompany";
    $rsQryCompany = $app->getDBConnection()->fetchAll($qryCompnay);
} else {
    $qryCompnay = "SELECT id, CompanyName FROM dataownercompany WHERE id = ?";
    $rsQryCompany = $app->getDBConnection()->fetchAll($qryCompnay, $loggedUserCompanyID);
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
                                <label class="col-lg-3 control-label text-sm-end pt-2">Project<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="company" name="Project"
                                            class="form-control populate" title="Please select a Project" required>
                                        <optgroup label="Choose a Project">
                                            <?PHP
                                            foreach ($rsQryCompany as $row) {
                                                echo '<option value="' . $row->id . '">' . $row->CompanyName . '</option>';
                                            }
                                            ?>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2">Agent Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="SelectedUserID"
                                            id="SelectedUserID" required
                                            onchange="ShowDropDown('SelectedUserID', 'ShowFormGroupDiv', 'ShowFormGroup', 'ShowForm')">
                                        <option value="">Choose a user</option>
                                        <?PHP
                                        if ($loggedUserName == 'admin') {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName <> '$loggedUserName' ORDER BY UserName ASC");
                                        } else if (strpos($loggedUserName, 'admin') !== false) {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '%$dataCollectorNamePrefix%' AND CompanyID = ? ORDER BY UserName ASC", $loggedUserCompanyID);
                                            //$qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '%$dataCollectorNamePrefix%'  AND CompanyID = ? ORDER BY UserName ASC", $loggedUserCompanyID);
                                        } else if ($SuperID) {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT u.id, u.UserName, u.FullName FROM assignsupervisor AS a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND a.SupervisorID = ?", $loggedUserID);
                                        } else if (strpos($loggedUserName, 'dist') !== false) {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT u.id, u.UserName, u.FullName FROM assignsupervisor AS a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND a.DistCoordinatorID = ?", $loggedUserID);
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
                            <div class="form-group row pb-3" id="ShowFormGroupDiv"></div>
                            <div class="form-group row pb-3" id="ShowFormDiv"></div>

                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-primary" name="show" type="submit" id="show"
                                               value="Assign">
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </section>
                <?php
                if ($_REQUEST['show'] === 'Assign') {
                    $UserID = $_REQUEST['SelectedUserID'];
                    $GroupID = $_REQUEST['FormGroupId'];
                    $CompanyID = $_REQUEST['company'];

                    $perMenu = $_REQUEST["MP"];
                    $cond = "UserID = '$UserID' AND FormGroupId = '$GroupID'";

                    if (DeleteAgentForm('assignformtoagent', $cond)) {

                        if (Edit_MenuPerr($perMenu, $GroupID, $CompanyID, $UserID, $loggedUserName, $xFormDefaultProvisionDate)) {
                            MsgBox("Data saved successfully.");
                        } else {
                            MsgBox("Failed to save data!");
                        }
                    }
                }
                ?>
                <!-- end: page -->
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

