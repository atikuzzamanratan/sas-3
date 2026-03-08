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
                                    <select data-plugin-selectTwo id="company" name="company"
                                            class="form-control populate" title="Please select a project" required
                                            onchange="getCompanyValidator((document.getElementById('company').value), 'val');">
                                        <option value="">Choose a Project</option>
                                        <?PHP
                                        foreach ($rsQryCompany as $row) {
                                            echo '<option value="' . $row->id . '">' . $row->CompanyName . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Validator Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate"
                                            name="SelectedSupervisorID"
                                            id="SelectedSupervisorID" title="Please select validator" required>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2">User Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="SelectedUserID"
                                            id="SelectedUserID" required>
                                        <option value="">Choose a user</option>
                                        <?PHP
                                        $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE CompanyID = ? AND UserName LIKE '$dataCollectorNamePrefix%' ORDER BY UserName ASC", $loggedUserCompanyID);

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
                                               value="Assign">
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                    <script type="text/javascript">
                        function getCompanyValidator(companyID, searchParam, data) {

                            $.ajax({
                                url: "Authentication/get-validator-user-list.php",
                                method: "GET",
                                datatype: "html",
                                data: {
                                    companyID: companyID,
                                    searchParam: searchParam
                                },
                                success: function (response) {
                                    //alert(response);
                                    $('#SelectedSupervisorID').html(response);
                                }
                            });
                            return false;
                        }
                    </script>
                </section>
                <?php
                if ($_REQUEST['show'] === 'Assign') {
                    $CompanyID = $_REQUEST['company'];
                    $UserID = $_REQUEST['SelectedUserID'];
                    $SupervisorID = $_REQUEST['SelectedSupervisorID'];

                    $param = "ValidatorID = '$SupervisorID'";
                    $cond = "UserID = '$UserID' AND CompanyID = $loggedUserCompanyID";

                    if (Edit('assignsupervisor', $param, $cond)) {
                        $msg = 'Successfully saved.';
                    } else
                        $msg = 'Failed to save!';

                    MsgBox($msg);
                    ReDirect($baseURL . 'index.php?parent=AssignUserToValidator');
                }
                ?>
                <!-- end: page -->
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

