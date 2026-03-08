<?php
$qryFormName = "SELECT id, FormName FROM datacollectionform WHERE CompanyID = ? ORDER BY id ASC";
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
                                            id="FormID" title="Please select a form" required
                                            onchange="getModuleName(document.getElementById('FormID').value)">
                                        <option value="">Select Form</option>
                                            <?PHP
                                            foreach ($rsQryFormName as $row) {
                                                echo '<option value="' . $row->id . '">' . $row->FormName . '</option>';
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
                                <label class="col-lg-3 control-label text-lg-end pt-2">Date<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <div class="input-daterange input-group">
                                        <input type="date" class="form-control" id="startDate"
                                               name="startDate" required>
                                    </div>
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
                    <script type="text/javascript">
                        function getModuleName(formID, data) {
                            $.ajax({
                                url: "Reports/get-module-name-list.php",
                                method: "GET",
                                datatype: "html",
                                data: {
                                    formID: formID
                                },
                                success: function (response) {
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
                    $FormID = $_REQUEST['FormID'];
                    $StartDate = xss_clean($_REQUEST['startDate']);

                    $DataDate = date("d-m-Y", strtotime($StartDate));

                    $StartDateTime = date("Y-m-d", strtotime($StartDate)) . $defaultStartTimeString;
                    $EndDateTime = date("Y-m-d", strtotime($StartDate)) . $defaultEndTimeString;

                    $FormName = getValue('datacollectionform', 'FormName', "id = $FormID");


                    $dataURL = $baseURL . 'Reports/ajax-data/data-send-value-report-ajax-data.php?frmID=' . $FormID . '&uid=' . $UserID . '&colName=' . $ColumnName;
                    ?>
                    <section class="card">
                        <div class="card-body">
                            <table class="table table-bordered table-striped" id="datatable-ajax"
                                   data-url="<?php echo $dataURL; ?>">
                                <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>User</th>
                                    <th>Mobile No</th>
                                    <th>Record ID</th>
                                    <th>Column Name</th>
                                    <th>Column Value</th>
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

