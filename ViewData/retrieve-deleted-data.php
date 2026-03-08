<?php
$qrySupervisor = "SELECT id FROM assignsupervisor WHERE SupervisorID = ?";
$rsSupervisor = $app->getDBConnection()->fetch($qrySupervisor, $loggedUserID);
$SuperID = $rsSupervisor->id;
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
                                <label class="col-lg-3 control-label text-sm-end pt-2">Form Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="SelectedFormID" name="SelectedFormID"
                                            class="form-control populate" required>
                                        <optgroup label="Select Form">
                                            <?PHP
                                            $qryForm = $app->getDBConnection()->query("SELECT id, FormName FROM datacollectionform WHERE CompanyID = ?", $loggedUserCompanyID);

                                            foreach ($qryForm as $row) {
                                                echo '<option value="' . $row->id . '">' . $row->FormName . '</option>';
                                            }
                                            ?>
                                        </optgroup>

                                    </select>
                                </div>
                            </div>
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">User Select</label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate"
                                            name="SelectedUserID"
                                            id="SelectedUserID" title="Please select user">
                                        <option value="">Choose user</option>
                                        <?PHP
                                        if ($loggedUserName == 'admin') {
                                            $qryDistUser = "SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '$dataCollectorNamePrefix%' ORDER BY UserName ASC";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser);
                                        } else if (strpos($loggedUserName, 'admin') !== false) {
                                            $qryDistUser = "SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '$dataCollectorNamePrefix%' AND CompanyID = ? ORDER BY UserName ASC";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserCompanyID);
                                        } else if ($SuperID) {
                                            $qryDistUser = "SELECT u.id, u.UserName, u.FullName FROM assignsupervisor as a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND u.UserName LIKE '$dataCollectorNamePrefix%' AND a.SupervisorID = ?";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserID);
                                        } else if (strpos($loggedUserName, 'dist') !== false) {
                                            $qryDistUser = "SELECT u.id, u.UserName, u.FullName FROM assignsupervisor as a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND u.UserName LIKE '$dataCollectorNamePrefix%' AND a.DistCoordinatorID = ?";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserID);
                                        } else {
                                            $qryDistUser = "SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '$dataCollectorNamePrefix%' AND CompanyID = ? and UserName = ? ORDER BY UserName ASC";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserCompanyID, $loggedUserName);
                                        }

                                        foreach ($resQryDistUser as $row) {
                                            echo '<option value="' . $row->id . '">' . $row->UserName . ' | ' . substr($row->FullName, 0, 102) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2">Date range</label>
                                <div class="col-lg-6">
                                    <div class="input-daterange input-group">
                                        <input type="date" class="form-control" id="startDate"
                                               name="startDate">
                                        <span class="input-group-text border-start-0 border-end-0 rounded-0">to</span>
                                        <input type="date" class="form-control" id="endDate" name="endDate">
                                    </div>
                                </div>
                            </div>


                            <?php if (strpos($loggedUserName, 'admin') !== false) { ?>
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
                    $SelectedFormID = $_REQUEST['SelectedFormID'];
                    $SelectedCompanyID = getValue('datacollectionform', 'CompanyID', "id = $SelectedFormID");
                    $SelectedUserID = $_REQUEST['SelectedUserID'];
                    $checkAll = $_REQUEST['chkAll'];

                    if ($checkAll == 'chkAll') {
                        $SelectedCheckAll = 1;
                    } else {
                        $SelectedCheckAll = 0;
                    }

                    if (!empty($_REQUEST['startDate'])) {
                        $SelectedStartDate = date('Y-m-d', strtotime($_REQUEST['startDate'])) . $defaultStartTimeString;
                    }
                    if (!empty($_REQUEST['endDate'])) {
                        $SelectedEndDate = date('Y-m-d', strtotime($_REQUEST['endDate'])) . $defaultEndTimeString;
                    }
                    if (empty($SelectedUserID) && empty($checkAll)) {
                        MsgBox('Please select an user option.');
                        exit();
                    }

                    $dataParams = "selFormID=$SelectedFormID&selUserID=$SelectedUserID&selStartDate=$SelectedStartDate&selEndDate=$SelectedEndDate
                    &selCheckAll=$SelectedCheckAll&ci=$SelectedCompanyID&lun=$loggedUserName&lui=$loggedUserID";

                    $dataURL = $baseURL . "ViewData/ajax-data/view-deleted-data-ajax-data.php?$dataParams";

                    ?>
                    <section class="card">
                        <div class="card-title">Form
                            : <?php echo getValue('datacollectionform', 'FormName', "id = $SelectedFormID"); ?></div>
                        <div class="card-subtitle"></div>
                        <div class="card-body" id="tableWrap">
                            <table class="table table-bordered table-striped" id="datatable-approveddata-ajax"
                                   data-url="<?php echo $dataURL; ?>">
                                <thead>
                                <tr>
                                    <th>Record ID</th>
                                    <th>HH No</th>
                                    <th>PSU</th>
                                    <th>User</th>
                                    <th>Mobile</th>
                                    <th>Data Name</th>
                                    <th>Device ID</th>
                                    <th>Entry Date</th>
                                    <th>Actions</th>
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
        <!-- end: page -->
    </section>
</div>


<script type="text/javascript">
    function DeleteDataRecord(id, sendTo, data) {
        let cause = prompt("Are you sure to delete this data?", "Cause of delete: ")
        if (cause) {
            $.ajax({
                url: "ViewData/delete-data.php",
                method: "GET",
                datatype: "json",
                data: {
                    id: id,
                    tbl: 'xformrecord',
                    SendTo: sendTo,
                    cause: cause,
                    FromState: 'Approved',
                    sendFrom: '<?php echo $loggedUserID; ?>',
                    companyID: '<?php echo $loggedUserCompanyID; ?>',
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

