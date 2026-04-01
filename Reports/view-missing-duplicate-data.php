<?php
$qryFormName = "SELECT id, FormName FROM datacollectionform WHERE CompanyID = ? AND Status = '$formActiveStatus' AND id = $formIdMainData ORDER BY id ASC";
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
                                                echo '<option value="' . $row->id . '"'. (!empty($FormID) && $FormID == $row->id ? ' selected' : '') .'>' . $row->FormName . '</option>';
                                            }
                                            ?>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>

                            <!--<div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2"
                                       for="inputedPSU">Insert PSU</label>
                                <div class="col-lg-6">
                                    <input name="inputedPSU" type="number" class="form-control form-control-md" placeholder="like: 801"/>
                                </div>
                            </div>-->

                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-primary" name="show" type="submit" id="show"
                                            value="Show">

                                        <!--<button type="button" class="btn btn-secondary ms-4" id="clearForm">Clear</button>-->
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </section>
                <?php
                if ($_REQUEST['show'] === 'Show') {
                    $FormID = xss_clean($_REQUEST['FormID']);

                    $FormName = getValue('datacollectionform', 'FormName', "id = $FormID");

                    if ($FormID == $formIdSamplingData) {
                        $column = $columnNameToUpdateValueForListingData;
                        $maxValue = $maxNumberOfHHForListing;
                    } else if ($FormID == $formIdMainData) {
                        $column = $columnNameToUpdateValueForMainData;
                        $maxValue = $maxNumberOfHHForSampling;
                    }

                    $dataURL = $baseURL . 'Reports/ajax-data/view_missing_duplicate_data-ajax-data.php?fid=' . $FormID . '&column=' . $column . '&maxValue=' . $maxValue;

                ?>
                    <section class="card">
                        <div class="card-header">
                            <div class="card-title">Survey : <?php echo $FormName; ?></div>

                            <div class="form-group ml-2 row col-lg-1 " style="margin-left: 1px; margin-top:20px;">
                                <button class="btn ml-2 btn-success"
                                    onclick="exportTableToExcel('DataGrowthByPSU', 'DataGrowthReportByPSU')">
                                    Download
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped" id="DataGrowthByPSU" style="table-layout: fixed; width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">SL</th>
                                        <th>Division Name</th>
                                        <th>District Name</th>
                                        <th>User</th>
                                        <th>PSU</th>
                                        <th>Unique</th>
                                        <th>Missing</th>
                                        <th>Duplicate</th>
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
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        
        var dataTable = $('#DataGrowthByPSU').DataTable({
			dom: '<"row"<"col-lg-6"l><"col-lg-6"f>><"table-responsive"t>p',
			bProcessing: true,
			sAjaxSource: "<?php echo $dataURL; ?>",
            "columns": [
                { "data": null },
                { "data": 0 },
                { "data": 1 },
                { "data": 2 },
                { "data": 3 },
                { "data": 4 },
                { "data": 5 },
                { "data": 6 },
                { "data": 7 },
            ],
            columnDefs: [{ orderable: false, targets: 0 }],
            "rowCallback": function(row, data, index) {
                var pageInfo = dataTable.page.info();
                var slNumber = pageInfo.start + index + 1;
                $('td:eq(0)', row).html(slNumber);
            }
		});
    });
</script>

<style>
    #DataGrowthByPSU th:first-child,
    #DataGrowthByPSU td:first-child {
        width: 40px !important;   /* adjust as needed */
        text-align: center;       /* optional for better look */
        white-space: nowrap;      /* prevents wrapping */
    }
</style>
