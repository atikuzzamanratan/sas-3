<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// error_reporting(E_ALL ^ E_WARNING);

if ($loggedUserName == 'admin') {
    $qryFormName = "SELECT id, FormName FROM datacollectionform ORDER BY id DESC";
    $rsQryFormName = $app->getDBConnection()->fetchAll($qryFormName);
} else {
    $qryFormName = "SELECT id, FormName FROM datacollectionform WHERE Status = ? AND CompanyID = ? ORDER BY id DESC";
    $rsQryFormName = $app->getDBConnection()->fetchAll($qryFormName, 'Active', $loggedUserCompanyID);
}

$qrySupervisor = "SELECT id, EditPermission, DeletePermission, ApprovePermission FROM assignsupervisor WHERE SupervisorID = ?";
$resQrySupervisor = $app->getDBConnection()->fetch($qrySupervisor, $loggedUserID);
$resQrySupervisor = $app->getDBConnection()->fetch($qrySupervisor, $loggedUserID);
$SuperID = isset($resQrySupervisor->id) ? $resQrySupervisor->id : null;

$UserID = $_REQUEST['UserID'] ?? '';
$check  = $_REQUEST['chkAll'] ?? '';
$show   = $_REQUEST['show'] ?? '';

if ($show === 'Show') {
    $UserID = $_REQUEST['UserID'] ?? '';
    $check  = $_REQUEST['chkAll'] ?? '';
} else {
    $UserID = '';
    $check  = '';
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
                                <label class="col-lg-3 control-label text-lg-end pt-2">User Select</label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="UserID"
                                        id="UserID">
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
                                        } else {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND CompanyID= ? AND UserName = ? ORDER BY UserName ASC", $loggedUserCompanyID, $loggedUserName);
                                        }

                                        foreach ($qryDistUser as $row) {
                                            echo '<option value="' . $row->id . '"'. (isset($UserID) && !empty($UserID) && $UserID == $row->id ? 'selected' : '') .'>' . $row->UserName . ' | ' . substr($row->FullName, 0, 102) . '</option>';
                                        }
                                        ?>

                                    </select>
                                </div>
                            </div>
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2"></label>
                                <div class="col-lg-6">
                                    <div class="checkbox-custom checkbox-warning">
                                        <input id="chkAll" value="chkAll" type="checkbox" name="chkAll" <?php echo isset($check) && $check == 'chkAll' ? 'checked' : ''; ?> />
                                        <label for="chkAll">All Users</label>
                                    </div>
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
                <?php

                if (isset($_REQUEST['show']) && $_REQUEST['show'] === 'Show') {

                    if (empty($UserID) && empty($check)) {
                        MsgBox('Please select All Users or a specific User.');
                        ReloadPage();
                    } else {

                        if ($check == "chkAll") {
                            $dataURL = $baseURL . 'Form/ajax-data/form-assign-to-user-ajax-data.php?par=1&company=' . $loggedUserCompanyID . '&loggedInUser=' . $loggedUserID;
                        } else {
                            $dataURL = $baseURL . 'Form/ajax-data/form-assign-to-user-ajax-data.php?par=0&ui=' . $UserID;
                        }

                ?>
                        <section class="card">
                            <div class="card-body">
                                <table class="table table-bordered table-striped" id="datatable-ajax"
                                    data-url="<?php echo $dataURL; ?>">
                                    <thead>
                                        <tr>
                                            <th>SL</th>
                                            <th>Project</th>
                                            <th>User</th>
                                            <th>Form Name</th>
                                            <th>Form Group</th>
                                            <th>Status</th>
                                            <th>Provision End Date</th>
                                            <th>Create Date</th>
                                            <th>Created By</th>
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
                    }
                }
                ?>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

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

    function updateStatus(id, status) {
        $.ajax({
            url: "Form/update-status.php",
            method: "POST",
            datatype: "json",
            data: {
                id: id,
                tbl: 'assignformtoagent',
                status: status
            },
            success: function(response) {
                window.location.reload();
            }
        });
    }
</script>