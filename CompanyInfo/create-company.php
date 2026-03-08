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
                            <h2 class="card-title">Company Create</h2>
                        </header>
                        <div class="card-body">
                            <form class="form-horizontal form-bordered" action="" method="post">
                                <div class="form-group row pb-4">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="companyName">Company Name<span class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="companyName" name="companyName"
                                               placeholder="company name" required>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="contactPerson">Contact Person<span class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="contactPerson" name="contactPerson"
                                               placeholder="contact person name" required>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="address">Address<span class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="address" name="address"
                                               placeholder="address" required>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="phoneNumber">Phone Number<span class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="phoneNumber" name="phoneNumber"
                                               placeholder="phone number" required>
                                    </div>
                                </div>

                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-sm-end pt-2">Is Active?<span
                                                class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <select data-plugin-selectTwo id="status" name="status"
                                                class="form-control populate" title="Please select status" required>
                                            <optgroup label="Select Status">
                                                <option value="1">Active</option>
                                                <option value="0">In-Active</option>
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
    $CompanyName = $_POST['companyName'];
    $ContactPerson = $_POST['contactPerson'];
    $Address = $_POST['address'];
    $PhoneNumber = $_POST['phoneNumber'];
    $IsActive = $_POST['status'];

    $Field = "CompanyName, ContactPersonName, Address, Phone, IsActive";
    $Value = "N'$CompanyName', N'$ContactPerson', N'$Address', '$PhoneNumber', '$IsActive'";

    $cond = "CompanyName='$CompanyName'";
    $totalExist = isExist('dataownercompany', $cond);

    if ($totalExist <> 0) {
        $errMsg = "Sorry same name company already exist!";
        MsgBox($errMsg);
    } else {
        if (Save('dataownercompany', $Field, $Value)) {
            MsgBox('Saved Successfully.');
            ReDirect($baseURL . 'index.php?parent=DataOwnerCompanyView');
        } else
            MsgBox('Failed to save!');
    }
}
