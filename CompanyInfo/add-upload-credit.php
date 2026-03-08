<?php
$qryCompanyName = "SELECT id, CompanyName FROM dataownercompany ORDER BY CompanyName ASC";
$rsQryCompanyName = $app->getDBConnection()->fetchAll($qryCompanyName);
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
                            <h2 class="card-title">Add Credit</h2>
                        </header>
                        <div class="card-body">
                            <form class="form-horizontal form-bordered" action="" method="post">
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2">Company Select<span
                                                class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <select data-plugin-selectTwo class="form-control populate" name="CompanyID"
                                                id="CompanyID">
                                            <optgroup label="Select Company">
                                                <?PHP
                                                foreach ($rsQryCompanyName as $row) {
                                                    echo '<option value="' . $row->id . '">' . $row->CompanyName . '</option>';
                                                }
                                                ?>
                                            </optgroup>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="uploadCredit">Upload Credit<span class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="uploadCredit" name="uploadCredit"
                                               placeholder="upload credit" required>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="amount">Amount<span class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="amount" name="amount"
                                               placeholder="amount" required>
                                    </div>
                                </div>

                                <footer class="card-footer">
                                    <div class="row justify-content-end">
                                        <div class="col-lg-9">
                                            <input class="btn btn-primary" name="add" type="submit" id="add"
                                                   value="Add">
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
if (isset($_POST['add'])) {
    $CompanyID = $_POST['CompanyID'];
    $UploadCredit = $_POST['uploadCredit'];
    $Amount = $_POST['amount'];

    $PackageID = getValue('company_packages', 'packageId', "CompanyId = '$CompanyID'");
    $currentCredits = getValue('company_packages', 'uploadCredit', "CompanyId = '$CompanyID'");

    $NewUploadCredit = $currentCredits + $UploadCredit;
    $PaymentType = UPLOAD_CREDIT_PAYMENT_TYPE_ID;

    $FieldPaymentInsert = "companyId, packageId, amount, createdDate, paymentType";
    $ValuePaymentInsert = "'$CompanyID', '$PackageID', '$Amount', GETDATE(), '$PaymentType'";

    $PackageUpdateParam = "uploadCredit = '$NewUploadCredit', modifiedOn = GETDATE()";
    $PackageUpdateCond = "companyId = '$CompanyID'";

    $cond = "companyId = '$CompanyID' AND validityDate >= GETDATE()";
    $totalExist = isExist('company_packages', $cond);

    if ($totalExist == 0) {
        $errMsg = "Validity date expired for this company!";
        MsgBox($errMsg);
        ReDirect($baseURL . 'index.php?parent=packagesDetails');
    } else {
        if (Save('payment_history', $FieldPaymentInsert, $ValuePaymentInsert)) {
            if (Edit('company_packages', $PackageUpdateParam, $PackageUpdateCond)) {
                MsgBox('Credit added successfully.');
                ReDirect($baseURL . 'index.php?parent=packagesDetails');
            } else
                MsgBox('Failed to add credit!');
        } else
            MsgBox('Failed to save payment info!');
    }
}
