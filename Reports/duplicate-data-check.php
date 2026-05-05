<?php
$qryFormName = "SELECT id, FormName FROM datacollectionform WHERE CompanyID = ? AND Status = '$formActiveStatus' $formViewOrder";
$rsQryFormName = $app->getDBConnection()->fetchAll($qryFormName, $loggedUserCompanyID);
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
                                            id="FormID" required>
                                        <optgroup label="Select Form">
                                            <?PHP
                                            foreach ($rsQryFormName as $row) {
                                                echo '<option value="' . $row->id . '"' . (isset($FormID) && !empty($FormID) && $FormID == $row->id ? ' selected' : '') . '>' . $row->FormName . '</option>';
                                            }
                                            ?>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>

                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-primary" name="show" type="submit" id="show" value="Show">
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </section>
                <?php
                if ($_REQUEST['show'] === 'Show') {
                    $FormID = xss_clean($_REQUEST['FormID']);

                    if($FormID != $formIdMainData ){
                        echo "No data available!";
                        exit();
                    }

                    $FormName = getValue('datacollectionform', 'FormName', "id = $FormID");

                    $dataURL = $baseURL . "Reports/ajax-data/duplicate-data-report-ajax-data.php?frmID=$FormID&colName=$columnNameToUpdateValueForListingData&targetData=$maxNumberOfHHForSampling";

                    ?>
                    <section class="card">
                        <div class="card-header">
                            <div class="form-group ml-2 row col-lg-1 " style="margin-left: 1px; margin-top:20px;">
                                <button class="btn ml-2 btn-success"
                                        onclick="exportTableToExcel('DataSendCountReport', 'DataSendCountReport')">
                                    Download
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped" id="DataSendCountReport">
                                <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>User Name</th>
                                    <th>Full Name</th>
                                    <th>Mobile No</th>
                                    <th>PSU</th>
                                    <th>Unique Count</th>
                                    <th>Missing Count</th>
                                    <th>Duplicate Count</th>
                                    <th>Collected</th>
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
                <!-- end: page -->
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var dataTable = $('#DataSendCountReport').DataTable({
            dom: '<"row"<"col-lg-6"l><"col-lg-6"f>><"table-responsive"t>p',
            bProcessing: true,
            sAjaxSource: "<?php echo $dataURL; ?>",
            autoWidth: false, // ✅ disable automatic column resizing
            columnDefs: [
                {orderable: false, targets: 0},
                {width: "45px", targets: 0}, // ✅ force fixed width for SL column
                {className: "text-center", targets: 0} // optional: center SL numbers
            ],
            "columns": [
                {"data": 0},
                {"data": 1},
                {"data": 2},
                {"data": 3},
                {"data": 4},
                {"data": 5},
                {"data": 6},
                {"data": 7},
                {"data": 8},
            ],
            "rowCallback": function (row, data, index) {
                var pageInfo = dataTable.page.info();
                var slNumber = pageInfo.start + index + 1;
                $('td:eq(0)', row).html(slNumber);
            }
        });


    });
</script>
