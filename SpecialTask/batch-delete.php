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
                            <!--<div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Form Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="SelectedFormID" name="SelectedFormID"
                                            class="form-control populate" required
                                            onchange="getColumnName(document.getElementById('SelectedFormID').value)">
                                        <optgroup label="Choose form">
                                            <?PHP
/*                                            $qryForm = $app->getDBConnection()->query("SELECT id, FormName FROM datacollectionform WHERE CompanyID = ?", $loggedUserCompanyID);

                                            foreach ($qryForm as $row) {
                                                echo '<option value="' . $row->id . '">' . $row->FormName . '</option>';
                                            }
                                            */?>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>-->

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2" for="textareaAutosize">Record
                                    ID(s)<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <textarea class="form-control" rows="3" id="recordID" name="recordID"
                                              data-plugin-textarea-autosize required
                                              placeholder="<?php echo $batchDeletePlaceHolderText; ?>"></textarea>
                                </div>
                            </div>

                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input onClick="return confirm('Are you sure to DELETE the records?')"
                                               class="btn btn-primary" name="delete" type="submit" id="delete"
                                               value="Delete">
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </section>
                <?php

                if ($_REQUEST['delete'] === 'Delete') {
                    //$SelectedFormID = $_REQUEST['SelectedFormID'];
                    $recordID = xss_clean($_REQUEST['recordID']);
                    $recordID = str_replace(' ', '', $recordID);

                    $RecordIDArray = explode(',', $recordID);

                    $QueryId = "";
                    for ($i = 0; $i < count($RecordIDArray); $i++) {
                        if (!empty($RecordIDArray[$i]) && $RecordIDArray[$i] != " ") {
                            if ($i == 0) {
                                $QueryId .= $RecordIDArray[$i];
                            } else {
                                $QueryId .= "," . $RecordIDArray[$i];
                            }
                        }
                    }

                    $InsertDeletedRecordQry = "
                    SET IDENTITY_INSERT deletedxformrecord ON;
                    INSERT INTO deletedxformrecord (id, UserID, FormId, DataName, FormGroupId, CompanyId, DeviceID, XFormsFilePath, EntryDate, IsApproved, PSU, Cause, SampleHHNo)
                    SELECT id, UserID, FormId, DataName, FormGroupId, CompanyId, DeviceID, XFormsFilePath, EntryDate, IsApproved, PSU, Cause, SampleHHNo 
                    FROM xformrecord WHERE id IN ($QueryId) ;
                    SET IDENTITY_INSERT deletedxformrecord OFF;";
                    $app->getDBConnection()->query($InsertDeletedRecordQry);

                    $tbl_name = 'xformrecord';
                    $cond = "id IN ($QueryId)";

                    if (Delete($tbl_name, $cond)) {
                        $msg = 'Successfully deleted.';
                    } else {
                        $msg = 'Failed to delete data!';
                    }
                    MsgBox($msg);
                }
                ?>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

