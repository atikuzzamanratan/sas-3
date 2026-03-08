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
                                    <select data-plugin-selectTwo class="form-control populate" name="SelectedUserID"
                                            id="SelectedUserID" onchange="userNameF(this.value)">
                                        <option value="">Choose a User</option>
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
                                        } else if (strpos($loggedUserName, 'val') !== false) {
											$qryDistUser = $app->getDBConnection()->query("SELECT u.id, u.UserName, u.FullName FROM assignsupervisor AS a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND a.ValidatorID = ?", $loggedUserID);
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

                            <?php if (strpos($loggedUserName, 'admin') !== false) { ?>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-sm-end pt-2"></label>
                                    <div class="col-lg-6">
                                        <div class="checkbox-custom checkbox-warning">
                                            <input id="chkAll" value="chkAll" type="checkbox" name="chkAll"
                                                   onchange="getUserIDList(<?php echo $loggedUserID; ?>, <?php echo $loggedUserCompanyID; ?>)"/>
                                            <label for="chkAll">Send to all</label>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2" for="UserList">Selected
                                    User(s)</label>
                                <div class="col-lg-6">
                                    <textarea class="form-control" rows="3" id="UserList" name="UserList"
                                              readonly></textarea>
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
                                        <input class="btn btn-primary" name="send" type="submit" id="send"
                                               value="Send">
                                        <input class="btn btn-danger" onclick="resetArray();" name="clear" type="button"
                                               id="clear"
                                               value="Clear">
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                    <script type="text/javascript">
                        function getUserIDList(loggedUserId, loggedCompanyId) {
                            $.ajax({
                                url: "../SpecialTask/get-user-id-list.php",
                                method: "GET",
                                datatype: "html",
                                data: {
                                    userID: loggedUserId,
                                    companyID: loggedCompanyId
                                },
                                success: function (response) {
                                    //alert(response);
                                    $('#UserList').html(response);
                                }
                            });
                            return false;
                        }
                    </script>
                    <script>
                        var UserListArray = [];

                        function userNameF(userName) {
                            if (UserListArray.indexOf(userName) > -1) {
                                alert(userName + " already exist");
                            } else {
                                UserListArray.push(userName);
                                let UserNameList = UserListArray.toString();
                                $('#UserList').val(UserNameList);
                            }
                        }

                        function resetArray() {
                            location.reload();
                            UserListArray.length = 0;
                            let UserNameList = UserListArray.toString();
                            $('#UserList').val(UserNameList);
                        }
                    </script>
                </section>
                <?php
                if ($_REQUEST['send'] === 'Send') {
                    $Notification = xss_clean($_REQUEST['notificationText']);
                    $recipients = xss_clean($_POST['UserList']);

                    if (empty($recipients)) {
                        MsgBox('Please select recepient');
                        exit();
                    }

                    $recipientsExplode = explode(",", $recipients);

                    foreach ($recipientsExplode as $SendTo) {
                        $noticeInsQry = "INSERT INTO Notification (FromUserID, ToUserID, Notification, Status, CompanyID) VALUES ($loggedUserID, $SendTo, N'$Notification', 0, $loggedUserCompanyID)";
                        $app->getDBConnection()->query($noticeInsQry);
                    }
                    MsgBox('Notification sent successfully.');
                }
                ?>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

