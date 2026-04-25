<?php
$FormID = $_SESSION["FORMID"];
$UserID = $_SESSION["USERID"];

$SelectedUserName = getValue('userinfo', 'UserName', "id = $UserID");
$SelectedUserFullName = getValue('userinfo', 'FullName', "id = $UserID");
$SelectedUserNameWithFullName = $SelectedUserFullName . ' (<b>' . $SelectedUserName . '</b>)';

$resXFormsQueryPending = $app->getDBConnection()->fetch("SELECT COUNT(id) AS NumberPending FROM xformrecord WHERE UserID = ? AND IsApproved = '0'  AND FormId = ? ", $UserID, $FormID);
$NumberOfRecordPending = $resXFormsQueryPending->NumberPending;

$resXFormsQueryApproved = $app->getDBConnection()->fetch("SELECT COUNT(id) AS NumberApproved FROM xformrecord WHERE UserID = ? AND IsApproved = '1' AND (IsChecked IS NULL OR IsChecked = '0')  AND FormId = ? ", $UserID, $FormID);
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
    FROM PSUList JOIN userinfo ON PSUList.PSUUserID = userinfo.id WHERE PSUList.FarmName = '' AND PSUList.PSUUserID = ?";

    $FindMissingAndDuplicateQuery = "EXEC find_Duplicate_Missing_HH_For_User $FormID, '$columnNameToUpdateValueForListingData', $maxNumberOfHHForSampling, $UserID;";
    $FindMissingAndDuplicateQueryRS = $app->getDBConnection()->fetchAll($FindMissingAndDuplicateQuery);

} else if ($FormID == $formIdSamplingData) {
    $QueryDataCollectionStatus = "SELECT DISTINCT PSUList.PSUUserID, PSUList.PSU, userinfo.FullName, PSUList.NumberOfRecord as 'Target',
    (SELECT COUNT(id) FROM xformrecord WHERE xformrecord.PSU = PSUList.PSU AND xformrecord.UserID=userinfo.id AND xformrecord.FormId = ?) AS Collected 
    FROM PSUList JOIN userinfo ON PSUList.PSUUserID = userinfo.id WHERE PSUList.FarmName = '' AND PSUList.PSUUserID = ?";
} else if ($FormID == $formIdFarmData) {
    $QueryDataCollectionStatus = "SELECT DISTINCT PSUList.PSUUserID, PSUList.PSU, userinfo.FullName, PSUList.NumberOfRecordForMainSurvey as 'Target',
    (SELECT COUNT(id) FROM xformrecord WHERE xformrecord.PSU = PSUList.PSU and xformrecord.UserID=userinfo.id AND xformrecord.FormId = ?) as Collected 
    FROM PSUList JOIN userinfo ON PSUList.PSUUserID = userinfo.id WHERE PSUList.FarmName <> '' AND PSUList.PSUUserID = ?";
}
$QueryDataCollectionStatusRS = $app->getDBConnection()->fetchAll($QueryDataCollectionStatus, $FormID, $UserID);

$DataSendingDateQuery = " SELECT CONVERT(date, EntryDate) AS DataDate, COUNT(*) AS Number FROM xformrecord WHERE UserID= ? AND FormId = ? GROUP BY CONVERT(date, EntryDate) ORDER BY DataDate DESC";
$DataSendingDateRS = $app->getDBConnection()->fetchAll($DataSendingDateQuery, $UserID, $FormID);
?>

<div class="inner-wrapper">
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Dashboard : <?php echo getName('FormName', 'datacollectionform', $FormID); ?></h2>

            <?php include_once 'Components/header-home-button.php'; ?>
        </header>

        <!-- start: page -->
        <div class="row">
            <div class="col-lg-6">
                <section class="card">
                    <header class="card-header">
                        <h2 class="card-title">Data Collection Status</h2>
                        <p class="card-subtitle">User: <?php echo $SelectedUserNameWithFullName; ?></p>
                    </header>
                    <div class="card-body">
                        <!-- Morris: Donut -->
                        <div class="chart chart-md" id="morrisDonut"></div>


                        <div class="table-responsive">
                            <table class="table table-responsive-lg table-bordered table-striped table-sm mb-0">
                                <thead>
                                <tr>
                                    <th>PSU</th>
                                    <th>Target</th>
                                    <th>Collected</th>
                                    <th>Not-Collected</th>
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
                                    ?>
                                    <tr>
                                        <td><?php echo $PsuID; ?></td>
                                        <td><?php echo $UserTargetData; ?></td>
                                        <td><?php echo $UserCollectedData; ?></td>
                                        <td><?php echo $UserNotCollectedData; ?></td>
                                        <td><?php echo $UserCollectionRatio; ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <tr>
                                    <td>Total</td>
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
                var morrisDonutData = [{
                    label: "Collected",
                    value: <?php echo $CollectedData; ?>
                }, {
                    label: "Not Collected",
                    value: <?php echo $TargetData - $CollectedData; ?>
                }];
            </script>
            <div class="col-lg-6">
                <section class="card">
                    <header class="card-header">
                        <h2 class="card-title">Data Status</h2>
                        <p class="card-subtitle">User: <?php echo $SelectedUserNameWithFullName; ?></p>
                    </header>
                    <div class="card-body">
                        <!-- Flot: Pie -->
                        <div class="chart chart-md" id="flotPie"></div>
                        <script type="text/javascript">
                            var flotPieData = [
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
                                <tr>
                                    <th>Approved</th>
                                    <th>Checked</th>
                                    <th>Pending</th>
                                    <th>Un-approved</th>
                                    <th>Deleted</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
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
        </div>

        <?php
        if ($FormID == $formIdMainData) {
            ?>
            <div class="row">
                <div class="col-lg-12">
                    <section class="card">
                        <header class="card-header">
                            <h2 class="card-title">Missing and Duplicate Data</h2>
                            <p class="card-subtitle">User: <?php echo $SelectedUserNameWithFullName; ?></p>
                        </header>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-responsive-lg table-bordered table-striped table-sm mb-0">
                                    <thead>
                                    <tr>
                                        <th>PSU</th>
                                        <th>Total Unique Data</th>
                                        <th>Total Missing Data</th>
                                        <th>Total Duplicate Data</th>
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
                <script type="text/javascript">
                    var morrisDonutData = [{
                        label: "Collected",
                        value: <?php echo $CollectedData; ?>
                    }, {
                        label: "Not Collected",
                        value: <?php echo $TargetData - $CollectedData; ?>
                    }];
                </script>
            </div>
            <?php
        }
        ?>

        <div class="row">
            <div class="col-lg-12">
                <section class="card">
                    <header class="card-header">
                        <h2 class="card-title">Day-wise Data Sending Report</h2>
                        <p class="card-subtitle">User: <?php echo $SelectedUserNameWithFullName; ?></p>
                    </header>
                    <div class="card-body">
                        <!-- Flot: Bars -->
                        <div class="chart chart-md" id="flotBars"></div>
                        <script type="text/javascript">
                            var flotBarsData = [
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
        </div>
        <!-- end: page -->
    </section>
</div>