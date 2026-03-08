<?php
$qrySupervisor = "SELECT id FROM assignsupervisor WHERE SupervisorID = ?";
$rsSupervisor = $app->getDBConnection()->fetch($qrySupervisor, $loggedUserID);
$SuperID = $rsSupervisor->id;

if ($_REQUEST['show'] === 'Show') {
    $SelectedFormID = xss_clean($_REQUEST['SelectedFormID']);
    $SelectedUserID = xss_clean($_REQUEST['SelectedUserID']);
    $SelectedFormStatus = xss_clean($_REQUEST['SelectedFormStatus']);
    $SelectedColumnName = xss_clean($_REQUEST['columnName']);
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
                                        onchange="getColumnName(document.getElementById('SelectedFormID').value)">
                                        <option value="">Choose Form</option>
                                            <?PHP
                                            $qryForm = $app->getDBConnection()->query("SELECT id, FormName FROM datacollectionform WHERE Status = 'Active' AND CompanyID = ?", $loggedUserCompanyID);

                                            foreach ($qryForm as $row) {
                                                echo '<option value="' . $row->id . '"' . (isset($SelectedFormID) && !empty($SelectedFormID) && $row->id == $SelectedFormID ? ' selected' : '') . '>' . $row->FormName . '</option>';
                                            }
                                            ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Data Status<span
                                        class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="SelectedFormStatus" name="SelectedFormStatus"
                                        class="form-control populate" required>
                                        <option value="">Choose Status</option>
                                        <option value="1" <?php echo isset($SelectedFormStatus) && $SelectedFormStatus == '1' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="0" <?php echo isset($SelectedFormStatus) && $SelectedFormStatus == '0' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="2" <?php echo isset($SelectedFormStatus) && $SelectedFormStatus == '2' ? 'selected' : ''; ?>>Un-approved</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">User Select<span
                                        class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate"
                                        name="SelectedUserID"
                                        id="SelectedUserID" title="Please select user" required>
                                        <option value="">Choose user</option>
                                        <?PHP
                                        if ($loggedUserName == 'admin') {
                                            $qryDistUser = "SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '$dataCollectorNamePrefix%' ORDER BY UserName ASC";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser);
                                        } else if (strpos($loggedUserName, 'admin') !== false) {
                                            $qryDistUser = "SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '$dataCollectorNamePrefix%' AND CompanyID = ? ORDER BY UserName ASC";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserCompanyID);
                                        } else if ($SuperID) {
                                            $qryDistUser = "SELECT u.id, u.UserName, u.FullName FROM assignsupervisor as a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND u.UserName LIKE '$dataCollectorNamePrefix%' AND a.SupervisorID = ?";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserID);
                                        } else if (strpos($loggedUserName, 'dist') !== false) {
                                            $qryDistUser = "SELECT u.id, u.UserName, u.FullName FROM assignsupervisor as a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND u.UserName LIKE '$dataCollectorNamePrefix%' AND a.DistCoordinatorID = ?";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserID);
                                        } else if (strpos($loggedUserName, 'val') !== false) {
                                            if (strpos($loggedUserName, 'cval') === false) {
                                                $qryDistUser = "SELECT u.id, u.UserName, u.FullName FROM assignsupervisor as a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND u.UserName LIKE '$dataCollectorNamePrefix%' AND a.ValidatorID = ?";
                                                $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserID);
                                            } else {
                                                $qryDistUser = "SELECT u.id, u.UserName, u.FullName FROM assignsupervisor as a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND u.UserName LIKE '$dataCollectorNamePrefix%'";
                                                $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser);
                                            }
                                        } else {
                                            $qryDistUser = "SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '$dataCollectorNamePrefix%' AND CompanyID = ? and UserName = ? ORDER BY UserName ASC";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserCompanyID, $loggedUserName);
                                        }

                                        foreach ($resQryDistUser as $row) {
                                            echo '<option value="' . $row->id . '"' . (isset($SelectedUserID) && !empty($SelectedUserID) && $row->id == $SelectedUserID ? ' selected' : '') . '>' . $row->UserName . ' | ' . substr($row->FullName, 0, 102) . '</option>';
                                        }
                                        ?>

                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Column Select<span
                                        class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="columnName"
                                        id="columnName" title="Please select a column" required>
                                        <option value="">Choose a column</option>
                                        <?php
                                        /*$qry = "SELECT ColumnName, ColumnLabel FROM xformcolumnname WHERE FormId = ?";
                                        $resQry = $app->getDBConnection()->fetchAll($qry, $formIdMainData);
                                        foreach ($resQry as $row) {
                                            echo '<option value="' . strip_tags($row->ColumnName) . '"' . (isset($SelectedColumnName) && !empty($SelectedColumnName) && strip_tags($row->ColumnName) == $SelectedColumnName ? ' selected' : '') . '>' . strip_tags($row->ColumnName) . ' (' . $row->ColumnLabel . ')' . '</option>';
                                        }*/
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-primary" name="show" type="submit" id="show"
                                            value="Show">

                                        <button type="button" class="btn btn-secondary ms-4" id="clearForm">Clear</button>
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                    <script type="text/javascript">
                        function getColumnName(formID, data) {
                            $.ajax({
                                url: "../ViewData/get-column-name-list.php",
                                method: "GET",
                                datatype: "html",
                                data: {
                                    formID: formID
                                },
                                success: function(response) {
                                    //alert(response);
                                    $('#columnName').html(response);
                                }
                            });
                            return false;
                        }
                    </script>
                </section>
                <?php

                if ($_REQUEST['show'] === 'Show') {
                    $SelectedCompanyID = getValue('datacollectionform', 'CompanyID', "id = $SelectedFormID");

                    $dataParams = "selFormID=$SelectedFormID&selUserID=$SelectedUserID&selDataStatus=$SelectedFormStatus&selColName=$SelectedColumnName&ci=$SelectedCompanyID&lun=$loggedUserName&lui=$loggedUserID";

                    $dataURL = $baseURL . "ViewData/ajax-data/view-master-data-ajax-data.php?$dataParams";

                    //Modified the Logic to show the column name
                    if ($_REQUEST['selColName'] != '') {
                        $SelectedColumnName = $_REQUEST['selColName'];
                    }

                    $SelectedColumnName = ucwords(str_replace("_", " ", $SelectedColumnName));

                ?>
                    <section class="card">
                        <div class="card-header">
                            <div class="card-title">Form
                                : <?php echo getValue('datacollectionform', 'FormName', "id = $SelectedFormID"); ?></div>
                            <div class="card-subtitle"></div>
                        </div>
                        <div class="card-body" id="tableWrap">
                            <table class="table table-bordered table-striped" id="datatable-approveddata-ajax"
                                data-url="<?php echo $dataURL; ?>">
                                <thead>
                                    <tr>
                                        <th>Record ID</th>
                                        <th>PSU</th>
                                        <th>Division Name</th>
                                        <th>District Name</th>
                                        <th>User</th>
                                        <th>Data Name</th>
                                        <th><?php echo $SelectedColumnName; ?></th>
                                        <!--<th>Value</th>-->
                                        <th>Entry Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    <!-- end: page -->
                <?php
                }
                ?>

                <script>
                    function ShowDataDetail(dataFromID, recordID, isAproved, psu, loggedUserID, agentID, XFormsFilePath) {
                        $.ajax({
                            url: "ViewData/ajax-data/data-detail-view.php",
                            method: "GET",
                            data: {
                                dataFromID: dataFromID,
                                id: recordID,
                                status: isAproved,
                                psu: psu,
                                loggedUserID: loggedUserID,
                                agentID: agentID,
                                XFormsFilePath: XFormsFilePath
                            },
                            success: function(response) {
                                $("#dataViewDiv").html(response);
                            }
                        });
                        return false;
                    }
                </script>

                <div class="modal fade bd-example-modal-lg" id="viewDataModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div id="dataViewDiv" class="modal-content"></div>
                    </div>
                </div>
                
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>