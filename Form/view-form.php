<?php
if ($loggedUserName == 'admin') {
    $qryFormName = "SELECT id, FormName FROM datacollectionform ORDER BY id DESC";
    $rsQryFormName = $app->getDBConnection()->fetchAll($qryFormName);
} else {
    $qryFormName = "SELECT id, FormName FROM datacollectionform WHERE Status = ? AND CompanyID = ? ORDER BY id DESC";
    $rsQryFormName = $app->getDBConnection()->fetchAll($qryFormName, 'Active', $loggedUserCompanyID);
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
                                <label class="col-lg-3 control-label text-lg-end pt-2">Form Select</label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="FormID"
                                            id="FormID">
                                        <optgroup label="Select Form">
                                            <?PHP
                                            foreach ($rsQryFormName as $row) {
                                                echo '<option value="' . $row->id . '">' . $row->FormName . '</option>';
                                            }
                                            ?>
                                        </optgroup>
                                    </select><br/>
                                    <?php if ($loggedUserName == 'admin') { ?>
                                        <div class="col-sm-9">
                                            <div class="checkbox-custom checkbox-warning">
                                                <input id="chkAll" value="chkAll" type="checkbox" name="chkAll"/>
                                                <label for="chkAll">All</label>
                                            </div>
                                        </div>
                                    <?php } ?>
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
                    $FormID = $_REQUEST['FormID'];
                    $check = $_REQUEST['chkAll'];

                    if ($check == "chkAll") {
                        $dataURL = $baseURL . 'Form/ajax-data/form-name-ajax-data.php?par=1';
                    } else {
                        $dataURL = $baseURL . 'Form/ajax-data/form-name-ajax-data.php?par=0&fi=' . $FormID;
                    }

                    ?>
                    <section class="card">
                        <div class="card-body">
                            <table class="table table-bordered table-striped" id="datatable-ajax"
                                   data-url="<?php echo $dataURL; ?>">
                                <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Form Name</th>
                                    <th>Description</th>
                                    <th>Company</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
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
    function EditItem(id, desc, status, data) {
        if (confirm("Are you sure to update this data?")) {
            $.ajax({
                url: "Form/form-name-edit.php",
                method: "GET",
                datatype: "json",
                data: {
                    id: id,
                    desc: desc,
                    status: status
                },
                success: function (response) {
                    alert(response);
                    //window.location.replace("<?php //echo get_base_url() . 'index.php?parent=FormGroupView';?>");
                    window.location.reload();
                    //location.replace(location.href);
                }
            });
        }
        return false;
    }
</script>

<script type="text/javascript">
    function DeleteItem(id, filePath, data) {
        if (confirm("Are you sure to delete this form?")) {
            $.ajax({
                url: "Form/form-name-delete.php",
                method: "GET",
                datatype: "json",
                data: {
                    id: id,
                    filePath: filePath
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
