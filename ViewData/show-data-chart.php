<?php
$qrySupervisor = "SELECT id FROM assignsupervisor WHERE SupervisorID = ?";
$rsSupervisor = $app->getDBConnection()->fetch($qrySupervisor, $loggedUserID);
$SuperID = $rsSupervisor->id;

if (strpos($user, 'dist') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = ? AND a.DistCoordinatorID = ?";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID, $loggedUserID);
} else {
    $divQuery = "SELECT DISTINCT DivisionName , DivisionCode FROM PSUList WHERE CompanyID = ? ORDER BY DivisionName ASC";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID);
}

if($_REQUEST['show'] === 'Show') {
    $SelectedFormID = xss_clean($_REQUEST['SelectedFormID']);
    $SelectedGender = xss_clean($_REQUEST['SelectedGender']);
	$SelectedModuleName = xss_clean($_REQUEST['SelectedModuleName']);

    $SelectedColumnName = xss_clean($_REQUEST['columnName']);
    $SelectedMaleColumnName = xss_clean($_REQUEST['maleColumnName']);
    $SelectedFemaleColumnName = xss_clean($_REQUEST['femaleColumnName']);
    
    $SelectedChartType = xss_clean($_REQUEST['SelectedChartType']);

    $DivisionCode = xss_clean($_REQUEST['DivisionCode']);
    $DistrictCode = xss_clean($_REQUEST['DistrictCode']);
    $UpazilaCode = xss_clean($_REQUEST['UpazilaCode']);
    $UnionWardCode = xss_clean($_REQUEST['UnionWardCode']);
    $MauzaCode = xss_clean($_REQUEST['MauzaCode']);
    $VillageCode = xss_clean($_REQUEST['VillageCode']);
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
                            <!-- Form Select -->
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Form Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="SelectedFormID" name="SelectedFormID"
                                            class="form-control populate" required
                                            onchange="getModuleName()">
											<option value="">Select Form</option>
                                            <?PHP
                                            $qryForm = $app->getDBConnection()->query("SELECT id, FormName FROM datacollectionform WHERE CompanyID = ? ORDER BY id", $loggedUserCompanyID);

                                            foreach ($qryForm as $row) {
                                                echo '<option value="' . $row->id . '"' . (isset($SelectedFormID) && !empty($SelectedFormID) && $row->id == $SelectedFormID ? ' selected' : '') . '>' . $row->FormName . '</option>';
                                            }
                                            ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Gender Select -->
                            <!--<div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Gender</label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="SelectedGender" name="SelectedGender"
                                            class="form-control populate" onchange="getSelectedGender()">
                                        <option value="">Choose Gender</option>
                                        <option value="1" <?php /*echo isset($SelectedGender) && $SelectedGender == '1' ? 'selected' : ''; */?>>Male</option>
                                        <option value="2" <?php /*echo isset($SelectedGender) && $SelectedGender == '2' ? 'selected' : ''; */?>>Female</option>
                                        <option value="both" <?php /*echo isset($SelectedGender) && $SelectedGender == 'both' ? 'selected' : ''; */?>>Both</option>
                                    </select>
                                </div>
                            </div>
                            <script type="text/javascript">
								function getSelectedGender() {
									let gender = $("#SelectedGender").find(":selected").val();
									if(gender == 'both') {
										$(".both-gender-column").removeClass("hidden");
										$(".specific-gender-column").addClass("hidden");
                                        $('#femaleColumnName').attr('required', true);
                                        $('#maleColumnName').attr('required', true);
                                        $('#columnName').attr('required', false);
									} else {
										$(".both-gender-column").addClass("hidden");
										$(".specific-gender-column").removeClass("hidden");
                                        $('#femaleColumnName').attr('required', false);
                                        $('#maleColumnName').attr('required', false);
                                        $('#columnName').attr('required', true);
									}
                                    getSelectedColumnName()
								}
							</script>-->

                            <!-- Module Select -->
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Module Select<span
                                        class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="SelectedModuleName"
                                        id="SelectedModuleName" required
										onchange="getSelectedColumnName()">
										<option value="">Select Module</option>
                                    </select>
                                </div>
                            </div>
							<script type="text/javascript">
								function getModuleName(formID, moduleName) {
									var formID = $("#SelectedFormID").find(":selected").val(),
										moduleName = '<?=$SelectedModuleName?>';
										
									$.ajax({
										url: "Reports/get-module-name-list-question-type-by-gender.php",
										method: "POST",
										datatype: "html",
										data: {
											formID: formID,
											questionType: 'straight',
											moduleName: moduleName
										},
										success: function(response) {
											//alert(response);
											$('#SelectedModuleName').html(response);
											$("#SelectedModuleName").trigger( "change" );
										}
									});
									return false;
								}
							</script>

                            <!-- Column Select -->
							<div class="form-group row pb-3 specific-gender-column <?php echo $SelectedGender == 'both' ? 'hidden' : ''; ?>">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Column Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="columnName"
                                            id="columnName" title="Please select a column" <?php echo $SelectedGender == 'both' ? '' : 'required'; ?>>
                                        <option value="">Choose a column</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row pb-3 both-gender-column <?php echo $SelectedGender == 'both' ? '' : 'hidden'; ?>">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Male Column Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="maleColumnName"
                                            id="maleColumnName" title="Please select a column">
                                        <option value="">Choose a column</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row pb-3 both-gender-column <?php echo $SelectedGender == 'both' ? '' : 'hidden'; ?>">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Female Column Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="femaleColumnName"
                                            id="femaleColumnName" title="Please select a column">
                                        <option value="">Choose a column</option>
                                    </select>
                                </div>
                            </div>
                            <script type="text/javascript">
								function getSelectedColumnName() {
									var formID = $("#SelectedFormID").find(":selected").val(),
										gender = $("#SelectedGender").find(":selected").val(),
										moduleName = $("#SelectedModuleName").find(":selected").val()
										columnName = '<?=$SelectedColumnName?>';
                                        maleColumnName = '<?=$SelectedMaleColumnName?>';
                                        femaleColumnName = '<?=$SelectedFemaleColumnName?>';
									
									$.ajax({
										url: "ViewData/get-column-name-list-by-module-name.php",
										method: "POST",
										datatype: "html",
										data: {
											formID: formID,
											gender: gender,
											moduleName: moduleName,
											columnName: columnName,
                                            maleColumnName: maleColumnName,
                                            femaleColumnName: femaleColumnName
										},
										success: function (response) {
											//alert(response);
                                            if(gender == 'both') {
                                                let responseArray = JSON.parse(response);
                                                $('#maleColumnName').html(responseArray[0]);
                                                $('#femaleColumnName').html(responseArray[1]);
                                            } else {
                                                $('#columnName').html(response);
                                            }
											$("#SelectedColumnName").trigger( "change" );
										}
									});
									return false;
								}
							</script>

                            <!-- Chart Type Select -->
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Chart Type</label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="SelectedChartType" name="SelectedChartType"
                                            class="form-control populate">
                                        <option value="">Choose chart</option>
                                        <option value="barChart" <?php echo isset($SelectedChartType) && $SelectedChartType == 'barChart' ? 'selected' : ''; ?>>Bar Chart</option>
                                        <option value="pieChart" <?php echo isset($SelectedChartType) && $SelectedChartType == 'pieChart' ? 'selected' : ''; ?>>Pie Chart</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Division Select -->
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
                                            onchange="ShowDropDown('DivisionCode', 'DistrictDiv', 'ShowDistrict', 'ShowUpazila')">
                                        <option value="">Choose division</option>
                                        <?PHP
                                        foreach ($rsDivQuery as $row) {
                                            echo '<option value="' . $row->DivisionCode . '"' . (isset($DivisionCode) && !empty($DivisionCode) && $row->DivisionCode == $DivisionCode ? ' selected' : '') . '>' . $row->DivisionName . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Geo Select -->
                            <div id="geoDiv" style="display: none">
                            <div class="form-group row pb-3" id="DistrictDiv"></div>
                            <div class="form-group row pb-3" id="UpazilaDiv"></div>
                            <div class="form-group row pb-3" id="UnionWardDiv"></div>
                            <div class="form-group row pb-3" id="MauzaDiv"></div>
                            <div class="form-group row pb-3" id="VillageDiv"></div>
                            </div>

                            <!-- Submit Button -->
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
                    $SelectedCompanyID = getValue('datacollectionform', 'CompanyID', "id = $SelectedFormID");

                    if (!empty($DivisionCode)) {
                        $DivisionName = getValue('PSUList', 'DISTINCT(DivisionName)', "DivisionCode = $DivisionCode");
                    }

                    if (!empty($DistrictCode)) {
                        $DistrictName = getValue('PSUList', 'DISTINCT(DistrictName)', "DivisionCode = $DivisionCode AND DistrictCode = $DistrictCode");
                        $DistrictName = ' > ' . $DistrictName;
                    }

                    if (!empty($UpazilaCode)) {
                        $UpazilaName = getValue('PSUList', 'DISTINCT(UpazilaName)',
                            "DivisionCode = $DivisionCode AND DistrictCode = $DistrictCode AND UpazilaCode = $UpazilaCode");
                        $UpazilaName = ' > ' . $UpazilaName;
                    }

                    if (!empty($UnionWardCode)) {
                        $UnionWardName = getValue('PSUList', 'DISTINCT(UnionWardName)',
                            "DivisionCode = $DivisionCode AND DistrictCode = $DistrictCode AND UpazilaCode = $UpazilaCode AND UnionWardCode = $UnionWardCode");
                        $UnionWardName = ' > ' . $UnionWardName;
                    }

                    if (!empty($MauzaCode)) {
                        $MauzaName = getValue('PSUList', 'DISTINCT(MauzaName)',
                            "DivisionCode = $DivisionCode AND DistrictCode = $DistrictCode AND UpazilaCode = $UpazilaCode AND UnionWardCode = $UnionWardCode AND MauzaCode = $MauzaCode");
                        $MauzaName = ' > ' . $MauzaName;
                    }

                    if (!empty($VillageCode)) {
                        $VillageName = getValue('PSUList', 'DISTINCT(VillageName)',
                            "DivisionCode = $DivisionCode AND DistrictCode = $DistrictCode AND UpazilaCode = $UpazilaCode AND UnionWardCode = $UnionWardCode AND MauzaCode = $MauzaCode AND VillageCode = $VillageCode");
                        $VillageName = ' > ' . $VillageName;
                    }

                    if($SelectedGender == 'both') {
                        $SelectedMaleColumnLabel = getValue('xformcolumnname', 'ColumnLabel', "FormId = $SelectedFormID AND ColumnName = '$SelectedMaleColumnName'");
                        $SelectedFemaleColumnLabel = getValue('xformcolumnname', 'ColumnLabel', "FormId = $SelectedFormID AND ColumnName = '$SelectedFemaleColumnName'");;

                        $MaleColumnDataType = getValue('xformcolumnname', 'DataType', "ColumnName = '$SelectedMaleColumnName'");
                        $FemaleColumnDataType = getValue('xformcolumnname', 'DataType', "ColumnName = '$SelectedFemaleColumnName'");
                        
                        $MaleChartData = getChartData($app, $MaleColumnDataType, $SelectedFormID, $SelectedMaleColumnName, $DivisionCode, $DistrictCode, $UpazilaCode, $UnionWardCode, $MauzaCode, $VillageCode);
                        $FemaleChartData = getChartData($app, $FemaleColumnDataType, $SelectedFormID, $SelectedFemaleColumnName, $DivisionCode, $DistrictCode, $UpazilaCode, $UnionWardCode, $MauzaCode, $VillageCode);
                        
                        $MaleResQry = $MaleChartData['resQry'];
                        $MaleQry = $MaleChartData['qry'];
                        $FemaleResQry = $FemaleChartData['resQry'];
                        $FemaleQry = $FemaleChartData['qry'];
                    } else {
                        $SelectedColumnLabel = getValue('xformcolumnname', 'ColumnLabel', "FormId = $SelectedFormID AND ColumnName = '$SelectedColumnName'");;
                        $ColumnDataType = getValue('xformcolumnname', 'DataType', "ColumnName = '$SelectedColumnName'");
                        $chartData = getChartData($app, $ColumnDataType, $SelectedFormID, $SelectedColumnName, $DivisionCode, $DistrictCode, $UpazilaCode, $UnionWardCode, $MauzaCode, $VillageCode);
                        $resQry = $chartData['resQry'];
                        $qry = $chartData['qry'];
                    }
                    

                    if ($SelectedGender != 'both' && !count($resQry) > 0) {
                        ?>
                        <h2 style="color: darkred; text-align: center">No data found!</h2>
                        <?php
                    } else {
                        if($SelectedGender == 'both') {
                            $MaleDataURL = $baseURL . 'ViewData/ajax-data/chart-data-ajax-data.php?qry=' . urlencode($MaleQry);
                            $FemaleDataURL = $baseURL . 'ViewData/ajax-data/chart-data-ajax-data.php?qry=' . urlencode($FemaleQry);
                        } else {
                            $dataURL = $baseURL . 'ViewData/ajax-data/chart-data-ajax-data.php?qry=' . $qry;
                        }

                        //echo $dataURL;
                        ?>

                        <section class="card">
                            <div class="card-header">
                                <div class="card-title">Form
                                    : <?php echo getValue('datacollectionform', 'FormName', "id = $SelectedFormID"); ?></div>
                                <div class="card-subtitle"><?php echo $DivisionName . $DistrictName . $UpazilaName . $UnionWardName . $MauzaName . $VillageName; ?></div>
                                <?php if($SelectedGender == 'both') { ?>
                                    <div class="card-subtitle d-flex justify-content-between">
                                        <b>Question: <i><?php echo "$SelectedMaleColumnLabel"; ?></i></b>
                                        <b>Question: <i><?php echo "$SelectedFemaleColumnLabel"; ?></i></b>
                                    </div>
                                <?php } else { ?>
                                    <div class="card-subtitle"><b>Question: <i><?php echo "$SelectedColumnLabel"; ?></i></b></div>
                                <?php } ?>
                                <div class="card-subtitle"></div>
                            </div>

                            <?php $i = 1;
                            $colorArray = array('#FF6633', '#FFB399', '#FF33FF', '#FFFF99', '#00B3E6',
                                '#E6B333', '#3366E6', '#999966', '#99FF99', '#B34D4D',
                                '#80B300', '#809900', '#E6B3B3', '#6680B3', '#66991A',
                                '#FF99E6', '#CCFF1A', '#FF1A66', '#E6331A', '#33FFCC',
                                '#66994D', '#B366CC', '#4D8000', '#B33300', '#CC80CC',
                                '#66664D', '#991AFF', '#E666FF', '#4DB3FF', '#1AB399',
                                '#E666B3', '#33991A', '#CC9999', '#B3B31A', '#00E680',
                                '#4D8066', '#809980', '#E6FF80', '#1AFF33', '#999933',
                                '#FF3380', '#CCCC00', '#66E64D', '#4D80CC', '#9900B3',
                                '#E64D66', '#4DB380', '#FF4D4D', '#99E6E6', '#6666FF');

                                if($SelectedGender == 'both') {
                                    $width = '50%';
                                    $showChartData = [$MaleResQry, $FemaleResQry];
                                } else {
                                    $width = '100%';
                                    $showChartData = [$resQry];
                                }
                            
                            if ($SelectedChartType === "barChart") {
                                ?>
                                <div class="card-body d-flex">
                                    <?php 
                                        foreach($showChartData as $index => $resQry) { 
                                    ?>
                                    <div style="width: <?php echo $width; ?>; height: 500px;" id="chart<?php echo $index; ?>"></div>
                                    <script>
                                        var chart = AmCharts.makeChart("chart<?php echo $index; ?>", {
                                            "type": "serial",
                                            "theme": "light",
                                            "marginRight": 70,
                                            "dataProvider": [<?php
                                                foreach ($resQry as $i => $row) {
                                                ?>
                                                {
                                                    "country": "<?php echo $row->ChoiceLabel;?>",
                                                    "visits": <?php echo $row->ColValTotal;?>,
                                                    "color": "<?php echo $colorArray[rand(0, count($colorArray) + 1)] ?>"
                                                }
                                                <?php
                                                if ($i <= count($resQry)) {
                                                    echo ",";
                                                    $i++;
                                                }
                                                }
                                                ?> ],
                                            "valueAxes": [{
                                                "axisAlpha": 0,
                                                "position": "left",
                                                "title": "Survey Data"
                                            }],
                                            "startDuration": 1,
                                            "graphs": [{
                                                "balloonText": "<b>[[category]]: [[value]]</b>",
                                                "fillColorsField": "color",
                                                "fillAlphas": 0.9,
                                                "lineAlpha": 0.2,
                                                "type": "column",
                                                "valueField": "visits"
                                            }],
                                            "chartCursor": {
                                                "categoryBalloonEnabled": false,
                                                "cursorAlpha": 0,
                                                "zoomable": false
                                            },
                                            "categoryField": "country",
                                            "categoryAxis": {
                                                "gridPosition": "start",
                                                "labelRotation": 45
                                            },
                                            "export": {
                                                "enabled": true
                                            }
                                        });
                                    </script>
                                    <?php } ?>
                                </div>
                            <?php } else {
                                ?>
                                <div class="card-body d-flex">
                                    <?php 
                                        foreach($showChartData as $index => $resQry) { 
                                    ?>
                                    <div style="width: <?php echo $width; ?>; height: 500px;" id="piechart<?php echo $index; ?>"></div>
                                    <script>
                                        var chart = AmCharts.makeChart("piechart<?php echo $index; ?>", {
                                            "type": "pie",
                                            "theme": "light",
                                            "dataProvider": [<?php
                                                foreach ($resQry as $i => $row) {
                                                ?>
                                                {
                                                    "country": "<?php echo $row->ChoiceLabel;?>",
                                                    "litres":<?php echo $row->ColValTotal;?>
                                                }<?php
                                                if ($i <= count($resQry)) {
                                                    echo ",";
                                                    $i++;
                                                }
                                                }
                                                ?> ],
                                            "valueField": "litres",
                                            "titleField": "country",
                                            "balloon": {
                                                "fixedPosition": true
                                            },
                                            "export": {
                                                "enabled": false
                                            }
                                        });
                                    </script>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                            <br>
                            <?php foreach($showChartData as $index => $resQry) { ?>
                            <section class="card">
                                <div class="card-header">
                                    <?php if($SelectedGender == 'both') { ?>
                                        <div class="card-subtitle d-flex justify-content-between">
                                            <b>Question: <i><?php echo ($index == 0 ? $SelectedMaleColumnLabel : $SelectedFemaleColumnLabel); ?></i></b>
                                        </div>
                                    <?php } else { ?>
                                        <div class="card-subtitle"><b>Question: <i><?php echo "$SelectedColumnLabel"; ?></i></b></div>
                                    <?php } ?>
                                    <div class="card-subtitle"><?php echo $DivisionName . $DistrictName . $UpazilaName . $UnionWardName . $MauzaName . $VillageName; ?></div>
                                    <div class="card-subtitle"></div>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered table-striped data-table"  id="datatable-ajax"
                                           data-url="<?php echo $dataURL; ?>">
                                        <thead>
                                        <tr>
                                            <th>Option Title</th>
                                            <th>Option Value</th>
                                            <th>Total Response</th>
                                            <th>Parcent</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                            <?php } ?>
                            </section>
                        <?php
                    }
                }

                function getChartData($app, $ColumnDataType, $SelectedFormID, $SelectedColumnName, $DivisionCode, $DistrictCode, $UpazilaCode, $UnionWardCode, $MauzaCode, $VillageCode) {

                    if ($ColumnDataType <> "select_multiple") {

                        $qryCreate = "IF object_id('tempdb..##TempTableForViewChart') is not null
                        BEGIN
                            drop table ##TempTableForViewChart;
                        END
                        CREATE TABLE ##TempTableForViewChart(ColName VARCHAR(500), ColVal VARCHAR(500), ColValTotal INT, ColValPercent VARCHAR(500));
                        
                        INSERT ##TempTableForViewChart(ColName, ColVal, ColValTotal, ColValPercent) 
                        select ColumnName, ColumnValue, COUNT(ColumnValue) AS ColumnValue1, 
                               CAST(count(ColumnValue) * 100.0 / sum(count(ColumnValue)) over() AS decimal (10, 2)) as Percentage 
                        FROM masterdatarecord_Approved WHERE FormId = $SelectedFormID AND CONVERT(NVARCHAR(MAX),ColumnName) = '$SelectedColumnName' AND ColumnValue <> ''";

                        if (!empty($DivisionCode)) {
                            $qryCreate .= " AND UserID IN (SELECT PSUUserID FROM PSUList WHERE DivisionCode = $DivisionCode";
                            if (!empty($DistrictCode)) {
                                $qryCreate .= " AND DistrictCode = $DistrictCode";
                            }
                            if (!empty($UpazilaCode)) {
                                $qryCreate .= " AND UpazilaCode = $UpazilaCode";
                            }
                            if (!empty($UnionWardCode)) {
                                $qryCreate .= " AND UnionWardCode = $UnionWardCode";
                            }
                            if (!empty($MauzaCode)) {
                                $qryCreate .= " AND MauzaCode = $MauzaCode";
                            }
                            if (!empty($VillageCode)) {
                                $qryCreate .= " AND VillageCode = $VillageCode";
                            }
                            $qryCreate .= ")";
                        }

                        $qryCreate .= " GROUP BY ColumnName, ColumnValue;";

                        //echo $qryCreate;

                        $app->getDBConnection()->Query($qryCreate);
                    }

                    if ($ColumnDataType === "select_multiple") {
                        $qryCreate2 = "
                        DECLARE @SampleData AS TABLE ( Food varchar(100)) INSERT INTO @SampleData select ColumnValue FROM masterdatarecord_Approved WHERE FormId = $SelectedFormID AND CONVERT(NVARCHAR(MAX),ColumnName) = '$SelectedColumnName' AND ColumnValue <> ''";

                        if (!empty($DivisionCode)) {
                            $qryCreate2 .= " AND UserID IN (SELECT PSUUserID FROM PSUList WHERE DivisionCode = $DivisionCode";
                            if (!empty($DistrictCode)) {
                                $qryCreate2 .= " AND DistrictCode = $DistrictCode";
                            }
                            if (!empty($UpazilaCode)) {
                                $qryCreate2 .= " AND UpazilaCode = $UpazilaCode";
                            }
                            if (!empty($UnionWardCode)) {
                                $qryCreate2 .= " AND UnionWardCode = $UnionWardCode";
                            }
                            if (!empty($MauzaCode)) {
                                $qryCreate2 .= " AND MauzaCode = $MauzaCode";
                            }
                            if (!empty($VillageCode)) {
                                $qryCreate2 .= " AND VillageCode = $VillageCode";
                            }
                            $qryCreate2 .= ")";
                        }

                        $qryCreate2 .= "IF object_id('tempdb..##TempTableForViewChart2') is not null
                        BEGIN
                            drop table ##TempTableForViewChart2;
                        END
                        CREATE TABLE ##TempTableForViewChart2(ColVal VARCHAR(500), ChoiceLabel NVARCHAR(max), ColValTotal INT, ColValPercent VARCHAR(500));
                        
                        INSERT ##TempTableForViewChart2(ColVal, ChoiceLabel, ColValTotal, ColValPercent) 
                        
                        SELECT ca.Value AS ColVal, ci.ChoiceLabel, count(*) AS ColValTotal, CAST(count(*) * 100.0 / sum(count(*)) over() AS decimal (10, 2)) as ColValPercent 
                        FROM @SampleData sd CROSS APPLY ( Select * from [dbo].[SplitString](sd.Food,' ') ) ca JOIN ChoiceInfo ci ON ca.Value = ci.ChoiceValue WHERE ci.FormId = $SelectedFormID AND ci.ChoiceListName = (SELECT ChoiceListName FROM xformcolumnname WHERE FormId = $SelectedFormID AND ColumnName = '$SelectedColumnName') GROUP BY ca.Value, ci.ChoiceLabel ORDER BY ca.Value;";

                        //echo $qryCreate2;
                        $app->getDBConnection()->Query($qryCreate2);

                        $qry = "SELECT * FROM %23%23TempTableForViewChart2 ORDER BY ColVal";
                    } else if ($ColumnDataType === "select_one") {
                        $qry = "SELECT ttvc.ColVal, ci.ChoiceLabel, ttvc.ColValTotal, ttvc.ColValPercent FROM %23%23TempTableForViewChart ttvc
                        JOIN ChoiceInfo ci ON ttvc.ColVal = ci.ChoiceValue
                        WHERE ci.FormId = $SelectedFormID AND ci.ChoiceListName = 
                        (SELECT ChoiceListName FROM xformcolumnname WHERE FormId = $SelectedFormID AND ColumnName = '$SelectedColumnName')";
                    } else {
                        $qry = "SELECT ColVal, '' as ChoiceLabel, ColValTotal, ColValPercent FROM %23%23TempTableForViewChart";
                    }

                    if ($ColumnDataType === "select_multiple") {
                        $qryChart = "SELECT * FROM ##TempTableForViewChart2 ORDER BY ColVal";
                    } else if ($ColumnDataType === "select_one") {
                        $qryChart = "SELECT ci.ChoiceLabel, ttvc.ColValTotal FROM ##TempTableForViewChart ttvc
                        JOIN ChoiceInfo ci ON ttvc.ColVal = ci.ChoiceValue
                        WHERE ci.FormId = $SelectedFormID AND ci.ChoiceListName = 
                        (SELECT ChoiceListName FROM xformcolumnname WHERE FormId = $SelectedFormID AND ColumnName = '$SelectedColumnName')";
                    } else {
                        $qryChart = "SELECT COUNT(ColumnValue) AS ColValTotal, ColumnValue AS ChoiceLabel FROM masterdatarecord_Approved
                        WHERE FormId = $SelectedFormID AND CONVERT(NVARCHAR(MAX),ColumnName) = '$SelectedColumnName' AND ColumnValue <> ''
                        GROUP BY ColumnValue";
                    }
                    $resQry = $app->getDBConnection()->fetchAll($qryChart);

                    return ['qry' => $qry, 'resQry' => $resQry];
                }
                ?>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		$.when( 
			$("#SelectedFormID").val('<?=$SelectedFormID?>').trigger( "change" )
		).done(function() {
			$.when(
				$("#SelectedModuleName").trigger( "change" )
			).done(function() {
				$("#SelectedColumnName").trigger( "change" )
			});
		});
	});
</script>

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