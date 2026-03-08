<?php
if ($loggedUserName == 'admin') {
    $qryFormGroup = "SELECT id, FormGroupName FROM datacollectionformgroup";
    $rsQryFormGroup = $app->getDBConnection()->fetchAll($qryFormGroup);
} else {
    $qryFormGroup = "SELECT id, FormGroupName FROM datacollectionformgroup WHERE Status = ? AND CompanyID = ?";
    $rsQryFormGroup = $app->getDBConnection()->fetchAll($qryFormGroup, 'Active', $loggedUserCompanyID);
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
                                <label class="col-lg-3 control-label text-lg-end pt-2">Form Group Select</label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="FormGroupID"
                                            id="FormGroupID">
                                        <optgroup label="Select Form Group">
                                            <?PHP
                                            foreach ($rsQryFormGroup as $row) {
                                                echo '<option value="' . $row->id . '">' . $row->FormGroupName . '</option>';
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
                    $FormGroupID = $_REQUEST['FormGroupID'];
                    $check = $_REQUEST['chkAll'];

                    if ($check == "chkAll") {
                        $dataURL = $baseURL . 'Form/ajax-data/form-group-ajax-data.php?par=1';
                    } else {
                        $dataURL = $baseURL . 'Form/ajax-data/form-group-ajax-data.php?par=0&fgi=' . $FormGroupID;
                    }

                    ?>
                    <section class="card">
                        <div class="card-body">
                            <table class="table table-bordered table-striped" id="datatable-ajax"
                                   data-url="<?php echo $dataURL; ?>">
                                <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Form Group Name</th>
                                    <th>Description</th>
                                    <th>Project</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                    <!--<th>Delete</th>-->
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
    function EditItem(id, name, desc, status, data) {
        if (confirm("Are you sure to update this data?")) {
            $.ajax({
                url: "Form/form-group-edit.php",
                method: "GET",
                datatype: "json",
                data: {
                    id: id,
                    name: name,
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
    function DeleteItem(id, data) {
        if (confirm("Are you sure to delete this data?")) {
            $.ajax({
                url: "Form/form-group-delete.php",
                method: "GET",
                datatype: "json",
                data: {
                    id: id
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
