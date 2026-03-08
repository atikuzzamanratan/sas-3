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
                                        $qryForm = $app->getDBConnection()->query("SELECT id, FormName FROM datacollectionform WHERE CompanyID = ?", $loggedUserCompanyID);

                                        // Preserve selected value after form submit
                                        $SelectedFormID = isset($_REQUEST['SelectedFormID']) ? $_REQUEST['SelectedFormID'] : '';

                                        foreach ($qryForm as $row) {
                                            $selected = ($SelectedFormID == $row->id) ? 'selected' : '';
                                            echo '<option value="' . $row->id . '" ' . $selected . '>' . $row->FormName . '</option>';
                                        }
                                        ?>
                                    </optgroup>
                                </select>
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
                </section>
                <?php

                if ($_REQUEST['show'] === 'Show') {
                    $SelectedFormID = $_REQUEST['SelectedFormID'];

                    if ($SelectedFormID == $formIdMainData) {
                        $formType = 'Main';
                    } elseif ($SelectedFormID == $formIdSamplingData) {
                        $formType = 'Listing';
                    }

                    $dataURL = $baseURL . "Authentication/ajax-data/assigned-user-to-psu-ajax-data.php?ci=$loggedUserCompanyID&fid=$SelectedFormID&lui=$loggedUserID";

                    ?>
                    <section class="card">
                        <div class="card-header">
                            <div class="card-title">Survey Type : <?php echo $formType; ?></div>
                            <div class="card-subtitle"></div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped" id="datatable-ajax"
                                   data-url="<?php echo $dataURL; ?>">
                                <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>User</th>
                                    <th>PSU</th>
                                    <th>Target</th>
                                    <th>Collected</th>
                                    <th>Remaining</th>
                                    <th>Progress</th>
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

<script type="text/javascript">
    function EditItem(id, fType, pTarget, data) {
        if (confirm("Are you sure to update this data?")) {
            $.ajax({
                url: "Authentication/assign-user-to-psu-edit.php",
                method: "GET",
                datatype: "json",
                data: {
                    id: id,
                    fType: fType,
                    pTarget: pTarget
                },
                success: function (response) {
                    alert(response);
                    window.location.reload();
                }
            });
        }
        return false;
    }
</script>

<script type="text/javascript">
    function DeleteItem(id, data) {
        if (confirm("Are you sure to delete this data?")) {
            $.ajax({
                url: "Authentication/user-to-psu-delete.php",
                method: "GET",
                datatype: "json",
                data: {
                    id: id,
                    tbl: 'PSUList'
                },
                success: function (response) {
                    alert(response);
                    window.location.reload();
                }
            });
        }
        return false;
    }
</script>
