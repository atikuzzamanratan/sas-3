<?php

function getColumnQuery($ColumnName, $ColOption) {
    return " ($ColumnName = ''$ColOption'' OR $ColumnName = ''% $ColOption'' OR $ColumnName = ''$ColOption %'' OR $ColumnName = ''% $ColOption %'')";
}

function getModifiedColOption($DataType, $ColOption) {
    return in_array($DataType, ['integer', 'select_multiple', 'select_one']) ? $ColOption : "''$ColOption''";
}

$qryFormName = "SELECT id, FormName FROM datacollectionform WHERE Status = 'Active' AND CompanyID = ? ORDER BY id ASC";
$rsQryFormName = $app->getDBConnection()->fetchAll($qryFormName, $loggedUserCompanyID);

if (strpos($loggedUserName, 'dist') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = ? AND a.DistCoordinatorID = ?";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID, $loggedUserID);
} else {
    $divQuery = "SELECT DISTINCT DivisionName , DivisionCode FROM PSUList WHERE CompanyID = ? ORDER BY DivisionName ASC";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID);
}

if ((isset($_REQUEST['show']) && $_REQUEST['show'] === 'Show') || (isset($_REQUEST['FormID']) && $_REQUEST['FormID'] !== '')) {

    $FormID = xss_clean($_REQUEST['FormID']);
    $DataStatus = xss_clean($_REQUEST['DataStatus']);

    $ColName1 = xss_clean($_REQUEST['columnName1']);
    $ColOperator1 = xss_clean($_REQUEST['operator1']);
    $ColOption1 = xss_clean($_REQUEST['columnOption1']);

    $ColName2 = xss_clean($_REQUEST['columnName2']);
    $ColOperator2 = xss_clean($_REQUEST['operator2']);
    $ColOption2 = xss_clean($_REQUEST['columnOption2']);

    $ColName3 = xss_clean($_REQUEST['columnName3']);
    $ColOperator3 = xss_clean($_REQUEST['operator3']);
    $ColOption3 = xss_clean($_REQUEST['columnOption3']);

    $ColAndOr1 = xss_clean($_REQUEST['andor1']);
    $ColAndOr2 = xss_clean($_REQUEST['andor2']);

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
                            <div class="form-group row pb-3">
                                <label class="col-lg-2 control-label text-sm-end pt-2">Form Select<span
                                        class="required">*</span></label>
                                <div class="col-lg-8">
                                    <select data-plugin-selectTwo class="form-control populate" name="FormID"
                                        id="FormID" title="Please select a form" required 
                                        onchange="getColumnName(document.getElementById('FormID').value)">
                                        <option value="">Choose a form</option>
                                        <?PHP
                                        foreach ($rsQryFormName as $row) {
                                            echo '<option value="' . $row->id . '"' . (isset($FormID) && $FormID == $row->id ? 'selected' : '') . '>' . $row->FormName . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-2 control-label text-sm-end pt-2">Column<span
                                        class="required">*</span></label>
                                <div class="col-lg-4">
                                    <select data-plugin-selectTwo class="form-control populate" name="columnName1"
                                        id="columnName1" title="Please select a column" required
                                        onchange="getColumnOption1(document.getElementById('FormID').value, document.getElementById('columnName1').value)">
                                        <option value="">Choose a column</option>
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <select data-plugin-selectTwo class="form-control populate" name="operator1"
                                        id="operator1">
                                        <option value="equal" <?php echo (isset($ColOperator1) && $ColOperator1 === 'equal' ? 'selected' : ''); ?>>EQUAL</option>
                                        <option value="not_equal" <?php echo (isset($ColOperator1) && $ColOperator1 === 'not_equal' ? 'selected' : ''); ?>>NOT EQUAL</option>
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <div id="columnOption1Div">
                                        <select data-plugin-selectTwo class="form-control populate" name="columnOption1"
                                            id="columnOption1" title="Please select a column">
                                            <option value="">Choose a column</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-1">
                                    <select data-plugin-selectTwo class="form-control populate" name="andor1"
                                        id="andor1">
                                        <option value="OR" <?php echo (isset($ColAndOr1) && $ColAndOr1 === 'OR' ? 'selected' : ''); ?>>OR</option>
                                        <option value="AND" <?php echo (isset($ColAndOr1) && $ColAndOr1 === 'AND' ? 'selected' : ''); ?>>AND</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-2 control-label text-sm-end pt-2">Column<span
                                        class="required">*</span></label>
                                <div class="col-lg-4">
                                    <select data-plugin-selectTwo class="form-control populate" name="columnName2"
                                        id="columnName2" title="Please select a column" required
                                        onchange="getColumnOption2(document.getElementById('FormID').value, document.getElementById('columnName2').value)">
                                        <option value="">Choose a column</option>
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <select data-plugin-selectTwo class="form-control populate" name="operator2"
                                        id="operator2">
                                        <option value="equal" <?php echo (isset($ColOperator2) && $ColOperator2 === 'equal' ? 'selected' : ''); ?>>EQUAL</option>
                                        <option value="not_equal" <?php echo (isset($ColOperator2) && $ColOperator2 === 'not_equal' ? 'selected' : ''); ?>>NOT EQUAL</option>
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <div id="columnOption2Div">
                                        <select data-plugin-selectTwo class="form-control populate" name="columnOption2"
                                            id="columnOption2" title="Please select a column">
                                            <option value="">Choose a column</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-1">
                                    <select data-plugin-selectTwo class="form-control populate" name="andor2"
                                        id="andor2">
                                        <option value="OR" <?php echo (isset($ColAndOr2) && $ColAndOr2 === 'OR' ? 'selected' : ''); ?>>OR</option>
                                        <option value="AND" <?php echo (isset($ColAndOr2) && $ColAndOr2 === 'AND' ? 'selected' : ''); ?>>AND</option>

                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-2 control-label text-sm-end pt-2">Column</label>
                                <div class="col-lg-4">
                                    <select data-plugin-selectTwo class="form-control populate" name="columnName3"
                                        id="columnName3" title="Please select a column"
                                        onchange="getColumnOption3(document.getElementById('FormID').value, document.getElementById('columnName3').value)">
                                        <option value="">Choose a column</option>
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <select data-plugin-selectTwo class="form-control populate" name="operator3"
                                        id="operator3">
                                        <option value="equal" <?php echo (isset($ColOperator3) && $ColOperator3 === 'equal' ? 'selected' : ''); ?>>EQUAL</option>
                                        <option value="not_equal" <?php echo (isset($ColOperator3) && $ColOperator3 === 'not_equal' ? 'selected' : ''); ?>>NOT EQUAL</option>
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <div id="columnOption3Div">
                                        <select data-plugin-selectTwo class="form-control populate" name="columnOption3"
                                            id="columnOption3" title="Please select a column">
                                            <option value="">Choose a column</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Data Status Select<span
                                        class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="DataStatus"
                                        id="DataStatus" required>
                                        <optgroup label="Select Data Status">
                                            <option value="1" <?php echo (isset($DataStatus) && $DataStatus === '1' ? 'selected' : ''); ?>>Approved</option>
                                            <option value="0" <?php echo (isset($DataStatus) && $DataStatus === '0' ? 'selected' : ''); ?>>Pending</option>
                                            <option value="2" <?php echo (isset($DataStatus) && $DataStatus === '2' ? 'selected' : ''); ?>>Un-approved</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Division Select
                                    <span class="required">*</span>
                                </label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="DivisionCode"
                                        id="DivisionCode" required
                                        onchange="ShowDropDown('DivisionCode', 'DistrictDiv', 'ShowDistrict', 'ShowUpazila')">
                                        <option value="">Choose division</option>
                                        <?PHP
                                        foreach ($rsDivQuery as $row) {
                                            echo '<option value="' . $row->DivisionCode . '"' . (isset($DivisionCode) && $DivisionCode === $row->DivisionCode ? 'selected' : '') . '>' . $row->DivisionName . '</option>';
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
                    $FormName = getValue('datacollectionform', 'FormName', "id = $FormID");

                    //First Column Param
                    $Column1Record = $app->getDBConnection()->fetch("SELECT * FROM xformcolumnname WHERE FormId = $FormID AND ColumnName = '$ColName1'");
                    $ColNameLabel1 = strip_tags($Column1Record->ColumnLabel);
                    $Column1DataType = $Column1Record->DataType;
                    
                    $ColOperator1 = $ColOperator1 === "equal" ? '=' : '<>';

                    $QryToFilterColumnValue = " WHERE (";

                    if ($ColName1 != '' && $ColOperator1 != '' && $ColOption1 != '') {
                        $ModifiedColOption1 = getModifiedColOption($Column1DataType, $ColOption1);
                        $QryToFilterColumnValue .= ($Column1DataType == 'select_multiple' ? getColumnQuery('Column1', $ModifiedColOption1) : "Column1 $ColOperator1 $ModifiedColOption1");
                    }

                    //Second Column Param
                    $Column2Record = $app->getDBConnection()->fetch("SELECT ColumnLabel, DataType FROM xformcolumnname WHERE FormId = $FormID AND ColumnName = '$ColName2'");
                    $ColNameLabel2 = strip_tags($Column2Record->ColumnLabel);
                    $Column2DataType = $Column2Record->DataType;

                    $ColOperator2 = $ColOperator2 === "equal" ? '=' : '<>';

                    if ($ColAndOr1 != '' && $ColName2 != '' && $ColOperator2 != '' && $ColOption2 != '') {
                        $ModifiedColOption2 = getModifiedColOption($Column2DataType, $ColOption2);
                        $QryToFilterColumnValue .= (" $ColAndOr1". ($Column2DataType == 'select_multiple' ? getColumnQuery('Column2', $ModifiedColOption2) : " Column2 $ColOperator2 $ModifiedColOption2"));
                    }

                    //Third Column Param
                    if ($ColName1 === $ColName3 || $ColName2 === $ColName3) {
                        $ColName3 = '';
                    }

                    if ($ColName1 === $ColName2) {
                        $ColName2 = '';
                    }

                    if (empty($ColName3)) {
                        $ColNameLabel3 = 'Indicator';
                    } else {
                        $Column3Record = $app->getDBConnection()->fetch("SELECT ColumnLabel, DataType FROM xformcolumnname WHERE FormId = $FormID AND ColumnName = '$ColName3'");
                        $ColNameLabel3 = strip_tags($Column3Record->ColumnLabel);
                        $Column3DataType = $Column3Record->DataType;
                    }

                    $ColOperator3 = $ColOperator3 === "equal" ? '=' : '<>';

                    if ($ColAndOr2 != '' && $ColName3 != '' && $ColOperator3 != '' && $ColOption3 != '') {
                        $ModifiedColOption3 = getModifiedColOption($Column3DataType, $ColOption3);
                        $QryToFilterColumnValue .= (" $ColAndOr2". ($Column3DataType == 'select_multiple' ? getColumnQuery('Column3', $ModifiedColOption3) : " Column3 $ColOperator3 $ModifiedColOption3"));
                    }

                    $QryToFilterColumnValue .= ")";

                    //Geolocation filter
                    if (!empty($DivisionCode)) {
                        $DivisionName = getValue('PSUList', 'DivisionName', "DivisionCode = $DivisionCode");
                    }

                    if (!empty($DistrictCode)) {
                        $DistrictName = getValue('PSUList', 'DISTINCT(DistrictName)', "DivisionCode = $DivisionCode AND DistrictCode = $DistrictCode");
                        $DistrictName = ' > ' . $DistrictName;
                    }

                    if (!empty($UpazilaCode)) {
                        $UpazilaName = getValue(
                            'PSUList',
                            'DISTINCT(UpazilaName)',
                            "DivisionCode = $DivisionCode AND DistrictCode = $DistrictCode AND UpazilaCode = $UpazilaCode"
                        );
                        $UpazilaName = ' > ' . $UpazilaName;
                    }

                    if (!empty($UnionWardCode)) {
                        $UnionWardName = getValue(
                            'PSUList',
                            'DISTINCT(UnionWardName)',
                            "DivisionCode = $DivisionCode AND DistrictCode = $DistrictCode AND UpazilaCode = $UpazilaCode AND UnionWardCode = $UnionWardCode"
                        );
                        $UnionWardName = ' > ' . $UnionWardName;
                    }

                    if (!empty($MauzaCode)) {
                        $MauzaName = getValue(
                            'PSUList',
                            'DISTINCT(MauzaName)',
                            "DivisionCode = $DivisionCode AND DistrictCode = $DistrictCode AND UpazilaCode = $UpazilaCode AND UnionWardCode = $UnionWardCode AND MauzaCode = $MauzaCode"
                        );
                        $MauzaName = ' > ' . $MauzaName;
                    }

                    if (!empty($VillageCode)) {
                        $VillageName = getValue(
                            'PSUList',
                            'DISTINCT(VillageName)',
                            "DivisionCode = $DivisionCode AND DistrictCode = $DistrictCode AND UpazilaCode = $UpazilaCode AND UnionWardCode = $UnionWardCode AND MauzaCode = $MauzaCode AND VillageCode = $VillageCode"
                        );
                        $VillageName = ' > ' . $VillageName;
                    }

                    if (!empty($DivisionCode)) {
                        $qryCreate = " AND UserID IN (SELECT PSUUserID FROM PSUList WHERE DivisionCode = $DivisionCode";
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

                    $QryToFilterColumnValue .= $qryCreate;

                    $CreateTempTable = "EXEC spReport_MasterDataReport_Pivot_For_DataValidation $FormID, $DataStatus, $DivisionCode, '$ColName1', '$ColName2', '$ColName3', '$QryToFilterColumnValue'";

                    $dataURL = $baseURL . "ViewData/ajax-data/data-validation-ajax-data.php?lui=$loggedUserID&lci=$loggedUserCompanyID&frmID=$FormID&dataStatus=$DataStatus&colName1=$ColName1&colName2=$ColName2&colName3=$ColName3&sql=$CreateTempTable";

                ?>
                    <section class="card">
                        <div class="card-header">
                            <div class="card-title">Data Validation : <?php echo $FormName; ?></div>
                            <div class="card-subtitle"><?php echo $DivisionName . $DistrictName . $UpazilaName . $UnionWardName . $MauzaName . $VillageName; ?></div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped" id="datatable-ajax"
                                data-url="<?php echo $dataURL; ?>">
                                <thead>
                                    <tr>
                                        <th>Record ID</th>
                                        <th>User</th>
                                        <th>Mobile No</th>
                                        <th><?php echo $ColNameLabel1 ?></th>
                                        <th><?php echo $ColNameLabel2 ?></th>
                                        <th><?php echo $ColNameLabel3 ?></th>
                                        <th>Actions</th>
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

<script type="text/javascript">
	$(document).ready(function() {
        
        if($('#FormID').val() != ''){
            $("#FormID").trigger( "change" );
        }
	});

</script>

<script type="text/javascript">
    function getColumnName(formID) {
        $.ajax({
            url: "ViewData/get-column-name-list.php",
            method: "GET",
            datatype: "html",
            data: {
                formID: formID
            },
            success: function(response) {
                $('#columnName1').html(response);
                $('#columnName1').val('<?=$ColName1?>').trigger("change");

                $('#columnName2').html(response);
                $('#columnName2').val('<?=$ColName2?>').trigger("change");

                $('#columnName3').html(response);
                $('#columnName3').val('<?=$ColName3?>').trigger("change");
            }
        });
        return false;
    }
</script>

<script type="text/javascript">
    function getColumnOption1(formID, colName) {

        let colOption = '<?=$ColOption1?>';

        $.ajax({
            url: "ViewData/get-column-option-list.php",
            method: "GET",
            datatype: "html",
            data: {
                formID: formID,
                colName: colName,
                colOptionID: 'columnOption1',
                colOption: colOption
            },
            success: function(response) {
                $('#columnOption1Div').html(response);
                runSelect2();
            }
        });
        return false;
    }
</script>

<script type="text/javascript">
    function getColumnOption2(formID, colName) {
        let colOption = '<?=$ColOption2?>';

        $.ajax({
            url: "ViewData/get-column-option-list.php",
            method: "GET",
            datatype: "html",
            data: {
                formID: formID,
                colName: colName,
                colOptionID: 'columnOption2',
                colOption: colOption
            },
            success: function(response) {
                $('#columnOption2Div').html(response);
                runSelect2();
            }
        });
        return false;
    }
</script>

<script type="text/javascript">
    function getColumnOption3(formID, colName) {
        let colOption = '<?=$ColOption3?>';
                
        $.ajax({
            url: "ViewData/get-column-option-list.php",
            method: "GET",
            datatype: "html",
            data: {
                formID: formID,
                colName: colName,
                colOptionID: 'columnOption3',
                colOption: colOption
            },
            success: function(response) {
                $('#columnOption3Div').html(response);
                runSelect2();
            }
        });
        return false;
    }
</script>

<script type="text/javascript">
    function SendNotification(senderID, toID, message, companyID, data) {
        if (confirm("Are you sure to send this message?")) {
            $.ajax({
                url: "ViewData/send-notification.php",
                method: "GET",
                datatype: "json",
                data: {
                    senderID: senderID,
                    toID: toID,
                    message: message,
                    companyID: companyID
                },
                success: function(response) {
                    alert(response);
                    window.location.reload();
                }
            });
        }
        return false;
    }
</script>

<script src="../js/populateDropdowns.js"></script>
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

    $(document).on('change', 'input[data-type="decimal"]', function() {
        this.value = Number.isInteger(Number(this.value)) ? parseFloat(this.value).toFixed(1) : this.value;
    });

    $(document).on('change', 'input[data-type="integer"]', function() {
        this.value = parseInt(this.value);
    });
</script>





<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {
    const $table = $('#datatable-ajax');
    const dataUrl = $table.data('url');

    // ✅ Disable DataTables built-in alert popups globally
    $.fn.dataTable.ext.errMode = 'none';

    // ✅ Destroy any existing instance before initializing
    if ($.fn.DataTable.isDataTable($table)) {
        $table.DataTable().clear().destroy(true);
        $table.find('tbody').empty();
    }

    // ✅ Initialize DataTable
    const table = $table.DataTable({
        processing: true,
        serverSide: false,
        destroy: true,
        ajax: {
            url: dataUrl,
            dataSrc: function (json) {
                if (json.customError) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Notice',
                        text: json.customError,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                    return [];
                }
                return json.aaData;
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Unable to load data. Please review your selection or try again.'
                });
            }
        },
        language: {
            emptyTable: "No data available for the selected filters."
        }
    });

    // ✅ Silently suppress all DataTables internal warnings
    $table.on('error.dt', function (e, settings, techNote, message) {
        console.warn('Suppressed DataTables warning:', message);
        e.preventDefault();
        return false;
    });
});
</script>




