<?php
$qrySelectPSU = "SELECT PSU FROM PSUList WHERE CompanyID = ? ORDER BY PSU ASC";
$resQrySelectPSU = $app->getDBConnection()->fetchAll($qrySelectPSU, $loggedUserCompanyID);

$qrySelectUser = "SELECT id, UserName, FullName FROM userinfo WHERE UserName LIKE '$dataCollectorNamePrefix%' AND CompanyID = ? ORDER BY UserName ASC";
$resQrySelectUser = $app->getDBConnection()->fetchAll($qrySelectUser, $loggedUserCompanyID);

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
                                <label class="col-lg-3 control-label text-sm-end pt-2">PSU Select<span
                                        class="required">*</span></label>
                                <div class="col-lg-6">
                                    <!-- <select data-plugin-selectTwo id="SelectedPSU" name="SelectedPSU"
                                            class="form-control populate" required> -->




                                    <select data-plugin-selectTwo id="SelectedPSU" name="SelectedPSU[]"
                                        class="form-control populate" multiple required>




                                        <option value="">Choose PSU</option>
                                        <?PHP
                                        foreach ($resQrySelectPSU as $row) {
                                            echo '<option value="' . $row->PSU . '">' . $row->PSU . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">User Select<span
                                        class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate"
                                        name="SelectedUser" id="SelectedUser" required>
                                        <option value="">Choose user</option>
                                        <?PHP
                                        foreach ($resQrySelectUser as $row) {
                                            echo '<option value="' . $row->id . '">' . $row->UserName . ' | ' . substr($row->FullName, 0, 102) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2"
                                    for="NumberOfData">Number of Data<span class="required">*</span></label>
                                <div class="col-lg-6">
                                    <input type="number" class="form-control" id="NumberOfData" name="NumberOfData"
                                        placeholder="number Of data" required>
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
                    // $SelectedPSU = xss_clean($_REQUEST['SelectedPSU']);
                    // $SelectedUser = xss_clean($_REQUEST['SelectedUser']);
                    // $NumberOfData = xss_clean($_REQUEST['NumberOfData']);

                    // $param = "PSUUserID = '$SelectedUser', NumberOfRecordForMainSurvey = '$NumberOfData'";
                    // $cond = "PSU = '$SelectedPSU' AND CompanyID = $loggedUserCompanyID";

                    // $condIsExist = "PSU = '$SelectedPSU' AND CompanyID = $loggedUserCompanyID AND PSUUserID = $SelectedUser AND NumberOfRecordForMainSurvey > 0";
                    // $totalExist = isExist('PSUList', $condIsExist);

                    // if ($totalExist > 0) {
                    //     $msg = "User alreay assigned to this PSU!";
                    // } else {
                    //     if (Edit('PSUList', $param, $cond)) {
                    //         $msg = 'Successfully saved.';
                    //     } else
                    //         $msg = 'Failed to save!';
                    // }
                    // MsgBox($msg);
                    // ReDirect($baseURL . 'index.php?parent=AssignUserToPSU');















                    $SelectedPSUs = $_REQUEST['SelectedPSU']; // this is now an array
                    $SelectedUser = xss_clean($_REQUEST['SelectedUser']);
                    $NumberOfData = xss_clean($_REQUEST['NumberOfData']);

                    $SelectedPSUs = $_REQUEST['SelectedPSU']; // now an array
                    $SelectedUser = xss_clean($_REQUEST['SelectedUser']);
                    $NumberOfData = xss_clean($_REQUEST['NumberOfData']);

                    $assigned = [];
                    $alreadyAssigned = [];
                    $failed = [];

                    foreach ($SelectedPSUs as $psu) {
                        $SelectedPSU = xss_clean($psu);

                        $param = "PSUUserID = '$SelectedUser', NumberOfRecordForMainSurvey = '$NumberOfData'";
                        $cond = "PSU = '$SelectedPSU' AND CompanyID = $loggedUserCompanyID";

                        $condIsExist = "PSU = '$SelectedPSU' AND CompanyID = $loggedUserCompanyID 
                                        AND PSUUserID = $SelectedUser AND NumberOfRecordForMainSurvey > 0";
                        $totalExist = isExist('PSUList', $condIsExist);

                        if ($totalExist > 0) {
                            $alreadyAssigned[] = $SelectedPSU;
                        } else {
                            if (Edit('PSUList', $param, $cond)) {
                                $assigned[] = $SelectedPSU;
                            } else {
                                $failed[] = $SelectedPSU;
                            }
                        }
                    }

                    // Build one combined message
                    $msg = "";
                    if (!empty($assigned)) {
                        $msg .= "✅ Successfully assigned PSUs: " . implode(', ', $assigned) . ".\n";
                    }
                    if (!empty($alreadyAssigned)) {
                        $msg .= "⚠️ Already assigned PSUs: " . implode(', ', $alreadyAssigned) . ".\n";
                    }
                    if (!empty($failed)) {
                        $msg .= "❌ Failed to assign PSUs: " . implode(', ', $failed) . ".\n";
                    }

                    if (empty($msg)) {
                        $msg = "No PSU was processed.";
                    }

                    MsgBox(nl2br($msg));

                    echo '<div class="alert alert-info" style="margin:20px;">' . nl2br($msg) . '</div>';
                    echo "<script>
                            setTimeout(function() {
                                window.location.href = '$baseURL' + 'index.php?parent=AssignUserToPSU';
                            }, 4000);
                        </script>";
                    exit;
                }
                ?>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>



<script>
    $(window).on('load', function() {
        if ($.fn.select2) {
            try {
                $('#SelectedPSU').select2('destroy');
            } catch (e) {
                console.warn('No existing Select2 instance to destroy.');
            }

            $('#SelectedPSU').select2({
                placeholder: "Choose one or more PSUs",
                allowClear: true,
                closeOnSelect: false, // 👈 keeps dropdown open for easier multi-select
                tags: true
            });

            console.log('Select2 reinitialized in multiple mode.');
        } else {
            console.warn("Select2 not loaded!");
        }
    });
</script>