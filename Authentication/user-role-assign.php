<?php
$dataURL = $baseURL . 'Authentication/ajax-data/assigned-role-list-ajax-data.php?cid=' . $loggedUserCompanyID . '&uname=' . $loggedUserName;
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
                    <header class="card-header">
                        <h2 class="card-title">Role Assign</h2>
                    </header>
                    <div class="card-body">
                        <form class="form-horizontal form-bordered" action="" method="post">
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2">User Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="SelectedUserID"
                                            id="SelectedUserID" required>
                                        <option value="">Choose a user</option>
                                        <?PHP
                                        if ($loggedUserName == 'admin') {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName <> '$loggedUserName' ORDER BY UserName ASC");
                                        } else {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName <> '$loggedUserName' AND CompanyID = ? ORDER BY UserName ASC", $loggedUserCompanyID);
                                        }

                                        foreach ($qryDistUser as $row) {
                                            echo '<option value="' . $row->id . '">' . $row->UserName . ' | ' . substr($row->FullName, 0, 102) . '</option>';
                                        }
                                        ?>

                                    </select>
                                </div>
                            </div>
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Role Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="SelectedRoleID" name="SelectedRoleID"
                                            class="form-control populate" required>
                                        <option value="">Choose a role</option>
                                        <?PHP
                                        if ($loggedUserName == 'admin') {
                                            $qryRole = $app->getDBConnection()->query("SELECT RoleId, RoleName FROM roleinfo WHERE CompanyID <> ? ORDER BY CompanyID DESC", $loggedUserCompanyID);
                                        } else {
                                            $qryRole = $app->getDBConnection()->query("SELECT RoleId, RoleName FROM roleinfo WHERE CompanyID = ? ORDER BY RoleName ASC", $loggedUserCompanyID);
                                        }
                                        foreach ($qryRole as $row) {
                                            echo '<option value="' . $row->RoleId . '">' . $row->RoleName . '</option>';
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
                </section>
                <?php

                if ($_REQUEST['show'] === 'Assign') {
                    $SelectedUserID = $_REQUEST['SelectedUserID'];
                    $SelectedRoleID = $_REQUEST['SelectedRoleID'];

                    $Field = "UserId, RoleId, CreateBy, CreatedDate";
                    $Value = "'$SelectedUserID', '$SelectedRoleID', '$loggedUserName', GETDATE()";

                    $cond = "UserId = '$SelectedUserID' AND RoleId = '$SelectedRoleID'";
                    $totalExist = isExist('userrole', $cond);

                    if ($totalExist <> 0) {
                        $msg = "Sorry user role already exist!";
                    } else {
                        if (Save('userrole', $Field, $Value)) {
                            $msg = 'Assigned successfully.';
                        } else
                            $msg = 'Failed to assign!';
                    }
                    MsgBox($msg);
                    ReDirect($baseURL . 'index.php?parent=AddUserRole');
                }
                ?>
                <section class="card">
                    <div class="card-body">
                        <table class="table table-bordered table-striped" id="datatable-ajax"
                               data-url="<?php echo $dataURL; ?>">
                            <thead>
                            <tr>
                                <th>SL</th>
                                <th>User</th>
                                <th>Role ID</th>
                                <th>Role Name</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </section>
                <!-- end: page -->
                <?php

                ?>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

<script type="text/javascript">
    function DeleteItem(userId, roleId, data) {
        if (confirm("Are you sure to delete this item?")) {
            $.ajax({
                url: "Authentication/delete-assigned-role.php",
                method: "GET",
                datatype: "json",
                data: {
                    userId: userId,
                    roleId: roleId,
                    tbl: 'userrole'
                },
                success: function (response) {
                    alert(response);
                    window.location.reload();
                }
            });
        }
        return false;
    }
</script>
