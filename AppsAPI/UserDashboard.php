<?PHP
require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$UserID = $app->cleanInput($_GET['UserID']);
$AuthToken = $_GET['authToken'];

if ($AuthToken != $AuthTokenValue) {
    echo $unAuthorizedMsg;
    exit();
}

$resQry = $app->getDBConnection()->fetch("SELECT id,CompanyID FROM userinfo Where id = ?", $UserID);
$CompanyID = $resQry->CompanyID;

$qryFormString = "SELECT dcf.id, dcf.FormName FROM assignformtoagent aftu
JOIN datacollectionform dcf on dcf.id = aftu.FormID 
WHERE dcf.Status = 'Active' AND aftu.UserID = ? 
ORDER BY dcf.id DESC";
$qryForm = $app->getDBConnection()->query($qryFormString, $UserID);

include_once '../Components/header-includes.php';
?>
<div class="row">
    <div class="col-lg-12 mb-3">
        <section class="card">
            <div class="card-body">
                <form class="form-horizontal form-bordered" action="" method="post">
                    <div class="form-group row pb-3">
                        <label class="col-lg-3 control-label text-lg-end pt-2">Form Select<span
                                    class="required">*</span></label>
                        <div class="col-lg-6">
                            <select data-plugin-selectTwo class="form-control populate" name="FormID"
                                    id="FormID">
                                <option value="">Select Form</option>
                                <?PHP
                                foreach ($qryForm as $row) {
                                    echo '<option value="' . $row->id . '">' . $row->FormName . '</option>';
                                }
                                ?>

                            </select>
                        </div>
                    </div>
                    <footer class="card-footer">
                        <div class="row justify-content-end">
                            <div class="col-lg-9">
                                <input class="btn btn-primary" name="show" type="submit" id="show"
                                       value="Show">
                            </div>
                        </div>
                    </footer>
                </form>
            </div>
        </section>
    </div>
