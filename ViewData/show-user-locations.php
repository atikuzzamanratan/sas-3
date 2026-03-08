<?php
$qrySupervisor = "SELECT id FROM assignsupervisor WHERE SupervisorID = ?";
$rsSupervisor = $app->getDBConnection()->fetch($qrySupervisor, $loggedUserID);
$SuperID = $rsSupervisor->id;

if(isset($_REQUEST['show']) && $_REQUEST['show'] === 'Show') {
    $SelectedFormID = $_REQUEST['SelectedFormID'];
    $SelectedUserID = $_REQUEST['SelectedUserID'];
    $SelectedLocationType = $_REQUEST['SelectedLocationType'];
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
                                            class="form-control populate" required>
                                        <optgroup label="Choose form">
                                            <?PHP
                                            $qryForm = $app->getDBConnection()->query("SELECT id, FormName FROM datacollectionform WHERE CompanyID = ?", $loggedUserCompanyID);

                                            foreach ($qryForm as $row) {
                                                echo '<option value="' . $row->id . '"' . (isset($SelectedFormID) && !empty($SelectedFormID) && $row->id == $SelectedFormID ? ' selected' : '') . '>' . $row->FormName . '</option>';
                                            }
                                            ?>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">User Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <?php if (strpos($loggedUserName, 'admin') === false) { ?>
                                        <select data-plugin-selectTwo class="form-control populate"
                                            name="SelectedUserID"
                                            id="SelectedUserID" title="Please select user" required>
                                        <?php } else { ?>
                                            <select data-plugin-selectTwo class="form-control populate"
                                                name="SelectedUserID"
                                                id="SelectedUserID" title="Please select user" required>
                                            <?php } ?>
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
                                            } else if (strpos($loggedUserName, 'div') !== false) {
                                                $qryDistUser = "SELECT u.id, u.UserName, u.FullName FROM assignsupervisor as a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND u.UserName LIKE '$dataCollectorNamePrefix%' AND a.DivCoordinatorID = ?";
                                                $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserID);
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
                                <label class="col-lg-3 control-label text-sm-end pt-2">Location Type<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="SelectedLocationType" name="SelectedLocationType"
                                            class="form-control populate" required>
                                        <optgroup label="Choose type">
                                            <option value="lastLocation" <?php echo isset($SelectedLocationType) && $SelectedLocationType == 'lastLocation' ? 'selected' : ''; ?>>Last live location</option>
                                            <option value="allLocation" <?php echo isset($SelectedLocationType) && $SelectedLocationType == 'allLocation' ? 'selected' : ''; ?>>All locations</option>
                                        </optgroup>

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
                </section>
                <?php

                if ($_REQUEST['show'] === 'Show') {
                    $SelectedFormID = $_REQUEST['SelectedFormID'];
                    $SelectedUserID = $_REQUEST['SelectedUserID'];
                    $SelectedUserName = getValue('userinfo', 'UserName', "id = $SelectedUserID");
                    $SelectedFullName = getValue('userinfo', 'FullName', "id = $SelectedUserID");
                    $UserData = "$SelectedFullName ($SelectedUserName)";

                    $SelectedLocationType = $_REQUEST['SelectedLocationType'];

                    if ($SelectedLocationType === "lastLocation") {
                        $selQuery = "SELECT TOP 1 * FROM UserLiveLocation WHERE UserId = ? ORDER BY DateTime DESC";
                    } elseif ($SelectedLocationType === "allLocation") {
                        $selQuery = "SELECT * FROM UserLiveLocation WHERE UserId = ? ORDER BY DateTime DESC";
                    }
                    $resQry = $app->getDBConnection()->fetchAll($selQuery, $SelectedUserID);

                    $locationArray = array();

                    foreach ($resQry as $row) {
                        $InigeoData = explode(",", $row->Location);
                        $IniGeoLat = $InigeoData[0];
                        $IniGeoLong = $InigeoData[1];

                        $locationArray[] = array("lat" => $IniGeoLat, "lon" => $IniGeoLong);
                    }

                    $locationData = json_encode($locationArray);

                    // Decode the JSON data into a PHP array
                    $phpArray = json_decode($locationData, true);

                    // Convert the PHP array into the desired JavaScript format
                    $javascriptData = json_encode(array_map(function ($item) {
                        return array_values($item);
                    }, $phpArray));

                    ?>
                    <section class="card">
                        <div class="card-header">
                            <div class="card-title">Form
                                : <?php echo getValue('datacollectionform', 'FormName', "id = $SelectedFormID"); ?></div>
                            <div class="card-subtitle">User: <?php echo $UserData; ?></div>
                            <div class="card-subtitle"></div>
                        </div>
                        <div class="card-body">
                            <div id="map" style="width:100%;height:700px;"></div>
                            <script type="text/javascript">
                                var locations = <?php echo $javascriptData;?>;
                                //alert(locations);

                                var initLocLat = locations[0][0];
                                var initLocLon = locations[0][1];

                                var map = new google.maps.Map(document.getElementById('map'), {
                                    zoom: 10,
                                    center: new google.maps.LatLng(initLocLat, initLocLon),
                                    mapTypeId: google.maps.MapTypeId.ROADMAP
                                });

                                var infowindow = new google.maps.InfoWindow();

                                var marker, i;

                                for (i = 0; i < locations.length; i++) {
                                    marker = new google.maps.Marker({
                                        position: new google.maps.LatLng(locations[i][0], locations[i][1]),
                                        map: map,
                                        title: 'Lat: ' + locations[i][0] + ' | Lon: ' + locations[i][1],
                                        animation: google.maps.Animation.BOUNCE
                                    });
                                }
                            </script>
                        </div>
                    </section>
                    <?php
                }
                ?>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

