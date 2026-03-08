<?php
$queryDC = "SELECT id, UserName, FullName FROM userinfo WHERE UserName LIKE 'Div%' AND CompanyID = ? ORDER BY id ASC";
$resQueryDC = $app->getDBConnection()->fetchAll($queryDC, $loggedUserCompanyID);

$queryDist = "SELECT id, UserName, FullName FROM userinfo WHERE UserName LIKE 'Dist%' AND CompanyID = ? ORDER BY id ASC";
$resQueryDist = $app->getDBConnection()->fetchAll($queryDist, $loggedUserCompanyID);
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
                                <label class="col-lg-3 control-label text-sm-end pt-2">Division Co-ordinator Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="SelectedDivCoID" name="SelectedDivCoID"
                                            class="form-control populate" required>
                                        <option value="">Choose division co-ordinator</option>
                                        <?PHP
                                        foreach ($resQueryDC as $row) {
                                            echo "<option value=\"" . $row->id . "\">" . $row->UserName . " | " . $row->FullName . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">District Co-ordinator Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="SelectedDistCoID" name="SelectedDistCoID"
                                            class="form-control populate" required>
                                        <option value="">Choose district co-ordinator</option>
                                        <?PHP
                                        foreach ($resQueryDist as $row) {
                                            echo "<option value=\"" . $row->id . "\">" . $row->UserName . " | " . $row->FullName . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-primary" name="show" type="submit" id="show"
                                               value="Assign">
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </section>
                <?php

                if ($_REQUEST['show'] === 'Assign') {
                    $SelectedDistCoID = $_REQUEST['SelectedDistCoID'];
                    $SelectedDivCoID = $_REQUEST['SelectedDivCoID'];

                    $param = "DivCoordinatorID = '$SelectedDivCoID'";
                    $cond = "DistCoordinatorID = '$SelectedDistCoID' AND CompanyID = $loggedUserCompanyID";

                    if (Edit('assignsupervisor', $param, $cond)) {
                        $msg = 'Successfully saved.';
                    } else
                        $msg = 'Failed to save!';

                    MsgBox($msg);
                    ReDirect($baseURL . 'index.php?parent=AssignDistCoToDivCo');
                }
                ?>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>