</div>
<?php
if ($_REQUEST['show'] === "Show") {
    $FormID = $_REQUEST['FormID'];

    $FormName = getName('FormName', 'datacollectionform', $FormID);

    $SelectedUserName = getValue('userinfo', 'UserName', "id = $UserID");
    $SelectedUserFullName = getValue('userinfo', 'FullName', "id = $UserID");
    $SelectedUserNameWithFullName = $SelectedUserFullName . ' (<b>' . $SelectedUserName . '</b>)';

    $resXFormsQueryPending = $app->getDBConnection()->fetch("SELECT COUNT(id) AS NumberPending FROM xformrecord WHERE UserID = ? AND IsApproved = '0'  AND FormId = ? ", $UserID, $FormID);
    $NumberOfRecordPending = $resXFormsQueryPending->NumberPending;

    $resXFormsQueryApproved = $app->getDBConnection()->fetch("SELECT COUNT(id) AS NumberApproved FROM xformrecord WHERE UserID = ? AND IsApproved = '1'  AND FormId = ? ", $UserID, $FormID);
    $NumberOfRecordApproved = $resXFormsQueryApproved->NumberApproved;

    $resXFormsQueryChecked = $app->getDBConnection()->fetch("SELECT COUNT(id) AS NumberChecked FROM xformrecord WHERE UserID = ? AND IsApproved = '1' AND IsChecked='1'  AND FormId = ? ", $UserID, $FormID);
    $NumberOfRecordChecked = $resXFormsQueryChecked->NumberChecked;

    $resXFormsQueryUnApproved = $app->getDBConnection()->fetch("SELECT COUNT(id) AS NumberUnApproved FROM xformrecord WHERE UserID = ? AND IsApproved = '2' AND FormId = ?", $UserID, $FormID);
    $NumberOfRecordUnApproved = $resXFormsQueryUnApproved->NumberUnApproved;

    $resXFormsQueryRejected = $app->getDBConnection()->fetch("SELECT COUNT(id) AS NumberRejected FROM deletedxformrecord WHERE UserID = ? AND FormId = ?", $UserID, $FormID);
    $NumberOfRecordRejected = $resXFormsQueryRejected->NumberRejected;

    $resQryDataCollection = $app->getDBConnection()->fetch("SELECT SUM(NumberOfRecordForMainSurvey) AS NumberOfRecordForMainSurvey, 
    (SELECT COUNT(id) FROM xformrecord WHERE PSU IN(SELECT PSU FROM PSUList WHERE PSUUSerID = ? AND FormId = ?)) AS Collected FROM PSUList 
    WHERE PSUUserID = ?", $UserID, $FormID, $UserID);
    $NumberOfRecordForMainSurvey = $resQryDataCollection->NumberOfRecordForMainSurvey;
    $Collected = $resQryDataCollection->Collected;
    $NotCollectedData = $NumberOfRecordForMainSurvey - $Collected;

    if ($FormID == $formIdMainData) {
        $QueryDataCollectionStatus = "SELECT DISTINCT PSUList.PSUUserID, PSUList.PSU, userinfo.FullName, PSUList.NumberOfRecordForMainSurvey as 'Target',
    (SELECT COUNT(id) FROM xformrecord WHERE xformrecord.PSU = PSUList.PSU and xformrecord.UserID=userinfo.id AND xformrecord.FormId = ?) as Collected 
    FROM PSUList JOIN userinfo ON PSUList.PSUUserID = userinfo.id WHERE PSUList.PSUUserID = ? AND PSUList.FarmName = ''";

    $FindMissingAndDuplicateQuery = "EXEC find_Duplicate_Missing_HH_For_User $FormID, '$columnNameToUpdateValueForMainData', $maxNumberOfHHForSampling, $UserID;";
    $FindMissingAndDuplicateQueryRS = $app->getDBConnection()->fetchAll($FindMissingAndDuplicateQuery);
    } else if ($FormID == $formIdSamplingData) {
        $QueryDataCollectionStatus = "SELECT DISTINCT PSUList.PSUUserID, PSUList.PSU, userinfo.FullName, PSUList.NumberOfRecord as 'Target',
    (SELECT COUNT(id) FROM xformrecord WHERE xformrecord.PSU = PSUList.PSU AND xformrecord.UserID=userinfo.id AND xformrecord.FormId = ?) AS Collected 
    FROM PSUList JOIN userinfo ON PSUList.PSUUserID = userinfo.id WHERE PSUList.PSUUserID = ? AND PSUList.FarmName = ''";
    } else if ($FormID == $formIdFarmData) {
        $QueryDataCollectionStatus = "SELECT DISTINCT PSUList.PSUUserID, PSUList.PSU, userinfo.FullName, PSUList.NumberOfRecordForMainSurvey as 'Target',
    (SELECT COUNT(id) FROM xformrecord WHERE xformrecord.PSU = PSUList.PSU and xformrecord.UserID=userinfo.id AND xformrecord.FormId = ?) as Collected 
    FROM PSUList JOIN userinfo ON PSUList.PSUUserID = userinfo.id WHERE PSUList.PSUUserID = ? AND PSUList.FarmName <> ''";
    }
    //echo $QueryDataCollectionStatus;
    $QueryDataCollectionStatusRS = $app->getDBConnection()->fetchAll($QueryDataCollectionStatus, $FormID, $UserID);

    $DataSendingDateQuery = " SELECT CONVERT(date, EntryDate) AS DataDate, COUNT(*) AS Number FROM xformrecord WHERE UserID= ? AND FormId = ? GROUP BY CONVERT(date, EntryDate) ORDER BY DataDate DESC";
    $DataSendingDateRS = $app->getDBConnection()->fetchAll($DataSendingDateQuery, $UserID, $FormID);

    ?>

    <section role="main" class="content-body">
        <header class="page-header">
            <h2><?php echo $FormName; ?></h2>
        </header>

        <div class="row" style="text-align: center">
            <div class="col-lg-1"></div>
            <div class="col-lg-10">
                <section class="card">
                    <header class="card-header">
                        <h2 class="card-title">Data Collection Status</h2>
                        <p class="card-subtitle">User: <?php echo $SelectedUserNameWithFullName; ?></p>
                    </header>
                    <div class="card-body">
                        <!-- Morris: Donut -->
                        <div class="chart chart-md" id="morrisDonut" style="padding-bottom: 15px"></div>

                        <div class="table-responsive">
                            <p class="card-title" style="text-align: left; padding: 5px">
                                Survey: <?php echo $FormName; ?></p>
                            <table class="table table-responsive-lg table-bordered table-striped table-sm mb-0">
                                <thead>
                                <tr style="text-align: center">
                                    <th>PSU</th>
                                    <th>Target</th>
                                    <th>Collected</th>
                                    <th>Remaining</th>
                                    <th>Progress</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $TargetData = 0;
                                $CollectedData = 0;

                                foreach ($QueryDataCollectionStatusRS as $row) {
                                    $PsuID = $row->PSU;
                                    $UserTargetData = $row->Target;
                                    $UserCollectedData = $row->Collected;
                                    $UserNotCollectedData = $UserTargetData - $UserCollectedData;
                                    $UserCollectionRatio = Ratio($UserCollectedData, $UserTargetData);

                                    $TargetData += $UserTargetData;
                                    $CollectedData += $UserCollectedData;

                                    $reqVar = "$UserID, $PsuID, $FormID"
                                    ?>
                                    <tr style="text-align: center">
                                        <td><?php echo $PsuID; ?></td>
                                        <td><?php echo $UserTargetData; ?></td>
                                        <td><?php echo $UserCollectedData; ?></td>
                                        <td><?php echo $UserNotCollectedData; ?></td>
                                        <td><?php echo $UserCollectionRatio; ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <tr style="text-align: center;">
                                    <td style="font-weight: bold">Total</td>
                                    <td><?php echo $TargetData; ?></td>
                                    <td><?php echo $CollectedData; ?></td>
                                    <td><?php echo $TargetData - $CollectedData; ?></td>
                                    <td><?php echo Ratio($CollectedData, $TargetData); ?></td>
                                </tr>

                                </tbody>
                            </table>
                        </div>

                    </div>
                </section>
            </div>
            <script type="text/javascript">
                function SendSamplingRequest(userid, psu, formid, data) {
                    if (confirm("Are you sure to send sampling request?")) {
                        //alert(userid + ',' + psu + ',' + formid);
                        $.ajax({
                            url: "send-sampling-request-to-supervisor.php",
                            method: "GET",
                            datatype: "json",
                            data: {
                                userid: userid,
                                psu: psu,
                                formid: formid
                            },
                            success: function (response) {
                                alert(response);
                                window.location.reload();
                            }
                        });
                    }
                    return false;
                }
            </script>
            <script type="text/javascript">
                const morrisDonutData = [{
                    label: "Collected",
                    value: <?php echo $CollectedData; ?>
                }, {
                    label: "Not Collected",
                    value: <?php echo $TargetData - $CollectedData; ?>
                }];
            </script>
            <div class="col-lg-1"></div>
        </div>
        <br>
        <?php
        if ($FormID == $formIdMainData) {
            ?>
            <div class="row" style="text-align: center">
                <div class="col-lg-1"></div>
                <div class="col-lg-10">
                    <section class="card">
                        <header class="card-header">
                            <h2 class="card-title">Missing and Duplicate Data</h2>
                            <p class="card-subtitle">User: <?php echo $SelectedUserNameWithFullName; ?></p>
                        </header>
                        <div class="card-body">
                            <div class="table-responsive">
                                <p class="card-title" style="text-align: left; padding: 5px">
                                    Survey: <?php echo $FormName; ?></p>
                                <table class="table table-responsive-lg table-bordered table-striped table-sm mb-0">
                                    <thead>
                                    <tr style="text-align: center">
                                        <th>PSU</th>
                                        <th>Unique Data</th>
                                        <th>Missing Data</th>
                                        <th>Duplicate Data</th>
                                        <th>Collected Data</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ($FindMissingAndDuplicateQueryRS as $row) {
                                        $PsuID = $row->PSU;
                                        $UniqueData = $row->UniqueData;
                                        $Missing = $row->Missing;
                                        $Duplicate = $row->Duplicate;
                                        $Collected = $row->Collected;
                                        ?>
                                        <tr>
                                            <td><?php echo $PsuID; ?></td>
                                            <td><?php echo $UniqueData; ?></td>
                                            <td><?php echo $Missing; ?></td>
                                            <td><?php echo $Duplicate; ?></td>
                                            <td><?php echo $Collected; ?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </section>
                </div>
                <div class="col-lg-1"></div>
            </div>
            <br>
            <?php
        }
        ?>

        <div class="row">
            <div class="col-lg-1"></div>
            <div class="col-lg-10">
                <section class="card">
                    <header class="card-header">
                        <h2 class="card-title">Data Status</h2>
                        <p class="card-subtitle">User: <?php echo $SelectedUserNameWithFullName; ?></p>
                    </header>
                    <div class="card-body">
                        <!-- Flot: Pie -->
                        <div class="chart chart-md" id="flotPie"></div>
                        <script type="text/javascript">
                            const flotPieData = [
                                {
                                    label: "Approved",
                                    data: [
                                        [1, <?php echo $NumberOfRecordApproved;?>]
                                    ],
                                    color: '#9c89b8'
                                }, {
                                    label: "Checked",
                                    data: [
                                        [1, <?php echo $NumberOfRecordChecked;?>]
                                    ],
                                    color: '#f0a6ca'
                                }, {
                                    label: "Pending",
                                    data: [
                                        [1, <?php echo $NumberOfRecordPending;?>]
                                    ],
                                    color: '#efc3e6'
                                }, {
                                    label: "Un-approved",
                                    data: [
                                        [1, <?php echo $NumberOfRecordUnApproved;?>]
                                    ],
                                    color: '#f0e6ef'
                                }, {
                                    label: "Deleted",
                                    data: [
                                        [1, <?php echo $NumberOfRecordRejected;?>]
                                    ],
                                    color: '#b8bedd'
                                }];
                            // See: js/examples/examples.charts.js for more settings.
                        </script>

                        <div class="table-responsive">
                            <table class="table table-responsive-lg table-bordered table-striped table-sm mb-0">
                                <thead>
                                <tr style="text-align: center">
                                    <th>Approved</th>
                                    <th>Checked</th>
                                    <th>Pending</th>
                                    <th>Un-approved</th>
                                    <th>Deleted</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr style="text-align: center">
                                    <td><?php echo $NumberOfRecordApproved; ?></td>
                                    <td><?php echo $NumberOfRecordChecked; ?></td>
                                    <td><?php echo $NumberOfRecordPending; ?></td>
                                    <td><?php echo $NumberOfRecordUnApproved; ?></td>
                                    <td><?php echo $NumberOfRecordRejected; ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-lg-1"></div>
        </div>

        <div class="row">
            <div class="col-lg-1"></div>
            <div class="col-lg-10">
                <section class="card">
                    <header class="card-header">
                        <div class="card-actions">
                            <a href="#" class="card-action card-action-toggle" data-card-toggle></a>
                        </div>
                        <h2 class="card-title">Day-wise Data Sending Report</h2>
                    </header>
                    <div class="card-body">
                        <!-- Flot: Bars -->
                        <div class="chart chart-lg" id="flotBars"></div>
                        <script type="text/javascript">
                            var flotBarsData =
                                [
                                    <?php
                                    foreach ($DataSendingDateRS as $row) {
                                    ?>
                                    ['<?php echo date_format($row->DataDate, "d/m"); ?>', <?php echo $row->Number; ?>],
                                    <?php
                                    }
                                    ?>
                                ];
                        </script>
                    </div>
                </section>
            </div>
            <div class="col-lg-1"></div>
        </div>
    </section>
    <?php
}
?>

<?php
include_once "../Components/footer-includes.php";
?>
