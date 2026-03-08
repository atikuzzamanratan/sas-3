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
                                            class="form-control populate" required
                                            onchange="getColumnName(document.getElementById('SelectedFormID').value)">
                                        <optgroup label="Choose form">
                                            <?PHP
                                            $qryForm = $app->getDBConnection()->query("SELECT id, FormName FROM datacollectionform WHERE CompanyID = ?", $loggedUserCompanyID);

                                            foreach ($qryForm as $row) {
                                                echo '<option value="' . $row->id . '">' . $row->FormName . '</option>';
                                            }
                                            ?>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Column Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="columnName"
                                            id="columnName" onchange="columnNameF(this.value)"
                                            title="Please select a column" required>
                                        <option value="">Choose a column</option>
                                        <?php
                                        $qry = "SELECT ColumnName, ColumnLabel FROM xformcolumnname WHERE FormId = ?";
                                        $resQry = $app->getDBConnection()->fetchAll($qry, $formIdMainData);
                                        foreach ($resQry as $row) {
                                            echo '<option value="' . $row->ColumnName . '">' . $row->ColumnName . ' (' . $row->ColumnLabel . ')' . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2" for="ColumnList">Selected
                                    Column(s)<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <textarea class="form-control" rows="3" id="ColumnList" name="ColumnList"
                                              data-plugin-textarea-autosize readonly></textarea>
                                    <p><span style="color: red;">Note: </span> please select maximum 5 columns to search
                                    </p>
                                </div>
                            </div>

                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-primary" name="show" type="submit" id="show"
                                               value="Find">
                                        <input class="btn btn-danger" onclick="resetArray();" name="reset" type="button"
                                               id="reset"
                                               value="Reset">
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                    <script type="text/javascript">
                        function getColumnName(formID, data) {
                            $.ajax({
                                url: "../Reports/get-column-name-list.php",
                                method: "GET",
                                datatype: "html",
                                data: {
                                    formID: formID
                                },
                                success: function (response) {
                                    //alert(response);
                                    $('#columnName').html(response);
                                }
                            });
                            return false;
                        }
                    </script>
                    <script>
                        var ColumnNameArray = [];

                        function columnNameF(columnName) {
                            if (ColumnNameArray.length <= 4) {
                                if (ColumnNameArray.indexOf(columnName) > -1) {
                                    alert(columnName + " already exist");
                                } else {
                                    ColumnNameArray.push(columnName);
                                    let ColumnNameList = ColumnNameArray.toString();
                                    $('#ColumnList').val(ColumnNameList);
                                }
                            } else {
                                alert("Sorry! You can't select more then 5 columns.");
                            }
                        }

                        function resetArray() {
                            ColumnNameArray.length = 0;
                            let ColumnNameList = ColumnNameArray.toString();
                            $('#ColumnList').val(ColumnNameList);
                        }
                    </script>
                </section>
                <?php

                if ($_REQUEST['show'] === 'Find') {
                    $SelectedFormID = $_REQUEST['SelectedFormID'];
                    $SelectedCompanyID = getValue('datacollectionform', 'CompanyID', "id = $SelectedFormID");
                    $ColumnList = xss_clean($_REQUEST['ColumnList']);
                    $ColumnList = explode(',', $ColumnList);
                    $SearchParam1 = NULL;
                    $SearchParam2 = NULL;
                    $SearchParam3 = NULL;
                    $SearchParam4 = NULL;
                    $SearchParam5 = NULL;

                    if (!empty($ColumnList[0])) {
                        $SearchParam1 = $ColumnList[0];
                    }
                    if (!empty($ColumnList[1])) {
                        $SearchParam2 = $ColumnList[1];
                    }
                    if (!empty($ColumnList[2])) {
                        $SearchParam3 = $ColumnList[2];
                    }
                    if (!empty($ColumnList[3])) {
                        $SearchParam4 = $ColumnList[3];
                    }
                    if (!empty($ColumnList[4])) {
                        $SearchParam5 = $ColumnList[4];
                    }

                    $dataParams = "selFormID=$SelectedFormID&selCompanyID=$SelectedCompanyID&param1=$SearchParam1&param2=$SearchParam2
                    &param3=$SearchParam3&param4=$SearchParam4&param5=$SearchParam5";

                    $dataURL = $baseURL . "SpecialTask/ajax-data/view-duplicate-data-ajax-data.php?$dataParams";

                    ?>
                    <section class="card">
                        <div class="card-title">Form
                            : <?php echo getValue('datacollectionform', 'FormName', "id = $SelectedFormID"); ?></div>
                        <div class="card-subtitle"></div>
                        <div class="card-body" id="tableWrap">
                            <table class="table table-bordered table-striped" id="datatable-approveddata-ajax"
                                   data-url="<?php echo $dataURL; ?>">
                                <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Record ID</th>
                                    <th>Duplicate Record ID</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    <!-- end: page -->
                    <?php
                }
                ?>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

