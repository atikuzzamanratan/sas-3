<?php
$qryCompnay = "SELECT id, CompanyName FROM dataownercompany";
$rsQryCompany = $app->getDBConnection()->fetchAll($qryCompnay);
?>
    <div class="inner-wrapper">
        <section role="main" class="content-body">
            <header class="page-header">
                <h2><?php echo $MenuLebel; ?></h2>

                <?php include_once 'Components/header-home-button.php'; ?>
            </header>

            <!-- start: page -->
            <div class="row">
                <div class="col-lg-2 mb-0"></div>
                <div class="col-lg-8 mb-0">
                    <section class="card">
                        <header class="card-header">
                            <h2 class="card-title">Form Group Create</h2>
                        </header>
                        <div class="card-body">
                            <form class="form-horizontal form-bordered" action="" method="post">
                                <div class="form-group row pb-4">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="formGroupName">Group Name<span class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="formGroupName" name="formGroupName"
                                               placeholder="form group name" required>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="formGroupDescription">Description<span class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="formGroupDescription"
                                               name="formGroupDescription"
                                               placeholder="form group description" required>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-sm-end pt-2">Company<span
                                                class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <select data-plugin-selectTwo id="company" name="company"
                                                class="form-control populate" title="Please select at least one company"
                                                required>
                                            <option value="">Choose a Company</option>
                                            <?PHP
                                            foreach ($rsQryCompany as $row) {
                                                echo '<option value="' . $row->id . '">' . $row->CompanyName . '</option>';
                                            }
                                            ?>

                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-sm-end pt-2">Status<span
                                                class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <select data-plugin-selectTwo id="status" name="status"
                                                class="form-control populate" title="Please select status"
                                                required>
                                            <optgroup label="Select Staus">
                                                <option value="Active">Active</option>
                                                <option value="InActive">In-Active</option>
                                        </select>
                                    </div>
                                </div>
                                <footer class="card-footer">
                                    <div class="row justify-content-end">
                                        <div class="col-lg-9">
                                            <input class="btn btn-primary" name="create" type="submit" id="create"
                                                   value="Create">
                                        </div>
                                    </div>
                                </footer>
                            </form>
                        </div>
                    </section>
                </div>
                <div class="col-lg-2 mb-0"></div>
            </div>
            <!-- end: page -->
        </section>
    </div>

<?php
if (isset($_POST['create'])) {
    $FormGroupName = $_POST['formGroupName'];
    $FormGroupDescription = $_POST['formGroupDescription'];
    $FormGroupCompany = $_POST['company'];
    $FormGroupStatus = $_POST['status'];

    $Field = "FormGroupName, FormGroupDesc, CompanyID, Status, CreatedBy";
    $Value = "'$FormGroupName', '$FormGroupDescription', '$FormGroupCompany', '$FormGroupStatus', '$loggedUserName'";

    $cond = "FormGroupName='$FormGroupName'";
    $totalExistingFormGroup = isExist('datacollectionformgroup', $cond);

    if ($totalExistingFormGroup <> 0) {
        $errMsg = "Sorry same name form already exist!";
        MsgBox($errMsg);
    } else {
        if (Save('datacollectionformgroup', $Field, $Value)) {
            MsgBox('Saved Successfully.');
            ReDirect($baseURL . 'index.php?parent=FormGroupView');
        } else
            MsgBox('Failed to save!');
    }
}
