<?php
if ($loggedUserName == 'admin') {
    $qryCompnay = "SELECT id, CompanyName FROM dataownercompany";
    $rsQryCompany = $app->getDBConnection()->fetchAll($qryCompnay);
} else {
    $qryCompnay = "SELECT id, CompanyName FROM dataownercompany WHERE id = ?";
    $rsQryCompany = $app->getDBConnection()->fetchAll($qryCompnay, $loggedUserCompanyID);
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
                                <label class="col-lg-3 control-label text-sm-end pt-2">Company<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="company" name="company"
                                            class="form-control populate" title="Please select a company" required>
                                        <optgroup label="Select company">
                                        <?PHP
                                        foreach ($rsQryCompany as $row) {
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
                    $SelectedCompanyID = $_REQUEST['company'];
                    $SelectedCompanyName = getValue('dataownercompany', 'CompanyName', "id = $SelectedCompanyID");

                    $dataURL = $baseURL . "Authentication/ajax-data/psu-list-ajax-data.php?ci=$SelectedCompanyID&lu=$loggedUserID&un=$loggedUserName";

                    ?>
                    <section class="card">
                        <div class="card-header">
                            <div class="card-title">Project : <?php echo $SelectedCompanyName; ?></div>
                            <div class="card-subtitle"></div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped" id="datatable-ajax"
                                   data-url="<?php echo $dataURL; ?>">
                                <thead>
                                <tr>
                                    <th>PSU</th>
                                    <th>Division</th>
                                    <th>District</th>
                                    <th>City Corp.</th>
                                    <th>Upazila</th>
                                    <th>Municipality</th>
                                    <th>Union/Ward</th>
                                    <th>Mauza</th>
                                    <th>Village</th>
                                    <th>User</th>
									<th>Mobile</th>
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
    function EditItem(id, pApprove, pEdit, pDelete, data) {
        if (confirm("Are you sure to update this data?")) {
            $.ajax({
                url: "Authentication/user-to-supervisor-status-edit.php",
                method: "GET",
                datatype: "json",
                data: {
                    id: id,
                    pApprove: pApprove,
                    pEdit: pEdit,
                    pDelete: pDelete
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
                url: "Authentication/user-to-supervisor-status-delete.php",
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
