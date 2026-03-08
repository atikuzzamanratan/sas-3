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
                                <label class="col-lg-3 control-label text-sm-end pt-2">Company<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="company" name="company"
                                            class="form-control populate" title="Please select a company" required
                                            onchange="getCompanySupervisor((document.getElementById('company').value), '<?php echo $supervisorNamePrefix; ?>');">
                                        <option value="">Choose a company</option>
                                        <?PHP
                                        foreach ($rsQryCompany as $row) {
                                            echo '<option value="' . $row->id . '">' . $row->CompanyName . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Supervisor Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate"
                                            name="SelectedSupervisorID"
                                            id="SelectedSupervisorID" title="Please select supervisor" required>
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
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Permission Select</label>
                                <div class="col-lg-6">
                                    <table cellpadding="0" cellspacing="0" border="0"
                                           class="table table-striped table-bordered datatables"
                                           id="example">
                                        <tr>
                                            <td>Edit</td>
                                            <td>
                                                <input class="checkbox-custom checkbox-default" name="edit" id="edit" type="checkbox"
                                                       value="1">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Approve</td>
                                            <td>
                                                <input class="checkbox-custom checkbox-default" name="approve" id="approve" type="checkbox"
                                                       value="1">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Delete</td>
                                            <td>
                                                <input class="checkbox-custom checkbox-default" name="delete" id="delete" type="checkbox"
                                                       value="1">
                                            </td>
                                        </tr>
                                    </table>
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
                        function getCompanySupervisor(companyID, searchParam, data) {

                            $.ajax({
                                url: "Authentication/get-supervisor-user-list.php",
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
                    $PermissionEdit = xss_clean($_REQUEST['edit']);
                    $PermissionApprove = xss_clean($_REQUEST['approve']);
                    $PermissionDelete = xss_clean($_REQUEST['delete']);

                    $Field = "CompanyID, SupervisorID, UserID, EditPermission, DeletePermission, ApprovePermission, CreatedBy, DataEntryDate";
                    $Value = "'$CompanyID','$SupervisorID','$UserID','$PermissionEdit','$PermissionDelete','$PermissionApprove','$loggedUserName',GETDATE()";

                    $condIsSupervisor = "CompanyID = '$CompanyID' AND SupervisorID = '$SupervisorID'";
                    $totalExistSupervisor = isExist('assignsupervisor', $condIsSupervisor);

                    $condIsUserAssignedToSupervisor = "CompanyID = '$CompanyID' AND UserID='$UserID'";
                    $totalExistUser = isExist('assignsupervisor', $condIsUserAssignedToSupervisor);

                    if ($totalExistSupervisor == 0) {
                        $msg = "This selected supervisor is not assigned for supervisor!";
                    } else {
                        if ($totalExistUser <> 0) {
                            $msg = "This selected user is already assigned to a supervisor!";
                        } else {
                            if (Save('assignsupervisor', $Field, $Value)) {
                                $msg = 'Assigned successfully.';
                            } else
                                $msg = 'Failed to assign!';
                        }
                    }
                    MsgBox($msg);
                    ReDirect($baseURL . 'index.php?parent=AssignUserToSupervisor');
                }
                ?>
                <!-- end: page -->
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

