<?php
$qrySupervisor = "SELECT id, EditPermission, DeletePermission, ApprovePermission FROM assignsupervisor WHERE SupervisorID = ?";
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
                                <label class="col-lg-3 control-label text-lg-end pt-2">User Select</label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="UserID"
                                            id="UserID">
                                        <option value="">Choose user</option>
                                        <?PHP
                                        if ($loggedUserName == 'admin') {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE UserName <> '$loggedUserName' ORDER BY CompanyID DESC");
                                        } else if (strpos($loggedUserName, 'admin') !== false) {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE CompanyID = ? AND UserName <> '$loggedUserName' ORDER BY id ASC", $loggedUserCompanyID);
                                        } else if ($SuperID) {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT u.id, u.UserName, u.FullName FROM assignsupervisor AS a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND u.UserName <> '$loggedUserName' AND a.SupervisorID = ?", $loggedUserID);
                                        } else if (strpos($loggedUserName, 'div') !== false) {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT u.id, u.UserName, u.FullName FROM assignsupervisor AS a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND u.UserName <> '$loggedUserName' AND a.DivCoordinatorID = ?", $loggedUserID);
                                        } else if (strpos($loggedUserName, 'dist') !== false) {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT u.id, u.UserName, u.FullName FROM assignsupervisor AS a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND u.UserName <> '$loggedUserName' AND a.DistCoordinatorID = ?", $loggedUserID);
                                        } else {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName <> '$loggedUserName' AND CompanyID= ? AND UserName = ? ORDER BY UserName ASC", $loggedUserCompanyID, $loggedUserName);
                                        }

                                        foreach ($qryDistUser as $row) {
                                            echo '<option value="' . $row->id . '">' . $row->UserName . ' | ' . substr($row->FullName, 0, 102) . '</option>';
                                        }
                                        ?>

                                    </select>
                                </div>
                            </div>
                            <?php if (strpos($loggedUserName, 'admin') !== false
                                    || strpos($loggedUserName, 'div') !== false
                                    || strpos($loggedUserName, 'dist') !== false
                            ) { ?>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-sm-end pt-2"></label>
                                    <div class="col-lg-6">
                                        <div class="checkbox-custom checkbox-warning">
                                            <input id="chkAll" value="chkAll" type="checkbox" name="chkAll"/>
                                            <label for="chkAll">All</label>
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
                    $UserID = $_REQUEST['UserID'];
                    $check = $_REQUEST['chkAll'];

                    if (empty($UserID) && empty($check)) {
                        MsgBox('Please select an option.');
                        ReloadPage();
                    } else {

                        if ($check == "chkAll") {
                            $dataURL = $baseURL . "Authentication/ajax-data/user-list-ajax-data.php?par=1&lun=$loggedUserName&lci=$loggedUserCompanyID&luid=$loggedUserID";
                        } else {
                            $dataURL = $baseURL . "Authentication/ajax-data/user-list-ajax-data.php?par=0&lun=$loggedUserName&lci=$loggedUserCompanyID&luid=$loggedUserID&ui=$UserID";
                        }

                        ?>
                        <section class="card">
                            <header class="card-header">
                                <div class="form-group ml-2 row col-lg-1 " style="margin-left: 1px; margin-top:20px;">
                                    <button class="btn ml-2 btn-success"
                                        onclick="exportTableToExcel('datatable-ajax', 'UserList')">
                                        Download
                                    </button>
                                </div>
                            </header>
                            <div class="card-body">
                                <table class="table table-bordered table-striped" id="datatable-ajax"
                                       data-url="<?php echo $dataURL; ?>">
                                    <thead>
                                    <tr>
                                        <th>SL</th>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Password</th>
                                        <th>Full Name</th>
                                        <th>Mobile No</th>
                                        <th>Email</th>
                                        <th>Project</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                        <!-- end: page -->
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
    function EditItem(id, uname, pass, fullName, mobileNo, email, status, data) {
        if (confirm("Are you sure to update this data?")) {
            $.ajax({
                url: "Authentication/user-info-edit.php",
                method: "GET",
                datatype: "json",
                data: {
                    id: id,
                    uname: uname,
                    pass: pass,
                    fullName: fullName,
                    mobileNo: mobileNo,
                    email: email,
                    status: status
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

<script type="text/javascript">
    function DeleteItem(id, data) {
        if (confirm("Are you sure to delete this form?")) {
            $.ajax({
                url: "Authentication/delete.php",
                method: "GET",
                datatype: "json",
                data: {
                    id: id,
                    tbl: 'userinfo'
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
