<?php
$qryCompanyName = "SELECT id, CompanyName FROM dataownercompany ORDER BY CompanyName ASC";
$rsQryCompanyName = $app->getDBConnection()->fetchAll($qryCompanyName);

$qryPackageName = "SELECT id, name, description FROM packages ORDER BY id ASC";
$rsQryPackageName = $app->getDBConnection()->fetchAll($qryPackageName);
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
                            <h2 class="card-title">Package Info</h2>
                        </header>
                        <div class="card-body">
                            <form class="form-horizontal form-bordered" action="" method="post">
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2">Company Select<span
                                                class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <select data-plugin-selectTwo class="form-control populate" name="CompanyID"
                                                id="CompanyID" required>
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
                                    <label class="col-lg-3 control-label text-lg-end pt-2">Package Select<span
                                                class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <select data-plugin-selectTwo class="form-control populate" name="packageID"
                                                id="packageID" required
                                                onchange="getPackageInfo(document.getElementById('packageID').value)">
                                            <option value="">Choose a package</option>
                                            <?PHP
                                            foreach ($rsQryPackageName as $row) {
                                                echo '<option value="' . $row->id . '">' . $row->description . ' (' . $row->name . ')' . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="amount">Amount</label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="amount" name="amount"
                                               placeholder="amount" readonly>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="maxNoUsers">Max No Users</label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="maxNoUsers" name="maxNoUsers"
                                               placeholder="max users" readonly>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="uploadCredits">Upload Credits</label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="uploadCredits" name="uploadCredits"
                                               placeholder="upload credits" readonly>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="storage">Storage</label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="storage" name="storage"
                                               placeholder="storage" readonly>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="formPerAccount">Form per Account</label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="formPerAccount"
                                               name="formPerAccount"
                                               placeholder="form per account" readonly>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="validityDate">Validity Date</label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="validityDate" name="validityDate"
                                               placeholder="validity date" readonly>
                                    </div>
                                </div>

                                <footer class="card-footer">
                                    <div class="row justify-content-end">
                                        <div class="col-lg-9">
                                            <input class="btn btn-primary" name="save" type="submit" id="save"
                                                   value="Save">
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

        <script type="text/javascript">
            function getPackageInfo(packageID, data) {
                $.ajax({
                    url: "CompanyInfo/package-info.php",
                    method: "GET",
                    datatype: "json",
                    data: {
                        packageId: packageID
                    },
                    success: function (response) {
                        //alert(response);
                        var data = response.split("|");
                        $('#amount').val(data[0]);
                        $('#maxNoUsers').val(data[1]);
                        $('#uploadCredits').val(data[2]);
                        $('#storage').val(data[3]);
                        $('#formPerAccount').val(data[4]);
                        $('#validityDate').val(data[5]);
                    }
                });
                return false;
            }
        </script>
    </div>

<?php
if (isset($_POST['save'])) {
    $CompanyID = $_POST['CompanyID'];
    $PackageID = $_POST['packageID'];
    $Amount = $_POST['amount'];
    $MaxNoUsers = $_POST['maxNoUsers'];
    $UploadCredits = $_POST['uploadCredits'];
    $Storage = $_POST['storage'];
    $FormPerAccount = $_POST['formPerAccount'];
    $ValidityDate = $_POST['validityDate'] . ' 23:59:59';

    $paymentType = PACKAGE_PAYMENT_TYPE_ID;

    $FieldPaymentInsert = "companyId, packageId, amount, createdDate, paymentType";
    $ValuePaymentInsert = "'$CompanyID', '$PackageID', '$Amount', GETDATE(), '$paymentType'";

    $FieldPackageInsert = "companyId, packageId, validityDate, uploadCredit, createdOn, modifiedOn";
    $ValuePackageInsert = "'$CompanyID', '$PackageID', '$ValidityDate', '$UploadCredits', GETDATE(), GETDATE()";

    $cond = "validityDate >= GETDATE() AND companyId = '$CompanyID'";
    $totalExist = isExist('company_packages', $cond);

    if ($totalExist <> 0) {
        $errMsg = "This company is already using a package!";
        MsgBox($errMsg);
        ReDirect($baseURL . 'index.php?parent=packagesDetails');
    } else {
        if (Save('payment_history', $FieldPaymentInsert, $ValuePaymentInsert)) {
            if (Save('company_packages', $FieldPackageInsert, $ValuePackageInsert)) {
                MsgBox('Saved Successfully.');
                ReDirect($baseURL . 'index.php?parent=packagesDetails');
            } else
                MsgBox('Failed to save package info!');
        } else
            MsgBox('Failed to save payment info!');
    }
}
