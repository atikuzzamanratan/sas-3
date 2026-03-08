<?php
$qryFormName = "SELECT id, FormName FROM datacollectionform WHERE Status = 'Active' AND CompanyID = ? ORDER BY id ASC";
$rsQryFormName = $app->getDBConnection()->fetchAll($qryFormName, $loggedUserCompanyID);
$qrySupervisor = "SELECT id FROM assignsupervisor WHERE SupervisorID = ?";
$rsSupervisor = $app->getDBConnection()->fetch($qrySupervisor, $loggedUserID);
$SuperID = $rsSupervisor->id;

if (strpos($loggedUserName, 'cs') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = ? AND a.SupervisorID = ? ORDER BY DivisionName ASC";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID, $loggedUserID);
} elseif (strpos($loggedUserName, 'val') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = ? AND a.ValidatorID = ? ORDER BY DivisionName ASC";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID, $loggedUserID);
} else {
    $divQuery = "SELECT DISTINCT DivisionName , DivisionCode FROM PSUList WHERE CompanyID = ? ORDER BY DivisionName ASC";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID);
}

if (strpos($loggedUserName, 'cval') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = ? ORDER BY DivisionName ASC";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID);
}

if ($_REQUEST['show'] === 'Show') {

    $FormID = xss_clean($_REQUEST['FormID']);

    $DivisionCode = xss_clean($_REQUEST['DivisionCode']);
    $DistrictCode = xss_clean($_REQUEST['DistrictCode']);
    $UpazilaCode = xss_clean($_REQUEST['UpazilaCode']);
    $UnionWardCode = xss_clean($_REQUEST['UnionWardCode']);
    $MauzaCode = xss_clean($_REQUEST['MauzaCode']);
    $VillageCode = xss_clean($_REQUEST['VillageCode']);

    $SelectedStartDate = xss_clean($_REQUEST['startDate']);
    $SelectedEndDate = xss_clean($_REQUEST['endDate']);

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
                                            onchange="getColumnName(document.getElementById('FormID').value)">
                                        <optgroup label="Choose a Form">
                                            <?PHP
                                            foreach ($rsQryFormName as $row) {
                                                echo '<option value="' . $row->id . '"'.(isset($FormID) && !empty($FormID) && $row->id == $FormID ? ' selected' : '').'>' . $row->FormName . '</option>';
                                            }
                                            ?>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
							<?php
								if (strpos($user, 'val') === false) {
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
							<div id="geoDiv" style="display: none">
                                <div class="form-group row pb-3" id="DistrictDiv"></div>
                                <div class="form-group row pb-3" id="UpazilaDiv"></div>
                                <div class="form-group row pb-3" id="UnionWardDiv"></div>
                                <div class="form-group row pb-3" id="MauzaDiv"></div>
                                <div class="form-group row pb-3" id="VillageDiv"></div>
                            </div>
							<?php
								}
							?>
							<?php
								if (strpos($loggedUserName, 'cs') !== false) {
							?>
                            <div class="form-group row pb-3" id="userDiv">
                                <label class="col-lg-3 control-label text-lg-end pt-2">Validator Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="SelectedUserID"
                                            id="SelectedUserID" required title="Please select validator">
                                        <option value="">Choose a Validator</option>
                                        <?PHP
                                        $qryDistUser = $app->getDBConnection()->query("SELECT u.id, u.UserName, u.FullName FROM assignsupervisor AS a JOIN userinfo as u ON a.ValidatorID = u.id WHERE u.IsActive = 1 AND a.SupervisorID = ?", $loggedUserID);

                                        foreach ($qryDistUser as $row) {
                                            echo '<option value="' . $row->id . '">' . $row->UserName . ' | ' . substr($row->FullName, 0, 102) . '</option>';
                                        }
                                        ?>

                                    </select>
                                </div>
                            </div>
							<?php
									}
							?>
							<div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2">Date range</label>
                                <div class="col-lg-6">
                                    <div class="input-daterange input-group">
                                        <input type="date" class="form-control" id="startDate"
                                            name="startDate" value="<?php echo isset($SelectedStartDate) ? $SelectedStartDate : ''; ?>">
                                        <span class="input-group-text border-start-0 border-end-0 rounded-0">to</span>
                                        <input type="date" class="form-control" id="endDate" name="endDate" value="<?php echo isset($SelectedEndDate) ? $SelectedEndDate : ''; ?>">
                                    </div>
                                </div>
                            </div>

							<?php
								if (strpos($loggedUserName, 'cs') !== false) {
							?>
							<div class="form-group row pb-3">
								<label class="col-lg-3 control-label text-sm-end pt-2"></label>
								<div class="col-lg-6">
									<div class="checkbox-custom checkbox-warning">
										<input id="chkAll" value="chkAll" type="checkbox" name="chkAll" />
										<label for="chkAll">All users</label>
									</div>
								</div>
							</div>
							<?php
									}
							?>

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
                    
					$SelectedFormID = $_REQUEST['FormID'];
                    $SelectedUserID = $_REQUEST['SelectedUserID'];

					$checkAll = $_REQUEST['chkAll'];
					if ($checkAll == 'chkAll') {
                        $SelectedCheckAll = 1;
                    } else {
                        $SelectedCheckAll = 0;
                    }

					if (!empty($_REQUEST['startDate'])) {
                        $SelectedStartDate = date('Y-m-d', strtotime($_REQUEST['startDate'])) . $defaultStartTimeString;
                    }
                    if (!empty($_REQUEST['endDate'])) {
                        $SelectedEndDate = date('Y-m-d', strtotime($_REQUEST['endDate'])) . $defaultEndTimeString;
                    }

					if (strpos($loggedUserName, 'cs') !== false) {
						if (empty($SelectedUserID) && empty($checkAll)) {
							MsgBox('Please select All Validators or a specific Validator.');
							ReloadPage();
						}
					}
				?>
                    <input type="hidden" id="DistrictCodeSelected" value="<?php echo $DistrictCode; ?>">
					<input type="hidden" id="UpazilaCodeSelected" value="<?php echo $UpazilaCode; ?>">
					<input type="hidden" id="UnionWardCodeSelected" value="<?php echo $UnionWardCode; ?>">
					<input type="hidden" id="MauzaCodeSelected" value="<?php echo $MauzaCode; ?>">
					<input type="hidden" id="VillageCodeSelected" value="<?php echo $VillageCode; ?>">
					
					<input type="hidden" id="DataFromID" value="<?php echo $SelectedFormID; ?>">
					<input type="hidden" id="DataUserID" value="<?php echo $SelectedUserID; ?>">
					<input type="hidden" id="DataChkAll" value="<?php echo $SelectedCheckAll; ?>">
					<input type="hidden" id="LoggedUserName" value="<?php echo $loggedUserName; ?>">
					<input type="hidden" id="LoggedUserID" value="<?php echo $loggedUserID; ?>">
					<input type="hidden" id="DataStartDate"
						value="<?php if (!empty($SelectedStartDate)) echo $SelectedStartDate; ?>">
					<input type="hidden" id="DataEndDate"
						value="<?php if (!empty($SelectedEndDate)) echo $SelectedEndDate; ?>">

					<br>
                        
					<section class="card">
						<div class="card-header">
							<div class="card-subtitle"></div>
							<div class="card-subtitle"></div>
							<div class="card-subtitle"></div>
							<div class="card-title"><?php echo getValue('datacollectionform', 'FormName', "id = $SelectedFormID"); ?></div>
							<div class="card-subtitle"></div>
						</div>
						<div class="card-body">
							<table class="table table-bordered table-striped" id="tblViewData">
								<thead>
									<tr>
										<th>Validator Name</th>
										<th>Check Date</th>
										<th>No of Checked Data</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
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

<script type="text/javascript">
    $(document).ready(function() {
        var body = $('body');
		
		var DivisionCode = body.find('#DivisionCode').find(":selected").val();
		
		var DistrictCode = body.find('#DistrictCodeSelected').val(),
			UpazilaCode = body.find('#UpazilaCodeSelected').val(),
			UnionWardCode = body.find('#UnionWardCodeSelected').val(),
			MauzaCode = body.find('#MauzaCodeSelected').val(),
			VillageCode = body.find('#VillageCodeSelected').val();
		
		var	DataFromID = body.find('#DataFromID').val(),
			DataUserID = body.find('#DataUserID').val(),
			DataChkAll = body.find('#DataChkAll').val(),
			LoggedUserName = body.find('#LoggedUserName').val(),
			LoggedUserID = body.find('#LoggedUserID').val(),
			DataStartDate = body.find('#DataStartDate').val(),
			DataEndDate = body.find('#DataEndDate').val();
		
        var dataTable = $('#tblViewData').DataTable({
            "aLengthMenu": [
                [20, 10, 50, 100, 500, 1000, 5000, 100000000],
                [20, 10, 50, 100, 500, 1000, 5000, "All"]
            ],
            "processing": true,
            "serverSide": true,
            "ajax": {
                data: {
                    DataFromID: DataFromID,
                    DataUserID: DataUserID,
                    DataChkAll: DataChkAll,
                    LoggedUserName: LoggedUserName,
                    LoggedUserID: LoggedUserID,
                    DataStartDate: DataStartDate,
                    DataEndDate: DataEndDate,
					DivisionCode: DivisionCode,
					DistrictCode: DistrictCode,
					UpazilaCode: UpazilaCode,
					UnionWardCode: UnionWardCode,
					MauzaCode: MauzaCode,
					VillageCode: VillageCode
                },
                url: "<?php echo $dataURL = $baseURL . "Reports/ajax-data/view-check-data-ajax-data.php"; ?>",
                type: "POST"
            }
        });
    });
</script>