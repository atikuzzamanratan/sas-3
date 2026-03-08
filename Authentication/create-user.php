<?php
if ($loggedUserName == 'admin') {
    $qryCompnay = "SELECT id, CompanyName FROM dataownercompany";
    $rsQryCompany = $app->getDBConnection()->fetchAll($qryCompnay);
} else {
    $qryCompnay = "SELECT id, CompanyName FROM dataownercompany WHERE id = ?";
    $rsQryCompany = $app->getDBConnection()->fetchAll($qryCompnay, $loggedUserCompanyID);
}
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
                            <h2 class="card-title">User Form</h2>
                        </header>
                        <div class="card-body">
                            <form class="form-horizontal form-bordered" action="" method="post">
                                <div class="form-group row pb-4">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="userName">Username<span class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="userName" name="userName"
                                               placeholder="username" required>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="userFullName">Full Name<span class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="userFullName" name="userFullName"
                                               placeholder="full name" required>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="userPassword">Password<span class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="userPassword" name="userPassword"
                                               placeholder="password" required>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="mobileNumber">Mobile Number<span class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="mobileNumber" name="mobileNumber"
                                               placeholder=" 11 digit mobile number" minlength="11" maxlength="11" required>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="userEmail">Email</label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="userEmail" name="userEmail"
                                               placeholder="email">
                                    </div>
                                </div>

                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-sm-end pt-2">Project Select<span
                                                class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <select data-plugin-selectTwo id="projectID" name="projectID"
                                                class="form-control populate" title="Please select project" required>
                                            <optgroup label="Select project">
                                                <?PHP
                                                foreach ($rsQryCompany as $row) {
                                                    echo '<option value="' . $row->id . '">' . $row->CompanyName . '</option>';
                                                }
                                                ?>
                                            </optgroup>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="supportID">Support ID</label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="supportID" name="supportID"
                                               placeholder="support id">
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
    $UserName = xss_clean($_POST['userName']);
    $UserFullName = xss_clean($_POST['userFullName']);
    $UserPassword = xss_clean($_POST['userPassword']);
    $encPassword = password_hash($UserPassword, PASSWORD_DEFAULT);
    $MobileNumber = xss_clean($_POST['mobileNumber']);
    $UserEmail = xss_clean($_POST['userEmail']);
    $CompanyID = xss_clean($_POST['projectID']);
    $SupportID = xss_clean($_POST['supportID']);

    $Field = "UserName, FullName, Password, enc_passw, CompanyID, MobileNumber, EmailAddress, CreatedBy, CreatedDate, SupportID, IsActive";
    $Value = "'$UserName', N'$UserFullName', '$UserPassword', '$encPassword', '$CompanyID', '$MobileNumber', '$UserEmail', '$loggedUserName', GETDATE(), N'$SupportID', '1'";

    $CompanyCurrentUser = getValue('userinfo', 'COUNT(id)', "CompanyID = $CompanyID");

    $currentPackageInfo = "SELECT packageId, maxUserNo FROM packages 
    JOIN company_packages ON (packages.id = company_packages.packageId)
    WHERE companyId = ? AND company_packages.validityDate >= getdate()";
    $userMaxAssain = $app->getDBConnection()->fetch($currentPackageInfo, $CompanyID);
    $MaxUserNo = $userMaxAssain->maxUserNo;

    $cond = "UserName='$UserName'";
    $totalExist = isExist('userinfo', $cond);

    if ($totalExist <> 0) {
        $msg = "Sorry username already exist!";
    } else {
        if (empty($MaxUserNo)) {
            $msg = "Please purchage a package first!";
        } elseif ($MaxUserNo <= $CompanyCurrentUser) {
            $msg = "Your project's users limit is over!";
        } else {
            if (Save('userinfo', $Field, $Value)) {
                $msg = 'User created successfully.';
            } else
                $msg = 'Failed to create user!';
        }
    }
    MsgBox($msg);
}
