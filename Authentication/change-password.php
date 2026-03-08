<?php
include_once 'Components/header.php';
?>

    <div class="inner-wrapper">
        <section role="main" class="content-body">
            <header class="page-header">
                <h2>Change Password</h2>
                <?php include_once 'Components/header-home-button.php'; ?>
            </header>

            <!-- start: page -->
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-8">
                    <form id="changepassword" name="changepassword" action="" method="post" class="form-horizontal">
                        <section class="card">
                            <header class="card-header">
                                <h2 class="card-title">Change Password</h2>
                            </header>
                            <div class="card-body">
                                <div class="form-group row pb-3">
                                    <label class="col-sm-3 control-label text-sm-end pt-2">Current Password <span
                                                class="required">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="password" name="PreviousPassword" class="form-control"
                                               placeholder="current password" required/>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-sm-3 control-label text-sm-end pt-2">New Password <span
                                                class="required">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="password" name="NewPassword" class="form-control"
                                               placeholder="new password" required/>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-sm-3 control-label text-sm-end pt-2">Confirm Password <span
                                                class="required">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="password" name="ReNewPassword" class="form-control"
                                               placeholder="confirm password" required/>
                                    </div>
                                </div>

                            </div>
                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-sm-9">
                                        <input class="btn btn-warning" name="Save" type="submit" id="Save" value="Save">
                                        <button type="reset" class="btn btn-default">Reset</button>
                                    </div>
                                </div>
                            </footer>
                        </section>
                    </form>
                </div>
                <div class="col-lg-2"></div>
            </div>
            <!-- end: page -->
        </section>
    </div>

<?php
if ($_REQUEST['Save'] === 'Save') {
    $User = xss_clean($_SESSION['User']);
    $PreviousPassword = xss_clean($_REQUEST['PreviousPassword']);
    $NewPassword = xss_clean($_REQUEST['NewPassword']);
    $ReNewPassword = xss_clean($_REQUEST['ReNewPassword']);
    $encPassword = password_hash($NewPassword, PASSWORD_DEFAULT);

    $cond = "UserName='$User' and Password='$PreviousPassword'";

    $userCount = isExist('userinfo', $cond);

    if ($userCount == '1') {
        if ($NewPassword != $ReNewPassword) {
            MsgBox('New password and re-new password do not match!.');
        } else {
            $param = "Password='$NewPassword', enc_passw='$encPassword'";
            if (Edit('userinfo', $param, $cond)) {
                MsgBox('Password Updated Successfully ');
                ReDirect("index.php?parent=home");
            } else
                MsgBox('Failed to update password');
        }
    } else {
        MsgBox('User not found!');
    }

}
?>

<?php
include_once 'Components/footer.php';
