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
                    <header class="card-header">
                        <h2 class="card-title">Supervisor Assign</h2>
                    </header>
                    <div class="card-body">
                        <form class="form-horizontal form-bordered" action="" method="post">
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Company Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="SelectedCompanyID" name="SelectedCompanyID"
                                            class="form-control populate" required
                                            onchange="getCompanyUser(document.getElementById('SelectedCompanyID').value)">
                                        <option value="">Choose a compnay</option>
                                        <?PHP
                                        if ($loggedUserName == 'admin') {
                                            $qryCompany = $app->getDBConnection()->query("SELECT id, CompanyName FROM dataownercompany ORDER BY id DESC");
                                        } else {
                                            $qryCompany = $app->getDBConnection()->query("SELECT id, CompanyName FROM dataownercompany WHERE id = ?", $loggedUserCompanyID);
                                        }
                                        foreach ($qryCompany as $row) {
                                            echo '<option value="' . $row->id . '">' . $row->CompanyName . '</option>';
                                        }
                                        ?>

                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">User Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="SelectedUserID"
                                            id="SelectedUserID" title="Please select user" required>
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
                        function getCompanyUser(companyID, data) {
                            $.ajax({
                                url: "Authentication/get-company-user-list.php",
                                method: "GET",
                                datatype: "html",
                                data: {
                                    companyID: companyID
                                },
                                success: function (response) {
                                    //alert(response);
                                    $('#SelectedUserID').html(response);
                                }
                            });
                            return false;
                        }
                    </script>
                </section>
                <?php

                if ($_REQUEST['show'] === 'Assign') {
                    $SelectedUserID = $_REQUEST['SelectedUserID'];
                    $SelectedCompanyID = $_REQUEST['SelectedCompanyID'];

                    $Field = "CompanyID, SupervisorID, UserID, EditPermission, DeletePermission, CreatedBy, DataEntryDate";
                    $Value = "'$SelectedCompanyID', '$SelectedUserID', '', '', '', '$loggedUserName', GETDATE()";

                    $cond = "CompanyID = '$SelectedCompanyID' AND SupervisorID = '$SelectedUserID'";
                    $totalExist = isExist('assignsupervisor', $cond);

                    if ($totalExist <> 0) {
                        $msg = "Sorry user role already exist!";
                    } else {
                        if (Save('assignsupervisor', $Field, $Value)) {
                            $msg = 'Assigned successfully.';
                        } else
                            $msg = 'Failed to assign!';
                    }
                    MsgBox($msg);
                    ReDirect($baseURL . 'index.php?parent=AssignSupervisor');
                }
                ?>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>
