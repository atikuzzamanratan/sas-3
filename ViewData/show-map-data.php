<?php
$qryFormName = "SELECT id, FormName FROM datacollectionform WHERE Status = 'Active' AND CompanyID = ? ORDER BY id ASC";
$rsQryFormName = $app->getDBConnection()->fetchAll($qryFormName, $loggedUserCompanyID);
$qrySupervisor = "SELECT id FROM assignsupervisor WHERE SupervisorID = ?";
$rsSupervisor = $app->getDBConnection()->fetch($qrySupervisor, $loggedUserID);
$SuperID = $rsSupervisor->id;

if (strpos($loggedUserName, 'dist') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = ? AND a.DistCoordinatorID = ?";
    //var_dump($divQuery, $loggedUserCompanyID, $loggedUserID);exit;
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID, $loggedUserID);
} elseif ($SuperID) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = ? AND a.SupervisorID = ?";
    //var_dump($divQuery, $loggedUserCompanyID, $loggedUserID);exit;
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID, $loggedUserID);
} else {
    $divQuery = "SELECT DISTINCT DivisionName , DivisionCode FROM PSUList WHERE CompanyID = ? ORDER BY DivisionName ASC";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID);
}

if ($_REQUEST['show'] === 'Show') {

    $SelectedFormID = xss_clean($_REQUEST['SelectedFormID']);

    $DivisionCode = xss_clean($_REQUEST['DivisionCode']);
    $DistrictCode = xss_clean($_REQUEST['DistrictCode']);
    $UpazilaCode = xss_clean($_REQUEST['UpazilaCode']);
    $UnionWardCode = xss_clean($_REQUEST['UnionWardCode']);
    $MauzaCode = xss_clean($_REQUEST['MauzaCode']);
    $VillageCode = xss_clean($_REQUEST['VillageCode']);

    $SelectedFormStatus = xss_clean($_REQUEST['DataStatus']);
    $SelectedUserID = xss_clean($_REQUEST['SelectedUserID']);
    $checkAll = xss_clean($_REQUEST['chkAll']);
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
                                        <optgroup label="Select Form">
                                            <?PHP
                                            $qryForm = $app->getDBConnection()->query("SELECT id, FormName FROM datacollectionform WHERE Status = 'Active' AND CompanyID = ?", $loggedUserCompanyID);

                                            foreach ($qryForm as $row) {
                                                echo '<option value="' . $row->id . '"' . (isset($SelectedFormID) && !empty($SelectedFormID) && $row->id == $SelectedFormID ? ' selected' : '') . '>' . $row->FormName . '</option>';
                                            }
                                            ?>
                                        </optgroup>

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
                                        <option value="Pending" <?php echo isset($SelectedFormStatus) && $SelectedFormStatus == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Approved" <?php echo isset($SelectedFormStatus) && $SelectedFormStatus == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="UnApproved" <?php echo isset($SelectedFormStatus) && $SelectedFormStatus == 'UnApproved' ? 'selected' : ''; ?>>UnApproved</option>
                                    </select>
                                </div>
                            </div>

                            <?php
                            if (!$SuperID) {
                            ?>
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
                                            onchange="ShowDropDown4('DivisionCode', 'DistrictDiv','userDiv', 'DistrictUser', ['DivisionCode'], {'RequiredUser':0})">
                                            <option value="">Choose division</option>
                                            <?PHP
                                            foreach ($rsDivQuery as $row) {
                                                echo '<option value="' . $row->DivisionCode . '"' . (isset($DivisionCode) && !empty($DivisionCode) && $row->DivisionCode == $DivisionCode ? ' selected' : '') . '>' . $row->DivisionName . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                            <div id="geoDiv" style="display: none">
                                <div class="form-group row pb-3" id="DistrictDiv"></div>
                                <div class="form-group row pb-3" id="UpazilaDiv"></div>
                                <div class="form-group row pb-3" id="UnionWardDiv"></div>
                                <div class="form-group row pb-3" id="MauzaDiv"></div>
                                <div class="form-group row pb-3" id="VillageDiv"></div>
                            </div>
                            <div class="form-group row pb-3" id="userDiv">
                                <label class="col-lg-3 control-label text-sm-end pt-2"><?php if (!$SuperID) {
                                                                                            echo "or ";
                                                                                        } ?>User Select<span
                                        class="required"></span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate"
                                        name="SelectedUserID"
                                        id="SelectedUserID" title="Please select user">
                                        <option label="" value="">Choose user</option>
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
                                            echo '<option value="' . $row->id . '"' . (isset($SelectedUserID) && !empty($SelectedUserID) && $row->id == $SelectedUserID ? ' selected' : '') . '>' . $row->UserName . ' | ' . substr($row->FullName, 0, 102) . '</option>';
                                        }
                                        ?>

                                    </select>
                                </div>
                            </div>
                            <?php if (strpos($loggedUserName, 'admin') !== false) { ?>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-sm-end pt-2"></label>
                                    <div class="col-lg-6">
                                        <div class="checkbox-custom checkbox-warning">
                                            <input id="chkAll" value="chkAll" type="checkbox" name="chkAll" <?php echo isset($checkAll) && $checkAll == 'chkAll' ? 'checked' : ''; ?> />
                                            <label for="chkAll">All Users</label>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

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
                    $SelectedCompanyID = getValue('datacollectionform', 'CompanyID', "id = $SelectedFormID");
                    $SelectedUserID = $_REQUEST['SelectedUserID'];
                    $checkAll = $_REQUEST['chkAll'];
                    $DataStatus = $_REQUEST['DataStatus'];
                    $DivisionCode = $_REQUEST['DivisionCode'];
                    $DistrictCode = $_REQUEST['DistrictCode'];
                    $UpazilaCode = $_REQUEST['UpazilaCode'];
                    $UnionWardCode = $_REQUEST['UnionWardCode'];
                    $MauzaCode = $_REQUEST['MauzaCode'];
                    $VillageCode = $_REQUEST['VillageCode'];
                    if (empty($SelectedUserID) && empty($checkAll) && empty($DivisionCode)) {
                        MsgBox('Please select All Users or a specific User.');
                        exit();
                    }
                    /*if ($checkAll == 'chkAll') {
                        $qry = "SELECT mdra.XFormRecordId, ui.UserName, ui.FullName, mdra.ColumnValue, mdra.EntryDate FROM masterdatarecord_Approved mdra 
                    JOIN userinfo ui ON mdra.UserID = ui.id JOIN datacollectionform dcf ON mdra.FormId = dcf.id 
                    WHERE CONVERT(NVARCHAR(MAX), ColumnName) = 'geopoint' AND mdra.ColumnValue <> '' AND mdra.FormId = $SelectedFormID 
                    ORDER BY mdra.XFormRecordId ASC";
                    } elseif (!empty($SelectedUserID)) {
                        $qry = "SELECT mdra.XFormRecordId, ui.UserName, ui.FullName, mdra.ColumnValue, mdra.EntryDate FROM masterdatarecord_Approved mdra 
                    JOIN userinfo ui ON mdra.UserID = ui.id JOIN datacollectionform dcf ON mdra.FormId = dcf.id 
                    WHERE CONVERT(NVARCHAR(MAX), ColumnName) = 'geopoint' AND mdra.ColumnValue <> '' AND mdra.FormId = $SelectedFormID AND mdra.UserID = $SelectedUserID
                    ORDER BY mdra.XFormRecordId ASC";
                    }*/
                    $TableName = "";
                    if ($DataStatus == 'Pending') {
                        $TableName = "masterdatarecord_Pending";
                    } elseif ($DataStatus == 'UnApproved') {
                        $TableName = "masterdatarecord_UnApproved";
                    } else {
                        $TableName = "masterdatarecord_Approved";
                    }
                    if ($checkAll == 'chkAll') {
                        $qry = "SELECT mdra.XFormRecordId, mdra.DataName, ui.id, ui.UserName, ui.FullName, ui.MobileNumber, mdra.ColumnValue, mdra.EntryDate, mdra.PSU, pl.DivisionName, pl.DistrictName, pl.CityCorporationName, pl.UpazilaName, pl.MunicipalityName, pl.UnionWardName, pl.MauzaName, pl.VillageName
                        FROM $TableName mdra 
                        JOIN userinfo ui ON mdra.UserID = ui.id 
                        JOIN datacollectionform dcf ON mdra.FormId = dcf.id 
                        JOIN PSUList pl ON pl.PSU = mdra.PSU
                        WHERE CONVERT(NVARCHAR(MAX), ColumnName) = 'geopoint' AND mdra.ColumnValue <> '' AND mdra.FormId = $SelectedFormID 
                        ORDER BY mdra.XFormRecordId ASC";
                    } elseif (!empty($SelectedUserID)) {
                        $qry = "SELECT mdra.XFormRecordId, mdra.DataName, ui.id, ui.UserName, ui.FullName, ui.MobileNumber, mdra.ColumnValue, mdra.EntryDate, mdra.PSU, pl.DivisionName, pl.DistrictName, pl.CityCorporationName, pl.UpazilaName, pl.MunicipalityName, pl.UnionWardName, pl.MauzaName, pl.VillageName
                        FROM $TableName mdra 
                        JOIN userinfo ui ON mdra.UserID = ui.id 
                        JOIN datacollectionform dcf ON mdra.FormId = dcf.id 
                        JOIN PSUList pl ON pl.PSU = mdra.PSU
                        WHERE CONVERT(NVARCHAR(MAX), ColumnName) = 'geopoint' AND mdra.ColumnValue <> '' 
                        AND mdra.FormId = $SelectedFormID AND mdra.UserID = $SelectedUserID
                        ORDER BY mdra.XFormRecordId ASC";
                    } elseif (!empty($DivisionCode)) {
                        $qry = "SELECT mdra.XFormRecordId, mdra.DataName, ui.id, ui.UserName, ui.FullName, ui.MobileNumber, mdra.ColumnValue, mdra.EntryDate, mdra.PSU, pl.DivisionName, pl.DistrictName, pl.CityCorporationName, pl.UpazilaName, pl.MunicipalityName, pl.UnionWardName, pl.MauzaName, pl.VillageName
                        FROM $TableName mdra 
                        JOIN userinfo ui ON mdra.UserID = ui.id 
                        JOIN datacollectionform dcf ON mdra.FormId = dcf.id 
                        JOIN PSUList pl ON pl.PSU = mdra.PSU
                        WHERE CONVERT(NVARCHAR(MAX), ColumnName) = 'geopoint' AND mdra.ColumnValue <> '' 
                        AND mdra.FormId = $SelectedFormID";
                        if (!empty($DistrictCode)) {
                            $qry .= " AND pl.DivisionCode = $DivisionCode";
                        }
                        if (!empty($DistrictCode)) {
                            $qry .= " AND pl.DistrictCode = $DistrictCode";
                        }
                        if (!empty($UpazilaCode)) {
                            $qry .= " AND pl.UpazilaCode = $UpazilaCode";
                        }
                        if (!empty($UnionWardCode)) {
                            $qry .= " AND pl.UnionWardCode = $UnionWardCode";
                        }
                        if (!empty($MauzaCode)) {
                            $qry .= " AND pl.MauzaCode = $MauzaCode";
                        }
                        if (!empty($VillageCode)) {
                            $qry .= " AND pl.VillageCode = $VillageCode";
                        }
                    }
                    // echo $qry;
                    $resQry = $app->getDBConnection()->fetchAll($qry);

                    $initLat = "23.777176";
                    $initLon = "90.399452";

                    if ($checkAll == 'chkAll') {
                        $checkAll = 1;
                    } else {
                        $checkAll = 0;
                    }

                    $locationArray = array();

                    foreach ($resQry as $row) {
                        $RecordID = $row->XFormRecordId;
                        $DataName = $row->DataName;

                        $UserID = $row->id;
                        $UserName = $row->UserName;
                        $UserFullName = $row->FullName;
                        $UserData = "$UserFullName ($UserName)";

                        $SupervisorID = getValue('assignsupervisor', 'SupervisorID', " UserID = $UserID");
                        $SupervisorUserName = getValue('userinfo', 'UserName', " id = $SupervisorID");
                        $SupervisorName = getValue('userinfo', 'FullName', " id = $SupervisorID");
                        $SupervisorMobileNo = getValue('userinfo', 'MobileNumber', " id = $SupervisorID");

                        $SupervisorInfo = "<b>Supervisor</b>: $SupervisorName ($SupervisorUserName) <br><b>Supervisor Mobile</b>: $SupervisorMobileNo <br> ";

                        $MobileNumber = $row->MobileNumber;
                        //$MobileNumber = substr($MobileNumber, -10);
                        //$MobileNumber = "0$MobileNumber";

                        $DataSentDate = date_format($row->EntryDate, "d/m/Y h:i a");

                        $InigeoData = explode(" ", $row->ColumnValue);
                        $IniGeoLat = $InigeoData[0];
                        $IniGeoLong = $InigeoData[1];

                        $PSUNo = $row->PSU;
                        $DivisionName = $row->DivisionName;
                        $DistrictName = $row->DistrictName;
                        $CityCorpName = $row->CityCorporationName;
                        $UpazilaName = $row->UpazilaName;
                        $MunicipalityName = $row->MunicipalityName;
                        $UnionWardName = $row->UnionWardName;
                        $MauzaName = $row->MauzaName;
                        $VillageName = $row->VillageName;

                        $PSUInfo = "<b>PSU : $PSUNo</b> <br><b>Division</b>: $DivisionName <br><b>District</b>: $DistrictName <br><b>City Corporation</b>: $CityCorpName <br><b>Upazila</b>: $UpazilaName <br><b>Municipality</b>: $MunicipalityName <br><b>Union/Ward</b>: $UnionWardName <br><b>Mauza</b>: $MauzaName <br><b>Village</b>: $VillageName <br>";

                        $locationArray[] = array("recordID" => $RecordID, "sender" => $UserData, "lat" => $IniGeoLat, "lon" => $IniGeoLong, "PSUInfo" => $PSUInfo, "SendingDate" => $DataSentDate, "MobileNo" => $MobileNumber, "DataName" => $DataName, "SupervisorInfo" => $SupervisorInfo);
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
                            <div class="card-subtitle"></div>
                        </div>
                        <div class="card-body">
                            <div id="map" style="width:100%;height:700px;"></div>
                            <!-- <script type="text/javascript">
                                var locations = <?php echo $javascriptData; ?>;
                                var isAll = <?php echo $checkAll; ?>;

                                //alert(initLocLon);

                                if (isAll === 1) {
                                    var initLocLat = <?php echo $initLat; ?>;
                                    var initLocLon = <?php echo $initLon; ?>;
                                } else {
                                    var initLocLat = locations[0][2];
                                    var initLocLon = locations[0][3];
                                }

                                var map = new google.maps.Map(document.getElementById('map'), {
                                    zoom: 10,
                                    center: new google.maps.LatLng(initLocLat, initLocLon),
                                    mapTypeId: google.maps.MapTypeId.ROADMAP
                                });

                                var infowindow = new google.maps.InfoWindow();

                                var marker, i;


                                for (i = 0; i < locations.length; i++) {
                                    marker = new google.maps.Marker({
                                        position: new google.maps.LatLng(locations[i][2], locations[i][3]),
                                        map: map,
                                        title: '\nRecord ID : ' + locations[i][0] + ' \nData Name : ' + locations[i][7] + ' \nSender : ' + locations[i][1] + ' \nLatitude : ' + locations[i][2] + '\nLongitude : ' + locations[i][3] + '\n',
                                    });

                                    const contentString =
                                        '<div id="content">' +
                                        '<div id="siteNotice">' +
                                        "</div>" +
                                        '<h3 id="firstHeading" class="firstHeading">Record ID : ' + locations[i][0] + '</h3>' +
                                        '<h5 id="firstHeading" class="firstHeading"><b>Sender</b>: ' + locations[i][1] + '</h5>' +
                                        '<h5 id="firstHeading" class="firstHeading"><b>Sender Moible</b>: ' + locations[i][6] + '</h5>' +
                                        '<h5 id="firstHeading" class="firstHeading">' + locations[i][8] + '</h5>' +
                                        '<div id="bodyContent">' +
                                        '<p><b>Send Time</b>: ' + locations[i][5] + '</p>' +
                                        '<p">' + locations[i][4] + '</p>' +
                                        "</div>" +
                                        "</div>";

                                    google.maps.event.addListener(marker, 'click', (function(marker, i) {
                                        return function() {
                                            //infowindow.setContent('Record ID: ' + locations[i][0] + ' | Sender: ' + locations[i][1] + locations[i][4]);
                                            infowindow.setContent(contentString);
                                            infowindow.open(map, marker);
                                        }
                                    })(marker, i));
                                }
                            </script> -->
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

















<?php if ($_REQUEST['show'] === 'Show') { ?>
    <script>
        // Define initMap in global scope immediately
        window.initMap = function() {
            var locations = <?php echo $javascriptData; ?>;
            var isAll = <?php echo $checkAll; ?>;

            var initLocLat = isAll === 1 ? <?php echo $initLat; ?> : locations[0][2];
            var initLocLon = isAll === 1 ? <?php echo $initLon; ?> : locations[0][3];

            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 10,
                center: new google.maps.LatLng(initLocLat, initLocLon),
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });

            const infowindow = new google.maps.InfoWindow();

            for (let i = 0; i < locations.length; i++) {
                const marker = new google.maps.Marker({
                    position: new google.maps.LatLng(locations[i][2], locations[i][3]),
                    map: map,
                    title: 'Record ID: ' + locations[i][0]
                });

                const contentString = `
                <div>
                    <h3>Record ID: ${locations[i][0]}</h3>
                    <p><b>Sender:</b> ${locations[i][1]}</p>
                    <p><b>Sender Mobile:</b> ${locations[i][6]}</p>
                    <p>${locations[i][8]}</p>
                    <p><b>Send Time:</b> ${locations[i][5]}</p>
                    <p>${locations[i][4]}</p>
                </div>`;

                marker.addListener("click", () => {
                    infowindow.setContent(contentString);
                    infowindow.open(map, marker);
                });
            }
        };

        // ✅ Dynamically insert Google Maps script AFTER defining initMap
        (function() {
            const script = document.createElement('script');
            script.src = "https://maps.googleapis.com/maps/api/js?key=<?php echo $googleMapApiKey; ?>&callback=initMap";
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
        })();
    </script>
<?php } ?>






















<script>
    $(document).ready(function() {
        // Initial population on page load
        populateDropdowns(
            <?php echo isset($DivisionCode) && $DivisionCode !== '' ? $DivisionCode : 'null'; ?>,
            <?php echo isset($DistrictCode) && $DistrictCode !== '' ? $DistrictCode : 'null'; ?>,
            <?php echo isset($UpazilaCode) && $UpazilaCode !== '' ? $UpazilaCode : 'null'; ?>,
            <?php echo isset($UnionWardCode) && $UnionWardCode !== '' ? $UnionWardCode : 'null'; ?>,
            <?php echo isset($MauzaCode) && $MauzaCode !== '' ? $MauzaCode : 'null'; ?>,
            <?php echo isset($VillageCode) && $VillageCode !== '' ? $VillageCode : 'null'; ?>
        );
    });
</script>