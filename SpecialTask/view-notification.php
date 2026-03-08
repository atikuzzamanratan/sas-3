<?php
$qrySupervisor = "SELECT id FROM assignsupervisor WHERE SupervisorID = ?";
$rsSupervisor = $app->getDBConnection()->fetch($qrySupervisor, $loggedUserID);
$SuperID = $rsSupervisor->id;

if($_REQUEST['show'] === 'Show'){
    $SelectedUserID = $_REQUEST['SelectedUserID'];
    $SelectedStatus = $_REQUEST['SelectedStatus'];
    $SelectedStartDate = $_REQUEST['startDate'];
    $SelectedEndDate = $_REQUEST['endDate'];
    $checkAll = $_REQUEST['chkAll'];
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
                                <label class="col-lg-3 control-label text-sm-end pt-2">User Select</label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate"
                                            name="SelectedUserID"
                                            id="SelectedUserID" title="Please select user">
                                        <option value="">Choose user</option>
                                        <?PHP
                                        if ($loggedUserName == 'admin') {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName <> '$loggedUserName' ORDER BY UserName ASC");
                                        } else if (strpos($loggedUserName, 'admin') !== false) {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName <> '$loggedUserName' AND CompanyID = ? ORDER BY UserName ASC", $loggedUserCompanyID);
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
                                            echo '<option value="' . $row->id . '" ' . (isset($SelectedUserID) && $SelectedUserID == $row->id ? 'selected' : '') . '>' . $row->UserName . ' | ' . substr($row->FullName, 0, 102) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Status Select</label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate"
                                            name="SelectedStatus"
                                            id="SelectedStatus" title="Please select status">
                                        <option value="">Choose status</option>
                                        <option value="0" <?php echo isset($SelectedStatus) && $SelectedStatus == '0' ? 'selected' : ''; ?>>Unread</option>
                                        <option value="1" <?php echo isset($SelectedStatus) && $SelectedStatus == '1' ? 'selected' : ''; ?>>Read</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2">Date range</label>
                                <div class="col-lg-6">
                                    <div class="input-daterange input-group">
                                        <input type="date" class="form-control" id="startDate"
                                               name="startDate" value="<?php echo isset($SelectedStartDate) ? $SelectedStartDate : ''; ?>">
                                        <span class="input-group-text border-start-0 border-end-0 rounded-0">to</span>
                                        <input type="date" class="form-control" id="endDate" name="endDate" value="<?php echo isset($SelectedEndDate) ? $SelectedEndDate : ''; ?>">
                                    </div>
                                </div>
                            </div>


                            <?php if (strpos($loggedUserName, 'admin') !== false) { ?>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-sm-end pt-2"></label>
                                    <div class="col-lg-6">
                                        <div class="checkbox-custom checkbox-warning">
                                            <input id="chkAll" value="chkAll" type="checkbox" name="chkAll" <?php echo isset($checkAll) && $checkAll == 'chkAll' ? 'checked' : ''; ?>/>
                                            <label for="chkAll">All Users</label>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-primary" name="show" type="submit" id="show"
                                               value="Show">

                                        <button type="button" class="btn btn-secondary ms-4" id="clearForm">Clear</button>
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </section>
                <?php

                if ($_REQUEST['show'] === 'Show') {

                    if ($checkAll == 'chkAll') {
                        $SelectedCheckAll = 1;
                    } else {
                        $SelectedCheckAll = 0;
                    }

                    if (!empty($SelectedStartDate)) {
                        $SelectedStartDate = date('Y-m-d', strtotime($SelectedStartDate)) . $defaultStartTimeString;
                    }
                    if (!empty($SelectedEndDate)) {
                        $SelectedEndDate = date('Y-m-d', strtotime($SelectedEndDate)) . $defaultEndTimeString;
                    }

                    if (empty($SelectedUserID) && empty($checkAll)) {
                        MsgBox('Please select All Users or a specific User.');
                        exit();
                    }

                    ?>

                    <input type="hidden" id="DataUserID"
                           value="<?php if (!empty($SelectedUserID)) echo $SelectedUserID; ?>">
                    <input type="hidden" id="SelectedDataStatus" value="<?php echo $SelectedStatus; ?>">
                    <input type="hidden" id="DataChkAll" value="<?php echo $SelectedCheckAll; ?>">
                    <input type="hidden" id="LoggedCompanyID" value="<?php echo $loggedUserCompanyID; ?>">
                    <input type="hidden" id="LoggedUserID" value="<?php echo $loggedUserID; ?>">
                    <input type="hidden" id="DataStartDate"
                           value="<?php if (!empty($SelectedStartDate)) echo $SelectedStartDate; ?>">
                    <input type="hidden" id="DataEndDate"
                           value="<?php if (!empty($SelectedEndDate)) echo $SelectedEndDate; ?>">

                    <section class="card">
                        <div class="card-body" id="tableWrap">
                            <table class="table table-bordered table-striped" id="tblViewData">
                                <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>ID</th>
                                    <th>Sender</th>
                                    <th>Recipient</th>
                                    <th>Message</th>
                                    <th>Send Time</th>
                                    <th>Read Time</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    <?php
                }
                ?>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        //alert($('#DataStatus').val());
        var DataUserID = $('#DataUserID').val();
        var SelectedDataStatus = $('#SelectedDataStatus').val();
        var DataChkAll = $('#DataChkAll').val();
        var LoggedCompanyID = $('#LoggedCompanyID').val();
        var LoggedUserID = $('#LoggedUserID').val();
        var DataStartDate = $('#DataStartDate').val();
        var DataEndDate = $('#DataEndDate').val();
        var dataTable = $('#tblViewData').DataTable({
            "aLengthMenu": [
                [20, 10, 50, 100, 500, 1000, 5000, 100000000],
                [20, 10, 50, 100, 500, 1000, 5000, "All"]
            ],
            "processing": true,
            "serverSide": true,
            "ajax": {
                data: {
                    DataUserID: DataUserID,
                    SelectedDataStatus: SelectedDataStatus,
                    DataChkAll: DataChkAll,
                    LoggedCompanyID: LoggedCompanyID,
                    LoggedUserID: LoggedUserID,
                    DataStartDate: DataStartDate,
                    DataEndDate: DataEndDate
                },
                url: "<?php echo $dataURL = $baseURL . "SpecialTask/ajax-data/view-notification-data-ajax-data.php"; ?>",
                type: "POST"
            },
            // 👇 ADD THIS PART
            "columnDefs": [
                { "orderable": false, "targets": [2, 3, 4, 7] }  // Disable sorting on Sender, Recipient, Message, Status
            ]
        });
    });
</script>
