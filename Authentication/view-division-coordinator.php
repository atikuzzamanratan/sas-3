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
                                <label class="col-lg-3 control-label text-sm-end pt-2">Project Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="SelectedCompanyID" name="SelectedCompanyID"
                                            class="form-control populate" required>
                                        <optgroup label="Select Project">
                                            <?PHP
                                            if ($loggedUserName == 'admin') {
                                                $qryCompany = $app->getDBConnection()->query("SELECT id, CompanyName FROM dataownercompany ORDER BY id DESC");
                                            } else {
                                                $qryCompany = $app->getDBConnection()->query("SELECT id, CompanyName FROM dataownercompany WHERE id = ?", $loggedUserCompanyID);
                                            }
                                            foreach ($qryCompany as $row) {
                                                echo '<option value="' . $row->id . '">' . $row->CompanyName . '</option>';
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
                    $SelectedCompanyID = $_REQUEST['SelectedCompanyID'];

                    $dataURL = $baseURL . "Authentication/ajax-data/div-coordinator-list-ajax-data.php?ci=$SelectedCompanyID&lun=$loggedUserName";

                    ?>
                    <section class="card">
                        <!--<div class="card-title"><?php /*echo $loggedUserCompanyName;*/?></div>
                        <div class="card-subtitle"></div>-->
                        <div class="card-body">
                            <table class="table table-bordered table-striped" id="datatable-ajax"
                                   data-url="<?php echo $dataURL; ?>">
                                <thead>
                                <tr>
                                    <th>SL</th>
									<th>Division Co-ordinator</th>
                                    <th>District Co-ordinator</th>
									<th>Supervisor</th>
									<th>Supervisor Mobile</th>
                                    <th>User</th>
                                    <th>Division Name</th>
									<th>District Name</th>
                                    <?php if (strpos($loggedUserName, 'admin') !== false) { ?>
                                    <th>Action</th>
                                    <?php } ?>
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
    function DeleteItem(id, data) {
        if (confirm("Are you sure to delete this data?")) {
            $.ajax({
                url: "Authentication/delete-div-coordinator.php",
                method: "GET",
                datatype: "json",
                data: {
                    id: id,
                    tbl: 'assignsupervisor'
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
