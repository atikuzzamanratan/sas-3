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
                                <label class="col-lg-3 control-label text-lg-end pt-2">User Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="SelectedUserID"
                                            id="SelectedUserID"
                                            onchange="getUserRecordList(this.value, <?php echo $loggedUserCompanyID; ?>)"
                                            required>
                                        <option value="">Choose a User</option>
                                        <?PHP
                                        if ($loggedUserName == 'admin') {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName <> '$loggedUserName' ORDER BY UserName ASC");
                                        } else if (strpos($loggedUserName, 'admin') !== false) {
                                            $qryDistUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '%$dataCollectorNamePrefix%'  AND CompanyID = ? ORDER BY UserName ASC", $loggedUserCompanyID);
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

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Record Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="recordID"
                                            id="recordID" title="Please select a record id" required>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2"
                                       for="notificationText">Message<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <textarea class="form-control" rows="3" id="notificationText"
                                              name="notificationText"
                                              data-plugin-textarea-autosize required
                                              placeholder="input message here"></textarea>
                                </div>
                            </div>

                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input onClick="return confirm('Are you sure to SEND this notification?')"
                                               class="btn btn-primary" name="send" type="submit" id="send"
                                               value="Send">
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                    <script type="text/javascript">
                        function getUserRecordList(selectedUserID, loggedCompanyId) {
                            $.ajax({
                                url: "../SpecialTask/get-record-id-list.php",
                                method: "GET",
                                datatype: "html",
                                data: {
                                    userID: selectedUserID,
                                    companyID: loggedCompanyId
                                },
                                success: function (response) {
                                    //alert(response);
                                    $('#recordID').html(response);
                                }
                            });
                            return false;
                        }
                    </script>
                </section>
                <?php
                if ($_REQUEST['send'] === 'Send') {
                    $recipientID = xss_clean($_POST['SelectedUserID']);
                    $recordID = xss_clean($_POST['recordID']);
                    $notificationText = xss_clean($_REQUEST['notificationText']);

                    $Msg = $notificationText . '<br>Please <a href="' . $baseURL . 'AppsAPI/edit_UserDataRecord_SpecialEdit.php?xID=' . $recordID . '">Click Here</a> to edit and resend your data to server';

                    $Field = "FromUserID, ToUserID, Notification, Status, CompanyID";
                    $Value = "'$loggedUserID', '$recipientID', N'$Msg', '0', '$loggedUserCompanyID'";

                    if (Save('Notification', $Field, $Value)) {
                        $msg = 'Notification sent successfully.';
                    } else {
                        $msg = 'Notification send failed!';
                    }

                    MsgBox($msg);
                    ReDirect($baseURL . "index.php?parent=ViewNotification");
                }
                ?>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

