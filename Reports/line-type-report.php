<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$cn = ConnectDB();
$qryFormName = "SELECT id, FormName FROM datacollectionform WHERE Status = 'Active' AND CompanyID = ? ORDER BY id ASC";
$rsQryFormName = $app->getDBConnection()->fetchAll($qryFormName, $loggedUserCompanyID);

$qrySupervisor = "SELECT id FROM assignsupervisor WHERE SupervisorID = ?";
$rsSupervisor = $app->getDBConnection()->fetch($qrySupervisor, $loggedUserID);
$SuperID = $rsSupervisor->id;

if (strpos($loggedUserName, 'dist') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = ? AND a.DistCoordinatorID = ?";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID, $loggedUserID);
} elseif (strpos($loggedUserName, 'val') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = ? AND a.ValidatorID = ?";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID, $loggedUserID);
} else {
    $divQuery = "SELECT DISTINCT DivisionName , DivisionCode FROM PSUList WHERE CompanyID = ? ORDER BY DivisionName ASC";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID);
}

if (strpos($loggedUserName, 'cval') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = ?";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID);
}

if(isset($_REQUEST['show']) && $_REQUEST['show'] === 'Show') {
    $FormID = xss_clean($_REQUEST['FormID']);
    $moduleName = xss_clean($_REQUEST['moduleName']);
    $DataStatus = xss_clean($_REQUEST['DataStatus']);

    $DivisionCode = xss_clean($_REQUEST['DivisionCode']);
    $DistrictCode = xss_clean($_REQUEST['DistrictCode']);
    $UpazilaCode = xss_clean($_REQUEST['UpazilaCode']);
    $UnionWardCode = xss_clean($_REQUEST['UnionWardCode']);
    $MauzaCode = xss_clean($_REQUEST['MauzaCode']);
    $VillageCode = xss_clean($_REQUEST['VillageCode']);

    $SelectedUserID = xss_clean($_REQUEST['SelectedUserID']);

    $StartDate = xss_clean($_REQUEST['startDate']);
    $EndDate = xss_clean($_REQUEST['endDate']);
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
                                    <select data-plugin-selectTwo class="form-control populate" name="FormID"
                                        id="FormID" title="Please select a form" required
                                        onchange="getModuleName(document.getElementById('FormID').value)">
                                        <option value="">Select Form</option>
                                        <?PHP
                                        foreach ($rsQryFormName as $row) {
                                            echo '<option value="' . $row->id . '" ' . (isset($FormID) && !empty($FormID) && $row->id == $FormID ? 'selected' : '') . '>' . $row->FormName . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Module Select<span
                                        class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="moduleName"
                                        id="moduleName" title="Please select a module" required>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">
                                    Status Select<span
                                        class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="DataStatus"
                                        id="DataStatus" title="select Status" required>
                                        <option value="">Select Status</option>
                                        <option value="Pending" <?php echo (isset($DataStatus) && $DataStatus == 'Pending' ? 'selected' : ''); ?>>Pending</option>
                                        <option value="Approved" <?php echo (isset($DataStatus) && $DataStatus == 'Approved' ? 'selected' : ''); ?>>Approved</option>
                                        <option value="UnApproved" <?php echo (isset($DataStatus) && $DataStatus == 'UnApproved' ? 'selected' : ''); ?>>UnApproved</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Division Select
                                    <?php if (strpos($loggedUserName, 'admin') === false) { ?>
                                        <span class="required">*</span>
                                    <?php } ?>
                                </label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="DivisionCode"
                                        id="DivisionCode"
                                        <?php if (strpos($loggedUserName, 'admin') === false) { ?>
                                        required
                                        <?php } ?>
                                        onchange="ShowDropDown4('DivisionCode', 'DistrictDiv','userDiv', 'DistrictUser', ['DivisionCode'])">
                                        <option value="">Choose division</option>
                                        <?PHP
                                        foreach ($rsDivQuery as $row) {
                                            echo '<option value="' . $row->DivisionCode . '" ' . (isset($DivisionCode) && !empty($DivisionCode) && $row->DivisionCode == $DivisionCode ? 'selected' : '') . '>' . $row->DivisionName . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div id="geoDiv" style="display: none">
                                <div class="form-group row pb-3" id="DistrictDiv"></div>
                                <div class="form-group row pb-3" id="UpazilaDiv"></div>
                                <div class="form-group row pb-3" id="UnionWardDiv"></div>
                                <div class="form-group row pb-3" id="MauzaDiv"></div>
                                <div class="form-group row pb-3" id="VillageDiv"></div>
                            </div>
                            <script>
                                $(document).ready(function() {
                                    $('#DivisionCode').on('change', function() {
                                        //alert('helllo Tessst');
                                        if (this.value > 1) {
                                            $("#geoDiv").show();
                                        } else {
                                            $("#geoDiv").hide();
                                        }
                                    });
                                });
                            </script>
                            <div class="form-group row pb-3" id="userDiv">
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
                                        } else {
                                            $qryDistUser = "SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '$dataCollectorNamePrefix%' AND CompanyID = ? and UserName = ? ORDER BY UserName ASC";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserCompanyID, $loggedUserName);
                                        }

                                        foreach ($resQryDistUser as $row) {
                                            echo '<option value="' . $row->id . '" ' . (isset($SelectedUserID) && !empty($SelectedUserID) && $row->id == $SelectedUserID ? 'selected' : '') . '>' . $row->UserName . ' | ' . substr($row->FullName, 0, 102) . '</option>';
                                        }
                                        ?>

                                    </select>
                                </div>
                            </div>

                            <!-- <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2">Date<span
                                        class="required">*</span></label>
                                <div class="col-lg-6">
                                    <div class="input-daterange input-group">
                                        <input type="date" class="form-control" id="startDate"
                                            name="startDate" required>
                                    </div>
                                </div>
                            </div> -->

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2">Date range<span
                                        class="required">*</span></label>
                                <div class="col-lg-6">
                                    <div class="input-daterange input-group">
                                        <input type="date" class="form-control" id="startDate"
                                            name="startDate" required value="<?php echo isset($StartDate) ? $StartDate : ''; ?>">
                                        <span class="input-group-text border-start-0 border-end-0 rounded-0">to</span>
                                        <input type="date" class="form-control" id="endDate" name="endDate" required value="<?php echo isset($EndDate) ? $EndDate : ''; ?>">
                                    </div>
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
                        function getModuleName(formID, moduleName) {
                            $.ajax({
                                url: "Reports/get-module-name-list-question-type.php",
                                method: "GET",
                                datatype: "html",
                                data: {
                                    formID: formID,
                                    moduleName: moduleName,
                                    questionType: 'line',
                                },
                                success: function(response) {
                                    //alert(response);
                                    $('#moduleName').html(response);
                                }
                            });
                            return false;
                        }
                    </script>
                </section>
                <?php
                if ($_REQUEST['show'] === 'Show') {
                    try {
                        $ColumnArrayType = array();
                        $ColumnLabelArrayType = array();
                        array_push($ColumnArrayType, "XFormRecordId");
                        array_push($ColumnArrayType, "PSUNo");
                        array_push($ColumnArrayType, "UserID");
                        array_push($ColumnArrayType, "RecordLineNo");

                        array_push($ColumnLabelArrayType, "RecordId");
                        array_push($ColumnLabelArrayType, "PSU");
                        array_push($ColumnLabelArrayType, "UserID");
                        array_push($ColumnLabelArrayType, "LineNo");
                        // $ColumnArrayType=["XFormRecordId","UserID","RecordLineNo"];

                        $FormName = getValue('datacollectionform', 'FormName', "id = $FormID");

                        // $MasterDataQuery = "SELECT ColumnName,ColumnLabel FROM ModuleInfo where FormId='$FormID' AND ModuleName = '$ModulemName' AND QuestionType='line' AND ColumnName NOT IN('LineNo') ORDER BY id ASC";
                        // exit();



                        $MasterDataQuery = "SELECT ColumnName,ColumnLabel FROM ModuleInfo where FormId='$FormID' AND ModuleName = '$moduleName' AND QuestionType='line' AND ColumnName NOT IN('LineNo') ORDER BY id ASC";



                        $rsResult1 = $app->getDBConnection()->fetchAll($MasterDataQuery);

                        foreach ($rsResult1 as $row3) {
                            $ColumnLabelData = $row3['ColumnName'] . " (" . $row3['ColumnLabel'] . ")";
                            array_push($ColumnArrayType, $row3['ColumnName']);
                            array_push($ColumnLabelArrayType, $ColumnLabelData);
                        }

                        $StartDateTime = date("Y-m-d", strtotime($StartDate)) . $defaultStartTimeString;
                        $EndDateTime = date("Y-m-d", strtotime($EndDate)) . $defaultEndTimeString;
                        $DataDate = "$StartDateTime to $EndDateTime";
                        // $qry2 = "spReport_LineTypeReport_B_Type  '$FormID','$DataStatus', 'LineNo','$ModulemName','$StartDateTime', '$EndDateTime','$SelectedUserID','$DivisionCode','$DistrictCode','$UpazilaCode','$UnionWardCode','$MauzaCode','$VillageCode'";




                        $qry2 = "spReport_LineTypeReport_B_Type  '$FormID','$DataStatus', 'LineNo','$moduleName','$StartDateTime', '$EndDateTime','$SelectedUserID','$DivisionCode','$DistrictCode','$UpazilaCode','$UnionWardCode','$MauzaCode','$VillageCode'";





                        $CallProcOutput = db_query($qry2, $cn);
                        $TotalRow = db_num_rows($CallProcOutput);
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                    $count = 0;

                    // $dataURL = $baseURL . 'Reports/ajax-data/data-send-value-report-ajax-data.php?frmID=' . $FormID . '&uid=' . $UserID . '&colName=' . $ColumnName;
                ?>
                    <section class="card">
                        <header class="card-header">
                            <div class="card-title">Data Report : <?php echo $FormName; ?></div>
                            <div class="title">
                                Module Name:<?php echo $modulemName ?> ->DataStatus:<?php echo $DataStatus ?> </h3>
                                <?php
                                if ($DivisionCode) {
                                    echo "->Division: " . getValue('PSUList', 'DivisionName', "DivisionCode = $DivisionCode");
                                }
                                if ($DistrictCode) {
                                    echo "->District: " . getValue('PSUList', 'DistrictName', "DistrictCode = $DistrictCode");
                                }
                                if ($UpazilaCode) {
                                    echo "->UpazilaName: " . getValue('PSUList', 'UpazilaName', "DistrictCode = $DistrictCode AND UpazilaCode = $UpazilaCode");
                                }
                                if ($UnionWardCode) {
                                    echo "->UnionWardName: " . getValue('PSUList', 'UnionWardName', "DistrictCode = $DistrictCode AND UpazilaCode = $UpazilaCode AND UnionWardCode = $UnionWardCode");
                                }
                                if ($MauzaCode) {
                                    echo "->MauzaName: " . getValue('PSUList', 'MauzaName', "DistrictCode = $DistrictCode AND UpazilaCode = $UpazilaCode AND UnionWardCode = $UnionWardCode AND MauzaCode = $MauzaCode");
                                }
                                if ($VillageCode) {
                                    echo "->VillageName: " . getValue('PSUList', 'VillageName', "DistrictCode = $DistrictCode AND UpazilaCode = $UpazilaCode AND UnionWardCode = $UnionWardCode AND MauzaCode = $MauzaCode AND VillageCode = $VillageCode");
                                }
                                ?>
                            </div>
                            <div class="title">
                                <?php
                                if ($SelectedUserID) {
                                    echo "User Name: " . getValue('userinfo', 'FullName', "id = $SelectedUserID");
                                }
                                ?>
                            </div>
                            <div class="card-subtitle">Date : <?php echo $DataDate; ?></div>
                            <div class="form-group ml-2 row col-lg-1 ">
                                <button class="btn ml-2 btn-success"
                                    onclick="exportTableToExcel('LineTypeReport', 'LineTypeReport-<?php echo $StartDateTime; ?>-<?php echo $EndDateTime; ?>')">
                                    Download
                                </button>
                            </div>
                        </header>
                        <div class="card-body">
                            <div class="table-responsive table-container">
                                <table class="table table-responsive-lg table-bordered table-striped table-sm mb-0" id="LineTypeReport">

                                    <thead id="tHeaders">
                                        <tr role="row">
                                            <?php
                                            echo "<th>SL No</th>";
                                            foreach ($ColumnLabelArrayType as $ColumnName) {
                                                echo "<th>" . $ColumnName . "</th>";
                                            }

                                            ?>
                                        </tr>

                                        <tr role="row">

                                        </tr>
                                    </thead>
                                    <tbody>

                                        <tr align="center" class="textRpt">
                                            <?php

                                            // while ($row = $rsResult->fetch(PDO::FETCH_ASSOC)) {
                                            while ($row = db_fetch_array($CallProcOutput)) {
                                                $count++;
                                                echo "<tr>";
                                                echo "<td>$count</td>";
                                                foreach ($ColumnArrayType as $ColumnName) {
                                                    if (is_null($row[$ColumnName])) {
                                                        echo "<td>NULL</td>";
                                                    } else {
                                                        echo "<td>" . $row[$ColumnName] . "</td>";
                                                    }
                                                }

                                                echo "</tr>";
                                            }
                                            ?>

                                        </tr>

                                    </tbody>

                                </table>
                                <td><?php echo "&nbsp; Total Row :" . $count; ?> </td>
                            </div>
                        </div>
                    </section>
                <?php
                }
                ?>
                <!-- end: page -->
                </td>

            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

<style>
    .table-container {
        height: 550px;
        /* Fixed height for vertical scrolling */
        width: 100%;
        /* Optional: set container width */
        overflow-y: auto;
        /* Enables vertical scrolling */
        border: 1px solid #ccc;
        /* Optional: Add a border for clarity */
    }

    table {
        width: 100%;
        /* Ensures the table fits the container width */
        border-collapse: collapse;
        /* Removes gaps between cells */
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
        /* Adjust alignment if needed */
    }

    th {
        background-color: #f9f9f9;
        /* Optional: header background */
        position: sticky;
        /* Makes the header stick when scrolling */
        top: 0;
        /* Required for sticky headers */
        z-index: 1;
        /* Ensures header stays on top */
    }
</style>

<script>
      $(document).ready(function() {

        populateDropdowns(
            <?php echo isset($DivisionCode) && $DivisionCode !== '' ? $DivisionCode : 'null'; ?>,
            <?php echo isset($DistrictCode) && $DistrictCode !== '' ? $DistrictCode : 'null'; ?>,
            <?php echo isset($UpazilaCode) && $UpazilaCode !== '' ? $UpazilaCode : 'null'; ?>,
            <?php echo isset($UnionWardCode) && $UnionWardCode !== '' ? $UnionWardCode : 'null'; ?>,
            <?php echo isset($MauzaCode) && $MauzaCode !== '' ? $MauzaCode : 'null'; ?>,
            <?php echo isset($VillageCode) && $VillageCode !== '' ? $VillageCode : 'null'; ?>
        );

        getModuleName(<?php echo isset($FormID) && $FormID !== '' ? $FormID : 'null'; ?>, <?php echo isset($moduleName) && $moduleName !== '' ? "'" . $moduleName . "'" : 'null'; ?>);
    });
</script>