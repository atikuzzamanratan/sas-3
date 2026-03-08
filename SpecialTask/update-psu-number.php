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
                                <label class="col-lg-3 control-label text-sm-end pt-2">User Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="SelectedUserID" name="SelectedUserID"
                                            class="form-control populate" required">
                                    <option>Select User</option>
                                    <?PHP
                                    $qryUser = $app->getDBConnection()->query("SELECT id, UserName, FullName FROM userinfo WHERE CompanyID = ? and IsActive = 1 and UserName like '$dataCollectorNamePrefix%'", $loggedUserCompanyID);

                                    foreach ($qryUser as $row) {
                                        $uID = $row->id;
                                        $uName = $row->UserName;
                                        $uFullName = $row->FullName;
                                        $uInfo = "$uFullName / $uName ($uID)";
                                        echo '<option value="' . $uID . '">' . $uInfo . '</option>';
                                    }
                                    ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Form Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="SelectedFormID" name="SelectedFormID"
                                            class="form-control populate" required
                                            onchange="getRecordID(document.getElementById('SelectedFormID').value)">
                                        <option>Select Form</option>
                                        <?PHP
                                        $qryForm = $app->getDBConnection()->query("SELECT id, FormName FROM datacollectionform WHERE Status = 'Active' AND CompanyID = ?", $loggedUserCompanyID);

                                        foreach ($qryForm as $row) {
                                            echo '<option value="' . $row->id . '">' . $row->FormName . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <!--<div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Record Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="SelectedRecordID"
                                            id="SelectedRecordID" title="Please select a record" required>
                                        <option value="">Choose a record</option>
                                        <?php
                            /*                                        $qry = "SELECT id, DataName FROM xformrecord WHERE FormId = ?";
                                                                    $resQry = $app->getDBConnection()->fetchAll($qry, $formIdMainData);
                                                                    foreach ($resQry as $row) {
                                                                        echo '<option value="' . $row->id . '">' . $row->id . ' - (' . $row->DataName . ')' . '</option>';
                                                                    }
                                                                    */ ?>
                                    </select>
                                </div>
                            </div>-->

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Record ID<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <input name="SelectedRecordID" id="SelectedRecordID" type="number"
                                           class="form-control form-control-lg" required/>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2"
                                       for="recordToUpdate">PSU to Update<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <input name="recordToUpdate" type="number" class="form-control form-control-lg"
                                           required/>
                                </div>
                            </div>

                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-primary" name="update" type="submit" id="update"
                                               value="Update">
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>

                    <script type="text/javascript">
                        function getRecordID(formID, data) {
                            $.ajax({
                                url: "../SpecialTask/get-record-list.php",
                                method: "GET",
                                datatype: "html",
                                data: {
                                    formID: formID
                                },
                                success: function (response) {
                                    //alert(response);
                                    $('#SelectedRecordID').html(response);
                                }
                            });
                            return false;
                        }
                    </script>
                </section>
                <?php
                if ($_REQUEST['update'] === 'Update') {
                    $userID = xss_clean($_REQUEST['SelectedUserID']);
                    $formID = xss_clean($_REQUEST['SelectedFormID']);
                    $recordID = xss_clean($_POST['SelectedRecordID']);
                    $psuToUpdate = xss_clean($_POST['recordToUpdate']);

                    //echo "$userID | $formID | $recordID | $recordToUpdate";

                    //exit();

                    if (empty($userID) || empty($formID) || empty($recordID) || empty($psuToUpdate)) {
                        MsgBox('Please fill all fields!');
                        exit();
                    } else {
                        $isRecordExist = isExist('xformrecord', "id = $recordID and FormId = $formID and UserID = $userID");
                        $isPSUExist = isExist('PSUList', "psu = $psuToUpdate and PSUUserID = $userID");
                        if ($isRecordExist) {
                            if ($isPSUExist) {
                                if (updatePSUNumber($recordID, $psuToUpdate)) {
                                    MsgBox('Successfully updated.');
                                } else {
                                    MsgBox('Failed to update data!');
                                }
                                //MsgBox('Ready to update!');
                            } else {
                                MsgBox('This PSU is not assigned for the selected user!');
                            }
                        } else {
                            MsgBox('Record not found for the selected user!');
                        }

                    }
                }
                ?>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

