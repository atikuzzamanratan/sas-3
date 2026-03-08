<?php
if ($loggedUserName == 'admin') {
    $qryCompnay = "SELECT id, CompanyName FROM dataownercompany";
    $rsQryCompany = $app->getDBConnection()->fetchAll($qryCompnay);
} else {
    $qryCompnay = "SELECT id, CompanyName FROM dataownercompany WHERE id = ?";
    $rsQryCompany = $app->getDBConnection()->fetchAll($qryCompnay, $loggedUserCompanyID);
}

$qrySupervisor = "SELECT id FROM assignsupervisor WHERE ValidatorID = ?";
$resQrySupervisor = $app->getDBConnection()->fetch($qrySupervisor, $loggedUserID);
$SuperID = $resQrySupervisor->id;
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
                                    <select data-plugin-selectTwo id="company" name="company"
                                            class="form-control populate" title="Please select a project" required>
                                        <optgroup label="Choose a company">
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
                                <label class="col-lg-3 control-label text-sm-end pt-2">Validator Select</label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate"
                                            name="SelectedSupervisorID"
                                            id="SelectedSupervisorID" title="Please select validator">
                                        <option value="">Select validator</option>
                                        <?PHP
                                        if ($loggedUserName == 'admin') {
                                            $qrySup = "SELECT DISTINCT u.id, u.UserName, u.FullName FROM assignsupervisor AS a JOIN userinfo AS u ON a.ValidatorID = u.id ORDER BY u.UserName ASC";
                                            $resQrySup = $app->getDBConnection()->fetchAll($qrySup);
                                        } else if (strpos($loggedUserName, 'admin') !== false) {
                                            $qrySup = "SELECT DISTINCT u.id, u.UserName, u.FullName FROM assignsupervisor AS a JOIN userinfo AS u ON a.ValidatorID = u.id where u.CompanyID = ? ORDER BY u.UserName ASC";
                                            $resQrySup = $app->getDBConnection()->fetchAll($qrySup, $loggedUserCompanyID);
                                        } else if ($SuperID) {
                                            $qrySup = "SELECT DISTINCT u.id, u.UserName, u.FullName FROM assignsupervisor AS a JOIN userinfo AS u ON a.ValidatorID = u.id WHERE a.SupervisorID = ? ORDER BY u.UserName ASC";
                                            $resQrySup = $app->getDBConnection()->fetchAll($qrySup, $loggedUserID);
                                        } else if (strpos($loggedUserName, 'dist') !== false) {
                                            $qrySup = "SELECT DISTINCT u.id, u.UserName, u.FullName FROM assignsupervisor AS a JOIN userinfo AS u ON a.ValidatorID = u.id WHERE a.DistCoordinatorID = ? ORDER BY u.UserName ASC";
                                            $resQrySup = $app->getDBConnection()->fetchAll($qrySup, $loggedUserID);
                                        } else {
                                            $qrySup = "SELECT DISTINCT u.id, u.UserName, u.FullName FROM assignsupervisor AS a JOIN userinfo AS u ON a.ValidatorID = u.id WHERE u.CompanyID = ? AND a.SupervisorID = ? ORDER BY u.UserName ASC";
                                            $resQrySup = $app->getDBConnection()->fetchAll($qrySup, $loggedUserCompanyID, $loggedUserID);
                                        }

                                        foreach ($resQrySup as $row) {
                                            echo '<option value="' . $row->id . '">' . $row->UserName . ' | ' . substr($row->FullName, 0, 102) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <?php if (strpos($loggedUserName, 'admin') !== false) { ?>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-sm-end pt-2"></label>
                                    <div class="col-lg-6">
                                        <div class="checkbox-custom checkbox-warning">
                                            <input id="chkAll" value="chkAll" type="checkbox" name="chkAll"/>
                                            <label for="chkAll">All Validators</label>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

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
                    $SelectedCompanyID = $_REQUEST['company'];
                    $SelectedCompanyName = getValue('dataownercompany', 'CompanyName', "id = $SelectedCompanyID");
                    $SelectedSupervisorID = $_REQUEST['SelectedSupervisorID'];
                    $check = $_REQUEST['chkAll'];

                    if (empty($check) && empty($SelectedSupervisorID)) {
                        if (strpos($loggedUserName, 'admin') !== false) {
                            $msg = 'Please select all Validators or a specific Validator.';
                        } else {
                            $msg = 'Please select validator';
                        }
                        MsgBox($msg);
                        ReloadPage();
                    } else {

                        if ($check == 'chkAll') {
                            $par = 1;
                        } else {
                            $par = 0;
                        }

                        $dataURL = $baseURL . "Authentication/ajax-data/validator-assign-status-list-ajax-data.php?par=$par&ci=$SelectedCompanyID&si=$SelectedSupervisorID&lun=$loggedUserName";

                        ?>
                        <section class="card">
                            <div class="card-header">
                                <div class="card-title">
                                    Project : <?php echo $SelectedCompanyName; ?>
                                </div>
                                <div class="card-subtitle"></div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped" id="datatable-ajax"
                                       data-url="<?php echo $dataURL; ?>">
                                    <thead>
                                    <tr>
                                        <th>SL</th>
                                        <th>Validator</th>
                                        <th>User</th>
                                        <th>Create Date</th>
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
        <!-- end: page -->
    </section>
</div>
