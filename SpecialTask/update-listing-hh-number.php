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
                                <label class="col-lg-3 control-label text-sm-end pt-2">Form Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="SelectedFormID" name="SelectedFormID"
                                            class="form-control populate" required
                                            onchange="getRecordID(document.getElementById('SelectedFormID').value)">
                                        <optgroup label="Select Form">
                                            <?PHP
                                            $qryForm = $app->getDBConnection()->query("SELECT id, FormName FROM datacollectionform WHERE Status = 'Active' AND CompanyID = ?", $loggedUserCompanyID);

                                            foreach ($qryForm as $row) {
                                                echo '<option value="' . $row->id . '">' . $row->FormName . '</option>';
                                            }
                                            ?>
                                        </optgroup>
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
                                <label class="col-lg-3 control-label text-sm-end pt-2">Record Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <input name="SelectedRecordID" id="SelectedRecordID" type="number"
                                           class="form-control form-control-lg" required/>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2"
                                       for="recordToUpdate">Number to Update<span
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
                    $formID = xss_clean($_REQUEST['SelectedFormID']);
                    $recordID = xss_clean($_POST['SelectedRecordID']);
                    $recordToUpdate = xss_clean($_POST['recordToUpdate']);

                    $recordStatus = getValue('xformrecord', 'IsApproved', "id=$recordID");
                    if ($recordStatus == 0) {
                        //echo "Ready to update pending table and $existingString AND new DataName: $newDataName";
                        $tableForMasterData = 'masterdatarecord_Pending';
                    } elseif ($recordStatus == 1) {
                        //echo "Ready to update approve table and $existingString AND new DataName: $newDataName";
                        $tableForMasterData = 'masterdatarecord_Approved';
                    } elseif ($recordStatus == 2) {
                        //echo "Ready to update un-approve table and $existingString AND new DataName: $newDataName";
                        $tableForMasterData = 'masterdatarecord_UnApproved';
                    }

                    if ($formID == $formIdMainData) {
                        $ColumnNameToUpdate = $columnNameToUpdateValueForMainData;
                    } else {
                        $ColumnNameToUpdate = $columnNameToUpdateValueForListingData;
                    }

                    //echo "$formID | $recordID | $recordToUpdate";

                    if (empty($formID) || empty($recordID) || empty($recordToUpdate)) {
                        MsgBox('Please fill all fields!');
                        exit();
                    } else {
                        if (updateListingHHNumberNew($recordID, $recordToUpdate, $tableForMasterData, $ColumnNameToUpdate)) {
                            MsgBox('Successfully updated.');
                        } else {
                            MsgBox('Failed to update data!');
                        }
                    }
                }
                ?>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

